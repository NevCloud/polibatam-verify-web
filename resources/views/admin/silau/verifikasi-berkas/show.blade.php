{{-- HW-26 · Detail verifikasi berkas (FR-19–21, FR-61, BR-13, BR-61, BR-66, A-64). Figma 1562:5004 (+ 409: 3053:816).
     Data: VerifikasiBerkasController::show (kunci = fixture hw-26). Satu form: tiap dokumen diputuskan Valid/Ditolak
     dengan checklist; Ditolak wajib alasan (kriteria tak terpenuhi dan/atau catatan). Kuota hanya info admin (BR-75). --}}
@extends('layouts.portal')
@section('judul', 'Verifikasi berkas')
@section('konten')
    @php
        $viewer = collect($dokumen)->map(fn ($d) => ['label' => $d['label'], 'status' => $d['status']] + $d['berkas'])->values();
        $konflik = ($konflik ?? false) || session('kode_galat') === \App\Exceptions\SudahDiverifikasi::KODE; // $konflik: state preview 409
    @endphp
    <x-pv.page-header breadcrumb="Portal Admin / Verifikasi Berkas / Detail" judul="Verifikasi & detail berkas pendaftar"
        deskripsi="Putuskan setiap dokumen (Valid / Ditolak + alasan), lalu selesaikan. Sistem menetapkan status pendaftaran secara otomatis."
        :chip="$pendaftar['periode']" />

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.verifikasi-berkas.index') }}" class="text-compact font-bold text-link hover:underline">← Kembali ke antrean verifikasi</a>
        <p class="text-caption text-muted">Kuota minimum {{ $pendaftar['peminatan'] }}: {{ $kuota['minimum'] }} · NPD Terbit: {{ $kuota['npd'] }}</p>
    </div>

    @if ($konflik)
        <x-pv.dialog id="dialog-konflik" judul="Data sudah diputuskan admin lain" x-data x-init="$el.showModal()"
            deskripsi="Keputusan Anda tidak disimpan karena pendaftaran atau dokumen ini sudah diubah sejak halaman dibuka. Data terbaru sudah dimuat.">
            <form method="dialog" class="flex justify-end"><x-pv.button type="submit">Lihat data terbaru</x-pv.button></form>
        </x-pv.dialog>
    @endif
    @if ($errors->has('selesai'))
        <x-pv.alert nada="error" :judul="$errors->first('selesai')" class="mb-5" />
    @endif
    @unless ($bisaDiputuskan)
        <x-pv.alert :judul="'Pendaftaran berstatus '.$pendaftar['status']" class="mb-5">Verifikasi sudah diselesaikan; halaman ini hanya untuk dilihat.</x-pv.alert>
    @endunless

    <section class="mb-5 flex flex-wrap items-center gap-4 rounded-sheet border border-line bg-surface p-5" aria-label="Pendaftar">
        <span class="grid size-12 shrink-0 place-items-center rounded-full bg-interactive text-body font-bold text-link" aria-hidden="true">{{ $pendaftar['inisial'] }}</span>
        <div class="flex min-w-0 flex-1 flex-col gap-1">
            <p class="text-subheading font-bold text-ink">{{ $pendaftar['nama'] }}</p>
            <p class="text-compact text-muted">{{ $pendaftar['nomor'] }} · {{ $pendaftar['track'] }} · {{ $pendaftar['peminatan'] }}</p>
        </div>
        <div class="flex flex-col items-end gap-1">
            <x-pv.badge-status :status="$pendaftar['status']" />
            <p class="text-caption text-muted">Diajukan {{ $pendaftar['diajukan'] }}</p>
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]" x-data="{ dok: {{ Js::from($viewer) }}, aktif: 0, zoom: 100, putar: 0,
            pilih(i) { this.aktif = i; this.zoom = 100; this.putar = 0 }, get d() { return this.dok[this.aktif] } }">
        {{-- Document viewer (FR-19, NFR-17) --}}
        <x-pv.panel class="gap-0 p-0! sm:p-0! xl:sticky xl:top-24 xl:self-start" aria-label="Pratinjau dokumen">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
                <div class="min-w-0">
                    <h2 class="text-subheading font-bold text-primary">Pratinjau dokumen</h2>
                    <p class="truncate text-caption text-muted" x-text="d ? `${d.label} · ${d.nama}` : 'Belum ada berkas'"></p>
                </div>
                <span x-show="d" class="rounded-badge bg-interactive px-2.5 py-1 text-meta font-medium text-link" x-text="d?.status"></span>
            </div>
            <div class="flex flex-wrap items-center gap-4 border-b border-line px-5 py-2.5 text-caption text-muted" x-show="d">
                <span class="flex items-center gap-2">Zoom
                    <button type="button" class="grid size-7 place-items-center rounded-control hover:bg-canvas" x-on:click="zoom = Math.max(50, zoom - 25)" aria-label="Perkecil">−</button>
                    <span class="w-10 text-center font-semibold text-ink" x-text="`${zoom}%`"></span>
                    <button type="button" class="grid size-7 place-items-center rounded-control hover:bg-canvas" x-on:click="zoom = Math.min(250, zoom + 25)" aria-label="Perbesar">+</button>
                </span>
                <button type="button" class="font-semibold text-link hover:underline" x-on:click="putar = (putar + 90) % 360" x-show="d?.gambar">Putar</button>
                <a x-bind:href="d?.url" target="_blank" rel="noopener" class="ml-auto font-semibold text-link hover:underline">Buka di tab baru</a>
            </div>
            <div class="h-[640px] overflow-auto bg-document p-4">
                <template x-if="d && d.gambar">
                    <img x-bind:src="d.url" x-bind:alt="d.label" class="mx-auto max-w-full origin-top shadow transition-transform"
                         x-bind:style="`transform: scale(${zoom / 100}) rotate(${putar}deg)`">
                </template>
                <template x-if="d && !d.gambar">
                    <iframe x-bind:src="d.url" x-bind:title="d.label" class="h-full w-full origin-top-left bg-surface"
                            x-bind:style="`transform: scale(${zoom / 100}); width: ${10000 / zoom}%; height: ${10000 / zoom}%`"></iframe>
                </template>
            </div>
        </x-pv.panel>

        <div class="flex min-w-0 flex-col gap-5">
            <x-pv.panel judul="Biodata pendaftar">
                <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-2">
                    @foreach ($biodata as $label => $nilai)
                        <div class="min-w-0"><dt class="text-caption text-muted">{{ $label }}</dt><dd class="text-compact font-medium wrap-break-word text-ink">{{ $nilai }}</dd></div>
                    @endforeach
                </dl>
            </x-pv.panel>

            <form method="POST" action="{{ route('admin.verifikasi-berkas.simpan', $pendaftar['nomor']) }}" class="flex flex-col gap-5" novalidate>
                @csrf
                <x-pv.panel judul="Verifikasi berkas">
                    <x-slot:aksi><x-pv.chip>{{ count($dokumen) }} berkas</x-pv.chip></x-slot:aksi>
                    <p class="-mt-3 text-caption text-muted">Centang kriteria yang terpenuhi. Valid hanya bila semua kriteria terpenuhi; Ditolak wajib alasan (kriteria yang tidak dicentang dan/atau catatan).</p>

                    @foreach ($dokumen as $i => $d)
                        @php
                            $dasar = "dokumen.{$d['id']}";
                            $nama = "dokumen[{$d['id']}]";
                            $galat = $errors->first("{$dasar}.keputusan") ?: $errors->first("{$dasar}.catatan");
                            $pilihan = old("{$dasar}.keputusan");
                            $dicentang = array_map('intval', (array) old("{$dasar}.kriteria", []));
                        @endphp
                        <fieldset @class(['flex flex-col gap-3 rounded-card border p-4', 'border-error-line bg-error-surface/40' => $galat, 'border-line' => ! $galat])
                                  x-data="{ keputusan: {{ Js::from($pilihan) }} }" x-bind:class="keputusan === 'ditolak' && 'border-error-line bg-error-surface/40'">
                            <legend class="sr-only">{{ $d['label'] }}</legend>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="flex items-center gap-2 text-body-sm font-bold text-ink"><x-pv.ikon nama="file" :ukuran="16" class="text-link" />{{ $d['label'] }}</p>
                                @if ($d['bisa_diputuskan'])
                                    <div class="flex items-center gap-4 text-compact">
                                        <label class="flex items-center gap-1.5"><input type="radio" name="{{ $nama }}[keputusan]" value="valid" x-model="keputusan" class="accent-primary">Valid</label>
                                        <label class="flex items-center gap-1.5 text-error"><input type="radio" name="{{ $nama }}[keputusan]" value="ditolak" x-model="keputusan" class="accent-error">Ditolak</label>
                                    </div>
                                @else
                                    <x-pv.badge-status :status="$d['status']" />
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-2 text-caption text-muted">
                                <span class="truncate">{{ $d['berkas']['nama'] }} · {{ $d['berkas']['info'] }}</span>
                                <button type="button" class="font-bold text-link hover:underline" x-on:click="pilih({{ $i }})">Lihat →</button>
                            </div>

                            @if ($d['bisa_diputuskan'])
                                <input type="hidden" name="{{ $nama }}[versi]" value="{{ $d['versi'] }}">
                                <ul class="flex flex-col gap-1.5 border-t border-line pt-3">
                                    @foreach ($d['kriteria'] as $k)
                                        <li><label class="flex items-start gap-2 text-compact text-ink">
                                            <input type="checkbox" name="{{ $nama }}[kriteria][]" value="{{ $k['id'] }}" @checked(in_array($k['id'], $dicentang, true)) class="mt-0.5 accent-primary">{{ $k['teks'] }}
                                        </label></li>
                                    @endforeach
                                </ul>
                                <div x-show="keputusan === 'ditolak'" @if ($pilihan !== 'ditolak') x-cloak @endif>
                                    <x-pv.field label="Catatan penolakan" :name="$nama.'[catatan]'" :id="'catatan-'.$d['id']" type="textarea" :value="old($dasar.'.catatan')" maxlength="1000"
                                        bantuan="Dikirim ke pendaftar bersama kriteria yang tidak terpenuhi." :galat="false" />
                                </div>
                            @endif

                            @if ($galat)<p class="text-caption text-error" role="alert">{{ $galat }}</p>@endif

                            @foreach ($d['riwayat'] as $r)
                                <p class="text-caption text-muted"><span @class(['font-semibold', 'text-error' => $r['keputusan'] === 'Ditolak', 'text-success' => $r['keputusan'] === 'Valid'])>{{ $r['keputusan'] }}</span> · {{ $r['waktu'] }}@if ($r['alasan']) — {{ $r['alasan'] }}@endif</p>
                            @endforeach
                        </fieldset>
                    @endforeach
                </x-pv.panel>

                @if ($bisaDiputuskan)
                    <div class="flex flex-col gap-3 rounded-sheet border border-line bg-surface p-5">
                        <x-pv.button type="submit" class="w-full">Selesaikan verifikasi</x-pv.button>
                        <p class="text-caption text-muted">Ada dokumen Ditolak → Perlu Revisi. Semua dokumen Valid → Masuk Kuota (otomatis). Pendaftar menerima notifikasi.</p>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <x-pv.alert class="mt-5">Tidak ada tahap approval terpisah. Jika data sudah diputuskan admin lain, penyimpanan ditolak (409) dan data terbaru dimuat ulang.</x-pv.alert>
@endsection
