{{-- HW-17 · Daftar tagihan peserta (FR-37, BR-04, BR-35). Figma 3048:1592 (berisi), 1562:11467 (kosong).
     Data: Peserta\TagihanController::index (kunci = fixture hw-17). Hanya tagihan milik akun sendiri. --}}
@extends('layouts.portal')
@section('judul', 'Tagihan')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Peserta / Tagihan" judul="Tagihan"
        deskripsi="Semua tagihan formulir dan semester. Nominal transfer = tarif + kode unik 3 digit." :chip="$npd ? 'NPD '.$npd : null" />

    <div class="mb-5 grid gap-5 md:grid-cols-3">
        <x-pv.stat-card label="Total lunas" :nilai="$ringkasan['lunas']['nominal']" :keterangan="$ringkasan['lunas']['jumlah'].' tagihan'" />
        <x-pv.stat-card label="Belum dibayar" :nilai="$ringkasan['belum']['nominal']" :keterangan="$ringkasan['belum']['jumlah'].' tagihan'" />
        <x-pv.stat-card label="Menunggu penetapan" :nilai="(string) $ringkasan['menunggu_penetapan']" keterangan="tagihan semester tanpa jatuh tempo" />
    </div>

    <x-pv.tabel :kolom="['Jenis', 'Kode referensi', 'Nominal', 'Jatuh tempo', 'Status', 'Aksi']">
        @forelse ($tagihan as $t)
            <tr @class(['bg-interactive/60' => $t['bisa_bayar']])>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $t['jenis'] }}</td>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $t['kode'] }}</td>
                <td class="px-6 py-4 text-body-sm whitespace-nowrap text-ink">{{ $t['nominal'] }}</td>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $t['jatuh_tempo'] }}</td>
                <td class="px-6 py-4"><x-pv.badge-status :status="$t['status']" /></td>
                <td class="px-6 py-4 text-body-sm">
                    @if ($t['bisa_bayar'])
                        <x-pv.button :href="$t['href']" class="h-10!">Bayar</x-pv.button>
                    @else
                        <a href="{{ $t['href'] }}" class="font-bold text-link hover:underline">Detail<span class="sr-only"> {{ $t['kode'] }}</span></a>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-6 py-10">
                <x-pv.empty-state judul="Belum ada tagihan" deskripsi="Tagihan formulir terbit setelah formulir diajukan. Tagihan Semester 1 terbit setelah NPD terbit." />
            </td></tr>
        @endforelse
    </x-pv.tabel>
@endsection
