<?php

namespace App\Notifications\Akun;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-02: tautan verifikasi email setelah registrasi (berlaku sesuai auth.verification.expire). */
class VerifikasiEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifikasi email — Polibatam Verify')
            ->greeting('Halo,')
            ->line('Klik tombol di bawah untuk memverifikasi email Anda. Formulir pendaftaran dapat diisi setelah email terverifikasi.')
            ->action('Verifikasi Email', $url)
            ->line('Abaikan email ini jika Anda tidak membuat akun di Polibatam Verify.');
    }
}
