{{-- HW-04 · Kebijakan privasi (NFR-11, UU No. 27/2022 PDP). Figma 3048:1442. Teks statis (bukan bagian CMS FR-10). --}}
@extends('layouts.publik')
@section('judul', 'Kebijakan Privasi')
@section('konten')
    <div class="mx-auto max-w-[1200px] px-5 py-12">
        <h1 class="text-display font-bold text-ink">Kebijakan privasi</h1>
        <p class="mt-4 text-caption text-muted">Berlaku sejak 1 Oktober 2026 · mengacu UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi</p>

        <div class="mt-8 flex flex-col gap-6">
            @foreach ([
                ['1. Data yang dikumpulkan', 'Nama lengkap, NIK, tempat & tanggal lahir, jenis kelamin, nomor HP, alamat, asal sekolah, tahun lulus, berkas persyaratan, bukti pembayaran, dan data rekening refund.'],
                ['2. Penggunaan & perlindungan', 'Data hanya dipakai untuk pendaftaran, verifikasi, tagihan, dan refund. NIK dan nomor rekening disimpan terenkripsi dan ditampilkan tersamar.'],
                ['3. Hak Anda', 'Anda dapat meminta salinan atau koreksi data melalui kontak bantuan. Persetujuan dicatat beserta waktunya saat registrasi.'],
            ] as [$judul, $isi])
                <section class="rounded-card border border-line bg-surface p-5">
                    <h2 class="text-subheading font-bold text-ink">{{ $judul }}</h2>
                    <p class="mt-3 text-body-sm text-muted">{{ $isi }}</p>
                </section>
            @endforeach
        </div>
    </div>
@endsection
