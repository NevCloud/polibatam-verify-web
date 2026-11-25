{{-- Layar/Sidebar (Figma 3057:3829, Role=…): lebar 232, px 20 / py 32, jarak 24:
     identitas portal → menu → kartu bantuan → "Politeknik Negeri Batam". --}}
@props(['portal'])
<aside class="hidden w-sidebar shrink-0 flex-col gap-6 border-r border-line bg-surface px-5 py-8 lg:flex">
    <div class="flex flex-col gap-1.5">
        <p class="text-meta font-extrabold text-link">{{ $portal->eyebrow }}</p>
        <p class="text-caption text-muted">{{ $portal->subjudul }}</p>
    </div>
    <nav class="flex flex-col gap-2" aria-label="Menu portal">
        @foreach ($portal->menu as $item)
            <x-pv.sidebar-item :href="$item['href']" :aktif="$item['aktif']" :ikon="$item['ikon']">{{ $item['label'] }}</x-pv.sidebar-item>
        @endforeach
    </nav>
    <div class="flex flex-col gap-2.5 rounded-sheet bg-interactive p-4">
        <x-pv.ikon nama="bantuan" :ukuran="20" class="text-link" />
        <p class="text-compact font-bold text-primary">{{ $portal->bantuan['judul'] }}</p>
        <p class="text-caption text-muted">{{ $portal->bantuan['teks'] }}</p>
        {{-- STUB-FE(P08): halaman panduan --}}
        <a href="#" class="text-caption font-bold text-link hover:underline">{{ $portal->bantuan['tautan'] }}</a>
    </div>
    <p class="text-[10px] text-control">Politeknik Negeri Batam</p>
</aside>
