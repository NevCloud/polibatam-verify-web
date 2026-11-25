<?php

namespace App\Queries;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\Ui\Format;
use App\Support\Ui\PortalContext;
use Illuminate\Pagination\LengthAwarePaginator;

/** Data HW-44: daftar akun admin + filter. Struktur kunci = fixture resources/preview/hw-44.php. */
class AkunAdminQuery
{
    public const PER_HALAMAN = 10;

    public const STATUS = ['aktif' => 'Aktif', 'menunggu_aktivasi' => 'Menunggu Aktivasi', 'nonaktif' => 'Nonaktif'];

    /**
     * @param  array<string, mixed>  $filter
     * @return array<string, mixed>
     */
    public function halaman(array $filter, User $pelaku): array
    {
        $filter = [
            'q' => trim((string) ($filter['q'] ?? '')),
            'role' => in_array($filter['role'] ?? '', User::ROLE_ADMIN, true) ? (string) $filter['role'] : '',
            'status' => array_key_exists((string) ($filter['status'] ?? ''), self::STATUS) ? (string) $filter['status'] : '',
        ];

        /** @var LengthAwarePaginator<int, User> $halaman */
        $halaman = User::role(User::ROLE_ADMIN)->with('roles')
            ->addSelect(['login_terakhir' => AuditLog::select('created_at')->whereColumn('user_id', 'users.id')
                ->where('aksi', 'login')->where('hasil', 'berhasil')->latest('id')->limit(1)])
            ->when($filter['q'] !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%'.$filter['q'].'%')->orWhere('email', 'like', '%'.$filter['q'].'%')))
            ->when($filter['role'] !== '', fn ($q) => $q->role($filter['role']))
            ->when($filter['status'] === 'aktif', fn ($q) => $q->where('is_active', true)->whereNotNull('two_factor_confirmed_at'))
            ->when($filter['status'] === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->when($filter['status'] === 'menunggu_aktivasi', fn ($q) => $q->where('is_active', true)->whereNull('two_factor_confirmed_at'))
            ->orderBy('name')
            ->paginate(self::PER_HALAMAN)->withQueryString();

        return [
            'filter' => $filter,
            'akun' => collect($halaman->items())->map(fn (User $u) => [
                'id' => $u->id,
                'nama' => $u->name,
                'inisial' => PortalContext::inisial($u->name),
                'email' => $u->email,
                'role' => $u->namaRole(),
                'peran' => User::LABEL_ROLE[$u->namaRole()] ?? '—',
                'status' => ! $u->is_active ? 'Nonaktif' : ($u->two_factor_confirmed_at ? 'Aktif' : 'Menunggu Aktivasi'),
                'login_terakhir' => $u->getAttribute('login_terakhir') ? Format::tanggalJam($u->getAttribute('login_terakhir')) : '—',
                'saya' => $u->id === $pelaku->id,
            ])->all(),
            'total' => $halaman->total(),
            'rentang' => $halaman->total() ? $halaman->firstItem().'–'.$halaman->lastItem() : '0',
            'tautan' => $halaman->linkCollection()->all(),
            'sebelumnya' => $halaman->previousPageUrl(),
            'berikutnya' => $halaman->nextPageUrl(),
            'peranTersedia' => array_intersect_key(User::LABEL_ROLE, array_flip(User::ROLE_ADMIN)),
            'statusTersedia' => self::STATUS,
        ];
    }
}
