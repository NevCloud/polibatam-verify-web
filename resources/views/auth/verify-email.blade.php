{{-- HW-06 · Verifikasi email (FR-02, BR-02, AC-02.4). Figma 3048:1460. Kirim ulang tautan dibatasi (throttle Fortify 6/menit). --}}
@extends('layouts.auth')
@section('judul', 'Verifikasi Email')
@section('konten')
    <x-pv.kartu-auth judul="Cek email Anda"
        :deskripsi="'Kami mengirim tautan verifikasi ke '.($email ?? auth()->user()?->email ?? 'email Anda').'. Formulir pendaftaran dapat diisi setelah email terverifikasi.'">
        @if (($status ?? session('status')) === 'verification-link-sent')
            <x-pv.alert nada="success" judul="Tautan baru telah dikirim">Periksa kotak masuk atau folder spam Anda.</x-pv.alert>
        @else
            <x-pv.alert nada="warning" judul="Tautan kedaluwarsa?">Minta tautan baru. Permintaan dibatasi untuk keamanan.</x-pv.alert>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-pv.button type="submit">Kirim ulang tautan</x-pv.button>
            </form>
            {{-- "Ganti email" (Figma): keluar lalu daftar ulang dengan email yang benar; ubah email di akun belum tersedia (A-79). --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-pv.button type="submit" variant="link">Ganti email</x-pv.button>
            </form>
        </div>
    </x-pv.kartu-auth>
@endsection
