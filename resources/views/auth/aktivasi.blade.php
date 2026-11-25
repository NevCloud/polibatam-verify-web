{{-- HW-11 · Aktivasi akun admin (FR-06, FR-59, AC-27.5). Figma 3048:1485 (kartu 640, PV/Step).
     $langkah 1 = atur password ($token, $email, $kedaluwarsa) · 2 = pindai QR ($qr SVG, $kunciManual) · 3 = recovery code ($kode). --}}
@extends('layouts.auth')
@section('judul', 'Aktivasi Akun Admin')
@section('konten')
    <section class="flex w-full max-w-[640px] flex-col gap-[18px] rounded-card border border-line bg-surface p-6 sm:p-9">
        <p class="text-caption font-semibold tracking-[0.72px] text-primary uppercase">Aktivasi akun admin</p>
        <h1 class="text-heading font-bold text-primary">Aktifkan akun Anda</h1>

        <ol class="flex flex-wrap items-center justify-between gap-4">
            @foreach ([1 => 'Atur password', 2 => 'Pindai QR', 3 => 'Recovery code'] as $nomor => $label)
                <li><x-pv.step :nomor="$nomor" :label="$label" :state="$nomor < $langkah ? 'selesai' : ($nomor === $langkah ? 'aktif' : 'belum')" /></li>
            @endforeach
        </ol>

        @if ($langkah === 1)
            @if ($kedaluwarsa)
                <x-pv.alert nada="error" judul="Tautan aktivasi tidak valid atau sudah kedaluwarsa">
                    Tautan berlaku 72 jam dan hanya dapat dipakai sekali. Minta Super Admin mengirim ulang undangan.
                </x-pv.alert>
            @else
                <p class="text-body-sm text-muted">Atur password untuk {{ $email }}. Minimal 8 karakter, mengandung huruf dan angka.</p>
                <form method="POST" action="{{ route('aktivasi.store') }}" class="flex flex-col gap-5" novalidate>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">
                    <x-pv.field label="Password baru" name="password" type="password" autocomplete="new-password" required autofocus />
                    <x-pv.field label="Konfirmasi password" name="password_confirmation" type="password" autocomplete="new-password" required />
                    <x-pv.button type="submit" class="self-start">Simpan & lanjut</x-pv.button>
                </form>
            @endif
        @elseif ($langkah === 2)
            <div class="flex flex-col gap-6 sm:flex-row">
                <div class="flex w-full flex-col items-center justify-center gap-3 rounded-control bg-document p-4 sm:w-[272px]">
                    <div class="rounded-control bg-surface p-2 [&_svg]:size-44" role="img" aria-label="QR code TOTP">{!! $qr !!}</div>
                    <p class="text-center text-meta text-muted">Tidak bisa memindai? Masukkan kunci:<br><code class="font-semibold break-all text-ink">{{ $kunciManual }}</code></p>
                </div>
                <form method="POST" action="{{ route('aktivasi.autentikator.store') }}" class="flex flex-1 flex-col gap-6" novalidate>
                    @csrf
                    <p class="text-body-sm text-muted">Pindai QR dengan aplikasi autentikator, lalu masukkan kode 6 digit untuk konfirmasi. 2FA wajib untuk semua admin.</p>
                    <x-pv.field label="Kode konfirmasi" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="7" required autofocus />
                    <x-pv.button type="submit" class="self-start">Konfirmasi & lanjut</x-pv.button>
                </form>
            </div>
        @else
            <x-pv.alert nada="warning" judul="Simpan recovery code sekarang">
                Setiap kode dipakai sekali untuk masuk bila perangkat autentikator hilang. Kode dapat dilihat ulang atau dibuat ulang di halaman Profil.
            </x-pv.alert>
            <ul class="grid grid-cols-2 gap-2 rounded-control bg-canvas p-4 font-mono text-body-sm text-ink">
                @foreach ($kode as $k)<li>{{ $k }}</li>@endforeach
            </ul>
            <form method="POST" action="{{ route('aktivasi.selesai') }}">
                @csrf
                <x-pv.button type="submit">Saya sudah menyimpan, lanjut ke Dashboard</x-pv.button>
            </form>
        @endif
    </section>
@endsection
