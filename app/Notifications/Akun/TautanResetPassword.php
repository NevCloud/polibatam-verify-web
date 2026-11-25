<?php

namespace App\Notifications\Akun;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-05, BR-03: tautan reset berlaku 60 menit dan sekali pakai. */
class TautanResetPassword extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset password — Polibatam Verify')
            ->greeting('Halo,')
            ->line('Kami menerima permintaan reset password untuk akun Anda.')
            ->action('Buat Password Baru', $url)
            ->line('Tautan berlaku '.config('auth.passwords.users.expire').' menit dan hanya dapat dipakai sekali.')
            ->line('Abaikan email ini jika Anda tidak meminta reset password.');
    }
}
