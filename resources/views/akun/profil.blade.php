{{-- HW-12 · Profil & keamanan akun (FR-03, FR-59, K-39). Figma 1562:5819.
     Data: ProfilController::show (kunci = fixture hw-12). Email & peran hanya-baca; nomor telepon tidak ada di skema (A-79). --}}
@extends('layouts.portal')
@section('judul', 'Profil')
@section('konten')
    <x-pv.page-header breadcrumb="Beranda › Profil" judul="Profil Pengguna" deskripsi="Kelola informasi akun dan keamanan Anda." />

    @if (session('status'))
        <x-pv.alert nada="success" :judul="session('status')" class="mb-4" />
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="flex flex-col gap-5 rounded-card border border-line bg-surface p-6" aria-labelledby="judul-info">
            <header class="flex items-center justify-between border-b border-line pb-4">
                <h2 id="judul-info" class="flex items-center gap-2 text-title font-semibold text-ink"><x-pv.ikon nama="user" />Informasi Akun</h2>
                <span class="text-caption font-semibold tracking-wide text-muted uppercase">Data utama</span>
            </header>
            <div class="flex items-center gap-4 border-b border-line pb-5">
                <span class="grid size-16 shrink-0 place-items-center rounded-card bg-primary text-title font-bold text-white" aria-hidden="true">{{ $akun['inisial'] }}</span>
                <div class="flex flex-col gap-1">
                    <p class="flex flex-wrap items-center gap-2 text-subheading font-bold text-ink">{{ $akun['nama'] }} <x-pv.chip :nada="$portal->nadaPeran">{{ $akun['peran'] }}</x-pv.chip></p>
                    <p class="flex items-center gap-1.5 text-caption text-muted"><x-pv.ikon nama="calendar" :ukuran="14" />Bergabung sejak {{ $akun['bergabung'] }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('akun.profil.update') }}" class="flex flex-col gap-4">
                @csrf @method('PUT')
                <x-pv.field label="Nama Lengkap" name="name" :value="old('name', $akun['nama'])" wajib maxlength="150" />
                <div class="flex flex-col gap-1.5">
                    <x-pv.field label="Email" name="email_tampil" :value="$akun['email']" readonly />
                    @if ($akun['email_terverifikasi'])
                        <x-pv.badge nada="success" class="self-start">Terverifikasi</x-pv.badge>
                    @endif
                </div>
                <x-pv.field label="Peran / Hak Akses" name="peran_tampil" :value="$akun['peran']" readonly
                    :bantuan="$akun['admin'] ? 'Peran akun dikelola Super Admin melalui menu Kelola User.' : 'Peran berubah otomatis menjadi Peserta Didik saat NPD terbit.'" />
                <x-pv.button type="submit" class="self-start">Simpan Perubahan</x-pv.button>
            </form>
        </section>

        <section class="flex flex-col gap-5 rounded-card border border-line bg-surface p-6" aria-labelledby="judul-sandi"
                 x-data="{ sandi: '', get skor() { return [this.sandi.length >= 8, /[A-Za-z]/.test(this.sandi), /[0-9]/.test(this.sandi)].filter(Boolean).length } }">
            <header class="border-b border-line pb-4">
                <h2 id="judul-sandi" class="flex items-center gap-2 text-title font-semibold text-ink"><x-pv.ikon nama="kunci" />Ubah Password</h2>
                <p class="text-caption text-muted">Perbarui kata sandi secara berkala untuk menjaga keamanan akun.</p>
            </header>
            <form method="POST" action="{{ route('akun.password.update') }}" class="flex flex-col gap-4">
                @csrf @method('PUT')
                <x-pv.field label="Password Lama" name="current_password" type="password" autocomplete="current-password" wajib :galat="$errors->sandi->first('current_password') ?: false" />
                <x-pv.field label="Password Baru" name="password" type="password" autocomplete="new-password" wajib x-model="sandi" :galat="$errors->sandi->first('password') ?: false"
                    bantuan="Minimal 8 karakter, mengandung huruf dan angka." />
                <div class="flex items-center gap-2" aria-hidden="true">
                    <span class="text-caption text-muted">Tingkat keamanan:</span>
                    @foreach ([1, 2, 3] as $segmen)
                        <span class="h-1 flex-1 rounded-full bg-line" x-bind:class="skor >= {{ $segmen }} ? (skor === 3 ? 'bg-success' : 'bg-warning') : 'bg-line'"></span>
                    @endforeach
                    <span class="w-12 text-right text-caption font-semibold" x-text="['', 'Lemah', 'Sedang', 'Kuat'][skor]"></span>
                </div>
                <x-pv.field label="Konfirmasi Password Baru" name="password_confirmation" type="password" autocomplete="new-password" wajib />
                <x-pv.alert nada="info">Perubahan password dicatat di audit log dan mengakhiri sesi aktif di perangkat lain.</x-pv.alert>
                <x-pv.button type="submit" class="self-start"><x-pv.ikon nama="key-round" :ukuran="16" />Perbarui Password</x-pv.button>
            </form>
        </section>
    </div>

    <section class="mt-6 flex flex-wrap items-center gap-3 rounded-card border border-line-accent bg-interactive px-5 py-4" aria-label="Riwayat aktivitas keamanan">
        <span class="grid size-9 place-items-center rounded-control bg-surface text-primary"><x-pv.ikon nama="history" /></span>
        <div>
            <p class="text-body-sm font-semibold text-ink">Riwayat Aktivitas Keamanan</p>
            <p class="text-caption text-muted">{{ $loginTerakhir ? 'Login berhasil sebelumnya: '.$loginTerakhir : 'Belum ada login sebelumnya yang tercatat.' }}</p>
        </div>
    </section>

    <div @class(['mt-4 grid gap-6', 'xl:grid-cols-2' => $akun['admin']])>
        @if ($akun['admin'])
            <section class="flex flex-col gap-4 rounded-card border border-line bg-surface p-6" aria-labelledby="judul-kode">
                <h2 id="judul-kode" class="text-subheading font-semibold text-ink">Recovery code 2FA</h2>
                <p class="text-body-sm text-muted">{{ $sisaKode ?? 0 }} kode cadangan tersisa. Masukkan password untuk melihat ulang atau membuat ulang (kode lama tidak berlaku).</p>
                @if ($kode)
                    <ul class="grid grid-cols-2 gap-2 rounded-control bg-canvas p-4 font-mono text-body-sm text-ink">
                        @foreach ($kode as $k)<li>{{ $k }}</li>@endforeach
                    </ul>
                @endif
                <form method="POST" action="{{ route('akun.kode-pemulihan') }}" class="flex flex-col gap-3">
                    @csrf
                    <x-pv.field id="f-sandi-kode" label="Password" name="current_password" type="password" autocomplete="current-password" wajib :galat="$errors->kode->first('current_password') ?: false" />
                    <div class="flex flex-wrap gap-3">
                        <x-pv.button type="submit" name="aksi" value="lihat" variant="secondary">Lihat Ulang Kode</x-pv.button>
                        <x-pv.button type="submit" name="aksi" value="buat_ulang" variant="danger">Buat Ulang Kode</x-pv.button>
                    </div>
                </form>
            </section>
        @endif

        <section class="flex flex-col gap-4 rounded-card border border-line bg-surface p-6" aria-labelledby="judul-sesi">
            <h2 id="judul-sesi" class="text-subheading font-semibold text-ink">Sesi aktif</h2>
            <ul class="flex flex-col divide-y divide-line">
                @forelse ($sesi as $s)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-2.5 text-body-sm">
                        <span class="text-ink">{{ $s['perangkat'] }} · IP {{ $s['ip'] }}</span>
                        <span class="text-caption text-muted">{{ $s['ini'] ? 'Sesi ini' : 'Terakhir aktif '.$s['terakhir'] }}</span>
                    </li>
                @empty
                    <li class="py-2.5 text-body-sm text-muted">Tidak ada sesi tercatat.</li>
                @endforelse
            </ul>
            <form method="POST" action="{{ route('akun.sesi.akhiri') }}" class="flex flex-col gap-3">
                @csrf
                <x-pv.field id="f-sandi-sesi" label="Password" name="current_password" type="password" autocomplete="current-password" wajib :galat="$errors->sesi->first('current_password') ?: false"
                    bantuan="Mengakhiri semua sesi web dan token aplikasi mobile selain sesi ini." />
                <x-pv.button type="submit" variant="secondary" class="self-start">Akhiri Semua Sesi Lain</x-pv.button>
            </form>
        </section>
    </div>
@endsection
