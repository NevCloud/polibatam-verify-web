{{-- HW-29 · Detail verifikasi pembayaran (FR-31–FR-35, BR-38, BR-39, BR-61, BR-72, A-64). Figma 1562:11114.
     Data: VerifikasiPembayaranController::show (kunci = fixture hw-29). Gerbang ganda: formulir Lunas hanya bila Masuk Kuota.
     Tolak wajib alasan baku dan/atau catatan. Konflik → dialog 409 (flash kode_galat). --}}
@extends('layouts.portal')
@section('judul', 'Verifikasi pembayaran')
@section('konten')
    @php
        $terakhir = $bukti[0] ?? null;
        $konflik = ($konflik ?? false) || session('kode_galat') === \App\Exceptions\SudahDiverifikasi::KODE; // $konflik: state preview 409
    @endphp
    <x-pv.page-header breadcrumb="Portal Keuangan / Verifikasi Pembayaran / Detail" judul="Verifikasi pembayaran"
        deskripsi="Cocokkan bukti dengan mutasi rekening, lalu tandai Lunas atau Ditolak. Keputusan satu per satu." :chip="$tagihan['kode']" />
    <a href="{{ route('admin.verifikasi-pembayaran.index') }}" class="mb-5 inline-block text-compact font-bold text-link hover:underline">← Kembali ke antrean pembayaran</a>

    @if ($konflik)
        <x-pv.dialog id="dialog-konflik" judul="Tagihan sudah diputuskan admin lain" x-data x-init="$el.showModal()"
            deskripsi="Keputusan Anda tidak disimpan karena tagihan ini sudah diubah sejak halaman dibuka. Data terbaru sudah dimuat.">
            <form method="dialog" class="flex justify-end"><x-pv.button type="submit">Lihat data terbaru</x-pv.button></form>
        </x-pv.dialog>
    @endif
    @if ($errors->has('keputusan'))<x-pv.alert nada="error" :judul="$errors->first('keputusan')" class="mb-5" />@endif
    @unless ($bisaDiputuskan)
        <x-pv.alert :judul="'Tagihan berstatus '.$tagihan['status']" class="mb-5">Tidak ada keputusan yang perlu diambil; halaman ini hanya untuk dilihat.</x-pv.alert>
    @endunless

    <section class="mb-5 flex flex-wrap items-center gap-4 rounded-sheet border border-line bg-surface p-5" aria-label="Pemilik tagihan">
        <span class="grid size-12 shrink-0 place-items-center rounded-card bg-primary text-body font-bold text-white" aria-hidden="true">{{ \App\Support\Ui\PortalContext::inisial($tagihan['nama']) }}</span>
        <div class="flex min-w-0 flex-1 flex-col gap-1">
            <p class="flex flex-wrap items-center gap-2 text-subheading font-bold text-ink">{{ $tagihan['nama'] }} <x-pv.chip>{{ $tagihan['jenis'] }}</x-pv.chip></p>
            <p class="text-compact text-muted">{{ $tagihan['nomor_pendaftaran'] }} · {{ $tagihan['track'] }} · {{ $tagihan['peminatan'] }} · {{ $tagihan['status_pendaftaran'] }}</p>
        </div>
        <x-pv.badge-status :status="$tagihan['status']" />
    </section>

    <div class="grid gap-5 xl:grid-cols-2">
        <x-pv.panel judul="Bukti transfer" class="xl:self-start">
            @if ($terakhir)
                <div class="h-[560px] overflow-auto rounded-card bg-document p-3">
                    @if ($terakhir['gambar'])
                        <img src="{{ $terakhir['url'] }}" alt="Bukti transfer {{ $tagihan['kode'] }}" class="mx-auto max-w-full shadow">
                    @else
                        <iframe src="{{ $terakhir['url'] }}" title="Bukti transfer {{ $tagihan['kode'] }}" class="h-full w-full bg-surface"></iframe>
                    @endif
                </div>
                <a href="{{ $terakhir['url'] }}" target="_blank" rel="noopener" class="self-end text-caption font-bold text-link hover:underline">Buka ukuran penuh ↗</a>
            @else
                <p class="text-compact text-muted">Belum ada bukti yang diunggah.</p>
            @endif
        </x-pv.panel>

        <div class="flex min-w-0 flex-col gap-5">
            <x-pv.panel judul="Rekonsiliasi transaksi">
                <x-slot:aksi><x-pv.chip>{{ $tagihan['kode'] }}</x-pv.chip></x-slot:aksi>
                <div class="overflow-x-auto rounded-control border border-line">
                    <table class="w-full min-w-[440px] text-left text-compact">
                        <thead class="border-b border-line bg-interactive/70 text-caption text-muted">
                            <tr><th class="px-3 py-2.5">Komponen</th><th class="px-3 py-2.5">Tagihan sistem</th><th class="px-3 py-2.5">Diisi pengirim</th><th class="px-3 py-2.5">Status</th></tr>
                        </thead>
                        <tbody class="divide-y divide-line text-ink">
                            <tr>
                                <td class="px-3 py-2.5 font-semibold">Nominal</td><td class="px-3 py-2.5">{{ $tagihan['nominal'] }}</td>
                                <td class="px-3 py-2.5">{{ $terakhir['nominal_transfer'] ?? '—' }}</td>
                                <td class="px-3 py-2.5">@if ($terakhir)<x-pv.badge :nada="$terakhir['selisih'] ? 'warning' : 'success'">{{ $terakhir['selisih'] ? 'Selisih' : 'Sesuai' }}</x-pv.badge>@endif</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2.5 font-semibold">Rekening tujuan</td>
                                <td class="px-3 py-2.5" colspan="3">{{ $tagihan['rekening'] ? $tagihan['rekening']['bank'].' '.$tagihan['rekening']['nomor'].' a.n. '.$tagihan['rekening']['atas_nama'] : '—' }}</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2.5 font-semibold">Tanggal transfer</td><td class="px-3 py-2.5">Batas: {{ $tagihan['jatuh_tempo'] }}</td>
                                <td class="px-3 py-2.5" colspan="2">{{ $terakhir['tanggal_transfer'] ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @if ($terakhir)
                    <x-pv.daftar-info :baris="['Nama rekening pengirim' => $terakhir['nama_pengirim'], 'Bank pengirim' => $terakhir['bank_pengirim'], 'Diunggah' => $terakhir['diunggah']]" />
                @endif
                @if ($pengirimBerbeda)
                    <x-pv.alert nada="warning">Pengirim {{ $terakhir['nama_pengirim'] }} berbeda dengan pemilik tagihan {{ $tagihan['nama'] }}. Konfirmasi hubungan pengirim sebelum memutuskan.</x-pv.alert>
                @endif
                @if ($tagihan['jenis_kode'] === 'formulir')
                    <x-pv.alert :nada="$gerbang ? 'success' : 'warning'" :judul="$gerbang ? 'Gerbang ganda terpenuhi' : 'Menunggu verifikasi berkas'">
                        {{ $gerbang ? ($tagihan['status_pendaftaran'] === 'NPD Terbit'
                        ? "Pendaftaran {$tagihan['nomor_pendaftaran']} sudah NPD Terbit (verifikasi ulang setelah koreksi); tagihan dapat ditandai Lunas tanpa menerbitkan NPD baru."
                        : "Pendaftaran {$tagihan['nomor_pendaftaran']} sudah Masuk Kuota; tagihan formulir dapat ditandai Lunas dan NPD terbit otomatis.") : "Pendaftaran {$tagihan['nomor_pendaftaran']} belum Masuk Kuota ({$tagihan['status_pendaftaran']}). Tagihan belum dapat ditandai Lunas." }}
                    </x-pv.alert>
                @endif
            </x-pv.panel>

            @if ($bisaDiputuskan)
                <form method="POST" action="{{ route('admin.verifikasi-pembayaran.putuskan', $tagihan['kode']) }}" novalidate>
                    @csrf
                    <input type="hidden" name="versi" value="{{ $tagihan['versi'] }}">
                    <x-pv.panel judul="Keputusan">
                        <fieldset class="flex flex-col gap-2">
                            <legend class="mb-1 text-caption font-semibold tracking-wide text-muted uppercase">Alasan penolakan (wajib bila Ditolak)</legend>
                            @foreach ($alasanBaku as $kunci => $teks)
                                <label class="flex items-center gap-2 text-compact text-ink"><input type="checkbox" name="alasan[]" value="{{ $kunci }}" @checked(in_array($kunci, (array) old('alasan', []), true)) class="accent-error">{{ $teks }}</label>
                            @endforeach
                        </fieldset>
                        <x-pv.field label="Catatan" name="catatan" type="textarea" :value="old('catatan')" maxlength="1000"
                            bantuan="Nomor referensi mutasi internal atau keterangan untuk pendaftar." />
                        <div class="flex flex-wrap justify-end gap-3 border-t border-line pt-4">
                            <x-pv.button type="submit" name="keputusan" value="tolak" variant="danger"><x-pv.ikon nama="x" :ukuran="14" />Tolak Pembayaran</x-pv.button>
                            <x-pv.button type="submit" name="keputusan" value="lunas" :disabled="! $gerbang"><x-pv.ikon nama="centang" :ukuran="14" />Tandai Lunas</x-pv.button>
                        </div>
                    </x-pv.panel>
                </form>
            @endif

            @if (count($bukti) > 1)
                <x-pv.panel judul="Riwayat bukti">
                    @foreach (array_slice($bukti, 1) as $b)
                        <p class="text-caption text-muted"><x-pv.badge-status :status="$b['keputusan']" /> {{ $b['diunggah'] }} · {{ $b['nominal_transfer'] }}@if ($b['alasan']) — {{ $b['alasan'] }}@endif
                            <a href="{{ $b['url'] }}" target="_blank" rel="noopener" class="font-bold text-link hover:underline">Lihat</a></p>
                    @endforeach
                </x-pv.panel>
            @endif
        </div>
    </div>
@endsection
