{{-- HW-43 · Cari tagihan (FR-36). Figma 3048:2705. Data: SuperAdmin\TagihanController::index (kunci = fixture hw-43). --}}
@extends('layouts.portal')
@section('judul', 'Tagihan')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Super Admin / Tagihan" judul="Cari & koreksi tagihan"
        deskripsi="Koreksi status Lunas hanya untuk kasus khusus; alasan wajib dan tercatat di audit log.">
        <x-slot:aksi><span></span><x-pv.button variant="secondary" :href="route('admin.tagihan.tanpa-jatuh-tempo')">Tagihan tanpa jatuh tempo</x-pv.button></x-slot:aksi>
    </x-pv.page-header>

    <form method="GET" action="{{ route('admin.tagihan.index') }}" class="mb-5 grid gap-4 sm:grid-cols-3" x-data>
        <x-pv.field label="Cari tagihan" name="q" type="search" :value="$filter['q']" placeholder="Kode referensi, nomor pendaftaran, atau nama" />
        <x-pv.field label="Jenis" name="jenis" type="select" x-on:change="$el.form.requestSubmit()">
            <option value="">Semua</option>
            @foreach (['formulir' => 'Formulir', 'semester' => 'Semester'] as $nilai => $teks)
                <option value="{{ $nilai }}" @selected($filter['jenis'] === $nilai)>{{ $teks }}</option>
            @endforeach
        </x-pv.field>
        <x-pv.field label="Status" name="status" type="select" x-on:change="$el.form.requestSubmit()">
            <option value="">Semua</option>
            @foreach ($statusTersedia as $nilai => $teks)
                <option value="{{ $nilai }}" @selected($filter['status'] === $nilai)>{{ $teks }}</option>
            @endforeach
        </x-pv.field>
    </form>

    <x-pv.tabel :kolom="['Kode referensi', 'Pemilik', 'Jenis', 'Nominal', 'Status', 'Aksi']">
        @forelse ($baris as $b)
            <tr>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $b['kode'] }}</td>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $b['nama'] }}<span class="block text-caption text-muted">{{ $b['nomor'] }}</span></td>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $b['jenis'] }}</td>
                <td class="px-6 py-4 text-body-sm whitespace-nowrap text-ink">{{ $b['nominal'] }}</td>
                <td class="px-6 py-4"><x-pv.badge-status :status="$b['status']" /></td>
                <td class="px-6 py-4 text-body-sm"><a href="{{ $b['href'] }}" class="font-bold text-link hover:underline">Detail<span class="sr-only"> {{ $b['kode'] }}</span></a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-6 py-10"><x-pv.empty-state judul="Tagihan tidak ditemukan" deskripsi="Periksa kode referensi, nomor pendaftaran, atau nama." /></td></tr>
        @endforelse
        <x-slot:kaki>
            <x-pv.paginasi :rentang="$rentang" :total="$total" satuan="tagihan" :tautan="$tautan" :sebelumnya="$sebelumnya" :berikutnya="$berikutnya" />
        </x-slot:kaki>
    </x-pv.tabel>
@endsection
