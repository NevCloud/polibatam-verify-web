{{-- HW-52 · 419 (Figma: Layar/Kartu galat). Tanpa detail teknis (NFR-35). Fixture boleh menimpa $judul/$deskripsi. --}}
@extends('layouts.auth')
@section('judul', $judul ?? 'Sesi Berakhir')
@section('konten')
    <x-pv.kartu-galat kode="419" :judul="$judul ?? 'Sesi Berakhir'" :deskripsi="$deskripsi ?? ('Sesi Anda berakhir karena tidak aktif. Silakan masuk kembali.')"
        tombol-utama="Masuk Kembali" :href-utama="route('login')" />
@endsection
