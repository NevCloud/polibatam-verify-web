{{-- HW-34 · Peminatan (FR-46, BR-43, BR-52, BR-53). Figma 1562:4270 "Peminatan & kuota" (+ dialog 1562:3911).
     K-42: kolom kuota maksimum/terisi/sisa & teks Daftar Tunggu Figma tidak dibuat; kuota minimum per periode ada di HW-36 (T-28).
     Data: Track\PeminatanController::index (kunci = fixture hw-34). --}}
@extends('layouts.portal')
@section('judul', 'Peminatan')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Admin / Pengaturan / Peminatan" judul="Kelola Peminatan" deskripsi="Atur peminatan, track induk, dan durasi semester Microcredential.">
        <x-slot:aksi>
            <span></span>
            <x-pv.button onclick="document.getElementById('peminatan-baru').showModal()"><x-pv.ikon nama="tambah" :ukuran="11" />Tambah Peminatan</x-pv.button>
        </x-slot:aksi>
    </x-pv.page-header>

    @if (session('status'))<x-pv.alert nada="success" :judul="session('status')" class="mb-5" />@endif
    @foreach (['peminatan', 'kode'] as $k)
        @if ($errors->has($k))<x-pv.alert nada="error" :judul="$errors->first($k)" class="mb-5" />@endif
    @endforeach

    <div class="mb-5 grid gap-5 sm:grid-cols-3">
        <x-pv.stat-card label="Total peminatan" :nilai="(string) $jumlah['total']" />
        <x-pv.stat-card label="Peminatan aktif" :nilai="(string) $jumlah['aktif']" keterangan="tampil di katalog bila punya tarif & kuota periode" />
        <x-pv.stat-card label="Track" :nilai="(string) $jumlah['track']" />
    </div>

    <form method="GET" action="{{ route('admin.peminatan.index') }}" class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-control border border-line bg-surface p-4">
        <label class="relative w-full sm:w-[320px]">
            <span class="sr-only">Cari peminatan</span>
            <x-pv.ikon nama="cari" :ukuran="14" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-muted" />
            <input type="search" name="q" value="{{ $filter['q'] }}" placeholder="Cari nama atau kode peminatan..."
                   class="h-10 w-full rounded-control border border-line bg-canvas pr-3 pl-9 text-body-sm text-ink placeholder:text-muted focus:outline-2 focus:outline-primary">
        </label>
        <div class="flex flex-wrap items-center gap-2">
            @foreach ([['track', 'Semua Track', $trackTersedia], ['status', 'Status: Semua', $statusTersedia]] as [$nama, $semua, $opsi])
                <label class="flex h-10 items-center rounded-control border border-line bg-canvas px-3 text-body-sm text-ink">
                    <span class="sr-only">{{ $semua }}</span>
                    <select name="{{ $nama }}" class="bg-transparent focus:outline-none" x-data x-on:change="$el.form.requestSubmit()">
                        <option value="">{{ $semua }}</option>
                        @foreach ($opsi as $nilai => $label)
                            <option value="{{ $nilai }}" @selected($filter[$nama] === (string) $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @endforeach
            <a href="{{ route('admin.peminatan.index') }}" class="grid size-10 place-items-center rounded-control border border-line bg-canvas text-muted hover:text-ink" aria-label="Reset filter">
                <x-pv.ikon nama="refresh" :ukuran="14" />
            </a>
        </div>
    </form>

    <x-pv.tabel :kolom="['Nama peminatan', 'Track', 'Durasi', 'Status', '>Aksi']">
        @forelse ($peminatan as $p)
            <tr>
                <td class="px-6 py-4"><span class="block text-body-sm font-semibold text-ink">{{ $p['nama'] }}</span><span class="text-caption text-muted">{{ $p['kode'] }}</span></td>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $p['track'] }}</td>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $p['durasi'] }} semester</td>
                <td class="px-6 py-4">
                    <form method="POST" action="{{ route('admin.peminatan.status', $p['id']) }}" class="flex items-center gap-2"
                          @if ($p['aktif']) onsubmit="return confirm('Nonaktifkan {{ $p['nama'] }}? Tidak tampil di katalog; peserta yang sudah ada tetap berjalan.')" @endif>
                        @csrf @method('PUT')
                        <input type="hidden" name="aktif" value="{{ $p['aktif'] ? 0 : 1 }}">
                        <button type="submit" role="switch" aria-checked="{{ $p['aktif'] ? 'true' : 'false' }}" aria-label="{{ $p['aktif'] ? 'Nonaktifkan' : 'Aktifkan' }} {{ $p['nama'] }}"
                                @class(['flex h-5 w-9 items-center rounded-full p-0.5 transition', 'justify-end bg-primary' => $p['aktif'], 'justify-start bg-line' => ! $p['aktif']])>
                            <span class="size-4 rounded-full bg-surface"></span>
                        </button>
                        <x-pv.badge :nada="$p['aktif'] ? 'success' : 'neutral'">{{ $p['aktif'] ? 'Aktif' : 'Nonaktif' }}</x-pv.badge>
                    </form>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ $p['tarif'] }}" class="mr-2 text-body-sm font-bold text-link hover:underline">Tarif</a>
                        <button type="button" class="grid size-8 place-items-center rounded-control text-muted hover:bg-canvas hover:text-ink" aria-label="Ubah {{ $p['nama'] }}"
                                onclick="document.getElementById('peminatan-{{ $p['id'] }}').showModal()"><x-pv.ikon nama="ubah" :ukuran="14" /></button>
                        @if ($p['bisa_hapus'])
                            <form method="POST" action="{{ route('admin.peminatan.destroy', $p['id']) }}" onsubmit="return confirm('Hapus peminatan {{ $p['nama'] }}? Tindakan ini tidak dapat dibatalkan.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="grid size-8 place-items-center rounded-control text-muted hover:bg-error-surface hover:text-error" aria-label="Hapus {{ $p['nama'] }}"><x-pv.ikon nama="hapus" :ukuran="14" /></button>
                            </form>
                        @else
                            <span class="grid size-8 place-items-center text-line" title="Sudah dipakai pendaftaran atau bertarif: nonaktifkan saja (BR-53)"><x-pv.ikon nama="hapus" :ukuran="14" /></span>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-6 py-10"><x-pv.empty-state judul="Peminatan tidak ditemukan" deskripsi="Ubah filter atau tambah peminatan baru." /></td></tr>
        @endforelse
        <x-slot:kaki>
            <p class="text-caption text-muted">Menampilkan {{ count($peminatan) }} dari {{ $jumlah['total'] }} peminatan</p>
        </x-slot:kaki>
    </x-pv.tabel>

    <x-pv.alert judul="Peminatan tampil di katalog bila" class="mt-5">aktif, track induknya aktif, sudah punya tarif formulir yang berlaku, dan sudah diberi kuota minimum pada periode yang sedang dibuka (menu Kuota per Periode).</x-pv.alert>

    @foreach ([['id' => 'peminatan-baru', 'judul' => 'Tambah peminatan', 'aksi' => route('admin.peminatan.store'), 'metode' => 'POST', 'tas' => 'peminatan', 'p' => null],
               ...array_map(fn ($p) => ['id' => 'peminatan-'.$p['id'], 'judul' => 'Edit peminatan', 'aksi' => route('admin.peminatan.update', $p['id']), 'metode' => 'PUT', 'tas' => 'peminatan-'.$p['id'], 'p' => $p], $peminatan)] as $d)
        @php $galat = $errors->getBag($d['tas']); @endphp
        <x-pv.dialog :id="$d['id']" :judul="$d['judul']" deskripsi="Durasi baru hanya berlaku untuk pendaftar berikutnya; kode dipakai pada format NPD."
            x-data x-init="{{ $galat->any() ? '$el.showModal()' : '' }}">
            <form method="POST" action="{{ $d['aksi'] }}" class="flex flex-col gap-4" novalidate>
                @csrf @if ($d['metode'] === 'PUT') @method('PUT') @endif
                <x-pv.field label="Track" name="track_id" type="select" :id="$d['id'].'-track'" wajib :galat="$galat->first('track_id') ?: false">
                    <option value="">Pilih track</option>
                    @foreach ($trackTersedia as $id => $label)
                        <option value="{{ $id }}" @selected(($d['p']['track_id'] ?? null) === $id)>{{ $label }}</option>
                    @endforeach
                </x-pv.field>
                <x-pv.field label="Kode" name="kode" :id="$d['id'].'-kode'" :value="$d['p']['kode'] ?? ''" wajib maxlength="20" :galat="$galat->first('kode') ?: false" bantuan="Unik, mis. TRPL (dipakai di NPD)" />
                <x-pv.field label="Nama peminatan" name="nama" :id="$d['id'].'-nama'" :value="$d['p']['nama'] ?? ''" wajib maxlength="150" :galat="$galat->first('nama') ?: false" />
                <x-pv.field label="Durasi (semester)" name="durasi_semester" type="number" min="1" max="20" :id="$d['id'].'-durasi'" :value="(string) ($d['p']['durasi'] ?? 2)" wajib :galat="$galat->first('durasi_semester') ?: false" />
                <div class="flex justify-end gap-3">
                    <x-pv.button variant="secondary" onclick="this.closest('dialog').close()">Batal</x-pv.button>
                    <x-pv.button type="submit">Simpan</x-pv.button>
                </div>
            </form>
        </x-pv.dialog>
    @endforeach
@endsection
