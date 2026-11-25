{{-- PV/Step (Figma 3018:842): lingkaran 32px + label 12px. State: selesai (navy + ✓), aktif (navy + nomor, label tebal),
     belum (putih bergaris, label abu) — design-memory §7.5. --}}
@props(['nomor', 'label', 'state' => 'belum'])
<div {{ $attributes->class(['flex items-center gap-2.5']) }} @if ($state === 'aktif') aria-current="step" @endif>
    <span @class(['grid size-8 shrink-0 place-items-center rounded-full text-compact font-semibold',
        'bg-primary text-white' => $state !== 'belum',
        'border border-control bg-surface text-muted' => $state === 'belum'])>{{ $state === 'selesai' ? '✓' : $nomor }}</span>
    <span @class(['text-caption', 'font-bold text-ink' => $state === 'aktif', 'text-ink' => $state === 'selesai', 'text-muted' => $state === 'belum'])>{{ $label }}</span>
</div>
