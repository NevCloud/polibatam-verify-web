{{-- HW-44 · Akun & role admin (FR-06, FR-07, BR-05, US-27). Figma 1562:6836 (+ dialog 1562:7592).
     Data: App\Queries\AkunAdminQuery (kunci = fixture hw-44). Satu aksi per akun (AE-08); alasan wajib untuk ubah status. --}}
@extends('layouts.portal')
@section('judul', 'Kelola User')
@section('konten')
    <x-pv.page-header breadcrumb="Portal Super Admin › Kelola User" judul="Kelola User & Hak Akses"
        deskripsi="Kelola akun admin: undangan aktivasi, role, dan status. Akun Calon Peserta dibuat melalui registrasi.">
        <x-slot:aksi>
            <span></span>
            <x-pv.button onclick="document.getElementById('buat-akun').showModal()"><x-pv.ikon nama="tambah" :ukuran="11" />Buat Akun Admin</x-pv.button>
        </x-slot:aksi>
    </x-pv.page-header>

    @if (session('status'))
        <x-pv.alert nada="success" :judul="session('status')" class="mb-4" />
    @endif
    @if (isset($errors) && $errors->hasAny(['akun', 'alasan']))
        <x-pv.alert nada="error" :judul="$errors->first('akun') ?: $errors->first('alasan')" class="mb-4" />
    @endif

    <form method="GET" action="{{ route('admin.akun.index') }}" class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-control border border-line bg-surface p-4">
        <div class="flex flex-wrap items-center gap-2">
            <label class="relative w-full sm:w-[340px]">
                <span class="sr-only">Cari nama atau email</span>
                <x-pv.ikon nama="cari" :ukuran="14" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-muted" />
                <input type="search" name="q" value="{{ $filter['q'] }}" placeholder="Cari nama atau email..."
                       class="h-10 w-full rounded-control border border-line bg-canvas pr-3 pl-9 text-body-sm text-ink placeholder:text-muted focus:outline-2 focus:outline-primary">
            </label>
            <label class="flex h-10 items-center gap-1 rounded-control border border-line bg-canvas px-3 text-caption font-semibold text-ink">
                Peran:
                <select name="role" class="bg-transparent font-normal text-muted focus:outline-none" x-data x-on:change="$el.form.requestSubmit()">
                    <option value="">Semua</option>
                    @foreach ($peranTersedia as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($filter['role'] === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="flex h-10 items-center gap-1 rounded-control border border-line bg-canvas px-3 text-caption font-semibold text-ink">
                Status:
                <select name="status" class="bg-transparent font-normal text-muted focus:outline-none" x-data x-on:change="$el.form.requestSubmit()">
                    <option value="">Semua</option>
                    @foreach ($statusTersedia as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($filter['status'] === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <x-pv.button type="submit" variant="secondary" class="h-10!">Terapkan Filter</x-pv.button>
        </div>
        <span class="flex items-center gap-1 rounded border border-line bg-interactive px-3 py-1.5 text-caption font-semibold text-muted">
            <x-pv.ikon nama="users-total" :ukuran="15" />Total: {{ $total }} akun admin
        </span>
    </form>

    <x-pv.tabel :kolom="['Pengguna', 'Peran', 'Status', 'Login terakhir', '>Aksi']">
        @forelse ($akun as $a)
            <tr>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 shrink-0 place-items-center rounded-card bg-primary text-body-sm font-bold text-white" aria-hidden="true">{{ $a['inisial'] }}</span>
                        <span class="flex flex-col">
                            <span class="text-body-sm font-bold text-ink">{{ $a['nama'] }}</span>
                            <span class="text-compact text-muted">{{ $a['email'] }}</span>
                        </span>
                    </div>
                </td>
                <td class="px-6 py-4"><x-pv.badge nada="info">{{ $a['peran'] }}</x-pv.badge></td>
                <td class="px-6 py-4">
                    <x-pv.badge :nada="['Aktif' => 'success', 'Nonaktif' => 'error', 'Menunggu Aktivasi' => 'warning'][$a['status']]">{{ $a['status'] }}</x-pv.badge>
                </td>
                <td class="px-6 py-4 text-compact text-muted">
                    {{ $a['login_terakhir'] }}@if ($a['saya']) <span class="font-semibold text-link">(Saat ini)</span>@endif
                </td>
                <td class="px-6 py-4 text-right">
                    <details class="relative inline-block text-left">
                        <summary class="grid size-8 cursor-pointer list-none place-items-center rounded hover:bg-canvas" aria-label="Aksi untuk {{ $a['nama'] }}">
                            <x-pv.ikon nama="titik-tiga" :ukuran="14" />
                        </summary>
                        <div class="absolute right-0 z-10 mt-1 flex w-56 flex-col rounded-control border border-line bg-surface py-1 text-body-sm shadow-lg">
                            <button type="button" class="px-4 py-2 text-left hover:bg-canvas" onclick="document.getElementById('role-{{ $a['id'] }}').showModal()">Ubah peran</button>
                            @if ($a['status'] === 'Nonaktif')
                                <button type="button" class="px-4 py-2 text-left hover:bg-canvas" onclick="document.getElementById('status-{{ $a['id'] }}').showModal()">Aktifkan kembali</button>
                            @else
                                <button type="button" class="px-4 py-2 text-left text-error hover:bg-error-surface" onclick="document.getElementById('status-{{ $a['id'] }}').showModal()">Nonaktifkan akun</button>
                            @endif
                            @if ($a['status'] === 'Menunggu Aktivasi')
                                <form method="POST" action="{{ route('admin.akun.undangan', $a['id']) }}">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 text-left hover:bg-canvas">Kirim ulang undangan</button>
                                </form>
                            @endif
                        </div>
                    </details>

                    <x-pv.dialog :id="'role-'.$a['id']" judul="Ubah peran" :deskripsi="'Peran baru untuk '.$a['nama'].'. Token aplikasi mobile akun ini akan dicabut.'" class="text-left">
                        <form method="POST" action="{{ route('admin.akun.role', $a['id']) }}" class="flex flex-col gap-5">
                            @csrf @method('PUT')
                            <x-pv.field :id="'f-role-'.$a['id']" label="Peran" name="role" type="select" wajib>
                                @foreach ($peranTersedia as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($a['role'] === $nilai)>{{ $label }}</option>
                                @endforeach
                            </x-pv.field>
                            <x-pv.button type="submit" class="self-end">Simpan Peran</x-pv.button>
                        </form>
                    </x-pv.dialog>

                    <x-pv.dialog :id="'status-'.$a['id']" :judul="$a['status'] === 'Nonaktif' ? 'Aktifkan kembali akun' : 'Nonaktifkan akun'"
                        :deskripsi="$a['status'] === 'Nonaktif' ? $a['nama'].' dapat masuk kembali setelah diaktifkan.' : 'Sesi web dan token mobile '.$a['nama'].' langsung diakhiri.'" class="text-left">
                        <form method="POST" action="{{ route('admin.akun.status', $a['id']) }}" class="flex flex-col gap-5">
                            @csrf @method('PUT')
                            <input type="hidden" name="aktif" value="{{ $a['status'] === 'Nonaktif' ? 1 : 0 }}">
                            <x-pv.field :id="'f-alasan-'.$a['id']" label="Alasan" name="alasan" type="textarea" wajib maxlength="500" />
                            <x-pv.button type="submit" :variant="$a['status'] === 'Nonaktif' ? 'primary' : 'danger'" class="self-end">
                                {{ $a['status'] === 'Nonaktif' ? 'Aktifkan Akun' : 'Nonaktifkan Akun' }}
                            </x-pv.button>
                        </form>
                    </x-pv.dialog>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-6 py-12"><x-pv.empty-state judul="Tidak ada akun admin" deskripsi="Ubah kata kunci atau filter pencarian." /></td></tr>
        @endforelse

        <x-slot:kaki>
            <x-pv.paginasi :rentang="$rentang" :total="$total" satuan="akun admin" :tautan="$tautan" :sebelumnya="$sebelumnya" :berikutnya="$berikutnya" />
        </x-slot:kaki>
    </x-pv.tabel>

    <p class="mt-4 flex items-center gap-2 text-compact text-muted">
        <x-pv.ikon nama="info" :ukuran="14" />Perubahan peran dan status akun dicatat di audit log. Super Admin terakhir dilindungi: tidak dapat dinonaktifkan atau diturunkan perannya.
    </p>

    <x-pv.dialog id="buat-akun" judul="Buat akun admin" deskripsi="Undangan aktivasi (berlaku 72 jam) dikirim ke email. Admin wajib mendaftarkan autentikator saat aktivasi.">
        <form method="POST" action="{{ route('admin.akun.store') }}" class="flex flex-col gap-4">
            @csrf
            <x-pv.field label="Nama lengkap" name="name" :value="old('name')" wajib maxlength="150" />
            <x-pv.field label="Email" name="email" type="email" :value="old('email')" wajib />
            <x-pv.field label="Peran" name="role" type="select" wajib>
                <option value="">Pilih peran</option>
                @foreach ($peranTersedia as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('role') === $nilai)>{{ $label }}</option>
                @endforeach
            </x-pv.field>
            <x-pv.button type="submit" class="self-end">Buat Akun & Kirim Undangan</x-pv.button>
        </form>
    </x-pv.dialog>
    @if (isset($errors) && $errors->hasAny(['name', 'email', 'role']))
        <script>document.getElementById('buat-akun').showModal();</script>
    @endif
@endsection
