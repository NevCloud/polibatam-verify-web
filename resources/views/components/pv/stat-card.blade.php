{{-- PV/Stat Card (Figma 3018:944): label 12 SemiBold UPPERCASE abu, nilai 28 Bold, keterangan 12 abu; px 20 / py 18, radius 12. --}}
@props(['label', 'nilai', 'keterangan' => null])
<div {{ $attributes->class(['flex flex-col gap-1 rounded-card border border-line bg-surface px-5 py-[18px]']) }}>
    <p class="text-caption font-semibold uppercase tracking-[0.72px] text-muted">{{ $label }}</p>
    <p class="text-display font-bold text-ink">{{ $nilai }}</p>
    @if ($keterangan)<p class="text-caption text-muted">{{ $keterangan }}</p>@endif
</div>
