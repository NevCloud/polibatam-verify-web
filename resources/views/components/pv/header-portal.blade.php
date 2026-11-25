{{-- Layar/Header portal (Figma 3057:3461): logo · tautan website · notifikasi · avatar inisial · nama + chip peran · Logout.
     Tinggi 72px (design-memory §8 keputusan final; Figma 96px). --}}
@props(['portal'])
<header class="sticky top-0 z-20 h-header shrink-0 border-b border-line bg-surface">
    <div class="flex h-full items-center justify-between gap-4 px-5 lg:px-12">
        <x-pv.logo />
        <nav class="hidden items-center gap-6 text-compact font-medium text-muted xl:flex" aria-label="Tautan website">
            {{-- STUB-FE(P08): rute halaman publik --}}
            @foreach (['Beranda', 'Program', 'FAQ', 'Kontak'] as $item)
                <a href="#" class="hover:text-ink">{{ $item }}</a>
            @endforeach
        </nav>
        <div class="flex items-center gap-4">
            {{-- Lonceng = menu Notifikasi peran ini (HW-20 peserta; admin STUB-FE(P12)) --}}
            <a href="{{ collect($portal->menu)->firstWhere('label', 'Notifikasi')['href'] ?? '#' }}" aria-label="Notifikasi" class="shrink-0"><img src="{{ asset('images/notifikasi.svg') }}" alt="" width="42" height="42" class="size-[42px]"></a>
            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-primary text-compact font-bold text-white" aria-hidden="true">{{ $portal->inisial }}</span>
            <div class="hidden flex-col items-start gap-[3px] sm:flex">
                <p class="text-compact font-bold text-ink">{{ $portal->nama }}</p>
                <x-pv.chip :nada="$portal->nadaPeran">{{ $portal->peran }}</x-pv.chip>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="h-[46px] w-[92px] shrink-0 rounded-menu border border-primary bg-surface text-body-sm font-bold text-primary hover:bg-interactive">Logout</button>
            </form>
        </div>
    </div>
</header>
