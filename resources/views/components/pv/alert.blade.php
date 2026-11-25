{{-- PV/Alert (Figma 3018:871): penanda "i", judul 13/SemiBold warna nada, teks 12 abu; px 20 / py 16, radius 12. --}}
@props(['nada' => 'info', 'judul' => null])
@php
    [$latar, $teks] = [
        'info' => ['bg-interactive border-line-accent', 'text-primary'],
        'warning' => ['bg-warning-surface border-line-accent', 'text-warning'],
        'error' => ['bg-error-surface border-error-line', 'text-error'],
        'success' => ['bg-success-surface border-line-accent', 'text-success'],
    ][$nada];
@endphp
<div role="{{ $nada === 'error' ? 'alert' : 'status' }}" {{ $attributes->class(['flex items-start gap-3 rounded-card border px-5 py-4', $latar]) }}>
    <span class="text-compact font-semibold {{ $teks }}" aria-hidden="true">i</span>
    <div class="min-w-0 flex-1">
        @if ($judul)<p class="text-compact font-semibold {{ $teks }}">{{ $judul }}</p>@endif
        <div @class(['text-caption text-muted', 'mt-0.5' => $judul])>{{ $slot }}</div>
    </div>
</div>
