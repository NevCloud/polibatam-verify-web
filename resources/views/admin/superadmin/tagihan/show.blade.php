{{-- HW-43 · Detail & koreksi tagihan (FR-36, BR-40, A-74). Figma 3048:2705.
     Data: SuperAdmin\TagihanController::show (kunci = fixture hw-43-detail di hw-43). Koreksi hanya untuk Lunas → Menunggu Verifikasi. --}}
@extends('layouts.portal')
@section('judul', 'Koreksi tagihan')
@section('konten')
    @php $konflik = ($konflik ?? false) || session('kode_galat') === \App\Exceptions\SudahDiverifikasi::KODE; @endphp
    <x-pv.page-header :breadcrumb="'Portal Super Admin / Tagihan / '.$tagihan['kode']" judul="Koreksi tagihan"
        deskripsi="Koreksi status Lunas hanya untuk kasus khusus; alasan wajib dan tercatat di audit log." />
    <a href="{{ route('admin.tagihan.index') }}" class="mb-5 inline-block text-compact font-bold text-link hover:underline">← Kembali ke daftar tagihan</a>

    @if (session('status'))<x-pv.alert nada="success" :judul="session('status')" class="mb-5" />@endif
    @if ($konflik)<x-pv.alert nada="error" judul="Tagihan sudah diubah pihak lain" class="mb-5">Koreksi tidak disimpan. Periksa data terbaru di bawah.</x-pv.alert>@endif

    <div class="grid gap-5 xl:grid-cols-2">
        <x-pv.panel :judul="$tagihan['kode']">
            <x-slot:aksi><x-pv.badge-status :status="$tagihan['status']" /></x-slot:aksi>
            <x-pv.daftar-info :baris="[
                'Pemilik' => $tagihan['nama'].' · '.$tagihan['nomor_pendaftaran'],
                'Jenis' => $tagihan['jenis'],
                'Nominal' => $tagihan['nominal'],
                'Lunas pada' => $tagihan['lunas_pada'] ?? '—',
                'Bukti terakhir' => isset($bukti[0]) ? $bukti[0]['keputusan'].($bukti[0]['alasan'] ? ' — '.$bukti[0]['alasan'] : '') : '—',
                'Transfer' => isset($bukti[0]) ? $bukti[0]['nominal_transfer'].' · '.$bukti[0]['tanggal_transfer'] : '—',
            ]" />
            @isset($bukti[0])<a href="{{ $bukti[0]['url'] }}" target="_blank" rel="noopener" class="self-start text-caption font-bold text-link hover:underline">Lihat bukti terakhir →</a>@endisset
        </x-pv.panel>

        <x-pv.panel judul="Koreksi status">
            @if ($bisaKoreksi)
                <form method="POST" action="{{ route('admin.tagihan.koreksi', $tagihan['kode']) }}" class="flex flex-col gap-4" novalidate>
                    @csrf
                    <input type="hidden" name="versi" value="{{ $tagihan['versi'] }}">
                    <x-pv.field label="Status baru" name="status_tampil" value="Menunggu Verifikasi (bukti dicek ulang Admin Keuangan)" readonly />
                    <x-pv.field label="Alasan koreksi" name="alasan" type="textarea" :value="old('alasan')" wajib maxlength="1000"
                        bantuan="Minimal 10 karakter. Tercatat di audit log bersama nama Anda." />
                    <x-pv.alert nada="warning">NPD atau status peserta yang sudah terbit tidak dibatalkan otomatis oleh koreksi ini.</x-pv.alert>
                    <x-pv.button type="submit" class="self-end">Simpan koreksi</x-pv.button>
                </form>
            @else
                <p class="text-compact text-muted">Koreksi hanya tersedia untuk tagihan berstatus <strong>Lunas</strong> (BR-40). Tagihan Ditolak atau Belum Bayar diselesaikan lewat unggah ulang bukti dan verifikasi Admin Keuangan.</p>
            @endif
        </x-pv.panel>
    </div>
@endsection
