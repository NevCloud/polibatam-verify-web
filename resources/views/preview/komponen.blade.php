{{-- Galeri FE-0: semua komponen x-pv.* dan variannya untuk tinjauan visual (/_preview/komponen). Data dari design-memory §9. --}}
@extends('layouts.portal')
@section('judul', 'Komponen PV')
@section('konten')
    <x-pv.page-header judul="Komponen PV" breadcrumb="Preview / FE-0" deskripsi="Semua komponen x-pv.* dalam satu halaman, sesuai Figma bagian 10 · Komponen layar (web)." chip="Gelombang 2 · 2026" />

    @isset($daftarPeran)
        <div class="mb-8 flex flex-wrap items-center gap-2">
            <span class="text-body-sm text-muted">Lihat sidebar sebagai:</span>
            @foreach ($daftarPeran as $p)
                <x-pv.tab :href="route('preview.komponen', ['peran' => $p])" :aktif="$p === $portal->peran">{{ $p }}</x-pv.tab>
            @endforeach
        </div>
        @if ($portal->peran === 'Peserta Didik')
            <x-pv.alert class="mb-8" judul="Sidebar Peserta Didik = sidebar Peserta">Satu role `peserta` (A-72); Figma hanya punya Role=Peserta. Bedanya hanya chip peran berwarna hijau.</x-pv.alert>
        @endif
    @endisset

    <section class="space-y-8">
        <div>
            <h2 class="mb-3 text-subheading font-semibold">Tombol</h2>
            <div class="flex flex-wrap items-center gap-3">
                <x-pv.button>Ajukan Formulir</x-pv.button>
                <x-pv.button variant="secondary">Terapkan Filter</x-pv.button>
                <x-pv.button variant="danger">Konfirmasi pembatalan</x-pv.button>
                <x-pv.button variant="link" href="#">Lihat detail ↗</x-pv.button>
            </div>
        </div>

        <div>
            <h2 class="mb-3 text-subheading font-semibold">Badge status resmi & chip</h2>
            <div class="flex flex-wrap gap-2">
                @foreach (['Draf', 'Diajukan', 'Perlu Revisi', 'Masuk Kuota', 'NPD Terbit', 'Dibatalkan', 'Valid', 'Lunas', 'Belum terbit'] as $s)
                    <x-pv.badge-status :status="$s" />
                @endforeach
                <x-pv.badge nada="info">Teknik Informatika</x-pv.badge>
                <x-pv.chip>Calon Peserta</x-pv.chip>
                <x-pv.chip nada="success">Peserta Didik</x-pv.chip>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <x-pv.alert nada="info" judul="Lengkapi berkas wajib">2 dari 3 berkas wajib terunggah.</x-pv.alert>
            <x-pv.alert nada="warning" judul="Tautan kedaluwarsa?">Minta tautan baru. Permintaan dibatasi untuk keamanan.</x-pv.alert>
            <x-pv.alert nada="error" judul="Sudah diverifikasi admin lain">Muat ulang untuk melihat keputusan terbaru.</x-pv.alert>
            <x-pv.alert nada="success" judul="Formulir diajukan">Nomor pendaftaran PV-2026-000426.</x-pv.alert>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <x-pv.field label="Nama lengkap" name="nama_lengkap" value="Nabila Putri Ramadhani" wajib />
            <x-pv.field label="Password baru" name="password" type="password" value="rahasia123" bantuan="Minimal 8 karakter, huruf dan angka" />
            <x-pv.field label="NIK" name="nik" value="2171" galat="NIK harus 16 digit angka." />
            <x-pv.field label="Track" name="track" type="select">
                <option>Teknik Informatika</option><option>Teknik Mesin</option>
            </x-pv.field>
            <x-pv.field label="Alasan penolakan" name="alasan" type="textarea" bantuan="Wajib diisi bila dokumen ditolak." class="md:col-span-2" />
        </div>

        <div>
            <h2 class="mb-3 text-subheading font-semibold">Tab & tahapan</h2>
            <div class="flex flex-wrap gap-2">
                <x-pv.tab aktif tanda="lengkap">Data Diri</x-pv.tab>
                <x-pv.tab tanda="kurang">Berkas</x-pv.tab>
                <x-pv.tab>Tinjau &amp; Ajukan</x-pv.tab>
            </div>
            <div class="mt-5 flex flex-wrap gap-6">
                <x-pv.step nomor="1" label="Formulir Diajukan" state="selesai" />
                <x-pv.step nomor="2" label="Verifikasi Berkas" state="aktif" />
                <x-pv.step nomor="3" label="Masuk Kuota" />
                <x-pv.step nomor="4" label="Verifikasi Pembayaran Formulir" />
                <x-pv.step nomor="5" label="NPD Terbit" />
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <x-pv.stat-card label="Total lunas" :nilai="\App\Support\Ui\Format::rupiah(6800413)" keterangan="2 tagihan" />
            <x-pv.stat-card label="Pendaftaran diajukan" nilai="486" keterangan="37 Draf tidak ditampilkan" />
            <x-pv.stat-card label="Sisa kuota" nilai="1" keterangan="Teknik Mesin · Gelombang 2" />
        </div>

        <x-pv.empty-state judul="Belum ada tagihan" deskripsi="Tagihan formulir terbit setelah formulir diajukan.">
            <x-slot:ikon><x-pv.ikon nama="card" :ukuran="32" /></x-slot:ikon>
        </x-pv.empty-state>
    </section>
@endsection
