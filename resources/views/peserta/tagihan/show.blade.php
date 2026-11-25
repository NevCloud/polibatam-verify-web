{{-- HW-18 · Detail tagihan & unggah bukti (FR-29, FR-30, BR-36, BR-37, BR-71, BR-72). Figma 1562:1662.
     Data: Peserta\TagihanController::show (kunci = fixture hw-18). Rekening = snapshot saat tagihan terbit.
     Unggah hanya untuk Belum Bayar/Ditolak; data pengiriman wajib; selisih nominal hanya peringatan. --}}
@extends('layouts.portal')
@section('judul', 'Detail tagihan')
@section('konten')
    <x-pv.page-header :breadcrumb="'Portal Peserta / Tagihan / '.$tagihan['kode']" :judul="'Tagihan '.mb_strtolower($tagihan['jenis'])"
        :deskripsi="$tagihan['jenis_kode'] === 'formulir' ? 'Tagihan terbit saat formulir diajukan. Anda dapat membayar sekarang; pembayaran disahkan Admin Keuangan setelah berkas Anda Masuk Kuota.' : 'Bayar sebelum jatuh tempo agar status semester Anda aktif.'" />

    @if (session('status'))<x-pv.alert nada="success" :judul="session('status')" class="mb-5" />@endif
    @if (session('peringatan'))<x-pv.alert nada="warning" judul="Nominal berbeda dari tagihan" class="mb-5">{{ session('peringatan') }}</x-pv.alert>@endif

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(0,17rem)]">
        <div class="flex min-w-0 flex-col gap-5">
            <x-pv.panel>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-subheading font-bold text-primary">Rincian tagihan</h2>
                        <p class="font-mono text-caption text-muted">{{ $tagihan['kode'] }}</p>
                    </div>
                    <x-pv.badge-status :status="$tagihan['status']" />
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-card border border-line-accent bg-interactive p-4">
                    <div>
                        <p class="text-meta font-semibold tracking-wide text-muted uppercase">Total yang harus dibayar</p>
                        <p class="text-heading font-extrabold text-primary">{{ $tagihan['nominal'] }}</p>
                    </div>
                    <p class="rounded-control border border-line bg-surface px-3 py-1.5 text-caption text-ink">{{ $tagihan['lunas_pada'] ? 'Lunas '.$tagihan['lunas_pada'] : 'Batas pelunasan: '.$tagihan['jatuh_tempo'] }}</p>
                </div>
                <x-pv.daftar-info :baris="[
                    'Track & peminatan' => $tagihan['track'].' · '.$tagihan['peminatan'],
                    'Jenis tagihan' => $tagihan['jenis'],
                    'Tarif' => $tagihan['nominal_dasar'],
                    'Kode unik transaksi (dicocokkan melalui mutasi)' => $tagihan['kode_unik'],
                ]" />

                @if ($tagihan['rekening'])
                    <section class="flex flex-col gap-3 rounded-card border border-line p-4" aria-labelledby="judul-rekening">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 id="judul-rekening" class="text-body font-bold text-ink">Rekening tujuan Polibatam</h3>
                            <x-pv.chip>Snapshot saat tagihan terbit</x-pv.chip>
                        </div>
                        <x-pv.daftar-info :garis="false" :baris="['Nama bank' => $tagihan['rekening']['bank'], 'Atas nama' => $tagihan['rekening']['atas_nama']]" />
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-control bg-canvas p-3" x-data="{ disalin: false }">
                            <div>
                                <p class="text-caption text-muted">Nomor rekening</p>
                                <p class="font-mono text-title font-bold tracking-wider text-ink">{{ $tagihan['rekening']['nomor'] }}</p>
                            </div>
                            <x-pv.button variant="secondary" class="h-10!" x-on:click="navigator.clipboard.writeText('{{ $tagihan['rekening']['nomor'] }}'); disalin = true"
                                x-text="disalin ? 'Tersalin ✓' : 'Salin No Rekening'">Salin No Rekening</x-pv.button>
                        </div>
                    </section>
                @endif
                <x-pv.alert nada="warning">Transfer <strong>tepat {{ $tagihan['nominal'] }}</strong> (termasuk kode unik). Admin Keuangan memverifikasi pembayaran melalui mutasi rekening.</x-pv.alert>
                @if ($menungguBerkas)
                    <x-pv.alert>Pembayaran dapat dilakukan sekarang, tetapi baru disahkan setelah berkas Anda dinyatakan valid (Masuk Kuota).</x-pv.alert>
                @endif
            </x-pv.panel>

            @if ($tagihan['bisa_bayar'])
                <x-pv.panel judul="Unggah bukti transfer">
                    <p class="-mt-3 text-compact text-muted">Unggah struk atau bukti transfer (PDF/JPG/PNG, maks. {{ $batasUnggah }}) dan isi data pengiriman.</p>
                    @if ($errors->has('berkas'))<x-pv.alert nada="error" :judul="$errors->first('berkas')" />@endif
                    <form method="POST" action="{{ route('tagihan.unggah', $tagihan['kode']) }}" enctype="multipart/form-data" class="flex flex-col gap-4" novalidate
                          x-data="{ nominal: {{ Js::from((string) old('nominal_transfer', '')) }}, total: {{ $tagihan['nominal_total_angka'] }},
                                    get selisih() { const n = parseInt(String(this.nominal).replace(/\D/g, '')); return n > 0 && n !== this.total } }">
                        @csrf
                        <label class="flex cursor-pointer flex-col items-center gap-2 rounded-card border border-dashed border-line-strong bg-canvas p-6 text-center focus-within:outline-2 focus-within:outline-primary">
                            <span class="text-compact font-semibold text-ink">Pilih berkas bukti transfer</span>
                            <span class="text-caption text-muted">JPG, PNG, atau PDF · maks. {{ $batasUnggah }}</span>
                            <input type="file" name="berkas" required accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="text-caption text-ink">
                        </label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-pv.field label="Tanggal transfer" name="tanggal_transfer" type="date" :value="old('tanggal_transfer')" wajib max="{{ now()->toDateString() }}" />
                            <x-pv.field label="Nama pemilik rekening pengirim" name="nama_pengirim" :value="old('nama_pengirim', $tagihan['nama'])" wajib maxlength="150" />
                            <x-pv.field label="Bank pengirim" name="bank_pengirim" :value="old('bank_pengirim')" wajib maxlength="100" placeholder="mis. Bank BNI" />
                            <x-pv.field label="Nominal ditransfer (Rp)" name="nominal_transfer" inputmode="numeric" wajib x-model="nominal" :placeholder="$tagihan['nominal']" />
                        </div>
                        <x-pv.alert nada="warning" x-show="selisih" x-cloak>Nominal berbeda dari tagihan ({{ $tagihan['nominal'] }}). Bukti tetap dapat dikirim, tetapi pastikan nominal benar.</x-pv.alert>
                        <p class="text-caption text-muted">Bukti transfer hanya bahan pendukung. Pembayaran diverifikasi dengan mutasi rekening.</p>
                        <div class="flex flex-wrap justify-end gap-3 border-t border-line pt-4">
                            <x-pv.button variant="secondary" :href="route('tagihan.index')">Batal</x-pv.button>
                            <x-pv.button type="submit">Kirim Bukti Pembayaran</x-pv.button>
                        </div>
                    </form>
                </x-pv.panel>
            @endif

            <x-pv.panel judul="Riwayat bukti pembayaran">
                @forelse ($bukti as $b)
                    <div class="flex flex-col gap-1 border-b border-line pb-3 last:border-0 last:pb-0">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-compact font-semibold text-ink">Diunggah {{ $b['diunggah'] }}</p>
                            <x-pv.badge-status :status="$b['keputusan']" />
                        </div>
                        <p class="text-caption text-muted">Transfer {{ $b['tanggal_transfer'] }} · {{ $b['nominal_transfer'] }} · {{ $b['bank_pengirim'] }} a.n. {{ $b['nama_pengirim'] }}</p>
                        @if ($b['alasan'])<p class="text-caption text-error">Alasan: {{ $b['alasan'] }}</p>@endif
                        <a href="{{ $b['url'] }}" target="_blank" rel="noopener" class="self-start text-caption font-bold text-link hover:underline">Lihat bukti →</a>
                    </div>
                @empty
                    <p class="text-compact text-muted">Belum ada bukti yang diunggah. Unggah ulang tersedia selama tagihan berstatus Belum Bayar atau Ditolak.</p>
                @endforelse
            </x-pv.panel>

            @if ($tagihan['jenis_kode'] === 'formulir')
                <x-pv.alert judul="Tagihan Semester 1">Tagihan Semester 1 terbit otomatis setelah NPD terbit (tagihan formulir Lunas), dengan jatuh tempo H-7 sebelum semester dimulai.</x-pv.alert>
            @endif
        </div>

        <x-pv.panel-pendaftaran :status="$pendaftaran['status']" :nomor="$pendaftaran['nomor']"
            :keterangan="$pendaftaran['track'].' · '.$pendaftaran['peminatan'].' · '.$pendaftaran['periode']"
            :tips="['Tunggu verifikasi mutasi rekening.', 'Lunas disahkan setelah Masuk Kuota.', 'NPD terbit saat tagihan formulir Lunas.', 'Tagihan Semester 1 terbit bersama NPD.']" />
    </div>
@endsection
