{{-- PV/Badge (Figma 3018:834): Tone=Success/Warning/Error/Neutral/Info, 11px Medium, px 10 / py 4, radius 6px. --}}
@props(['nada' => 'neutral'])
@php
    $kelas = [
        'success' => 'bg-success-surface text-success',
        'warning' => 'bg-warning-surface text-warning',
        'error' => 'bg-error-surface text-error',
        'neutral' => 'bg-neutral-surface text-neutral',
        'info' => 'bg-info-surface text-info',
    ][$nada];
@endphp
<span {{ $attributes->class(['inline-flex items-center whitespace-nowrap rounded-badge px-2.5 py-1 text-meta font-medium', $kelas]) }}>{{ $slot }}</span>
