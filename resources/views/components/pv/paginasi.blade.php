{{-- Paginasi kaki tabel (Figma HW-44/HW-25): "Menampilkan a–b dari n …" + ← Sebelumnya · nomor · Berikutnya →.
     $tautan = LengthAwarePaginator::linkCollection(); $satuan = kata benda setelah total (mis. "akun admin"). --}}
@props(['rentang', 'total', 'satuan', 'tautan' => [], 'sebelumnya' => null, 'berikutnya' => null, 'keterangan' => null])
<p class="text-compact text-muted">Menampilkan {{ $rentang }} dari {{ $total }} {{ $satuan }}{{ $keterangan ? ' · '.$keterangan : '' }}</p>
<nav class="flex items-center gap-1 text-caption" aria-label="Halaman">
    <a @if ($sebelumnya) href="{{ $sebelumnya }}" @else aria-disabled="true" @endif
       @class(['rounded border border-line px-3 py-1.5 font-semibold', 'text-ink hover:bg-canvas' => $sebelumnya, 'pointer-events-none text-control' => ! $sebelumnya])>← Sebelumnya</a>
    @foreach ($tautan as $t)
        @if (is_numeric($t['label']) || $t['label'] === '...')
            <a @if ($t['url']) href="{{ $t['url'] }}" @endif @if ($t['active']) aria-current="page" @endif
               @class(['grid size-8 place-items-center rounded', 'bg-primary font-semibold text-white' => $t['active'], 'text-ink hover:bg-canvas' => ! $t['active']])>{{ $t['label'] }}</a>
        @endif
    @endforeach
    <a @if ($berikutnya) href="{{ $berikutnya }}" @else aria-disabled="true" @endif
       @class(['rounded border border-line px-3 py-1.5 font-semibold', 'text-ink hover:bg-canvas' => $berikutnya, 'pointer-events-none text-control' => ! $berikutnya])>Berikutnya →</a>
</nav>
