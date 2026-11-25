{{-- Berkas aktif satu slot (HW-15 Tab Berkas, HW-16): nama, jenis · ukuran · tanggal, pratinjau lewat URL bertanda tangan 15 menit (NFR-07). --}}
@if ($b['berkas'])
    <div class="flex items-start gap-3 rounded-card border border-line bg-canvas p-3">
        <span class="grid size-10 shrink-0 place-items-center rounded-control bg-surface text-link" aria-hidden="true"><x-pv.ikon nama="file" /></span>
        <div class="flex min-w-0 flex-col gap-0.5">
            <p class="truncate text-compact font-semibold text-ink">{{ $b['berkas']['nama'] }}</p>
            <p class="text-meta text-muted">{{ $b['berkas']['info'] }}</p>
            <a href="{{ $b['berkas']['url'] }}" target="_blank" rel="noopener" class="text-caption font-bold text-link hover:underline">Lihat preview →<span class="sr-only"> {{ $b['label'] }} (tab baru)</span></a>
        </div>
    </div>
@endif
