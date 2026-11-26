{{-- HW-02 · Katalog peminatan (FR-08, FR-09, BR-27, BR-75, AC-01.1–01.5). Figma 3048:1196; baris "Sisa kuota" & label
     "Kuota Penuh – Daftar Tunggu" tidak dibuat (K-42, T-29). Data: Publik\PublikController::katalog (kunci = fixture hw-02). --}}
@extends('layouts.publik')
@section('judul', 'Katalog Peminatan')
@section('konten')
    <div class="mx-auto max-w-[1200px] px-5 py-12">
        <p class="text-caption font-semibold uppercase tracking-[0.6px] text-primary">Katalog</p>
        <h1 class="mt-3 text-display font-bold text-ink">Peminatan Microcredential{{ $periode['nama'] ? ' · '.$periode['nama'] : '' }}</h1>

        @unless ($periode['buka'])
            <x-pv.alert nada="warning" judul="Pendaftaran sedang ditutup" class="mt-5">{{ $periode['teks'] }}</x-pv.alert>
        @endunless

        <nav class="mt-6 flex gap-1 overflow-x-auto rounded-card bg-interactive p-1" aria-label="Filter track">
            @foreach (['' => 'Semua track'] + $trackTersedia as $kode => $nama)
                <a href="{{ route('katalog', $kode === '' ? [] : ['track' => $kode]) }}" @if ($track === (string) $kode) aria-current="page" @endif
                   @class(['shrink-0 rounded-control px-5 py-2.5 text-body-sm whitespace-nowrap', 'bg-surface font-semibold text-ink shadow-sm' => $track === (string) $kode, 'text-muted hover:text-ink' => $track !== (string) $kode])>{{ $nama }}</a>
            @endforeach
        </nav>

        <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($peminatan as $p)
                <article class="flex flex-col gap-4 rounded-card border border-line bg-surface p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="text-body-sm font-bold text-ink">{{ $p['nama'] }}</h2>
                        <x-pv.badge nada="info">{{ $p['track'] }}</x-pv.badge>
                    </div>
                    <dl class="grid grid-cols-[110px_1fr] gap-y-2 text-body-sm">
                        <dt class="text-muted">Durasi</dt><dd class="text-ink">{{ $p['durasi'] }} semester</dd>
                        <dt class="text-muted">Tarif formulir</dt><dd class="text-ink">{{ $p['formulir'] ?? 'Belum tersedia' }}</dd>
                        <dt class="text-muted">Tarif / semester</dt><dd class="text-ink">{{ $p['semester'] ?? 'Belum tersedia' }}</dd>
                    </dl>
                    <div class="mt-auto flex items-center justify-between gap-3">
                        <a href="{{ $p['href'] }}" class="text-body-sm font-bold text-ink hover:underline">Lihat detail peminatan →</a>
                        @unless ($p['dibuka'])<span class="text-caption text-muted">Tidak dibuka periode ini</span>@endunless
                    </div>
                </article>
            @empty
                <div class="md:col-span-2 lg:col-span-3"><x-pv.empty-state judul="Belum ada peminatan" deskripsi="Peminatan akan tampil setelah diumumkan." /></div>
            @endforelse
        </div>

        <p class="mt-6 text-caption text-muted">Tarif diambil dari data master dan dapat berubah untuk tagihan baru; nominal tagihan = tarif + kode unik 3 digit.</p>
    </div>
@endsection
