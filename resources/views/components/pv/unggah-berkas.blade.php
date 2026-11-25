{{-- Area unggah (Figma HW-15 Tab Berkas, HW-16 "Seret & lepas berkas di sini"): garis putus, radius 12, tombol Pilih File.
     Dengan JS: XHR + indikator progres (NFR-16) lalu kembali ke `kembali`. Tanpa JS: form biasa (tombol Unggah).
     Format/ukuran divalidasi server dari isi berkas (BR-12, NFR-07). --}}
@props(['action', 'id', 'kembali' => null, 'label' => 'Seret & lepas berkas di sini', 'bantuan' => null])
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="unggah" data-kembali="{{ $kembali }}"
      x-on:dragover.prevent="seret = true" x-on:dragleave.prevent="seret = false" x-on:drop.prevent="lepas($event)"
      {{ $attributes->class(['flex flex-col gap-3 rounded-card border border-dashed border-line-strong bg-canvas p-4']) }}
      x-bind:class="seret && 'border-primary bg-interactive'">
    @csrf
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex min-w-0 flex-col gap-0.5">
            <p class="text-compact font-semibold text-ink">{{ $label }}</p>
            @if ($bantuan)<p class="text-caption text-muted">{{ $bantuan }}</p>@endif
        </div>
        <input id="{{ $id }}" type="file" name="berkas" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required
               class="peer sr-only" x-ref="berkas" x-on:change="kirim()">
        <label for="{{ $id }}" class="inline-flex h-10 cursor-pointer items-center justify-center rounded-control border border-primary bg-surface px-5 text-compact font-semibold text-primary hover:bg-interactive peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary">Pilih File</label>
    </div>
    <div x-show="persen !== null" x-cloak class="flex items-center gap-3">
        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-line" role="progressbar" aria-label="Progres unggah" aria-valuemin="0" aria-valuemax="100" x-bind:aria-valuenow="persen">
            <div class="h-full rounded-full bg-primary transition-[width]" x-bind:style="`width: ${persen}%`"></div>
        </div>
        <span class="w-10 text-right text-caption font-semibold text-ink" x-text="`${persen}%`"></span>
    </div>
    <p x-show="galat" x-cloak x-text="galat" class="text-caption text-error" role="alert"></p>
    <noscript><x-pv.button type="submit" variant="secondary" class="w-full">Unggah</x-pv.button></noscript>
</form>
