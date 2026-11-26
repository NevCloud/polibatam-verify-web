{{-- HW-35 · Tarif berversi satu peminatan (FR-47, BR-25–BR-28, AC-22.1–22.5). Rute PRD /admin/peminatan/{kode}/tarif.
     Admin Keuangan hanya membaca ($bisaUbah = false). Data: Track\TarifController::show (kunci = fixture hw-35 state peminatan_*). --}}
@extends('layouts.portal')
@section('judul', 'Tarif '.$peminatan['nama'])
@section('konten')
    <x-pv.page-header breadcrumb="Portal Admin / Tarif Peminatan / {{ $peminatan['kode'] }}" :judul="'Tarif '.$peminatan['nama']"
        :deskripsi="$peminatan['track'].' · '.$peminatan['durasi'].' semester'.($peminatan['aktif'] ? '' : ' · nonaktif')" chip="Berversi">
        <x-slot:aksi>
            <x-pv.button variant="link" :href="route('admin.tarif.index')"><x-pv.ikon nama="arrow-left" :ukuran="14" />Kembali ke ringkasan tarif</x-pv.button>
        </x-slot:aksi>
    </x-pv.page-header>

    @if (session('status'))<x-pv.alert nada="success" :judul="session('status')" class="mb-5" />@endif
    @if ($errors->has('nominal'))<x-pv.alert nada="error" :judul="$errors->first('nominal')" class="mb-5" />@endif

    <div class="grid gap-5 lg:grid-cols-[1fr_360px]">
        <x-pv.panel judul="Tarif berlaku hari ini">
            <p class="-mt-3 text-caption text-muted">{{ $bisaUbah ? 'Ubah nominal yang perlu saja; hanya yang berubah dibuatkan versi baru.' : 'Nominal yang dipakai untuk tagihan yang terbit hari ini.' }}</p>
            @if ($bisaUbah)
                <form id="form-tarif" method="POST" action="{{ route('admin.tarif.store', $peminatan['kode']) }}" class="flex flex-col gap-4" novalidate>
                    @csrf
            @endif
            <div class="divide-y divide-line rounded-control border border-line">
                @foreach ($tarif as $kunci => $t)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-body-sm font-semibold text-ink">{{ $t['label'] }}</p>
                            <p class="text-caption text-muted">
                                {{ $t['rupiah'] ? $t['rupiah'].' sejak '.$t['sejak'] : 'Belum diatur' }}@if ($t['terjadwal']) · <span class="text-info">terjadwal {{ $t['terjadwal'] }}</span>@endif
                            </p>
                        </div>
                        @if ($bisaUbah)
                            <label class="flex flex-col gap-1">
                                <span class="sr-only">Nominal baru {{ $t['label'] }}</span>
                                <input name="nominal[{{ $kunci }}]" inputmode="numeric" value="{{ $t['nominal'] }}" placeholder="Rp"
                                       @class(['h-10 w-44 rounded-control border bg-surface px-3 text-right font-mono text-body-sm text-ink focus:outline-2 focus:outline-primary', 'border-error' => $errors->has('nominal.'.$kunci), 'border-control' => ! $errors->has('nominal.'.$kunci)])
                                       @if ($errors->has('nominal.'.$kunci)) aria-invalid="true" @endif>
                                @if ($errors->has('nominal.'.$kunci))<span class="text-caption text-error">{{ $errors->first('nominal.'.$kunci) }}</span>@endif
                            </label>
                        @elseif (! $t['rupiah'])
                            <x-pv.badge nada="warning">Belum diatur</x-pv.badge>
                        @endif
                    </div>
                @endforeach
            </div>
            @if ($bisaUbah)
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <x-pv.field label="Tanggal berlaku efektif" name="tanggal_efektif" type="date" :min="$hariIni" :value="$hariIni" wajib class="w-56"
                            bantuan="Hari ini atau setelahnya; versi lama tetap tersimpan." />
                        <x-pv.button type="submit"><x-pv.ikon nama="centang" :ukuran="14" />Simpan sebagai versi baru</x-pv.button>
                    </div>
                </form>
            @else
                <p class="mt-4 text-caption text-muted">Anda hanya dapat melihat tarif. Perubahan dilakukan Admin Track.</p>
            @endif
        </x-pv.panel>

        <x-pv.panel judul="Riwayat versi">
            <p class="-mt-3 text-caption text-muted">Versi lama tidak dapat diubah atau dihapus (BR-26).</p>
            <ol class="flex flex-col gap-3">
                @forelse ($riwayat as $r)
                    <li class="rounded-control border border-line px-4 py-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-body-sm font-semibold text-ink">{{ $r['item'] }}</p>
                            @if ($r['terjadwal'])<x-pv.badge nada="info">Terjadwal</x-pv.badge>@endif
                        </div>
                        <p class="font-mono text-body-sm text-ink">{{ $r['rupiah'] }}</p>
                        <p class="text-caption text-muted">Efektif {{ $r['efektif'] }} · oleh {{ $r['oleh'] }} · {{ $r['dibuat'] }}</p>
                    </li>
                @empty
                    <li><x-pv.empty-state judul="Belum ada tarif" deskripsi="Tagihan peminatan ini belum dapat terbit sampai tarif diatur." /></li>
                @endforelse
            </ol>
        </x-pv.panel>
    </div>
@endsection
