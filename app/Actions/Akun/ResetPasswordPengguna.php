<?php

namespace App\Actions\Akun;

use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CabutAkses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

/** FR-05, BR-03: password baru dari tautan reset; semua sesi & token lama dicabut (HW-10). */
class ResetPasswordPengguna implements ResetsUserPasswords
{
    use AturanPassword;

    /** @param  array<string, mixed>  $input */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, ['password' => $this->aturanPassword()])->validate();

        DB::transaction(function () use ($user, $input) {
            $user->forceFill(['password' => $input['password']])->save();
            app(CabutAkses::class)->semua($user);
            app(AuditLogger::class)->catat('reset_password', 'user', $user->id, pelaku: $user);
        });
    }
}
