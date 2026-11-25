{{-- HW-42 · Tagihan tanpa jatuh tempo (FR-57, BR-35). Figma 3048:2627.
     Data: SuperAdmin\TagihanController::tanpaJatuhTempo (kunci = fixture hw-42). Penetapan massal diperbolehkan (bukan keputusan verifikasi, BR-66). --}}
@extends('layouts.portal')
@section('judul', 'Tagihan tanpa jatuh tempo')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Super Admin / Tagihan / Tanpa jatuh tempo" judul="Tagihan tanpa jatuh tempo"
        deskripsi="Tagihan semester yang belum memiliki jatuh tempo karena terbit pada/sesudah H-7 atau periode akademik belum ditetapkan." :chip="count($baris).' tagihan'" />

    @if (session('status'))<x-pv.alert nada="success" :judul="session('status')" class="mb-5" />@endif

    <form method="POST" action="{{ route('admin.tagihan.tanpa-jatuh-tempo.simpan') }}" x-data="{ dipilih: {{ Js::from(array_map('intval', (array) old('tagihan', []))) }} }" novalidate>
        @csrf
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-card bg-interactive px-4 py-3">
            <p class="text-body-sm font-semibold text-ink"><span x-text="dipilih.length">0</span> dipilih</p>
            @error('tagihan')<p class="text-caption text-error">{{ $message }}</p>@enderror
        </div>

        <x-pv.tabel :kolom="['Pilih', 'Kode referensi', 'Pemilik', 'Peminatan', 'Jenis', 'Terbit', 'Status']">
            @forelse ($baris as $b)
                <tr>
                    <td class="px-6 py-4"><input type="checkbox" name="tagihan[]" value="{{ $b['id'] }}" x-model.number="dipilih" class="size-4 accent-primary" aria-label="Pilih {{ $b['kode'] }}"></td>
                    <td class="px-6 py-4 text-body-sm text-ink">{{ $b['kode'] }}</td>
                    <td class="px-6 py-4 text-body-sm text-ink">{{ $b['pemilik'] }}</td>
                    <td class="px-6 py-4 text-body-sm text-ink">{{ $b['peminatan'] }}</td>
                    <td class="px-6 py-4 text-body-sm text-ink">{{ $b['jenis'] }}</td>
                    <td class="px-6 py-4 text-body-sm text-ink">{{ $b['terbit'] }}</td>
                    <td class="px-6 py-4"><x-pv.badge nada="error">Tanpa jatuh tempo</x-pv.badge></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-6 py-10"><x-pv.empty-state judul="Semua tagihan semester sudah memiliki jatuh tempo" /></td></tr>
            @endforelse
        </x-pv.tabel>

        @if ($baris)
            <x-pv.panel class="mt-5">
                <h2 class="text-subheading font-bold text-primary">Tetapkan untuk <span x-text="dipilih.length">0</span> tagihan terpilih</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-pv.field label="Tanggal jatuh tempo" name="jatuh_tempo" type="date" :value="old('jatuh_tempo')" wajib
                        bantuan="Boleh lebih awal dari hari ini; tagihan langsung dihitung lewat jatuh tempo (BR-35)." />
                    <x-pv.field label="Catatan" name="catatan" :value="old('catatan')" maxlength="255" placeholder="mis. Mengikuti periode Genap 2026/2027" />
                </div>
                <x-pv.button type="submit" class="self-end" x-bind:disabled="dipilih.length === 0">Tetapkan jatuh tempo</x-pv.button>
            </x-pv.panel>
        @endif
    </form>
@endsection
