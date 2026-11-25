{{-- HW-28 · Antrean verifikasi pembayaran (FR-31, BR-66). Figma 1562:9852.
     Data: App\Queries\AntreanPembayaranQuery (kunci = fixture hw-28). Tab status, tanpa aksi massal. --}}
@extends('layouts.portal')
@section('judul', 'Verifikasi Pembayaran')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Keuangan / Verifikasi Pembayaran" judul="Antrean verifikasi pembayaran"
        deskripsi="Tagihan formulir dan semester berstatus Menunggu Verifikasi. Setiap tagihan diputuskan satu per satu." />

    @if (session('status'))<x-pv.alert nada="success" :judul="session('status')" class="mb-5" />@endif

    <div class="mb-5 grid gap-5 sm:grid-cols-2 xl:w-1/2">
        <x-pv.stat-card label="Perlu tindakan" :nilai="$jumlahTab['menunggu_verifikasi'].' antrean'" />
        <x-pv.stat-card label="Lunas hari ini" :nilai="$lunasHariIni" />
    </div>

    <form method="GET" action="{{ route('admin.verifikasi-pembayaran.index') }}" class="mb-4 flex flex-col gap-4" x-data>
        <input type="hidden" name="tab" value="{{ $filter['tab'] }}">
        <nav class="flex flex-wrap gap-1 self-start rounded-card bg-interactive p-1.5" aria-label="Status tagihan">
            @foreach ($tabTersedia as $nilai => $label)
                <x-pv.tab :aktif="$filter['tab'] === $nilai" :href="route('admin.verifikasi-pembayaran.index', ['tab' => $nilai] + array_filter($filter, fn ($v, $k) => $k !== 'tab' && $v !== '', ARRAY_FILTER_USE_BOTH))">
                    {{ $label }} <span class="rounded-badge bg-canvas px-1.5 text-meta text-muted">{{ $jumlahTab[$nilai] }}</span>
                </x-pv.tab>
            @endforeach
        </nav>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-pv.field label="Cari" name="q" type="search" :value="$filter['q']" placeholder="Nama, nomor pendaftaran, atau kode referensi" />
            @foreach ([['jenis', 'Jenis tagihan', 'Semua jenis', $jenisTersedia], ['peminatan', 'Peminatan', 'Semua peminatan', $peminatanTersedia], ['periode', 'Periode', 'Semua periode', $periodeTersedia]] as [$nama, $label, $semua, $pilihan])
                <x-pv.field :label="$label" :name="$nama" type="select" x-on:change="$el.form.requestSubmit()">
                    <option value="">{{ $semua }}</option>
                    @foreach ($pilihan as $nilai => $teks)
                        <option value="{{ $nilai }}" @selected((string) $filter[$nama] === (string) $nilai)>{{ $teks }}</option>
                    @endforeach
                </x-pv.field>
            @endforeach
        </div>
    </form>

    <x-pv.tabel :kolom="['No. pendaftaran', 'Nama', 'Nominal', 'Jenis tagihan', 'Tanggal transfer', 'Status', 'Aksi']">
        @forelse ($baris as $b)
            <tr>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $b['nomor'] }}<span class="block text-caption text-muted">{{ $b['kode'] }}</span></td>
                <td class="px-6 py-4 text-body-sm font-semibold text-ink">{{ $b['nama'] }}</td>
                <td class="px-6 py-4 text-body-sm whitespace-nowrap text-ink">{{ $b['nominal'] }}</td>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $b['jenis'] }}</td>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $b['transfer'] }}</td>
                <td class="px-6 py-4"><x-pv.badge-status :status="$b['status']" /></td>
                <td class="px-6 py-4 text-body-sm">
                    <a href="{{ $b['href'] }}" class="font-bold text-link hover:underline">{{ $b['perlu_tindakan'] ? 'Verifikasi' : 'Lihat' }}<span class="sr-only"> {{ $b['kode'] }}</span></a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-6 py-10"><x-pv.empty-state judul="Tidak ada tagihan" deskripsi="Tagihan dengan bukti transfer yang menunggu verifikasi akan muncul di sini." /></td></tr>
        @endforelse
        <x-slot:kaki>
            <x-pv.paginasi :rentang="$rentang" :total="$total" satuan="tagihan" :keterangan="$jumlahTab['menunggu_verifikasi'].' menunggu verifikasi'"
                :tautan="$tautan" :sebelumnya="$sebelumnya" :berikutnya="$berikutnya" />
        </x-slot:kaki>
    </x-pv.tabel>
@endsection
