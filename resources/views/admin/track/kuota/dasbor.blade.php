{{-- HW-37 · Dasbor kuota (FR-49, AC-24.1/24.2, BR-75: hanya admin). Figma 3048:2282; kolom Daftar Tunggu & Min/Maks diganti
     Diajukan & Kuota minimum (K-42, T-28). Data: Track\KuotaController::dasbor (kunci = fixture hw-37). --}}
@extends('layouts.portal')
@section('judul', 'Dasbor Kuota')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Admin / Dasbor Kuota" judul="Dasbor kuota"
        deskripsi="Keterisian per peminatan. Penanda peringatan tampil H-7 sebelum periode ditutup bila NPD Terbit di bawah kuota minimum."
        :chip="$periode ? ($periode['ditutup'] ? 'Periode ditutup' : 'H-'.$periode['sisa_hari'].' penutupan') : null">
        @if ($periode)
            <x-slot:aksi>
                <form method="GET" action="{{ route('admin.kuota.dasbor') }}">
                    <label class="flex h-10 items-center gap-1 rounded-control border border-line bg-surface px-3 text-caption font-semibold text-ink">
                        Periode:
                        <select name="periode" class="bg-transparent font-normal text-ink focus:outline-none" x-data x-on:change="$el.form.requestSubmit()">
                            @foreach ($periodeTersedia as $id => $nama)
                                <option value="{{ $id }}" @selected($periode['id'] === $id)>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </label>
                </form>
                <span class="text-caption text-muted">{{ $periode['rentang'] }}</span>
            </x-slot:aksi>
        @endif
    </x-pv.page-header>

    @if (! $periode)
        <x-pv.empty-state judul="Belum ada periode pendaftaran" deskripsi="Dasbor tampil setelah Super Admin membuat periode pendaftaran." />
    @else
        <div class="mb-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-pv.stat-card label="Diajukan" :nilai="(string) $jumlah['diajukan']" keterangan="menunggu verifikasi berkas" />
            <x-pv.stat-card label="Masuk Kuota" :nilai="(string) $jumlah['masuk_kuota']" keterangan="berkas valid, belum lunas" />
            <x-pv.stat-card label="NPD Terbit" :nilai="(string) $jumlah['npd_terbit']" keterangan="sudah lunas formulir" />
            <x-pv.stat-card label="Di bawah minimum" :nilai="(string) $jumlah['di_bawah']" keterangan="peminatan bertanda peringatan" />
        </div>

        <x-pv.tabel :kolom="['Peminatan', 'Diajukan', 'Masuk Kuota', 'NPD Terbit', 'Kuota minimum', 'NPD / minimum', 'Penanda']">
            @forelse ($baris as $b)
                <tr>
                    <td class="px-6 py-4"><span class="block text-body-sm font-semibold text-ink">{{ $b['peminatan'] }}</span><span class="text-caption text-muted">{{ $b['track'] }}</span></td>
                    <td class="px-6 py-4 text-body-sm text-ink">{{ $b['diajukan'] }}</td>
                    <td class="px-6 py-4 text-body-sm text-ink">{{ $b['masuk_kuota'] }}</td>
                    <td class="px-6 py-4 text-body-sm font-semibold text-ink">{{ $b['npd_terbit'] }}</td>
                    <td class="px-6 py-4 text-body-sm text-ink">{{ $b['kuota_min'] }}</td>
                    <td class="px-6 py-4 text-body-sm text-ink">{{ $b['persen'] === null ? '—' : $b['persen'].'%' }}</td>
                    <td class="px-6 py-4"><x-pv.badge :nada="$b['nada']">{{ $b['penanda'] }}</x-pv.badge></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-6 py-10"><x-pv.empty-state judul="Belum ada peminatan dibuka" deskripsi="Atur kuota minimum peminatan pada periode ini di menu Kuota per Periode." /></td></tr>
            @endforelse
        </x-pv.tabel>
        <p class="mt-3 text-caption text-muted">Informasi kuota hanya untuk admin; tidak ditampilkan kepada calon peserta (BR-75). Keputusan pembatalan peminatan di bawah minimum diambil Super Admin di Evaluasi Kuota.</p>
    @endif
@endsection
