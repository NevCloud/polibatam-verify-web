{{-- HW-13 · Dasbor peserta (FR-15, FR-53, FR-68). Figma 1562:8886 (Draf). Data: DasborController::index (kunci = fixture hw-13).
     Tanpa pendaftaran: `pendaftaran` null + `periodeBuka`. Tagihan formulir STUB-BE(P05): selalu "Belum terbit". --}}
@extends('layouts.portal')
@section('judul', 'Dashboard')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Peserta / Dashboard" :judul="'Selamat datang, '.$sapaan.'!'"
        deskripsi="Lanjutkan pendaftaran dan pantau setiap tahap verifikasi di sini." :chip="$pendaftaran['periode'] ?? null" />

    @if (session('status'))
        <x-pv.alert nada="success" :judul="session('status')" class="mb-5" />
    @endif

    @if (! $pendaftaran)
        <div class="grid gap-5 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <x-pv.panel>
                <x-pv.empty-state judul="Belum ada pendaftaran"
                    :deskripsi="$periodeBuka ? 'Periode '.$periodeBuka['nama'].' dibuka hingga '.$periodeBuka['tutup'].'. Pilih track dan peminatan untuk memulai.' : 'Belum ada periode pendaftaran yang dibuka. Pantau pengumuman pada halaman beranda.'">
                    @if ($periodeBuka)
                        <x-slot:aksi><x-pv.button :href="route('pendaftaran.baru')">Mulai Pendaftaran</x-pv.button></x-slot:aksi>
                    @endif
                </x-pv.empty-state>
            </x-pv.panel>
            @include('peserta.partials.aktivitas', ['notifikasi' => $notifikasi])
        </div>
    @else
        <section class="mb-5 flex flex-col gap-5 rounded-sheet border border-line-accent bg-interactive p-5 sm:p-6" aria-labelledby="judul-progres">
            <header class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="judul-progres" class="text-body font-bold text-ink">Progres pendaftaran Anda</h2>
                <p class="text-caption font-semibold text-link">{{ $tracker['keterangan'] }}</p>
            </header>
            @include('peserta.partials.tracker', ['langkah' => $tracker['langkah']])
        </section>

        <x-pv.alert :nada="$banner['nada']" :judul="$banner['judul']" class="mb-5">{{ $banner['teks'] }}</x-pv.alert>

        @php
            $kolomLengkap = $kartu['kolom_terisi'] === $kartu['kolom_total'];
            $berkasLengkap = $kartu['berkas_terunggah'] === $kartu['berkas_total'];
        @endphp
        <div class="mb-5 grid gap-5 md:grid-cols-3">
            @foreach ([
                ['ikon' => 'file', 'judul' => 'Formulir', 'nada' => $kolomLengkap ? 'success' : 'warning', 'badge' => $kolomLengkap ? 'Lengkap' : 'Belum lengkap',
                    'teks' => $kartu['kolom_terisi'].' dari '.$kartu['kolom_total'].' kolom terisi', 'persen' => intdiv(100 * $kartu['kolom_terisi'], $kartu['kolom_total']),
                    'tautan' => $pendaftaran['draf'] ? 'Lanjutkan formulir' : 'Lihat formulir', 'href' => $tautan['formulir']],
                ['ikon' => 'layers-tumpuk', 'judul' => 'Berkas', 'nada' => $berkasLengkap ? 'success' : 'error', 'badge' => $berkasLengkap ? 'Lengkap' : 'Belum lengkap',
                    'teks' => $kartu['berkas_terunggah'].' dari '.$kartu['berkas_total'].' berkas wajib diunggah', 'persen' => intdiv(100 * $kartu['berkas_terunggah'], $kartu['berkas_total']),
                    'tautan' => $pendaftaran['draf'] ? 'Buka tab Berkas' : 'Lihat berkas', 'href' => $tautan['berkas']],
                ['ikon' => 'card', 'judul' => 'Tagihan Formulir', 'nada' => 'neutral', 'badge' => 'Belum terbit',
                    'teks' => $pendaftaran['draf'] ? 'Terbit setelah formulir diajukan' : 'Penerbitan tagihan sedang disiapkan', 'persen' => 0,
                    'tautan' => 'Lihat tagihan', 'href' => '#'],
            ] as $k){{-- STUB-FE(P05): tautan tagihan ke HW-17 --}}
                <x-pv.panel class="gap-4">
                    <div class="flex items-center justify-between">
                        <x-pv.ikon :nama="$k['ikon']" class="text-link" />
                        <x-pv.badge :nada="$k['nada']">{{ $k['badge'] }}</x-pv.badge>
                    </div>
                    <h2 class="text-title font-bold text-primary">{{ $k['judul'] }}</h2>
                    <p class="text-compact text-muted">{{ $k['teks'] }}</p>
                    <div class="h-1.5 overflow-hidden rounded-full bg-line" aria-hidden="true"><div class="h-full rounded-full bg-link" style="width: {{ $k['persen'] }}%"></div></div>
                    <a href="{{ $k['href'] }}" class="text-compact font-bold text-link hover:underline">{{ $k['tautan'] }} →</a>
                </x-pv.panel>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <div class="flex min-w-0 flex-col gap-5">
                <x-pv.panel judul="Ringkasan pendaftaran">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-body font-bold text-ink">{{ $pendaftaran['nomor'] }}</p>
                        <x-pv.badge-status :status="$pendaftaran['status']" />
                    </div>
                    <x-pv.daftar-info :baris="['Track' => $pendaftaran['track'], 'Peminatan' => $pendaftaran['peminatan'], 'Tanggal pendaftaran' => $pendaftaran['tanggal_daftar']]" />
                    @if ($tautan['kartu'])
                        <x-pv.button variant="secondary" :href="$tautan['kartu']" class="self-start"><x-pv.ikon nama="file" :ukuran="16" />Unduh Kartu Pendaftar</x-pv.button>
                    @endif
                </x-pv.panel>

                <x-pv.panel judul="Biodata">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <p class="text-compact text-muted">Nama lengkap</p>
                            <p class="text-body-sm font-semibold text-ink">{{ $biodata['nama'] }}</p>
                            <p class="text-compact text-muted">NIK · {{ $biodata['nik'] ?? 'belum diisi' }}</p>
                        </div>
                        <div class="flex min-w-0 flex-col gap-1.5">
                            <p class="text-compact text-muted">Kontak</p>
                            <p class="text-body-sm wrap-break-word text-ink">{{ $biodata['email'] }}</p>
                            <p class="text-compact text-ink">{{ $biodata['no_hp'] ?? 'Nomor HP belum diisi' }}</p>
                        </div>
                    </div>
                    <a href="{{ $tautan['formulir'] }}" class="self-start text-compact font-bold text-link hover:underline">Periksa biodata →</a>
                </x-pv.panel>
            </div>

            <div class="flex min-w-0 flex-col gap-5">
                <x-pv.panel judul="Ringkasan tagihan">
                    <x-pv.badge-status status="Belum terbit" class="self-start" />
                    <p class="text-compact leading-relaxed text-muted">Tagihan formulir terbit setelah formulir diajukan. Tagihan Semester 1 terbit setelah NPD terbit.</p>
                </x-pv.panel>
                @include('peserta.partials.aktivitas', ['notifikasi' => $notifikasi])
            </div>
        </div>
    @endif
@endsection
