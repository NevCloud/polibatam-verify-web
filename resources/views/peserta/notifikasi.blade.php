{{-- HW-20 · Notifikasi peserta (FR-53). Figma 3048:1716. Data: NotifikasiController::index (kunci = fixture hw-20). --}}
@extends('layouts.portal')
@section('judul', 'Notifikasi')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Peserta / Notifikasi" judul="Notifikasi" deskripsi="Pemberitahuan in-app terkait pendaftaran dan tagihan Anda." />

    @if (session('status'))
        <x-pv.alert nada="success" :judul="session('status')" class="mb-5" />
    @endif

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-card bg-interactive px-4 py-3">
        <p class="text-body-sm font-medium text-ink">{{ $belumDibaca ? $belumDibaca.' belum dibaca' : 'Semua notifikasi sudah dibaca' }}</p>
        @if ($belumDibaca)
            <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
                @csrf
                <x-pv.button type="submit" variant="secondary">Tandai semua dibaca</x-pv.button>
            </form>
        @endif
    </div>

    <x-pv.panel>
        @forelse ($notifikasi as $n)
            <a href="{{ $n['href'] }}" class="group flex items-start gap-3">
                <span @class(['mt-1.5 size-2 shrink-0 rounded-full', 'bg-primary' => ! $n['dibaca'], 'bg-line-strong' => $n['dibaca']])></span>
                <span class="flex min-w-0 flex-col gap-0.5">
                    <span @class(['text-body-sm text-ink group-hover:underline', 'font-semibold' => ! $n['dibaca']])>{{ $n['judul'] }}<span class="sr-only">{{ $n['dibaca'] ? '' : ' (belum dibaca)' }}</span></span>
                    <span class="text-compact text-muted">{{ $n['isi'] }}</span>
                    <span class="text-caption text-muted">{{ $n['waktu'] }}</span>
                </span>
            </a>
        @empty
            <x-pv.empty-state judul="Belum ada notifikasi" deskripsi="Notifikasi muncul saat status pendaftaran, berkas, atau tagihan Anda berubah." class="py-6" />
        @endforelse

        @if ($sebelumnya || $berikutnya)
            <nav class="flex justify-between border-t border-line pt-4" aria-label="Halaman notifikasi">
                <x-pv.button variant="link" :href="$sebelumnya ?? '#'" class="{{ $sebelumnya ? '' : 'invisible' }}">← Sebelumnya</x-pv.button>
                <x-pv.button variant="link" :href="$berikutnya ?? '#'" class="{{ $berikutnya ? '' : 'invisible' }}">Berikutnya →</x-pv.button>
            </nav>
        @endif
    </x-pv.panel>
@endsection
