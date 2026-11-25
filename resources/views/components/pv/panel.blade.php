{{-- Kartu konten portal (Figma HW-13–20: kartu putih bergaris, radius 16, p 24; judul 18 Bold navy).
     Slot `aksi` (opsional) = elemen di kanan judul (badge, tautan). --}}
@props(['judul' => null])
<section {{ $attributes->class(['flex min-w-0 flex-col gap-5 rounded-sheet border border-line bg-surface p-5 sm:p-6']) }}>
    @if ($judul || isset($aksi))
        <header class="flex flex-wrap items-center justify-between gap-3">
            @if ($judul)<h2 class="text-subheading font-bold text-primary">{{ $judul }}</h2>@endif
            {{ $aksi ?? '' }}
        </header>
    @endif
    {{ $slot }}
</section>
