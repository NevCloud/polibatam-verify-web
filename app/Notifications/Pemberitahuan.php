<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Satu pemberitahuan perubahan status (FR-53, BR-56), dikirim per kanal oleh App\Services\Notifier:
 * `database` (in-app, langsung) atau `mail` (antrean, 3 percobaan, NFR-26).
 */
class Pemberitahuan extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> detik jeda antarpercobaan */
    public array $backoff = [30, 90];

    public function __construct(
        public readonly string $kanal,
        public readonly string $jenis,
        public readonly string $judul,
        public readonly string $isi,
        public readonly ?string $url = null,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return [$this->kanal];
    }

    /** @return array<string, string|null> */
    public function toArray(object $notifiable): array
    {
        return ['jenis' => $this->jenis, 'judul' => $this->judul, 'isi' => $this->isi, 'url' => $this->url];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pesan = (new MailMessage)->subject($this->judul.' — Polibatam Verify')->greeting('Halo,')->line($this->isi);

        return $this->url ? $pesan->action('Buka Polibatam Verify', $this->url) : $pesan;
    }

    /** NFR-26: kegagalan email setelah semua percobaan dicatat; notifikasi in-app sudah tersimpan terpisah. */
    public function failed(Throwable $e): void
    {
        Log::warning('Email notifikasi gagal terkirim setelah 3 percobaan.', [
            'jenis' => $this->jenis,
            'judul' => $this->judul,
            'galat' => $e->getMessage(),
        ]);
    }
}
