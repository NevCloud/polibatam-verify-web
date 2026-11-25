{{-- HW-56 · Rekening institusi (FR-67, BR-70, BR-71, A-62). Figma 1562:10220 (+ dialog 1562:10500).
     Data: Keuangan\RekeningController::index (kunci = fixture hw-56). Tidak ada hapus (rekening terpakai wajib disimpan). --}}
@extends('layouts.portal')
@section('judul', 'Rekening Institusi')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Keuangan / Rekening Institusi" judul="Kelola rekening institusi"
        deskripsi="Rekening resmi Polibatam yang ditampilkan kepada peserta untuk pembayaran. Satu rekening aktif per jenis tagihan.">
        <x-slot:aksi>
            <span></span>
            <x-pv.button onclick="document.getElementById('rekening-baru').showModal()"><x-pv.ikon nama="tambah" :ukuran="11" />Tambah Rekening</x-pv.button>
        </x-slot:aksi>
    </x-pv.page-header>

    @if (session('status'))<x-pv.alert nada="success" :judul="session('status')" class="mb-5" />@endif

    <div class="mb-5 grid gap-5 sm:grid-cols-3">
        <x-pv.stat-card label="Total rekening" :nilai="(string) $jumlah['total']" />
        <x-pv.stat-card label="Rekening aktif" :nilai="(string) $jumlah['aktif']" keterangan="maks. 1 per jenis tagihan" />
        <x-pv.stat-card label="Nonaktif" :nilai="(string) ($jumlah['total'] - $jumlah['aktif'])" />
    </div>

    <x-pv.tabel :kolom="['Bank', 'Nomor rekening', 'Atas nama', 'Jenis tagihan', 'Status', '>Aksi']">
        @forelse ($rekening as $r)
            <tr>
                <td class="px-6 py-4 text-body-sm font-semibold text-ink">{{ $r['bank'] }}</td>
                <td class="px-6 py-4"><span class="rounded-control bg-interactive px-2 py-1 font-mono text-body-sm text-ink">{{ $r['nomor'] }}</span></td>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $r['atas_nama'] }}<span class="block text-caption text-muted">{{ $r['dipakai'] ? 'Pernah dipakai tagihan · tidak dapat dihapus' : 'Belum pernah dipakai' }}</span></td>
                <td class="px-6 py-4 text-body-sm text-ink">{{ $r['jenis_label'] }}</td>
                <td class="px-6 py-4"><x-pv.badge :nada="$r['aktif'] ? 'success' : 'neutral'">{{ $r['aktif'] ? 'Aktif' : 'Nonaktif' }}</x-pv.badge></td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-3 text-body-sm">
                        <form method="POST" action="{{ route('admin.rekening.status', $r['id']) }}"
                              @unless ($r['aktif']) onsubmit="return confirm('Aktifkan rekening ini untuk tagihan {{ mb_strtolower($r['jenis_label']) }}? Rekening aktif sebelumnya untuk jenis yang sama akan dinonaktifkan.')" @endunless>
                            @csrf @method('PUT')
                            <input type="hidden" name="aktif" value="{{ $r['aktif'] ? 0 : 1 }}">
                            <button type="submit" class="font-bold text-link hover:underline">{{ $r['aktif'] ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                        <button type="button" class="font-bold text-link hover:underline" onclick="document.getElementById('rekening-{{ $r['id'] }}').showModal()">Ubah</button>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-6 py-10"><x-pv.empty-state judul="Belum ada rekening institusi" deskripsi="Tagihan tidak dapat terbit sebelum ada rekening aktif untuk jenisnya." /></td></tr>
        @endforelse
    </x-pv.tabel>

    <x-pv.alert judul="Informasi penting" class="mt-5">Mengganti rekening aktif meminta konfirmasi. Tagihan yang sudah terbit tetap memakai rekening lama (snapshot); semua perubahan tercatat di audit log.</x-pv.alert>

    @foreach ([['id' => 'rekening-baru', 'judul' => 'Tambah rekening', 'aksi' => route('admin.rekening.store'), 'metode' => 'POST', 'tas' => 'rekening', 'r' => null],
               ...array_map(fn ($r) => ['id' => 'rekening-'.$r['id'], 'judul' => 'Ubah rekening', 'aksi' => route('admin.rekening.update', $r['id']), 'metode' => 'PUT', 'tas' => 'rekening-'.$r['id'], 'r' => $r], $rekening)] as $d)
        @php $galat = $errors->getBag($d['tas']); @endphp
        <x-pv.dialog :id="$d['id']" :judul="$d['judul']" deskripsi="Rekening baru berstatus Nonaktif; aktifkan dari daftar bila sudah siap."
            x-data x-init="{{ $galat->any() ? '$el.showModal()' : '' }}">
            <form method="POST" action="{{ $d['aksi'] }}" class="flex flex-col gap-4" novalidate>
                @csrf @if ($d['metode'] === 'PUT') @method('PUT') @endif
                <x-pv.field label="Jenis tagihan" name="jenis_tagihan" type="select" :id="$d['id'].'-jenis'" wajib :galat="$galat->first('jenis_tagihan') ?: false">
                    @foreach ($jenisTersedia as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(($d['r']['jenis'] ?? 'formulir') === $nilai)>{{ $label }}</option>
                    @endforeach
                </x-pv.field>
                <x-pv.field label="Nama bank" name="nama_bank" :id="$d['id'].'-bank'" :value="$d['r']['bank'] ?? ''" wajib maxlength="100" :galat="$galat->first('nama_bank') ?: false" />
                <x-pv.field label="Nomor rekening" name="nomor_rekening" :id="$d['id'].'-nomor'" :value="$d['r']['nomor'] ?? ''" wajib inputmode="numeric" maxlength="30" :galat="$galat->first('nomor_rekening') ?: false" />
                <x-pv.field label="Atas nama" name="atas_nama" :id="$d['id'].'-nama'" :value="$d['r']['atas_nama'] ?? 'Politeknik Negeri Batam'" wajib maxlength="150" :galat="$galat->first('atas_nama') ?: false" />
                <div class="flex justify-end gap-3">
                    <x-pv.button variant="secondary" onclick="this.closest('dialog').close()">Batal</x-pv.button>
                    <x-pv.button type="submit">Simpan</x-pv.button>
                </div>
            </form>
        </x-pv.dialog>
    @endforeach
@endsection
