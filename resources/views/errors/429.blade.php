{{-- HW-53 · 429 (Figma: Layar/Kartu galat). Tanpa detail teknis (NFR-35). Fixture boleh menimpa $judul/$deskripsi. --}}
@extends('layouts.auth')
@section('judul', $judul ?? 'Terlalu Banyak Permintaan')
@section('konten')
    <x-pv.kartu-galat kode="429" :judul="$judul ?? 'Terlalu Banyak Permintaan'" :deskripsi="$deskripsi ?? ('Anda mengirim terlalu banyak permintaan. Tunggu sebentar sebelum mencoba lagi.')"
        tombol-utama="Coba Lagi" :href-utama="url()->current()" />
@endsection
