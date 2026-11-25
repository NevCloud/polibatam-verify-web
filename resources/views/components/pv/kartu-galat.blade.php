{{-- Layar/Kartu galat (Figma 3058:8465): ilustrasi, kode, judul, deskripsi, tombol utama + tautan "Ke Halaman Utama". --}}
@props([
    'kode',
    'judul',
    'deskripsi',
    'tombolUtama' => null,
    'hrefUtama' => null,
    'tombolKedua' => 'Ke Halaman Utama',
    'hrefKedua' => null,
])
<div {{ $attributes->class(['flex w-full max-w-[520px] flex-col items-center rounded-card border border-line bg-surface p-10 text-center']) }}>
    <div class="mb-6 grid size-24 place-items-center rounded-full bg-interactive">
        <img src="{{ asset('images/ikon-galat.svg') }}" alt="" width="44" height="44" class="size-11">
    </div>
    <p class="text-kode font-extrabold tracking-[-1.5px] text-primary">{{ $kode }}</p>
    <h1 class="mt-5 text-heading font-bold text-ink">{{ $judul }}</h1>
    <p class="mt-5 max-w-96 text-body text-muted">{{ $deskripsi }}</p>
    @if ($tombolUtama || $tombolKedua)
        <div class="mt-11 flex flex-col items-center gap-3">
            @if ($tombolUtama)
                <x-pv.button :href="$hrefUtama">{{ $tombolUtama }}</x-pv.button>
            @endif
            @if ($tombolKedua)
                <a href="{{ $hrefKedua ?? url('/') }}" class="pt-1 text-body-sm font-medium text-link hover:underline">{{ $tombolKedua }}</a>
            @endif
        </div>
    @endif
</div>
