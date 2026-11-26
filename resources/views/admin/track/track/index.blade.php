{{-- HW-33 · Track (FR-45, BR-52, BR-53). Figma 1562:2138 (+ dialog edit 1562:4806). Kolom "Deskripsi" Figma tidak dibuat (skema tanpa deskripsi, T-28).
     Data: Track\TrackController::index (kunci = fixture hw-33). --}}
@extends('layouts.portal')
@section('judul', 'Track')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Admin / Pengaturan / Track" judul="Kelola Track" deskripsi="Tambah, ubah, dan atur status track Microcredential.">
        <x-slot:aksi>
            <span></span>
            <x-pv.button onclick="document.getElementById('track-baru').showModal()"><x-pv.ikon nama="tambah" :ukuran="11" />Tambah Track</x-pv.button>
        </x-slot:aksi>
    </x-pv.page-header>

    @if (session('status'))<x-pv.alert nada="success" :judul="session('status')" class="mb-5" />@endif
    @if ($errors->has('track'))<x-pv.alert nada="error" :judul="$errors->first('track')" class="mb-5" />@endif

    <form method="GET" action="{{ route('admin.track.index') }}" class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-control border border-line bg-surface p-4">
        <label class="relative w-full sm:w-[320px]">
            <span class="sr-only">Cari nama track</span>
            <x-pv.ikon nama="cari" :ukuran="14" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-muted" />
            <input type="search" name="q" value="{{ $filter['q'] }}" placeholder="Cari nama track..."
                   class="h-10 w-full rounded-control border border-line bg-canvas pr-3 pl-9 text-body-sm text-ink placeholder:text-muted focus:outline-2 focus:outline-primary">
        </label>
        <div class="flex items-center gap-2">
            <label class="flex h-10 items-center gap-1 rounded-control border border-line bg-canvas px-3 text-caption font-semibold text-ink">
                <span class="sr-only">Status</span>
                <select name="status" class="bg-transparent font-normal text-ink focus:outline-none" x-data x-on:change="$el.form.requestSubmit()">
                    <option value="">Semua Status</option>
                    @foreach ($statusTersedia as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($filter['status'] === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <a href="{{ route('admin.track.index') }}" class="inline-flex h-10 items-center gap-1.5 rounded-control px-3 text-body-sm font-semibold text-muted hover:text-ink">
                <x-pv.ikon nama="refresh" :ukuran="14" />Reset
            </a>
        </div>
    </form>

    <x-pv.tabel :kolom="['Kode · Nama track', 'Peminatan', 'Status', '>Aksi']">
        @forelse ($track as $t)
            <tr>
                <td class="px-6 py-4 text-body font-semibold text-ink">{{ $t['kode'] }} · {{ $t['nama'] }}</td>
                <td class="px-6 py-4 text-body-sm text-muted">{{ $t['peminatan'] }} peminatan</td>
                <td class="px-6 py-4">
                    <form method="POST" action="{{ route('admin.track.status', $t['id']) }}" class="flex items-center gap-2"
                          @if ($t['aktif']) onsubmit="return confirm('Nonaktifkan track {{ $t['nama'] }}? Peminatannya tidak tampil di katalog; data peserta tetap berjalan.')" @endif>
                        @csrf @method('PUT')
                        <input type="hidden" name="aktif" value="{{ $t['aktif'] ? 0 : 1 }}">
                        <button type="submit" role="switch" aria-checked="{{ $t['aktif'] ? 'true' : 'false' }}" aria-label="{{ $t['aktif'] ? 'Nonaktifkan' : 'Aktifkan' }} {{ $t['nama'] }}"
                                @class(['flex h-5 w-9 items-center rounded-full p-0.5 transition', 'justify-end bg-primary' => $t['aktif'], 'justify-start bg-line' => ! $t['aktif']])>
                            <span class="size-4 rounded-full bg-surface"></span>
                        </button>
                        <x-pv.badge :nada="$t['aktif'] ? 'success' : 'neutral'">{{ $t['aktif'] ? 'Aktif' : 'Nonaktif' }}</x-pv.badge>
                    </form>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-1">
                        <button type="button" class="grid size-8 place-items-center rounded-control text-muted hover:bg-canvas hover:text-ink" aria-label="Ubah {{ $t['nama'] }}"
                                onclick="document.getElementById('track-{{ $t['id'] }}').showModal()"><x-pv.ikon nama="ubah" :ukuran="14" /></button>
                        @if ($t['bisa_hapus'])
                            <form method="POST" action="{{ route('admin.track.destroy', $t['id']) }}" onsubmit="return confirm('Hapus track {{ $t['nama'] }}? Tindakan ini tidak dapat dibatalkan.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="grid size-8 place-items-center rounded-control text-muted hover:bg-error-surface hover:text-error" aria-label="Hapus {{ $t['nama'] }}"><x-pv.ikon nama="hapus" :ukuran="14" /></button>
                            </form>
                        @else
                            <span class="grid size-8 place-items-center text-line" title="Sudah memiliki peminatan: nonaktifkan saja (BR-53)"><x-pv.ikon nama="hapus" :ukuran="14" /></span>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-6 py-10"><x-pv.empty-state judul="Track tidak ditemukan" deskripsi="Ubah pencarian atau tambah track baru." /></td></tr>
        @endforelse
        <x-slot:kaki>
            <p class="text-caption text-muted">Menampilkan {{ count($track) }} dari {{ $jumlah['total'] }} track · {{ $jumlah['aktif'] }} aktif</p>
        </x-slot:kaki>
    </x-pv.tabel>

    @foreach ([['id' => 'track-baru', 'judul' => 'Tambah track', 'aksi' => route('admin.track.store'), 'metode' => 'POST', 'tas' => 'track', 't' => null],
               ...array_map(fn ($t) => ['id' => 'track-'.$t['id'], 'judul' => 'Edit track', 'aksi' => route('admin.track.update', $t['id']), 'metode' => 'PUT', 'tas' => 'track-'.$t['id'], 't' => $t], $track)] as $d)
        @php $galat = $errors->getBag($d['tas']); @endphp
        <x-pv.dialog :id="$d['id']" :judul="$d['judul']" deskripsi="Kode track harus unik dan dipakai sebagai pengenal singkat."
            x-data x-init="{{ $galat->any() ? '$el.showModal()' : '' }}">
            <form method="POST" action="{{ $d['aksi'] }}" class="flex flex-col gap-4" novalidate>
                @csrf @if ($d['metode'] === 'PUT') @method('PUT') @endif
                <x-pv.field label="Kode" name="kode" :id="$d['id'].'-kode'" :value="$d['t']['kode'] ?? ''" wajib maxlength="20" :galat="$galat->first('kode') ?: false" bantuan="Huruf kapital/angka, mis. TI" />
                <x-pv.field label="Nama track" name="nama" :id="$d['id'].'-nama'" :value="$d['t']['nama'] ?? ''" wajib maxlength="100" :galat="$galat->first('nama') ?: false" />
                <div class="flex justify-end gap-3">
                    <x-pv.button variant="secondary" onclick="this.closest('dialog').close()">Batal</x-pv.button>
                    <x-pv.button type="submit">Simpan</x-pv.button>
                </div>
            </form>
        </x-pv.dialog>
    @endforeach
@endsection
