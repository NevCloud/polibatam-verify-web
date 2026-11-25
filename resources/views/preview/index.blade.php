@extends('layouts.auth')
@section('judul', 'Preview')
@section('konten')
    <h1 class="text-title font-semibold">Preview halaman</h1>
    <p class="mt-1 text-body-sm text-muted">Hanya aktif bila APP_ENV=local dan PV_PREVIEW=true.</p>
    <ul class="mt-6 space-y-3">
        @forelse ($halaman as $id => $state)
            <li class="flex flex-wrap items-center gap-2">
                <span class="w-16 font-semibold uppercase">{{ $id }}</span>
                @foreach ($state as $s)
                    <x-pv.button variant="link" :href="route('preview.show', [$id, $s])">{{ $s }}</x-pv.button>
                @endforeach
            </li>
        @empty
            <li class="text-muted">Belum ada fixture.</li>
        @endforelse
    </ul>
@endsection
