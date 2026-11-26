{{-- HW-35 · Tarif peminatan — ringkasan tarif berlaku hari ini (FR-47, BR-25, BR-27). Figma 1562:3111 "Kelola Harga Semester".
     Pengeditan per peminatan di admin.track.tarif.show (rute PRD /admin/peminatan/{kode}/tarif); fitur Figma "Salin Biaya" tidak dibuat (T-28).
     Data: Track\TarifController::index (kunci = fixture hw-35). --}}
@extends('layouts.portal')
@section('judul', 'Tarif Peminatan')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Admin / Pengaturan / Tarif Peminatan" judul="Kelola Harga Semester"
        deskripsi="Tarif formulir dan tarif per semester per peminatan. Perubahan disimpan sebagai versi baru; tagihan yang sudah terbit tidak berubah."
        :chip="$jumlah['lengkap'].' dari '.$jumlah['peminatan'].' peminatan lengkap'" />

    <x-pv.tabel :kolom="array_merge(['Peminatan', 'Formulir'], array_map(fn ($n) => 'Sem '.$n, range(1, max(1, $semester))), ['>Aksi'])">
        @forelse ($baris as $b)
            <tr>
                <td class="px-6 py-4">
                    <span class="block text-body-sm font-semibold text-ink">{{ $b['nama'] }}</span>
                    <span class="text-caption text-muted">{{ $b['track'] }} · {{ $b['durasi'] }} semester @unless ($b['aktif'])· nonaktif @endunless</span>
                </td>
                @foreach (array_merge([$b['formulir']], $b['semester']) as $i => $nilai)
                    <td class="px-6 py-4 font-mono text-body-sm whitespace-nowrap">
                        @if ($nilai)
                            <span class="text-ink">{{ $nilai }}</span>
                        @elseif ($i === 0 || $i <= $b['durasi'])
                            <x-pv.badge nada="warning">Belum diatur</x-pv.badge>
                        @else
                            <span class="text-line">—</span>
                        @endif
                    </td>
                @endforeach
                <td class="px-6 py-4 text-right"><a href="{{ $b['href'] }}" class="text-body-sm font-bold text-link hover:underline">Kelola</a></td>
            </tr>
        @empty
            <tr><td colspan="{{ 3 + $semester }}" class="px-6 py-10"><x-pv.empty-state judul="Belum ada peminatan" deskripsi="Tambah peminatan di menu Peminatan terlebih dahulu." /></td></tr>
        @endforelse
    </x-pv.tabel>

    <x-pv.alert judul="Harga yang berubah hanya berlaku untuk tagihan baru" class="mt-5">Tagihan memakai versi tarif dengan tanggal efektif terbaru yang ≤ tanggal penerbitan (BR-27). Tarif yang belum diatur membuat tagihan tidak terbit dan Admin Track diperingatkan (BR-34).</x-pv.alert>
@endsection
