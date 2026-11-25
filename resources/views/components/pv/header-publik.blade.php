{{-- Layar/Header publik (Figma 3057:3470): logo kiri, tautan navigasi kanan. Tinggi 72px (design-memory §8 keputusan final; Figma 96px). --}}
@props(['nav' => ['Beranda' => '#', 'Panduan Pendaftaran' => '#']])
<header class="h-header shrink-0 border-b border-line bg-surface">
    <div class="flex h-full items-center justify-between gap-6 px-5 md:px-16">
        <x-pv.logo />
        <nav class="hidden items-center gap-8 text-[13px] text-muted sm:flex" aria-label="Navigasi utama">
            {{-- shortcut: tautan disembunyikan di bawah 640 px agar halaman 360 px tidak meluber; menu ponsel dibuat di P08 --}}
            {{-- STUB-FE(P08): rute halaman publik --}}
            @foreach ($nav as $label => $href)
                <a href="{{ $href }}" class="hover:text-ink">{{ $label }}</a>
            @endforeach
            {{ $slot }}
        </nav>
    </div>
</header>
