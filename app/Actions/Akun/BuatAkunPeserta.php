<?php

namespace App\Actions\Akun;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/** FR-01, BR-01, NFR-11: registrasi Calon Peserta (role `peserta`), persetujuan privasi dicatat bersama waktunya. */
class BuatAkunPeserta implements CreatesNewUsers
{
    use AturanPassword;

    /** @param  array<string, mixed>  $input */
    public function create(array $input): User
    {
        $data = Validator::make($input, [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users,email'],
            'password' => $this->aturanPassword(),
            'persetujuan_privasi' => ['accepted'],
        ])->validate();

        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => mb_strtolower($data['email']),
                'password' => $data['password'],
                'is_active' => true,
                'privacy_consented_at' => now(),
            ]);
            $user->assignRole('peserta');

            return $user;
        });
    }
}
