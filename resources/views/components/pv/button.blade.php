{{-- PV/Button (Figma): tinggi 48px, radius 8px, 14/SemiBold. Danger = latar merah muda + garis + teks merah (Figma 3018:821). --}}
@props(['variant' => 'primary', 'href' => null, 'type' => 'button'])
@php
    $kelas = [
        'primary' => 'bg-primary text-white hover:bg-primary/90',
        'secondary' => 'border border-primary bg-surface text-primary hover:bg-interactive',
        'danger' => 'border border-error-line bg-error-surface text-error hover:bg-error-line/40',
        'link' => 'text-link underline-offset-4 hover:underline',
    ][$variant];
    $dasar = $variant === 'link'
        ? 'inline-flex items-center gap-1 text-body-sm font-semibold'
        : 'inline-flex h-control items-center justify-center gap-2 rounded-control px-5 text-body-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary';
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$dasar, $kelas]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$dasar, $kelas]) }}>{{ $slot }}</button>
@endif
