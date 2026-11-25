{{-- HW-07 · Login (satu untuk semua role, K-39). Figma 1562:9165 (+ state 3048:5024 kredensial salah, 3048:5153 terkunci).
     FR-03 pesan gagal umum · FR-04 info akun terkunci. "Ingat Saya" sengaja tidak ada (NFR-03, A-77). --}}
@extends('layouts.auth')
@section('judul', 'Masuk')
@section('konten')
    <div class="flex w-full max-w-[1144px] items-start justify-center gap-16">
        <x-pv.branding-auth judul="Satu akun,|lanjutkan langkah Anda."
            deskripsi="Akses pendaftaran, lengkapi berkas, dan pantau proses verifikasi melalui Polibatam Verify." />

        <div class="flex w-full min-w-0 max-w-[520px] flex-col items-center gap-5">
            <x-pv.kartu-auth eyebrow="SELAMAT DATANG KEMBALI" judul="Masuk ke akun Anda"
                deskripsi="Masuk untuk memantau status pendaftaran dan melanjutkan proses Anda.">
                <x-pv.pilihan-auth aktif="masuk" />

                @if ($errors->any())
                    <x-pv.alert nada="error" :judul="$errors->first()" />
                @elseif ($status ?? session('status'))
                    <x-pv.alert nada="info" :judul="$status ?? session('status')" />
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6" novalidate>
                    @csrf
                    <div class="flex flex-col gap-5">
                        <x-pv.field label="Email" name="email" type="email" ikon="mail" :value="old('email')" :galat="false"
                            autocomplete="username" required autofocus />
                        <x-pv.field label="Kata Sandi" name="password" type="password" ikon="kunci" :galat="false"
                            autocomplete="current-password" required />
                    </div>
                    <div class="flex justify-end">
                        <a href="{{ route('password.request') }}" class="text-caption font-bold text-link hover:underline">Lupa Password?</a>
                    </div>
                    <x-pv.button type="submit" class="w-full">Masuk Ke Sistem</x-pv.button>
                </form>

                <p class="text-center text-compact text-muted">Belum punya akun? <a href="{{ route('register') }}" class="font-extrabold text-link hover:underline">Daftar akun</a></p>
            </x-pv.kartu-auth>

            <p class="flex items-center gap-2 text-meta text-muted"><x-pv.ikon nama="shield-check" :ukuran="16" />Jangan bagikan kata sandi Anda kepada siapa pun.</p>
        </div>
    </div>
@endsection
