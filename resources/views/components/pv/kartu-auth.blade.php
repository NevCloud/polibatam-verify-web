{{-- Kartu formulir autentikasi (Figma "Kartu login" 1562:9203, "Kartu lupa password" 1562:9129):
     putih, garis, radius 20, p-8, lebar maks 520; eyebrow 11 ExtraBold biru · judul 28 ExtraBold navy · deskripsi 13 abu.
     Slot `ikon` (opsional) = kotak ikon 56px di atas eyebrow (HW-09). --}}
@props(['eyebrow' => null, 'judul', 'deskripsi' => null])
<section {{ $attributes->class(['flex w-full min-w-0 max-w-[520px] flex-col gap-6 rounded-auth border border-line bg-surface p-6 shadow-[0_8px_32px_0_rgb(0_43_91/0.03)] sm:p-8']) }}>
    @isset($ikon)
        <span class="grid size-14 place-items-center rounded-sheet bg-interactive text-link">{{ $ikon }}</span>
    @endisset
    <header class="flex flex-col gap-2.5">
        @if ($eyebrow)<p class="text-meta font-extrabold text-link">{{ $eyebrow }}</p>@endif
        <h1 class="text-display font-extrabold text-primary">{{ $judul }}</h1>
        @if ($deskripsi)<p class="text-compact leading-[1.65] text-muted">{{ $deskripsi }}</p>@endif
    </header>
    {{ $slot }}
</section>
