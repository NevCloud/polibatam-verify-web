{{-- Layar/Header publik (Figma 3057:3470 / HW-01 1562:9361): logo · Beranda/Program/FAQ/Kontak · Masuk + Daftar (tamu) atau Dasbor.
     Tinggi 72px (design-memory §8). < 768 px: tautan & tombol pindah ke menu lipat (T-19, NFR-33). $aktif = label tautan aktif. --}}
@props(['aktif' => null])
@php
    $nav = ['Beranda' => route('beranda'), 'Program' => route('katalog'), 'FAQ' => route('beranda').'#faq', 'Kontak' => route('beranda').'#kontak'];
    $tautan = fn (string $label) => ['href' => $nav[$label], 'aktif' => $aktif === $label];
@endphp
<header class="relative z-20 shrink-0 border-b border-line bg-surface" x-data="{ buka: false }" x-on:keydown.escape.window="buka = false">
    <div class="flex h-header items-center justify-between gap-6 px-5 md:px-16">
        <x-pv.logo />
        <nav class="hidden items-center gap-8 text-compact md:flex" aria-label="Navigasi utama">
            @foreach (array_keys($nav) as $label)
                <a href="{{ $nav[$label] }}" @if ($aktif === $label) aria-current="page" @endif
                   @class(['py-6 hover:text-ink', 'border-b-2 border-link font-semibold text-link' => $aktif === $label, 'text-muted' => $aktif !== $label])>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="hidden items-center gap-3 md:flex">
            @auth
                <x-pv.button :href="\App\Support\Http\ArahPortal::untuk(auth()->user())">Dasbor</x-pv.button>
            @else
                <x-pv.button variant="secondary" :href="route('login')">Masuk</x-pv.button>
                <x-pv.button :href="route('register')">Daftar</x-pv.button>
            @endauth
        </div>
        <button type="button" class="grid size-10 place-items-center rounded-control border border-line text-ink md:hidden"
                x-on:click="buka = !buka" x-bind:aria-expanded="buka" aria-controls="menu-publik" aria-expanded="false">
            <span class="sr-only">Menu</span>
            <x-pv.ikon nama="titik-tiga" :ukuran="18" />
        </button>
    </div>
    <div id="menu-publik" x-show="buka" x-cloak x-transition.opacity class="border-t border-line bg-surface px-5 pb-5 md:hidden">
        <nav class="flex flex-col py-2 text-body-sm" aria-label="Navigasi utama (ponsel)">
            @foreach (array_keys($nav) as $label)
                <a href="{{ $nav[$label] }}" x-on:click="buka = false" @if ($aktif === $label) aria-current="page" @endif
                   @class(['py-3', 'font-semibold text-link' => $aktif === $label, 'text-ink' => $aktif !== $label])>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="grid grid-cols-2 gap-3">
            @auth
                <x-pv.button class="col-span-2" :href="\App\Support\Http\ArahPortal::untuk(auth()->user())">Dasbor</x-pv.button>
            @else
                <x-pv.button variant="secondary" :href="route('login')">Masuk</x-pv.button>
                <x-pv.button :href="route('register')">Daftar</x-pv.button>
            @endauth
        </div>
    </div>
</header>
