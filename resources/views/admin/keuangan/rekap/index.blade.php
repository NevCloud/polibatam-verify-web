{{-- HW-32 · Rekap & ekspor keuangan (FR-38, NFR-19). Figma 3048:2207.
     Data: App\Queries\RekapKeuanganQuery (kunci = fixture hw-32). Ekspor Excel = rincian per tagihan; PDF = ringkasan. --}}
@extends('layouts.portal')
@section('judul', 'Rekap & Ekspor')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Keuangan / Rekap & Ekspor" judul="Rekap keuangan"
        deskripsi="Jumlah dan nominal tagihan per status, termasuk tunggakan (belum lunas dan lewat jatuh tempo)." />

    <form method="GET" action="{{ route('admin.rekap') }}" class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" x-data>
        @foreach ([['periode', 'Periode', 'Semua periode', $periodeTersedia], ['peminatan', 'Peminatan', 'Semua peminatan', $peminatanTersedia], ['angkatan', 'Angkatan', 'Semua angkatan', $angkatanTersedia], ['jenis', 'Jenis tagihan', 'Semua jenis', $jenisTersedia]] as [$nama, $label, $semua, $pilihan])
            <x-pv.field :label="$label" :name="$nama" type="select" x-on:change="$el.form.requestSubmit()">
                <option value="">{{ $semua }}</option>
                @foreach ($pilihan as $nilai => $teks)
                    <option value="{{ $nilai }}" @selected((string) $filter[$nama] === (string) $nilai)>{{ $teks }}</option>
                @endforeach
            </x-pv.field>
        @endforeach
        <noscript><x-pv.button type="submit" variant="secondary">Terapkan Filter</x-pv.button></noscript>
    </form>

    <div class="mb-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-pv.stat-card label="Lunas" :nilai="$ringkasan['lunas']['nominal']" :keterangan="$ringkasan['lunas']['jumlah'].' tagihan'" />
        <x-pv.stat-card label="Menunggu verifikasi" :nilai="$ringkasan['menunggu']['nominal']" :keterangan="$ringkasan['menunggu']['jumlah'].' tagihan'" />
        <x-pv.stat-card label="Belum bayar" :nilai="$ringkasan['belum']['nominal']" :keterangan="$ringkasan['belum']['jumlah'].' tagihan (termasuk Ditolak)'" />
        <x-pv.stat-card label="Tunggakan" :nilai="$ringkasan['tunggakan']['nominal']" :keterangan="$ringkasan['tunggakan']['jumlah'].' tagihan lewat jatuh tempo'" />
    </div>

    @php $kueri = array_filter($filter, fn ($v) => $v !== ''); @endphp
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-card bg-interactive px-4 py-3">
        <p class="text-body-sm font-semibold text-ink">Ekspor sesuai filter</p>
        <div class="flex gap-3">
            <x-pv.button variant="secondary" :href="route('admin.rekap.excel', $kueri)">Export Excel</x-pv.button>
            <x-pv.button variant="secondary" :href="route('admin.rekap.pdf', $kueri)">Export PDF</x-pv.button>
        </div>
    </div>

    <x-pv.tabel :kolom="['Peminatan', '>Lunas', '>Nominal lunas', '>Belum bayar', '>Tunggakan']">
        @forelse ($baris as $b)
            <tr>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $b['peminatan'] }}</td>
                <td class="px-6 py-4 text-right text-body-sm text-ink">{{ $b['lunas'] }}</td>
                <td class="px-6 py-4 text-right text-body-sm whitespace-nowrap text-ink">{{ $b['nominal_lunas'] }}</td>
                <td class="px-6 py-4 text-right text-body-sm text-ink">{{ $b['belum'] }}</td>
                <td class="px-6 py-4 text-right text-body-sm whitespace-nowrap text-ink">{{ $b['tunggakan'] }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-6 py-10"><x-pv.empty-state judul="Belum ada tagihan" deskripsi="Ubah filter atau tunggu tagihan terbit." /></td></tr>
        @endforelse
    </x-pv.tabel>
@endsection
