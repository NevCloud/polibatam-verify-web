{{-- PV/Field (Figma 3018:887): label 14/Medium, kontrol 48px (border/control, radius 8, px 14), bantuan 12 abu; jarak 6px.
     Type=Input/Select/Textarea. Galat: garis & pesan merah. Opsi Select lewat slot.
     `ikon` (opsional): ikon 18px di kiri kontrol (Figma "Input" halaman autentikasi).
     type=password: tombol tampilkan/sembunyikan (Alpine). --}}
@props(['label', 'name', 'type' => 'text', 'bantuan' => null, 'galat' => null, 'value' => null, 'wajib' => false, 'ikon' => null])
@php
    $id = $attributes->get('id') ?? 'f-'.str_replace(['[', ']', '.'], '-', $name);
    $galat ??= isset($errors) ? ($errors->first($name) ?: null) : null; // $errors hanya ada di middleware web
    $kontrol = 'w-full rounded-control border bg-surface px-3.5 text-body-sm text-ink placeholder:text-control focus:outline-2 focus:outline-offset-0 focus:outline-primary read-only:bg-canvas '.($galat ? 'border-error' : 'border-control');
    $deskripsi = $galat ? $id.'-galat' : ($bantuan ? $id.'-bantuan' : null);
    $sandi = $type === 'password';
@endphp
<div {{ $attributes->only('class')->class(['flex flex-col gap-1.5']) }}>
    <label for="{{ $id }}" class="text-body-sm font-medium text-ink">{{ $label }}@if ($wajib)<span class="text-error"> *</span>@endif</label>
    @if ($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}" @if ($deskripsi) aria-describedby="{{ $deskripsi }}" @endif @if ($galat) aria-invalid="true" @endif @required($wajib) {{ $attributes->except(['class', 'id']) }} class="{{ $kontrol }} h-control">{{ $slot }}</select>
    @elseif ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="4" @if ($deskripsi) aria-describedby="{{ $deskripsi }}" @endif @if ($galat) aria-invalid="true" @endif @required($wajib) {{ $attributes->except(['class', 'id']) }} class="{{ $kontrol }} py-3">{{ $value }}</textarea>
    @else
        <div class="relative" @if ($sandi) x-data="{ lihat: false }" @endif>
            @if ($ikon)
                <x-pv.ikon :nama="$ikon" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-control" />
            @endif
            <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" @if ($sandi) x-bind:type="lihat ? 'text' : 'password'" @endif
                   @unless ($sandi) value="{{ $value }}" @endunless
                   @if ($deskripsi) aria-describedby="{{ $deskripsi }}" @endif @if ($galat) aria-invalid="true" @endif @required($wajib)
                   {{ $attributes->except(['class', 'id']) }} @class([$kontrol, 'h-control', 'pl-11' => $ikon, 'pr-11' => $sandi])>
            @if ($sandi)
                <button type="button" x-on:click="lihat = !lihat" x-bind:aria-pressed="lihat" aria-controls="{{ $id }}"
                        class="absolute top-1/2 right-2 grid size-8 -translate-y-1/2 place-items-center rounded-control text-control hover:text-ink focus-visible:outline-2 focus-visible:outline-primary">
                    <span class="sr-only" x-text="lihat ? 'Sembunyikan password' : 'Tampilkan password'">Tampilkan password</span>
                    <x-pv.ikon nama="mata-tertutup" :ukuran="16" />
                </button>
            @endif
        </div>
    @endif
    @if ($galat)
        <p id="{{ $id }}-galat" class="text-caption text-error">{{ $galat }}</p>
    @elseif ($bantuan)
        <p id="{{ $id }}-bantuan" class="text-caption text-muted">{{ $bantuan }}</p>
    @endif
</div>
