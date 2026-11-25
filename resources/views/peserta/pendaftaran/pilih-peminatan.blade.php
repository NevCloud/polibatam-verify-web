{{-- HW-14 · Pilih track & peminatan (FR-11, BR-07–09, AC-04.6). Figma 1562:1327.
     Data: PendaftaranController::pilih (kunci = fixture hw-14). `tracks` hanya peminatan yang lolos BR-07; `draf` = ubah pilihan saat masih Draf.
     BR-75 (K-42): tidak ada informasi kuota apa pun di halaman ini (kotak "Kuota & Daftar Tunggu" Figma tidak dipakai). --}}
@extends('layouts.portal')
@section('judul', 'Pilih track & peminatan')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Peserta / Pendaftaran baru" judul="Pilih track & peminatan"
        deskripsi="Pilih satu track dan satu peminatan. Pilihan tidak dapat diubah setelah formulir diajukan." />

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(0,17rem)]">
        <div class="flex min-w-0 flex-col gap-5">
            @if (! $tracks)
                <x-pv.panel>
                    <x-pv.empty-state judul="Belum ada peminatan yang dapat dipilih"
                        deskripsi="Periode pendaftaran belum dibuka atau peminatan belum memiliki kuota dan tarif. Pantau pengumuman pada halaman beranda." />
                </x-pv.panel>
            @else
                @php
                    $pilihanAwal = old('peminatan_periode_id', $draf['pilihan'] ?? null);
                    $trackAwal = collect($tracks)->first(fn ($t) => collect($t['peminatan'])->contains('id', (int) $pilihanAwal))['kode'] ?? '';
                @endphp
                <x-pv.panel judul="Track & peminatan"
                    x-data="{ tracks: {{ Js::from($tracks) }}, track: {{ Js::from($trackAwal) }}, pilihan: {{ Js::from((string) $pilihanAwal) }},
                              get aktif() { return this.tracks.find(t => t.kode === this.track) },
                              get peminatan() { return this.aktif ? this.aktif.peminatan : [] } }">
                    <p class="-mt-3 text-compact text-muted">Satu akun hanya dapat memiliki satu pendaftaran aktif per periode. <span class="text-error">*</span> Wajib diisi</p>
                    <form method="POST" action="{{ route('pendaftaran.store') }}" class="flex flex-col gap-5">
                        @csrf
                        <x-pv.field label="Track" name="track" type="select" wajib x-model="track" x-on:change="pilihan = ''"
                            bantuan="Peminatan yang tersedia mengikuti track yang Anda pilih.">
                            <option value="">Pilih track</option>
                            @foreach ($tracks as $t)
                                <option value="{{ $t['kode'] }}" @selected($t['kode'] === $trackAwal)>{{ $t['nama'] }}</option>
                            @endforeach
                        </x-pv.field>

                        <div x-show="aktif" x-cloak class="flex items-center gap-4 rounded-card bg-interactive p-4">
                            <span class="grid size-11 shrink-0 place-items-center rounded-control bg-surface text-compact font-bold text-link" x-text="aktif?.kode"></span>
                            <div>
                                <p class="text-body-sm font-bold text-ink" x-text="aktif?.nama"></p>
                                <p class="text-compact text-muted" x-text="`${peminatan.length} peminatan tersedia pada periode ini`"></p>
                            </div>
                        </div>

                        <x-pv.field label="Peminatan" name="peminatan_periode_id" type="select" wajib x-model="pilihan" x-bind:disabled="!aktif">
                            <option value="">Pilih peminatan</option>
                            <template x-for="p in peminatan" x-bind:key="p.id">
                                <option x-bind:value="p.id" x-text="`${p.nama} · ${p.durasi} semester`" x-bind:selected="String(p.id) === pilihan"></option>
                            </template>
                        </x-pv.field>

                        <p class="flex items-center gap-2 text-caption text-success"><x-pv.ikon nama="circle-check" :ukuran="14" />
                            {{ $draf ? 'Nomor pendaftaran '.$draf['nomor'].' tetap sama bila pilihan diubah.' : 'Nomor pendaftaran dibuat saat pilihan disimpan.' }}</p>

                        <div class="flex flex-wrap justify-between gap-3 border-t border-line pt-5">
                            {{-- Batal saat mengubah Draf = kembali ke formulir tanpa mengubah pilihan --}}
                            <x-pv.button variant="secondary" :href="$draf ? route('pendaftaran.formulir', $draf['nomor']) : route('dasbor')" class="min-w-36">Batal</x-pv.button>
                            <x-pv.button type="submit">Simpan & lanjut ke formulir →</x-pv.button>
                        </div>
                    </form>
                </x-pv.panel>
            @endif
        </div>

        <x-pv.panel-pendaftaran :status="$draf ? 'Draf' : 'Belum disimpan'" :nomor="$draf['nomor'] ?? null" :keterangan="$periode"
            :tips="['Baca deskripsi peminatan.', 'Periksa durasi dan tarif di katalog.', 'Pilihan terkunci setelah diajukan.', 'Periksa track dan peminatan.']" />
    </div>
@endsection
