{{-- HW-54 · 500 (Figma: Layar/Kartu galat). Tanpa detail teknis (NFR-35). Fixture boleh menimpa $judul/$deskripsi. --}}
@extends('layouts.auth')
@section('judul', $judul ?? 'Terjadi Kesalahan pada Sistem')
@section('konten')
    <x-pv.kartu-galat kode="500" :judul="$judul ?? 'Terjadi Kesalahan pada Sistem'" :deskripsi="$deskripsi ?? ('Kami sedang memeriksanya. Coba lagi beberapa saat lagi.')"
        tombol-utama="Muat Ulang" :href-utama="url()->current()" />
@endsection
