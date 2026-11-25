{{-- PV/Tab (Figma 3018:837): State=Active = kartu putih, garis border/default, tinggi 44, radius 8, teks navy 14/SemiBold.
     Default [ASUMSI, varian tidak ada di Figma]: tanpa latar, teks abu. "tanda" = penanda kelengkapan (lengkap/kurang).
     Mode tautan: `href` + `aktif` (server). Mode klien: `pilih="nilai"` = tombol yang mengubah variabel Alpine `tab`
     (HW-15: pindah tab tanpa kehilangan isian). --}}
@props(['aktif' => false, 'href' => '#', 'tanda' => null, 'pilih' => null])
@php
    $dasar = 'inline-flex h-11 items-center justify-center gap-2 rounded-control border px-5 text-body-sm';
    $nyala = 'border-line bg-surface font-semibold text-primary';
    $padam = 'border-transparent font-medium text-muted hover:text-ink';
@endphp
@if ($pilih)
    <button type="button" role="tab" x-on:click="tab = '{{ $pilih }}'" x-bind:aria-selected="tab === '{{ $pilih }}'"
            x-bind:class="tab === '{{ $pilih }}' ? '{{ $nyala }}' : '{{ $padam }}'" {{ $attributes->class([$dasar]) }}>
@else
    <a href="{{ $href }}" @if ($aktif) aria-current="page" @endif {{ $attributes->class([$dasar, $nyala => $aktif, $padam => ! $aktif]) }}>
@endif
    {{ $slot }}
    @if ($tanda === 'lengkap')
        <span class="grid size-4 place-items-center rounded-full bg-success text-[10px] text-white" aria-label="lengkap">✓</span>
    @elseif ($tanda === 'kurang')
        <span class="size-2 rounded-full bg-error" aria-label="belum lengkap"></span>
    @endif
@if ($pilih)</button>@else</a>@endif
