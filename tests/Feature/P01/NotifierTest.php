<?php

use App\Models\User;
use App\Notifications\Pemberitahuan;
use App\Services\Notifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('FR-53/NFR-26: in-app tersimpan langsung, email lewat antrean dengan 3 percobaan', function () {
    Notification::fake();
    $user = User::factory()->create();

    app(Notifier::class)->kirim($user, 'status_pendaftaran', 'Formulir diajukan', 'Nomor pendaftaran PV-2026-000426.', '/dasbor');

    Notification::assertSentTo($user, Pemberitahuan::class, fn (Pemberitahuan $n, array $kanal) => $kanal === ['database'] && $n->judul === 'Formulir diajukan');
    Notification::assertSentTo($user, Pemberitahuan::class, fn (Pemberitahuan $n, array $kanal) => $kanal === ['mail'] && $n->tries === 3);
    expect(new Pemberitahuan('mail', 'x', 'x', 'x'))->toBeInstanceOf(ShouldQueue::class);
});

it('NFR-26: notifikasi in-app benar-benar tersimpan di tabel notifications', function () {
    $user = User::factory()->create();

    app(Notifier::class)->kirim($user, 'status_pendaftaran', 'Masuk Kuota', 'Silakan lunasi tagihan formulir.');

    expect($user->notifications()->sole()->data)->toMatchArray(['judul' => 'Masuk Kuota', 'jenis' => 'status_pendaftaran']);
});

it('NFR-26: kegagalan email dicatat di log', function () {
    Log::spy();

    (new Pemberitahuan('mail', 'status', 'Judul', 'Isi'))->failed(new RuntimeException('SMTP mati'));

    Log::shouldHaveReceived('warning')->once()->withArgs(fn ($pesan, $konteks) => str_contains($pesan, 'gagal') && $konteks['galat'] === 'SMTP mati');
});
