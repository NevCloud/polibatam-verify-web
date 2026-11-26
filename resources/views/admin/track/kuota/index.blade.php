{{-- HW-36 · Kuota minimum per periode (FR-48, BR-15, BR-20, AC-23.1–23.6, K-42). Figma 1562:4270 bagian kuota (tanpa kuota maksimum, T-28).
     Admin Track mengubah sebelum periode dimulai; setelahnya hanya Super Admin dengan alasan. Admin SILAU baca saja.
     Data: Track\KuotaController::index (kunci = fixture hw-36). --}}
@extends('layouts.portal')
@section('judul', 'Kuota per Periode')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Admin / Pengaturan / Kuota per Periode" judul="Kuota minimum per periode"
        deskripsi="Tidak ada kuota maksimum: semua pendaftar dengan berkas valid diterima. Kuota minimum dievaluasi saat periode ditutup terhadap jumlah NPD Terbit."
        :chip="$periode ? ($periode['ditutup'] ? 'Periode ditutup' : 'H-'.$periode['sisa_hari'].' penutupan') : null">
        <x-slot:aksi>
            <form method="GET" action="{{ route('admin.kuota.index') }}">
                <label class="flex h-10 items-center gap-1 rounded-control border border-line bg-surface px-3 text-caption font-semibold text-ink">
                    Periode:
                    <select name="periode" class="bg-transparent font-normal text-ink focus:outline-none" x-data x-on:change="$el.form.requestSubmit()">
                        @foreach ($periodeTersedia as $id => $nama)
                            <option value="{{ $id }}" @selected(($periode['id'] ?? null) === $id)>{{ $nama }}</option>
                        @endforeach
                    </select>
                </label>
            </form>
            <x-pv.button variant="secondary" :href="route('admin.kuota.dasbor', ['periode' => $periode['id'] ?? null])"><x-pv.ikon nama="gauge" :ukuran="14" />Dasbor kuota</x-pv.button>
        </x-slot:aksi>
    </x-pv.page-header>

    @if (session('status'))<x-pv.alert nada="success" :judul="session('status')" class="mb-5" />@endif
    @if ($errors->has('kuota_min'))<x-pv.alert nada="error" :judul="$errors->first('kuota_min')" class="mb-5" />@endif

    @if (! $periode)
        <x-pv.empty-state judul="Belum ada periode pendaftaran" deskripsi="Super Admin membuat periode pendaftaran terlebih dahulu (menu Periode)." />
    @else
        <div class="mb-5 grid gap-5 sm:grid-cols-3">
            <x-pv.stat-card label="Peminatan dibuka" :nilai="(string) $jumlah['peminatan']" :keterangan="$periode['nama'].' · '.$periode['rentang']" />
            <x-pv.stat-card label="Total kuota minimum" :nilai="(string) $jumlah['kuota_min']" />
            <x-pv.stat-card label="NPD Terbit" :nilai="(string) $jumlah['npd_terbit']" keterangan="dihitung terhadap kuota minimum" />
        </div>

        @if ($periode['dimulai'] && ! $bisaUbah)
            <x-pv.alert nada="warning" judul="Kuota minimum terkunci" class="mb-5">Periode pendaftaran sudah dimulai: kuota minimum hanya dapat diubah Super Admin dengan alasan (BR-20).</x-pv.alert>
        @endif
        @if ($belumDiatur && ! $periode['dimulai'])
            <x-pv.alert nada="warning" judul="{{ count($belumDiatur) }} peminatan aktif belum punya kuota di periode ini" class="mb-5">Peminatan tanpa kuota tidak tampil di katalog saat periode dibuka (AC-23.6). Isi kuota minimum untuk membukanya.</x-pv.alert>
        @endif

        <form method="POST" action="{{ route('admin.kuota.simpan') }}" novalidate>
            @csrf
            <input type="hidden" name="periode" value="{{ $periode['id'] }}">
            <x-pv.tabel :kolom="['Peminatan', 'Track', 'Kuota minimum', 'NPD Terbit', 'Status', '>Aksi']">
                @foreach ([...$baris, ...$belumDiatur] as $b)
                    @php $kunci = 'kuota.'.$b['peminatan_id']; $baru = ! isset($b['id']); @endphp
                    <tr>
                        <td class="px-6 py-4"><span class="block text-body-sm font-semibold text-ink">{{ $b['peminatan'] }}</span><span class="text-caption text-muted">{{ $b['kode'] }}</span></td>
                        <td class="px-6 py-4 text-body-sm text-ink">{{ $b['track'] }}</td>
                        <td class="px-6 py-4">
                            @if ($bisaUbah && ! ($b['dibatalkan'] ?? false))
                                <label class="flex flex-col gap-1">
                                    <span class="sr-only">Kuota minimum {{ $b['peminatan'] }}</span>
                                    <input type="number" min="0" name="kuota[{{ $b['peminatan_id'] }}]" value="{{ $b['kuota_min'] ?? '' }}" placeholder="{{ $baru ? 'Belum diatur' : '' }}"
                                           @class(['h-10 w-28 rounded-control border bg-surface px-3 text-center font-mono text-body-sm text-ink focus:outline-2 focus:outline-primary', 'border-error' => $errors->has($kunci), 'border-control' => ! $errors->has($kunci)])
                                           @if ($errors->has($kunci)) aria-invalid="true" @endif>
                                    @if ($errors->has($kunci))<span class="max-w-56 text-caption text-error">{{ $errors->first($kunci) }}</span>@endif
                                </label>
                            @else
                                <span class="font-mono text-body-sm text-ink">{{ $baru ? '—' : $b['kuota_min'] }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-body-sm text-ink">{{ $baru ? '—' : $b['npd_terbit'] }}</td>
                        <td class="px-6 py-4">
                            @if ($baru)
                                <x-pv.badge nada="warning">Belum diatur</x-pv.badge>
                            @else
                                <x-pv.badge :nada="$b['nada']">{{ $b['penanda'] }}</x-pv.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if (! $baru && $bisaUbah && ! $periode['dimulai'] && $b['diajukan'] + $b['masuk_kuota'] + $b['npd_terbit'] === 0)
                                <button type="submit" form="lepas-{{ $b['id'] }}" class="text-body-sm font-bold text-error hover:underline">Lepas</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if (! $baris && ! $belumDiatur)
                    <tr><td colspan="6" class="px-6 py-10"><x-pv.empty-state judul="Belum ada peminatan aktif" deskripsi="Tambah peminatan di menu Peminatan." /></td></tr>
                @endif
                @if ($bisaUbah)
                    <x-slot:kaki>
                        @if ($wajibAlasan)
                            <div class="w-full sm:max-w-xl">
                                <x-pv.field label="Alasan perubahan" name="alasan" type="textarea" wajib maxlength="500" :galat="$errors->first('alasan') ?: false"
                                    bantuan="Wajib karena periode sudah dimulai; tercatat di audit log." />
                            </div>
                        @else
                            <p class="text-caption text-muted">Kosongkan isian peminatan yang tidak dibuka pada periode ini.</p>
                        @endif
                        <x-pv.button type="submit">Simpan kuota</x-pv.button>
                    </x-slot:kaki>
                @endif
            </x-pv.tabel>
        </form>
        @foreach ($baris as $b)
            <form id="lepas-{{ $b['id'] }}" method="POST" action="{{ route('admin.kuota.lepas', $b['id']) }}" class="hidden"
                  onsubmit="return confirm('Lepas {{ $b['peminatan'] }} dari periode ini? Peminatan tidak akan dibuka pada periode tersebut.')">
                @csrf @method('DELETE')
            </form>
        @endforeach
    @endif
@endsection
