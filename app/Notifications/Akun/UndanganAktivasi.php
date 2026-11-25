<?php

namespace App\Notifications\Akun;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** FR-06, AC-27.1: undangan aktivasi akun admin (HW-11), 72 jam sekali pakai (A-76). */
class UndanganAktivasi extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly string $token, public readonly string $peran) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('aktivasi.create', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]);

        return (new MailMessage)
            ->subject('Undangan aktivasi akun admin — Polibatam Verify')
            ->greeting('Halo,')
            ->line("Super Admin membuatkan akun {$this->peran} untuk Anda di Polibatam Verify.")
            ->line('Aktivasi akun: atur password, lalu daftarkan aplikasi autentikator (2FA wajib untuk semua admin).')
            ->action('Aktifkan Akun', $url)
            ->line('Tautan berlaku '.intdiv((int) config('auth.passwords.aktivasi.expire'), 60).' jam dan hanya dapat dipakai sekali.');
    }
}
