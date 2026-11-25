{{-- HW-25 · Antrean verifikasi berkas (FR-18, AC-09.1, AC-09.5, BR-64, BR-66). Figma 3048:1890.
     Data: App\Queries\AntreanVerifikasiQuery (kunci = fixture hw-25). Tanpa Draf, tanpa aksi massal, NIK tersamar. --}}
@extends('layouts.portal')
@section('judul', 'Verifikasi Berkas')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Admin / Verifikasi Berkas" judul="Antrean verifikasi berkas"
        deskripsi="Pendaftaran berstatus Diajukan, diurutkan dari pengajuan terlama. Verifikasi dilakukan satu per satu." :chip="$menunggu.' menunggu'" />

    @if (session('status'))
        <x-pv.alert nada="success" :judul="session('status')" class="mb-5" />
    @endif

    <form method="GET" action="{{ route('admin.verifikasi-berkas.index') }}" class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-5" x-data>
        <x-pv.field label="Cari" name="q" type="search" :value="$filter['q']" placeholder="Nama, nomor pendaftaran, atau NIK" />
        @foreach ([['track', 'Track', 'Semua track', $trackTersedia], ['peminatan', 'Peminatan', 'Semua peminatan', $peminatanTersedia], ['periode', 'Periode', 'Semua periode', $periodeTersedia], ['jenis', 'Jenis antrean', 'Semua (baru & revisi)', $jenisTersedia]] as [$nama, $label, $semua, $pilihan])
            <x-pv.field :label="$label" :name="$nama" type="select" x-on:change="$el.form.requestSubmit()">
                <option value="">{{ $semua }}</option>
                @foreach ($pilihan as $nilai => $teks)
                    <option value="{{ $nilai }}" @selected((string) $filter[$nama] === (string) $nilai)>{{ $teks }}</option>
                @endforeach
            </x-pv.field>
        @endforeach
        <noscript><x-pv.button type="submit" variant="secondary">Terapkan Filter</x-pv.button></noscript>
    </form>

    <x-pv.tabel :kolom="['Nomor', 'Nama', 'NIK', 'Peminatan', 'Diajukan', 'Jenis', 'Aksi']">
        @forelse ($baris as $b)
            <tr>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $b['nomor'] }}</td>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $b['nama'] }}</td>
                <td class="px-6 py-4 text-body-sm whitespace-nowrap text-ink">{{ $b['nik'] }}</td>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $b['peminatan'] }}</td>
                <td class="px-6 py-4 text-body-sm whitespace-nowrap text-ink">{{ $b['diajukan'] }}</td>
                <td class="px-6 py-4"><x-pv.badge-status :status="$b['jenis']" /></td>
                <td class="px-6 py-4 text-body-sm"><a href="{{ $b['href'] }}" class="font-bold text-link hover:underline">Verifikasi<span class="sr-only"> {{ $b['nomor'] }}</span></a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-6 py-10"><x-pv.empty-state judul="Tidak ada berkas yang menunggu" deskripsi="Pendaftaran yang diajukan atau diajukan ulang setelah revisi akan muncul di sini." /></td></tr>
        @endforelse
        <x-slot:kaki>
            <x-pv.paginasi :rentang="$rentang" :total="$total" satuan="pendaftaran" keterangan="urut pengajuan terlama"
                :tautan="$tautan" :sebelumnya="$sebelumnya" :berikutnya="$berikutnya" />
        </x-slot:kaki>
    </x-pv.tabel>

    <p class="mt-4 text-compact text-muted">Tidak ada aksi massal: setiap keputusan Valid/Ditolak diambil per dokumen dan tercatat di audit log.</p>
@endsection
