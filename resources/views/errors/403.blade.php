{{-- HW-49 · 403 (Figma: Layar/Kartu galat). Tanpa detail teknis (NFR-35). Fixture boleh menimpa $judul/$deskripsi. --}}
@extends('layouts.auth')
@section('judul', $judul ?? 'Akses Ditolak')
@section('konten')
    <x-pv.kartu-galat kode="403" :judul="$judul ?? 'Akses Ditolak'" :deskripsi="$deskripsi ?? ('Anda tidak memiliki izin untuk membuka halaman ini.')"
        tombol-utama="Kembali ke Dashboard" :href-utama="auth()->check() ? App\Support\Http\ArahPortal::untuk(auth()->user()) : url('/')" />
@endsection
