{{-- Empty state (§6.3): ikon outline 32px abu, judul 16 SemiBold, deskripsi 14 abu, maks. 1 tombol aksi primer. --}}
@props(['judul', 'deskripsi' => null, 'kode' => null])
<div {{ $attributes->class(['flex flex-col items-center text-center']) }}>
    @isset($ikon)
        <div class="mb-3 size-8 text-control" aria-hidden="true">{{ $ikon }}</div>
    @endisset
    @if ($kode)
        <p class="text-display font-bold text-primary">{{ $kode }}</p>
    @endif
    <h1 class="text-body font-semibold text-ink">{{ $judul }}</h1>
    @if ($deskripsi)
        <p class="mt-1 text-body-sm text-muted">{{ $deskripsi }}</p>
    @endif
    @isset($aksi)
        <div class="mt-6">{{ $aksi }}</div>
    @endisset
</div>
