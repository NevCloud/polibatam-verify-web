{{-- HW-08 · Verifikasi 2FA admin (FR-59, BR-59). Figma 3010:1192. Kode TOTP atau recovery code (Alpine: ganti mode). --}}
@extends('layouts.auth')
@section('judul', 'Verifikasi 2FA')
@section('konten')
    <div class="flex w-full max-w-[1144px] items-start justify-center gap-16">
        <x-pv.branding-auth judul="Satu akun,|lanjutkan langkah Anda."
            deskripsi="Akses pendaftaran, lengkapi berkas, dan pantau proses verifikasi melalui Polibatam Verify." />

        <div class="flex w-full min-w-0 max-w-[520px] flex-col items-center gap-5" x-data="{ cadangan: {{ $errors->has('recovery_code') ? 'true' : 'false' }} }">
            <x-pv.kartu-auth eyebrow="VERIFIKASI DUA LANGKAH" judul="Masukkan kode autentikator"
                :deskripsi="'Akun admin wajib 2FA. Buka aplikasi autentikator dan masukkan kode untuk '.$email.'.'">
                <form method="POST" action="{{ route('two-factor.login.store') }}" class="flex flex-col gap-6" novalidate>
                    @csrf
                    <div x-show="!cadangan">
                        <x-pv.field label="Kode TOTP (6 digit)" name="code" ikon="kunci" inputmode="numeric" autocomplete="one-time-code"
                            maxlength="7" x-bind:disabled="cadangan" autofocus />
                    </div>
                    <div x-show="cadangan" x-cloak>
                        <x-pv.field label="Recovery code" name="recovery_code" ikon="key-round" autocomplete="off" x-bind:disabled="!cadangan"
                            bantuan="Setiap recovery code hanya dapat dipakai sekali." />
                    </div>
                    <button type="button" class="self-start text-caption font-bold text-link hover:underline" x-on:click="cadangan = !cadangan"
                            x-text="cadangan ? 'Gunakan kode autentikator' : 'Gunakan recovery code'">Gunakan recovery code</button>
                    <x-pv.button type="submit" class="w-full">Verifikasi</x-pv.button>
                </form>
                <p class="text-center text-compact text-muted">Bukan Anda? <a href="{{ route('login') }}" class="font-extrabold text-link hover:underline">Kembali ke login</a></p>
            </x-pv.kartu-auth>

            <p class="flex items-center gap-2 text-meta text-muted"><x-pv.ikon nama="shield-check" :ukuran="16" />Kode berlaku 30 detik. Jangan bagikan kode kepada siapa pun.</p>
        </div>
    </div>
@endsection
