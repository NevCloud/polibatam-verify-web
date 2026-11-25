{{-- HW-10 · Reset password (FR-05, BR-03). Figma 3048:1471. Tautan 60 menit, sekali pakai; status kedaluwarsa = galat token. --}}
@extends('layouts.auth')
@section('judul', 'Reset Password')
@section('konten')
    <x-pv.kartu-auth judul="Buat password baru"
        deskripsi="Tautan berlaku 60 menit dan hanya dapat dipakai sekali. Setelah disimpan, semua sesi dan token lama dicabut.">
        @if ($errors->has('email'))
            <x-pv.alert nada="error" :judul="$errors->first('email')">
                <a href="{{ route('password.request') }}" class="font-semibold text-link hover:underline">Minta tautan baru →</a>
            </x-pv.alert>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-5" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <x-pv.field label="Password baru" name="password" type="password" ikon="kunci" autocomplete="new-password"
                bantuan="Minimal 8 karakter, mengandung huruf dan angka." required autofocus />
            <x-pv.field label="Konfirmasi password" name="password_confirmation" type="password" ikon="kunci" autocomplete="new-password" required />
            <x-pv.button type="submit" class="self-start">Simpan password</x-pv.button>
        </form>
    </x-pv.kartu-auth>
@endsection
