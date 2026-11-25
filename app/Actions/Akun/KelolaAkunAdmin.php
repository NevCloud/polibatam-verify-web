<?php

namespace App\Actions\Akun;

use App\Models\User;
use App\Notifications\Akun\UndanganAktivasi;
use App\Services\AuditLogger;
use App\Services\CabutAkses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * FR-06, BR-04, BR-05, BR-58, BR-60 (US-27): Super Admin membuat akun admin + undangan aktivasi,
 * mengubah role, menonaktifkan/mengaktifkan kembali. Hanya akun admin; akun peserta lahir dari registrasi (FR-01)
 * dan role-nya tidak diubah manual (BR-06).
 */
class KelolaAkunAdmin
{
    public const PESAN_SUPER_ADMIN_TERAKHIR = 'Super Admin terakhir tidak dapat dinonaktifkan atau diturunkan perannya.';

    public function __construct(private AuditLogger $audit, private CabutAkses $cabut) {}

    /** @param  array<string, mixed>  $input */
    public function buat(array $input): User
    {
        $data = Validator::make($input, [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users,email'],
            'role' => ['required', Rule::in(User::ROLE_ADMIN)],
        ])->validate();

        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => mb_strtolower($data['email']),
                'password' => Str::password(40), // tidak pernah diketahui siapa pun; diganti saat aktivasi (HW-11)
                'is_active' => true,
            ]);
            $user->assignRole($data['role']);
            $this->kirimUndangan($user);
            $this->audit->catat('buat_akun_admin', 'user', $user->id,
                sesudah: ['name' => $user->name, 'email' => $user->email, 'role' => $data['role']]);

            return $user;
        });
    }

    public function kirimUlangUndangan(User $user): void
    {
        if ($user->two_factor_confirmed_at || ! $user->is_active) {
            throw ValidationException::withMessages(['akun' => 'Undangan hanya untuk akun aktif yang belum menyelesaikan aktivasi.']);
        }

        DB::transaction(function () use ($user) {
            $this->kirimUndangan($user);
            $this->audit->catat('kirim_ulang_undangan', 'user', $user->id);
        });
    }

    public function ubahRole(User $user, string $role): void
    {
        Validator::make(['role' => $role], ['role' => ['required', Rule::in(User::ROLE_ADMIN)]])->validate();
        $lama = $user->namaRole();
        if ($lama === $role) {
            return;
        }

        $this->lindungiSuperAdminTerakhir($user, $lama === 'super_admin', 'ubah_role', function () use ($user, $lama, $role) {
            $user->syncRoles([$role]);
            $this->cabut->tokenMobile($user); // BR-60, AC-27.6
            $this->audit->catat('ubah_role', 'user', $user->id, sebelum: ['role' => $lama], sesudah: ['role' => $role]);
        });
    }

    public function ubahStatus(User $user, bool $aktif, string $alasan): void
    {
        Validator::make(['alasan' => $alasan], ['alasan' => ['required', 'string', 'max:500']])->validate();
        if ($user->is_active === $aktif) {
            return;
        }

        $aksi = $aktif ? 'aktifkan_akun' : 'nonaktifkan_akun';
        $this->lindungiSuperAdminTerakhir($user, ! $aktif && $user->hasRole('super_admin'), $aksi, function () use ($user, $aktif, $alasan, $aksi) {
            $user->forceFill(['is_active' => $aktif])->save();
            if (! $aktif) {
                $this->cabut->semua($user); // NFR-03, AC-27.2, AC-27.6
            }
            $this->audit->catat($aksi, 'user', $user->id,
                sebelum: ['is_active' => ! $aktif], sesudah: ['is_active' => $aktif], alasan: $alasan);
        });
    }

    private function kirimUndangan(User $user): void
    {
        $token = Password::broker('aktivasi')->createToken($user);
        $user->notify(new UndanganAktivasi($token, User::LABEL_ROLE[$user->namaRole()] ?? 'admin'));
    }

    /**
     * BR-05, AC-27.4: perubahan yang mengurangi jumlah Super Admin aktif dijalankan dalam transaksi yang mengunci
     * baris Super Admin aktif lain (AE-11), sehingga dua Super Admin tidak bisa saling menonaktifkan bersamaan.
     */
    private function lindungiSuperAdminTerakhir(User $user, bool $mengurangi, string $aksi, callable $ubah): void
    {
        $ditolak = DB::transaction(function () use ($user, $mengurangi, $ubah) {
            if ($mengurangi) {
                $lain = User::role('super_admin')->where('is_active', true)->whereKeyNot($user->id)->lockForUpdate()->pluck('id');
                if ($lain->isEmpty()) {
                    return true;
                }
            }
            $ubah();

            return false;
        });

        if ($ditolak) {
            $this->audit->catat($aksi, 'user', $user->id, 'ditolak', alasan: 'super_admin_terakhir');
            throw ValidationException::withMessages(['akun' => self::PESAN_SUPER_ADMIN_TERAKHIR]);
        }
    }
}
