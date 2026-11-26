{{-- HW-46 · CMS landing page (FR-10, BR-55, AC-29.1–29.4). Figma 1562:2740 (+ Kelola FAQ 1562:2337). Bagian per FR-10 + FAQ;
     isi teks polos (tanpa HTML), tarif & kuota tidak diketik di sini. Banner hero/gambar/crop Figma tidak dibuat (T-29).
     Data: SuperAdmin\CmsController::index (kunci = fixture hw-46). --}}
@extends('layouts.portal')
@section('judul', 'CMS Landing Page')
@section('konten')
    @php $b = $bagian[$aktif]; @endphp
    <x-pv.page-header breadcrumb="Portal Admin / Pengaturan / Landing Page" judul="Kelola konten landing page"
        deskripsi="Simpan sebagai draf, pratinjau, lalu terbitkan. Tarif, jadwal periode, dan peminatan diambil otomatis dari data master." />

    @if (session('status'))<x-pv.alert nada="success" :judul="session('status')" class="mb-5" />@endif
    @if ($errors->has('bagian'))<x-pv.alert nada="error" :judul="$errors->first('bagian')" class="mb-5" />@endif

    <nav class="mb-5 flex gap-1 overflow-x-auto rounded-card bg-interactive p-1" aria-label="Bagian landing page">
        @foreach ($bagian as $kunci => $item)
            <a href="{{ route('admin.cms.index', ['bagian' => $kunci]) }}" @if ($aktif === $kunci) aria-current="page" @endif
               @class(['shrink-0 rounded-control px-4 py-2 text-body-sm whitespace-nowrap', 'bg-surface font-semibold text-ink shadow-sm' => $aktif === $kunci, 'text-muted hover:text-ink' => $aktif !== $kunci])>{{ $item['label'] }}</a>
        @endforeach
    </nav>

    <div class="grid gap-5 lg:grid-cols-[1fr_320px]">
        <x-pv.panel :judul="$b['label']">
            <form method="POST" action="{{ route('admin.cms.simpan') }}" class="flex flex-col gap-4" novalidate>
                @csrf
                <input type="hidden" name="bagian" value="{{ $aktif }}">
                <x-pv.field label="Judul" name="judul" :value="$b['judul']" wajib maxlength="200" />
                <div class="flex flex-col gap-1.5">
                    <label for="cms-isi" class="text-body-sm font-medium text-ink">Isi<span class="text-error"> *</span></label>
                    <textarea id="cms-isi" name="isi" rows="10" maxlength="5000" required aria-describedby="cms-isi-bantuan"
                              @class(['w-full rounded-control border bg-surface px-3.5 py-3 font-mono text-body-sm text-ink focus:outline-2 focus:outline-primary', 'border-error' => $errors->has('isi'), 'border-control' => ! $errors->has('isi')])>{{ $b['isi'] }}</textarea>
                    @if ($errors->has('isi'))
                        <p id="cms-isi-bantuan" class="text-caption text-error">{{ $errors->first('isi') }}</p>
                    @else
                        <p id="cms-isi-bantuan" class="text-caption text-muted">{{ $b['format'] }} Teks ditampilkan apa adanya (tanpa HTML).</p>
                    @endif
                </div>
                <div class="flex flex-wrap justify-end gap-3">
                    <x-pv.button variant="secondary" :href="route('admin.cms.pratinjau')" target="_blank" rel="noopener">Pratinjau beranda</x-pv.button>
                    <x-pv.button type="submit">Simpan draf</x-pv.button>
                </div>
            </form>
        </x-pv.panel>

        <aside class="flex flex-col gap-5">
            <x-pv.panel judul="Status">
                <x-pv.badge :nada="$b['status'] === 'Terbit' ? 'success' : 'warning'">{{ $b['status'] }}</x-pv.badge>
                <p class="text-caption text-muted">{{ $b['terbit_pada'] ? 'Terakhir diterbitkan '.$b['terbit_pada'] : 'Beranda memakai isi bawaan sampai bagian ini diterbitkan.' }}</p>
                <form method="POST" action="{{ route('admin.cms.terbitkan') }}" onsubmit="return confirm('Terbitkan {{ mb_strtolower($b['label']) }}? Perubahan langsung tampil di beranda.')">
                    @csrf
                    <input type="hidden" name="bagian" value="{{ $aktif }}">
                    <x-pv.button type="submit" class="w-full" :disabled="! $b['bisa_terbit']">Terbitkan</x-pv.button>
                </form>
                @unless ($b['bisa_terbit'])<p class="text-caption text-muted">Simpan draf terlebih dahulu.</p>@endunless
            </x-pv.panel>
            <x-pv.alert judul="Data dinamis">Tanggal buka/tutup periode, daftar track & peminatan, serta tarif tampil otomatis dari data master (BR-55). Informasi kuota tidak pernah tampil di halaman publik.</x-pv.alert>
        </aside>
    </div>
@endsection
