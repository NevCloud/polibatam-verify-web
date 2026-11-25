{{-- HW-51 · 410 (Figma: Layar/Kartu galat). Tanpa detail teknis (NFR-35). Fixture boleh menimpa $judul/$deskripsi. --}}
@extends('layouts.auth')
@section('judul', $judul ?? 'Tautan Kedaluwarsa')
@section('konten')
    {{-- STUB-FE(P11): rute Data Pendaftaran --}}
    <x-pv.kartu-galat kode="410" :judul="$judul ?? 'Tautan Kedaluwarsa'" :deskripsi="$deskripsi ?? (isset($masaBerlakuJam) ? 'Tautan unduhan ekspor hanya berlaku '.$masaBerlakuJam.' jam. Silakan ekspor ulang data.' : 'Tautan unduhan ekspor sudah tidak berlaku. Silakan ekspor ulang data.')"
        tombol-utama="Buka Data Pendaftaran" :href-utama="url('/')" />
@endsection
