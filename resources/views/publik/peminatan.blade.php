{{-- HW-03 · Detail peminatan (FR-08, FR-09, BR-27, BR-75). Figma 3048:1357; kartu "Kuota periode ini" diganti kartu periode
     pendaftaran tanpa angka kuota (K-42, T-29). Data: Publik\PublikController::peminatan (kunci = fixture hw-03). --}}
@extends('layouts.publik')
@section('judul', $peminatan['nama'])
@section('konten')
    <div class="mx-auto grid max-w-[1200px] items-start gap-6 px-5 py-12 lg:grid-cols-[1fr_360px]">
        <div class="flex min-w-0 flex-col gap-6">
            <div>
                <p class="text-caption text-muted"><a href="{{ route('beranda') }}" class="hover:underline">Beranda</a> / <a href="{{ route('katalog') }}" class="hover:underline">Katalog</a> / {{ $peminatan['nama'] }}</p>
                <h1 class="mt-3 text-display font-bold text-ink">{{ $peminatan['nama'] }}</h1>
                <p class="mt-2 text-body-sm text-muted">Track {{ $peminatan['track'] }} · durasi {{ $peminatan['durasi'] }} semester</p>
            </div>

            <x-pv.panel judul="Tarif berlaku">
                <div class="overflow-x-auto rounded-control border border-line">
                    <table class="w-full min-w-[420px] text-left text-body-sm">
                        <thead class="bg-canvas text-caption text-muted"><tr><th class="px-4 py-3 font-medium">Komponen</th><th class="px-4 py-3 font-medium">Nominal</th><th class="px-4 py-3 font-medium">Berlaku sejak</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($peminatan['tarif'] as $t)
                                <tr>
                                    <td class="px-4 py-3 text-ink">{{ $t['komponen'] }}</td>
                                    <td class="px-4 py-3 text-ink">{{ $t['nominal'] ?? 'Belum tersedia' }}</td>
                                    <td class="px-4 py-3 text-ink">{{ $t['sejak'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-caption text-muted">Nominal tagihan = tarif + kode unik 3 digit. Tagihan yang sudah terbit tidak berubah bila tarif diubah.</p>
            </x-pv.panel>

            <x-pv.panel judul="Persyaratan berkas">
                <ul class="space-y-2 text-body-sm text-ink">
                    @foreach ($peminatan['berkas'] as $b)
                        <li class="flex items-center gap-2"><x-pv.ikon nama="centang" :ukuran="14" class="text-success" />{{ $b }}</li>
                    @endforeach
                </ul>
            </x-pv.panel>
        </div>

        <aside class="flex flex-col gap-4 rounded-sheet border border-line bg-surface p-5">
            <h2 class="text-subheading font-bold text-ink">Pendaftaran</h2>
            <p class="text-body-sm text-ink">{{ $periode['nama'] ?? 'Belum ada periode dibuka' }}</p>
            @if ($periode['buka'] && $peminatan['dibuka'])
                <x-pv.button :href="$tautanDaftar">Daftar peminatan ini</x-pv.button>
                <p class="text-caption text-muted">{{ $periode['teks'] }}.</p>
            @else
                <x-pv.button disabled aria-disabled="true" class="cursor-not-allowed opacity-60">Pendaftaran ditutup</x-pv.button>
                <p class="text-caption text-muted">{{ $periode['buka'] ? 'Peminatan ini tidak dibuka pada periode pendaftaran saat ini.' : $periode['teks'] }}</p>
            @endif
        </aside>
    </div>
@endsection
