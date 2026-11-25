{{-- HW-13 · Status & riwayat pendaftaran (menu "Status"; FR-15, FR-68, AC-07.3). Figma 1562:7958 (Masuk Kuota), 3053:1101–1925.
     Data: DasborController::status (kunci = fixture hw-13-status). "Bukti verifikasi berkas" Figma tidak dipakai (S-08). --}}
@extends('layouts.portal')
@section('judul', 'Status pendaftaran')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Peserta / Status pendaftaran" judul="Status pendaftaran"
        deskripsi="Pantau tahap verifikasi berkas, kuota, dan pembayaran formulir Anda." :chip="$pendaftaran['nomor']" />

    @if (session('status'))
        <x-pv.alert nada="success" :judul="session('status')" class="mb-5" />
    @endif
    <x-pv.alert :nada="$banner['nada']" :judul="$banner['judul']" class="mb-5">{{ $banner['teks'] }}</x-pv.alert>

    <div class="mb-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(0,22rem)]">
        <x-pv.panel judul="Riwayat status pendaftaran">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-compact text-muted">Diperbarui {{ $diperbarui }}</p>
                <x-pv.badge-status :status="$pendaftaran['status']" />
            </div>
            <ol class="flex flex-col">
                @foreach ($riwayat as $r)
                    <li class="relative flex gap-4 pb-6 last:pb-0">
                        @unless ($loop->last)<span class="absolute top-9 bottom-0 left-4 w-px bg-line-strong" aria-hidden="true"></span>@endunless
                        <span @class(['grid size-8 shrink-0 place-items-center rounded-full text-compact text-white', 'bg-success' => $loop->last, 'bg-primary' => ! $loop->last]) aria-hidden="true">✓</span>
                        <div class="flex min-w-0 flex-col gap-1">
                            <p class="text-body-sm font-bold text-ink">{{ $r['judul'] }}</p>
                            <p class="text-meta text-muted">{{ $r['waktu'] }}</p>
                            <p class="text-compact text-muted">{{ $r['teks'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-pv.panel>

        <div class="flex min-w-0 flex-col gap-5">
            <x-pv.panel judul="Pendaftaran Anda">
                <div class="flex flex-col gap-1">
                    <p class="text-body font-bold text-ink">{{ $pendaftaran['nama'] }}</p>
                    <p class="text-body-sm font-bold text-link">{{ $pendaftaran['nomor'] }}</p>
                </div>
                <x-pv.daftar-info :baris="['Track' => $pendaftaran['track'], 'Peminatan' => $pendaftaran['peminatan'], 'Periode' => $pendaftaran['periode']]" />
            </x-pv.panel>

            <x-pv.panel judul="Langkah berikutnya">
                <x-pv.badge nada="warning" class="self-start">{{ $langkah['badge'] }}</x-pv.badge>
                <p class="text-compact leading-relaxed text-muted">{{ $langkah['teks'] }}</p>
                @if ($langkah['tombol'])
                    <x-pv.button :href="$langkah['href']" class="w-full">{{ $langkah['tombol'] }} →</x-pv.button>
                @endif
            </x-pv.panel>
        </div>
    </div>

    <x-pv.panel judul="Dokumen pendaftaran">
        <div class="grid gap-5 lg:grid-cols-2 lg:divide-x lg:divide-line">
            <div class="flex flex-wrap items-center justify-between gap-4 lg:pr-5">
                <div class="flex items-start gap-3">
                    <x-pv.ikon nama="file" class="mt-0.5 text-link" />
                    <div>
                        <p class="text-body-sm font-semibold text-ink">Kartu pendaftar</p>
                        <p class="text-caption text-muted">PDF · Identitas dan pilihan program</p>
                    </div>
                </div>
                @if ($tautan['kartu'])
                    <x-pv.button variant="secondary" :href="$tautan['kartu']">Unduh Kartu Pendaftar</x-pv.button>
                @else
                    <p class="text-caption text-muted">Tersedia setelah formulir diajukan.</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center justify-between gap-4 lg:pl-5">
                <div class="flex items-start gap-3">
                    <x-pv.ikon nama="filecheck" class="mt-0.5 text-link" />
                    <div>
                        <p class="text-body-sm font-semibold text-ink">Berkas & revisi</p>
                        <p class="text-caption text-muted">Status per dokumen, alasan penolakan, riwayat versi</p>
                    </div>
                </div>
                @if ($tautan['berkas'])
                    <x-pv.button variant="secondary" :href="$tautan['berkas']">Lihat Berkas</x-pv.button>
                @else
                    <p class="text-caption text-muted">Tersedia setelah formulir diajukan.</p>
                @endif
            </div>
        </div>
    </x-pv.panel>
@endsection
