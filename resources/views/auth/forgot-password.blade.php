{{-- HW-09 · Lupa password (FR-05, K-32). Figma 1562:9079 (tautan terkirim) & 1562:9122 (email tidak terdaftar). --}}
@extends('layouts.auth')
@section('judul', 'Lupa Password')
@section('konten')
    <div class="flex w-full max-w-[520px] flex-col gap-9">
        <x-pv.kartu-auth eyebrow="PEMULIHAN AKUN" judul="Lupa kata sandi?"
            deskripsi="Tidak perlu khawatir. Masukkan email terdaftar Anda untuk menerima tautan reset kata sandi.">
            <x-slot:ikon><x-pv.ikon nama="key-round" :ukuran="28" /></x-slot:ikon>

            <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6" novalidate>
                @csrf
                <x-pv.field label="Email Terdaftar" name="email" type="email" ikon="mail" :value="old('email')" :galat="false"
                    bantuan="Gunakan email yang Anda gunakan saat membuat akun." autocomplete="email" required autofocus />
                <x-pv.button type="submit" class="w-full">Kirim Tautan Reset</x-pv.button>
            </form>

            <div class="flex items-start gap-2.5 rounded-menu bg-interactive p-3.5 text-caption leading-[1.7] text-muted">
                <x-pv.ikon nama="mail" class="text-link" />
                <p>Buka tautan pada email, lalu buat kata sandi baru untuk mengakses kembali akun Anda.</p>
            </div>

            <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 text-compact font-bold text-link hover:underline">
                <x-pv.ikon nama="arrow-left" :ukuran="16" />Kembali ke Login
            </a>
        </x-pv.kartu-auth>

        @if ($status ?? session('status'))
            <x-pv.alert nada="success" judul="Tautan reset berhasil dikirim">Periksa kotak masuk atau folder spam pada email terdaftar Anda.</x-pv.alert>
        @elseif ($errors->has('email'))
            <x-pv.alert nada="error" :judul="$errors->first('email')">
                @if ($errors->first('email') === __('passwords.user'))
                    Periksa kembali penulisan email Anda, atau buat akun baru jika belum terdaftar.
                @endif
            </x-pv.alert>
        @endif
    </div>
@endsection
