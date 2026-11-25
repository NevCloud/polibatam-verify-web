{{-- Layar/Judul halaman (Figma 3057:3493): breadcrumb 12 abu · judul 28 ExtraBold navy · deskripsi 14 abu · chip pil di kanan. --}}
@props(['judul', 'breadcrumb' => null, 'deskripsi' => null, 'chip' => null])
<div {{ $attributes->class(['mb-6']) }}>
    <div class="flex items-center gap-5">
        <div class="flex min-w-0 flex-1 flex-col gap-2">
            @if ($breadcrumb)<p class="text-caption text-muted">{{ $breadcrumb }}</p>@endif
            <h1 class="text-display font-extrabold text-primary">{{ $judul }}</h1>
            @if ($deskripsi)<p class="text-body-sm text-muted">{{ $deskripsi }}</p>@endif
        </div>
        @if ($chip)<x-pv.chip>{{ $chip }}</x-pv.chip>@endif
    </div>
    @isset($aksi)
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">{{ $aksi }}</div>
    @endisset
</div>
