{{-- Layar/Menu item (Figma 3057:3485): tinggi 48, px 14, radius 10, ikon 18 + label 13px.
     State=Aktif: latar navy, teks putih tebal. State=Default: teks abu Medium. --}}
@props(['href' => '#', 'aktif' => false, 'ikon' => null])
<a href="{{ $href }}" @if ($aktif) aria-current="page" @endif
   {{ $attributes->class(['flex h-12 items-center gap-3 rounded-menu px-3.5 text-compact',
       'bg-primary font-bold text-white' => $aktif,
       'font-medium text-muted hover:bg-nav-active hover:text-ink' => ! $aktif]) }}>
    @if ($ikon)<x-pv.ikon :nama="$ikon" />@endif
    {{ $slot }}
</a>
