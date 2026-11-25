{{-- HW-55 · 503 (Figma: Layar/Kartu galat). Tanpa detail teknis (NFR-35). Fixture boleh menimpa $judul/$deskripsi. --}}
@extends('layouts.auth')
@section('judul', $judul ?? 'Sedang Dalam Pemeliharaan')
@section('konten')
    <x-pv.kartu-galat kode="503" :judul="$judul ?? 'Sedang Dalam Pemeliharaan'" :deskripsi="$deskripsi ?? ('Polibatam Verify sedang dalam pemeliharaan terjadwal. Silakan kembali beberapa saat lagi.')"
        tombol-utama="Muat Ulang" :href-utama="url()->current()" :tombol-kedua="false" />
@endsection
