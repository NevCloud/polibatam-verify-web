{{-- Dasbor sementara agar login bisa diuji per peran (P02). Diganti HW-13 (P03) dan HW-21 (P12). --}}
@extends('layouts.portal', ['portal' => App\Support\Ui\PortalContext::dariPengguna(auth()->user())])
@section('judul', 'Dashboard')
@section('konten')
    <x-pv.page-header judul="Dashboard" :deskripsi="'Halo, '.auth()->user()->name.'.'" />
    <x-pv.empty-state class="rounded-card border border-line bg-surface p-12" judul="Dashboard belum tersedia"
        :deskripsi="$halaman.' dibangun di paket '.$paket.'. Gunakan menu Profil untuk mengelola akun.'" />
@endsection
