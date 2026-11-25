{{-- HW-16 · Detail berkas & revisi (FR-16, FR-17, BR-14, AC-08.1–08.4). Figma 3048:1508.
     Data: BerkasController::index (kunci = fixture hw-16). Unggah ulang hanya untuk dokumen Ditolak saat Perlu Revisi;
     setelah tidak ada lagi dokumen Ditolak, status kembali Diajukan otomatis (A-82). --}}
@extends('layouts.portal')
@section('judul', 'Berkas & revisi')
@section('konten')
    @php $revisi = collect($berkas)->where('bisa_unggah', true); @endphp
    <x-pv.page-header breadcrumb="Portal Peserta / Status / Berkas" judul="Berkas & revisi"
        deskripsi="Unggah ulang hanya dokumen yang Ditolak. Dokumen Valid terkunci." :chip="$pendaftaran['status']" />

    @if (session('status'))
        <x-pv.alert nada="success" :judul="session('status')" class="mb-5" />
    @endif
    @if ($errors->has('berkas'))
        <x-pv.alert nada="error" judul="Berkas tidak dapat diunggah" class="mb-5">{{ $errors->first('berkas') }}</x-pv.alert>
    @endif
    @if ($revisi->isNotEmpty())
        <x-pv.alert nada="error" :judul="$revisi->count().' dokumen perlu diperbaiki'" class="mb-5">
            Admin SILAU menolak {{ $revisi->pluck('label')->implode(', ') }}. Unggah versi baru; status kembali Diajukan setelah semua dokumen yang ditolak diunggah ulang.
        </x-pv.alert>
    @endif

    <x-pv.tabel :kolom="['Dokumen', 'Status', 'Catatan verifikator', 'Aksi']" class="mb-5">
        @foreach ($berkas as $b)
            <tr @class(['bg-interactive/60' => $b['bisa_unggah']])>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $b['label'] }}</td>
                <td class="px-6 py-4"><x-pv.badge-status :status="$b['status']" /></td>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $b['alasan'] ?? ($b['berkas'] ? '—' : 'Tidak diunggah') }}</td>
                <td class="px-6 py-4 text-body-sm">
                    @if ($b['bisa_unggah'])
                        <a href="#unggah-{{ $b['jenis'] }}" class="font-bold text-link hover:underline">Unggah ulang</a>
                    @elseif ($b['berkas'])
                        <a href="{{ $b['berkas']['url'] }}" target="_blank" rel="noopener" class="font-bold text-link hover:underline">Lihat<span class="sr-only"> {{ $b['label'] }}</span></a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-pv.tabel>

    <div class="grid gap-5 xl:grid-cols-2">
        <div class="flex min-w-0 flex-col gap-5">
            @forelse ($revisi as $b)
                <x-pv.panel :judul="'Unggah ulang: '.$b['label']">
                    @if ($b['alasan'])<p class="-mt-2 text-compact text-error">Alasan penolakan: {{ $b['alasan'] }}</p>@endif
                    @include('peserta.pendaftaran.partials.berkas-terunggah', ['b' => $b])
                    <x-pv.unggah-berkas :action="route('pendaftaran.berkas.unggah', [$pendaftaran['nomor'], $b['jenis']])" :id="'unggah-'.$b['jenis']"
                        :kembali="route('pendaftaran.berkas', $pendaftaran['nomor'])" :bantuan="'PDF/JPG/PNG · maks. '.$batasUnggah" />
                </x-pv.panel>
            @empty
                <x-pv.panel judul="Unggah ulang">
                    <p class="text-compact text-muted">Tidak ada dokumen yang perlu diunggah ulang. Dokumen hanya dapat diganti bila ditolak Admin SILAU.</p>
                </x-pv.panel>
            @endforelse
        </div>

        <x-pv.panel judul="Riwayat versi">
            @if ($riwayat)
                <div class="overflow-x-auto rounded-control border border-line">
                    <table class="w-full min-w-[480px] text-left">
                        <thead class="border-b border-line bg-interactive/70">
                            <tr>
                                @foreach (['Dokumen', 'Versi', 'Berkas', 'Status'] as $k)
                                    <th scope="col" class="px-4 py-3 text-caption font-semibold text-muted">{{ $k }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($riwayat as $r)
                                <tr>
                                    <td class="px-4 py-3 text-compact text-ink">{{ $r['dokumen'] }}</td>
                                    <td class="px-4 py-3 text-compact text-ink">{{ $r['versi'] }}</td>
                                    <td class="px-4 py-3 text-compact text-ink">{{ $r['berkas'] }}
                                        @if ($r['alasan'])<span class="block text-caption text-error">{{ $r['alasan'] }}</span>@endif
                                    </td>
                                    <td class="px-4 py-3"><x-pv.badge-status :status="$r['status']" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-compact text-muted">Belum ada berkas.</p>
            @endif
        </x-pv.panel>
    </div>
@endsection
