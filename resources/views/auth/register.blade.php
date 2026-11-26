{{-- HW-05 · Registrasi (FR-01, BR-01, NFR-02, NFR-11). Figma 1562:9244.
     Indikator kekuatan & kriteria hanya bantuan UI (Alpine); aturan sebenarnya divalidasi server (BuatAkunPeserta). --}}
@extends('layouts.auth')
@section('judul', 'Registrasi Akun')
@section('konten')
    <div class="flex w-full max-w-[1144px] items-start justify-center gap-16">
        <x-pv.branding-auth judul="Langkah awal untuk|masa depan Anda."
            deskripsi="Mulai perjalanan belajar di Polibatam. Buat akun untuk memilih program dan mengelola pendaftaran Anda." />

        <x-pv.kartu-auth eyebrow="AKUN PENDAFTAR" judul="Registrasi Akun"
            deskripsi="Daftarkan diri Anda untuk memulai pendaftaran di Polibatam Verify.">
            <x-pv.pilihan-auth aktif="daftar" />

            <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5" novalidate
                  x-data="{ sandi: '', get panjang() { return this.sandi.length >= 8 }, get huruf() { return /[A-Za-z]/.test(this.sandi) }, get angka() { return /[0-9]/.test(this.sandi) }, get skor() { return [this.panjang, this.huruf, this.angka].filter(Boolean).length } }">
                @csrf
                <x-pv.field label="Nama Lengkap" name="name" ikon="user-round" :value="old('name')" autocomplete="name" required autofocus />
                <x-pv.field label="Email" name="email" type="email" ikon="mail" :value="old('email')" autocomplete="email" required />

                <div class="flex flex-col gap-2.5">
                    <x-pv.field label="Kata Sandi" name="password" type="password" ikon="kunci" autocomplete="new-password" required x-model="sandi" />
                    <div class="flex flex-col gap-1.5" aria-hidden="true">
                        <div class="flex gap-1.5">
                            @foreach ([1, 2, 3] as $segmen)
                                <span class="h-[5px] flex-1 rounded-sm bg-line" x-bind:class="skor >= {{ $segmen }} ? (skor === 3 ? 'bg-success' : 'bg-warning') : 'bg-line'"></span>
                            @endforeach
                        </div>
                        <div class="flex justify-between text-[10px] text-control">
                            <span x-bind:class="skor === 1 && 'font-extrabold text-warning'">Lemah</span>
                            <span x-bind:class="skor === 2 && 'font-extrabold text-warning'">Sedang</span>
                            <span x-bind:class="skor === 3 && 'font-extrabold text-success'">Kuat</span>
                        </div>
                    </div>
                    <ul class="flex flex-wrap gap-4 text-meta" aria-label="Syarat kata sandi">
                        @foreach (['panjang' => 'Minimal 8 karakter', 'huruf' => 'Huruf', 'angka' => 'Angka'] as $syarat => $teks)
                            <li class="flex items-center gap-1.5 text-muted" x-bind:class="{{ $syarat }} && 'text-success'">
                                <x-pv.ikon nama="circle-check" :ukuran="13" />{{ $teks }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <x-pv.field label="Konfirmasi Kata Sandi" name="password_confirmation" type="password" ikon="kunci" autocomplete="new-password" required />

                <div class="flex flex-col gap-1.5">
                    <label class="flex items-start gap-2.5 text-caption leading-[1.6] text-muted">
                        <input type="checkbox" name="persetujuan_privasi" value="1" @checked(old('persetujuan_privasi')) required
                               class="mt-0.5 size-[18px] shrink-0 rounded accent-primary">
                        <span>Saya menyetujui <a href="{{ route('kebijakan-privasi') }}" class="font-bold text-link hover:underline" target="_blank">Kebijakan Privasi</a> Polibatam Verify.</span>
                    </label>
                    @error('persetujuan_privasi')<p class="text-caption text-error">{{ $message }}</p>@enderror
                </div>

                <x-pv.button type="submit" class="w-full">Buat Akun</x-pv.button>
            </form>

            <p class="text-center text-compact text-muted">Sudah punya akun? <a href="{{ route('login') }}" class="font-extrabold text-link hover:underline">Masuk di sini</a></p>
        </x-pv.kartu-auth>
    </div>
@endsection
