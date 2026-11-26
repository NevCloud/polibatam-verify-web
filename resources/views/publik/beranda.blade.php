{{-- HW-01 · Beranda (FR-08, FR-10, BR-55, BR-75). Figma 1562:9360. Jadwal, pengumuman, alur, FAQ, kontak dari CMS (teks polos, di-escape);
     jadwal buka/tutup, track & peminatan dari data master — tidak ada informasi kuota (K-42).
     Data: Publik\PublikController::dataBeranda (kunci = fixture hw-01); $pratinjau = mode pratinjau CMS Super Admin. --}}
@extends('layouts.publik')
@section('judul', 'Beranda')
@section('konten')
    @if ($pratinjau)
        <div class="bg-warning-surface px-5 py-3 text-center text-body-sm font-semibold text-warning" role="status">
            Mode pratinjau CMS — konten draf belum diterbitkan. <a href="{{ route('admin.cms.index') }}" class="underline">Kembali ke CMS</a>
        </div>
    @endif

    {{-- Hero --}}
    <section class="mx-auto grid max-w-[1200px] items-center gap-10 px-5 py-12 md:py-16 lg:grid-cols-2">
        <div class="flex flex-col gap-5">
            <p class="inline-flex items-center gap-2 text-caption font-semibold uppercase tracking-[0.6px] text-link">
                <x-pv.ikon nama="badge-check" :ukuran="14" />Sistem verifikasi Microcredential
            </p>
            <h1 class="text-[32px] leading-[40px] font-extrabold text-primary md:text-[44px] md:leading-[52px]">Langkah baru untuk kompetensi Anda.</h1>
            <p class="max-w-xl text-body text-muted">Daftar program Microcredential melalui Polibatam Verify. Pilih peminatan, lengkapi berkas, dan pantau proses verifikasi dalam satu tempat.</p>
            <div class="flex flex-wrap gap-3">
                @if ($periode['buka'])
                    <x-pv.button :href="$tautanDaftar">Daftar Sekarang</x-pv.button>
                @else
                    <x-pv.button disabled aria-disabled="true" class="cursor-not-allowed opacity-60">Pendaftaran ditutup</x-pv.button>
                @endif
                <x-pv.button variant="secondary" :href="route('katalog')">Lihat Katalog Peminatan</x-pv.button>
            </div>
        </div>
        <figure class="relative overflow-hidden rounded-sheet shadow-md">
            <img src="{{ asset('images/kampus-polibatam.jpg') }}" alt="Gedung kampus Politeknik Negeri Batam" width="678" height="452" class="h-[260px] w-full object-cover md:h-[327px]">
            <figcaption class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-primary/90 via-primary/30 to-transparent p-4">
                <span class="inline-flex items-center gap-1.5 text-caption font-semibold text-accent"><x-pv.ikon nama="badge-check" :ukuran="14" />Pendidikan Vokasi Kelas Dunia</span>
                <span class="text-subheading font-semibold text-white">Kampus Terintegrasi Teaching Factory &amp; Hanggar Aviasi Terbesar di Batam</span>
            </figcaption>
        </figure>
    </section>

    {{-- Jadwal & pengumuman (CMS + tanggal periode otomatis, BR-54) --}}
    <section class="mx-auto grid max-w-[1200px] gap-5 px-5 md:grid-cols-[1.2fr_1fr]" aria-label="Jadwal dan pengumuman">
        <article class="rounded-card border border-line bg-surface p-5">
            <h2 class="text-subheading font-bold text-ink">{{ $konten['jadwal']['judul'] }}{{ $periode['nama'] ? ' · '.$periode['nama'] : '' }}</h2>
            <ul class="mt-3 space-y-2 text-body-sm text-ink">
                <li @class(['font-semibold', 'text-warning' => ! $periode['buka']])>{{ $periode['teks'] }}</li>
                @foreach ($konten['jadwal']['baris'] as $b)<li>{{ $b }}</li>@endforeach
            </ul>
        </article>
        <article class="rounded-card border border-line bg-surface p-5">
            <h2 class="text-subheading font-bold text-ink">{{ $konten['pengumuman']['judul'] }}</h2>
            <ul class="mt-3 space-y-2 text-body-sm text-ink">
                @foreach ($konten['pengumuman']['baris'] as $b)<li>{{ $b }}</li>@endforeach
            </ul>
        </article>
    </section>

    {{-- Track & peminatan (data master) --}}
    <section id="program" class="mx-auto max-w-[1200px] px-5 py-14">
        <p class="text-caption font-semibold uppercase tracking-[0.6px] text-link">Track &amp; peminatan</p>
        <h2 class="mt-1 text-heading font-bold text-primary">Satu registrasi, pilih track dan peminatan</h2>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($track as $t)
                <article class="flex flex-col gap-3 rounded-card border border-line bg-surface p-5">
                    <div class="flex items-center gap-3">
                        <span class="grid size-9 place-items-center rounded-control bg-interactive text-caption font-bold text-link">{{ $t['kode'] }}</span>
                        <h3 class="text-body font-bold text-ink">{{ $t['nama'] }}</h3>
                    </div>
                    <p class="flex-1 text-caption text-muted">{{ implode(' · ', $t['peminatan']) }}</p>
                    <a href="{{ $t['href'] }}" class="text-caption font-bold text-link hover:underline">Lihat peminatan →</a>
                </article>
            @empty
                <p class="text-body-sm text-muted sm:col-span-2 lg:col-span-4">Peminatan akan diumumkan bersama periode pendaftaran berikutnya.</p>
            @endforelse
        </div>
    </section>

    {{-- Alur pendaftaran (CMS) --}}
    <section class="mx-auto grid max-w-[1200px] items-start gap-10 px-5 pb-14 lg:grid-cols-2">
        <div class="flex flex-col gap-4">
            <p class="text-caption font-semibold uppercase tracking-[0.6px] text-link">Alur pendaftaran</p>
            <h2 class="text-heading font-bold text-primary">{{ $konten['alur']['judul'] }}</h2>
            <p class="text-body-sm text-muted">Dari membuat akun hingga melihat hasil, ikuti proses pendaftaran secara bertahap. Status berkas dan pembayaran dapat dipantau di dasbor.</p>
            <div class="rounded-card bg-interactive p-4 text-body-sm">
                <p class="font-bold text-primary">Sebelum Anda mulai</p>
                <p class="mt-1 text-muted">Siapkan email aktif, data diri, dan dokumen persyaratan sesuai peminatan pilihan Anda.</p>
                <a href="{{ route('katalog') }}" class="mt-2 inline-block font-bold text-link hover:underline">Lihat persyaratan di katalog →</a>
            </div>
            @if ($periode['buka'])<x-pv.button :href="$tautanDaftar" class="self-start">Mulai Pendaftaran</x-pv.button>@endif
        </div>
        <ol class="flex flex-col gap-4 rounded-sheet border border-line bg-surface p-6 shadow-sm">
            @foreach ($konten['alur']['pasangan'] as $i => [$judul, $ket])
                <li class="flex gap-4">
                    <span @class(['grid size-9 shrink-0 place-items-center rounded-full text-caption font-bold', 'bg-primary text-white' => $i === 0, 'bg-interactive text-primary' => $i > 0])>{{ sprintf('%02d', $i + 1) }}</span>
                    <div><p class="text-body-sm font-bold text-ink">{{ $judul }}</p>@if ($ket)<p class="text-caption text-muted">{{ $ket }}</p>@endif</div>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- FAQ (CMS) --}}
    <section id="faq" class="mx-auto max-w-[1200px] scroll-mt-20 px-5 pb-14">
        <p class="text-caption font-semibold uppercase tracking-[0.6px] text-link">Masih ragu?</p>
        <h2 class="mt-1 text-heading font-bold text-primary">{{ $konten['faq']['judul'] }}</h2>
        <div class="mt-6 flex flex-col gap-3">
            @foreach ($konten['faq']['pasangan'] as $i => [$tanya, $jawab])
                <details class="group rounded-card border border-line bg-surface px-5 py-4" @if ($i === 0) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-body-sm font-semibold text-ink">
                        {{ $tanya }}<x-pv.ikon nama="chevron-bawah" :ukuran="14" class="text-muted transition group-open:rotate-180" />
                    </summary>
                    <p class="mt-3 text-body-sm text-muted">{{ $jawab }}</p>
                </details>
            @endforeach
        </div>
    </section>

    {{-- Kontak bantuan (CMS) --}}
    <section id="kontak" class="scroll-mt-20 bg-interactive">
        <div class="mx-auto flex max-w-[1200px] flex-col gap-3 px-5 py-12">
            <h2 class="text-heading font-bold text-primary">{{ $konten['kontak']['judul'] }}</h2>
            <ul class="space-y-1 text-body-sm text-ink">
                @foreach ($konten['kontak']['baris'] as $b)<li>{{ $b }}</li>@endforeach
            </ul>
        </div>
    </section>
@endsection
