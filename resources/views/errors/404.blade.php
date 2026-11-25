{{-- HW-50 · 404 (Figma: Layar/Kartu galat). Tanpa detail teknis (NFR-35). Fixture boleh menimpa $judul/$deskripsi. --}}
@extends('layouts.auth')
@section('judul', $judul ?? 'Halaman Tidak Ditemukan')
@section('konten')
    <x-pv.kartu-galat kode="404" :judul="$judul ?? 'Halaman Tidak Ditemukan'" :deskripsi="$deskripsi ?? ('Alamat yang Anda tuju tidak tersedia atau sudah dipindahkan.')"
        tombol-utama="Kembali ke Dashboard" :href-utama="auth()->check() ? App\Support\Http\ArahPortal::untuk(auth()->user()) : url('/')" />
@endsection
