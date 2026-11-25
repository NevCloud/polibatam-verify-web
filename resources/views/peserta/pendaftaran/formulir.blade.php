{{-- HW-15 · Formulir pendaftaran, satu halaman 3 tab (FR-11–14, K-39). Figma 1562:1145 (Data Diri), 1562:1453 (Berkas), 3008:1184 (Tinjau).
     Data: PendaftaranController::formulir (kunci = fixture hw-15). Tab berpindah di klien (Alpine `tab`) agar isian tidak hilang;
     tombol Ajukan mengirim form Data Diri yang sama (formaction). Setelah Diajukan semua tab baca-saja. --}}
@extends('layouts.portal')
@section('judul', 'Formulir pendaftaran')
@section('konten')
    @php
        $draf = $pendaftaran['draf'];
        $siap = ! $kekurangan['data'] && ! $kekurangan['berkas'];
        $kembaliBerkas = route('pendaftaran.formulir', ['pendaftaran' => $pendaftaran['nomor'], 'tab' => 'berkas']);
        $tinjauBerkas = ['Terunggah' => '✓ Terunggah', 'Belum terunggah' => 'Belum diunggah', 'Opsional' => 'Opsional · tidak diunggah'];
    @endphp
    <x-pv.page-header breadcrumb="Portal Peserta / Formulir pendaftaran" judul="Formulir pendaftaran"
        deskripsi="Lengkapi data diri dan berkas, lalu tinjau dan ajukan formulir dari tab terakhir." :chip="$pendaftaran['nomor']" />

    @if (session('status'))
        <x-pv.alert nada="success" :judul="session('status')" class="mb-5" />
    @endif
    @if ($errors->has('formulir'))
        <x-pv.alert nada="error" judul="Formulir tidak dapat disimpan" class="mb-5">{{ $errors->first('formulir') }}</x-pv.alert>
    @endif
    @unless ($draf)
        <x-pv.alert :judul="'Formulir sudah '.mb_strtolower($pendaftaran['status'])" class="mb-5">
            Track, peminatan, dan data formulir terkunci dan hanya dapat dilihat. Perbaikan berkas dilakukan di halaman Berkas & revisi.
        </x-pv.alert>
    @endunless

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(0,17rem)]" x-data="{ tab: {{ Js::from($tab) }} }">
        <div class="flex min-w-0 flex-col gap-5">
            <x-pv.panel>
                <div role="tablist" aria-label="Bagian formulir" class="flex gap-1 overflow-x-auto rounded-card bg-interactive p-1.5">
                    <x-pv.tab pilih="data" :tanda="$kekurangan['data'] ? 'kurang' : 'lengkap'" class="min-w-32 flex-1">1. Data Diri</x-pv.tab>
                    <x-pv.tab pilih="berkas" :tanda="$kekurangan['berkas'] ? 'kurang' : 'lengkap'" class="min-w-32 flex-1">2. Berkas</x-pv.tab>
                    <x-pv.tab pilih="tinjau" class="min-w-40 flex-1">3. Tinjau & Ajukan</x-pv.tab>
                </div>

                {{-- Tab 1 · Data Diri --}}
                <div x-show="tab === 'data'" role="tabpanel" @if ($tab !== 'data') x-cloak @endif>
                    <form id="form-draf" method="POST" action="{{ route('pendaftaran.simpan', $pendaftaran['nomor']) }}" novalidate>
                        @csrf
                        <input type="hidden" name="tab_tujuan" x-bind:value="tab" value="{{ $tab }}">
                        <fieldset @disabled(! $draf) class="flex min-w-0 flex-col gap-5">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <h2 class="text-title font-bold text-primary">Data diri</h2>
                                    <p class="text-compact text-muted">Pastikan data sesuai dokumen identitas. <span class="text-error">*</span> Wajib diisi</p>
                                </div>
                                <p class="text-caption font-bold text-link">{{ $terisi }}</p>
                            </div>
                            <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                                <x-pv.field label="NIK" name="nik" :value="old('nik', $data['nik'])" wajib inputmode="numeric" maxlength="19" autocomplete="off"
                                    bantuan="16 digit angka sesuai KTP" />
                                <x-pv.field label="Nama lengkap" name="nama_lengkap" :value="old('nama_lengkap', $data['nama_lengkap'])" wajib maxlength="150" autocomplete="name"
                                    bantuan="Sesuai KTP, tanpa gelar" />
                                <x-pv.field label="Tempat lahir" name="tempat_lahir" :value="old('tempat_lahir', $data['tempat_lahir'])" wajib maxlength="100" />
                                <x-pv.field label="Tanggal lahir" name="tanggal_lahir" type="date" :value="old('tanggal_lahir', $data['tanggal_lahir'])" wajib max="{{ now()->toDateString() }}" />
                                @php $jk = old('jenis_kelamin', $data['jenis_kelamin']); @endphp
                                <fieldset class="flex flex-col gap-1.5" @if ($errors->has('jenis_kelamin')) aria-describedby="f-jenis_kelamin-galat" @endif>
                                    <legend class="mb-1.5 text-body-sm font-medium text-ink">Jenis kelamin<span class="text-error"> *</span></legend>
                                    <div class="flex h-control items-center gap-8">
                                        @foreach (\App\Models\Pendaftaran::JENIS_KELAMIN as $kode => $label)
                                            <label class="flex items-center gap-3 text-body-sm text-ink">
                                                <input type="radio" name="jenis_kelamin" value="{{ $kode }}" @checked($jk === $kode) class="size-4 accent-primary">{{ $label }}
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('jenis_kelamin')<p id="f-jenis_kelamin-galat" class="text-caption text-error">{{ $message }}</p>@enderror
                                </fieldset>
                                <x-pv.field label="Nomor HP" name="no_hp" type="tel" :value="old('no_hp', $data['no_hp'])" wajib inputmode="tel" maxlength="20" autocomplete="tel"
                                    bantuan="Nomor aktif yang dapat dihubungi" />
                            </div>

                            <div class="flex flex-col gap-4 border-t border-line pt-5">
                                <h2 class="text-subheading font-bold text-primary">Alamat & pendidikan</h2>
                                <x-pv.field label="Alamat" name="alamat" type="textarea" :value="old('alamat', $data['alamat'])" wajib maxlength="500" />
                                <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                                    <x-pv.field label="Asal sekolah" name="asal_sekolah" :value="old('asal_sekolah', $data['asal_sekolah'])" wajib maxlength="150" />
                                    @php $tahun = (string) old('tahun_lulus', $data['tahun_lulus']); @endphp
                                    <x-pv.field label="Tahun lulus" name="tahun_lulus" type="select" wajib>
                                        <option value="">Pilih tahun</option>
                                        @foreach (range(now()->year + 1, now()->year - 30) as $th)
                                            <option value="{{ $th }}" @selected($tahun === (string) $th)>{{ $th }}</option>
                                        @endforeach
                                    </x-pv.field>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                    @include('peserta.pendaftaran.partials.kaki-formulir', ['kembali' => null, 'lanjut' => ['berkas', 'Berikutnya: Berkas']])
                </div>

                {{-- Tab 2 · Berkas --}}
                <div x-show="tab === 'berkas'" role="tabpanel" class="flex flex-col gap-5" @if ($tab !== 'berkas') x-cloak @endif>
                    <div>
                        <h2 class="text-title font-bold text-primary">Berkas</h2>
                        <p class="text-compact text-muted">Unggah 3 berkas wajib (PDF/JPG/PNG, maks. {{ $batasUnggah }}). Bukti Pendaftaran Polibatam bersifat opsional.</p>
                    </div>
                    @if ($errors->has('berkas'))
                        <x-pv.alert nada="error" judul="Berkas tidak dapat diunggah">{{ $errors->first('berkas') }}</x-pv.alert>
                    @endif
                    <div class="grid gap-5 md:grid-cols-2">
                        @foreach ($berkas as $b)
                            @php $galat = $errors->first('berkas.'.$b['jenis']); @endphp
                            <div id="slot-{{ $b['jenis'] }}" @class(['flex min-w-0 flex-col gap-4 rounded-sheet border p-5', 'border-error-line bg-error-surface/40' => $galat, 'border-line' => ! $galat])>
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="flex items-center gap-2 text-body font-bold text-primary"><x-pv.ikon nama="file" class="text-link" />{{ $b['label'] }}</h3>
                                    <x-pv.badge-status :status="$b['status']" class="shrink-0" />
                                </div>
                                <p class="-mt-2 text-caption text-muted">PDF / JPG / PNG · Maks. {{ $batasUnggah }} · {{ $b['keterangan'] }}</p>
                                @include('peserta.pendaftaran.partials.berkas-terunggah', ['b' => $b])
                                @if ($draf)
                                    <x-pv.unggah-berkas :action="route('pendaftaran.berkas.unggah', [$pendaftaran['nomor'], $b['jenis']])" :id="'unggah-'.$b['jenis']" :kembali="$kembaliBerkas"
                                        :label="$b['berkas'] ? 'Seret file baru untuk mengganti' : 'Seret & lepas berkas di sini'" :bantuan="$b['berkas'] ? null : 'atau pilih dari perangkat'" />
                                @endif
                                @if ($galat)<p class="text-caption text-error" role="alert">{{ $galat }}</p>@endif
                                @if ($draf && $b['berkas'])
                                    <form method="POST" action="{{ route('pendaftaran.berkas.hapus', [$pendaftaran['nomor'], $b['jenis']]) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="flex items-center gap-1.5 text-compact font-semibold text-error hover:underline"><x-pv.ikon nama="x" :ukuran="14" />Hapus</button>
                                    </form>
                                @elseif (! $b['wajib'] && ! $b['berkas'])
                                    <p class="text-caption text-muted">Dokumen opsional, tidak memengaruhi pengajuan formulir.</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @include('peserta.pendaftaran.partials.kaki-formulir', ['kembali' => ['data', '← Data Diri'], 'lanjut' => ['tinjau', 'Berikutnya: Tinjau & Ajukan']])
                </div>

                {{-- Tab 3 · Tinjau & Ajukan --}}
                <div x-show="tab === 'tinjau'" role="tabpanel" class="flex flex-col gap-5" @if ($tab !== 'tinjau') x-cloak @endif>
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <h2 class="text-title font-bold text-primary">Tinjau & ajukan</h2>
                            <p class="text-compact text-muted">Periksa ringkasan di bawah. Setelah diajukan, track, peminatan, dan data formulir terkunci.</p>
                        </div>
                        @if ($draf)<p @class(['text-caption font-bold', 'text-link' => $siap, 'text-error' => ! $siap])>{{ $siap ? 'Siap diajukan' : 'Belum lengkap' }}</p>@endif
                    </div>

                    @if ($draf && ! $siap)
                        <x-pv.alert nada="error" judul="Belum dapat diajukan">
                            <ul class="mt-1 flex flex-col gap-1">
                                @if ($kekurangan['data'])
                                    <li>Data diri: {{ implode(', ', $kekurangan['data']) }} — <button type="button" class="font-bold text-link hover:underline" x-on:click="tab = 'data'">lengkapi</button></li>
                                @endif
                                @if ($kekurangan['berkas'])
                                    <li>Berkas wajib: {{ implode(', ', $kekurangan['berkas']) }} — <button type="button" class="font-bold text-link hover:underline" x-on:click="tab = 'berkas'">unggah</button></li>
                                @endif
                            </ul>
                        </x-pv.alert>
                    @endif

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-body font-bold text-primary">Pilihan</h3>
                            @if ($draf)<a href="{{ route('pendaftaran.baru') }}" class="text-caption font-bold text-link hover:underline">Ubah pilihan</a>@endif
                        </div>
                        <x-pv.daftar-info :garis="false" :baris="['Nomor pendaftaran' => $pendaftaran['nomor'], 'Track' => $pendaftaran['track'], 'Peminatan' => $pendaftaran['peminatan'], 'Periode' => $pendaftaran['periode']]" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <h3 class="text-body font-bold text-primary">Data diri</h3>
                        <x-pv.daftar-info :garis="false" :baris="$tinjau" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <h3 class="text-body font-bold text-primary">Berkas</h3>
                        <x-pv.daftar-info :garis="false" :baris="collect($berkas)->mapWithKeys(fn ($b) => [$b['label'] => $tinjauBerkas[$b['status']] ?? $b['status']])->all()" />
                    </div>

                    @if ($draf)
                        <p class="text-caption text-muted">Dengan mengajukan, saya menyatakan data dan berkas benar. Tagihan formulir (tarif formulir peminatan + kode unik) terbit setelah pengajuan dan dapat langsung dibayar.</p>
                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5">
                            <x-pv.button variant="secondary" x-on:click="tab = 'berkas'" class="min-w-36">← Berkas</x-pv.button>
                            <x-pv.button type="submit" form="form-draf" formaction="{{ route('pendaftaran.ajukan', $pendaftaran['nomor']) }}" class="min-w-60">Ajukan formulir</x-pv.button>
                        </div>
                    @endif
                </div>
            </x-pv.panel>

            <x-pv.alert judul="Lanjutkan tanpa kehilangan data">Pindah tab kapan saja tanpa kehilangan isian. Formulir hanya dapat diajukan dari tab Tinjau & Ajukan; bila ada yang belum lengkap, sistem membuka tab yang perlu dilengkapi.</x-pv.alert>
        </div>

        <x-pv.panel-pendaftaran :status="$draf ? 'Draf · '.($siap ? 'Siap diajukan' : 'Belum lengkap') : $pendaftaran['status']" :nomor="$pendaftaran['nomor']"
            :keterangan="$pendaftaran['track'].' · '.$pendaftaran['peminatan'].' · '.$pendaftaran['periode']" :ubah="$draf ? route('pendaftaran.baru') : null"
            :tips="['Gunakan data sesuai KTP.', 'Pastikan nomor HP aktif.', 'Isi seluruh kolom bertanda *.', 'Periksa track dan peminatan.']" />
    </div>
@endsection
