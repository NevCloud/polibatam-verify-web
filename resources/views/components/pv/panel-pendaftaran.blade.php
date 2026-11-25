{{-- Kolom kanan HW-14/HW-15 (Figma "Pendaftaran Anda" + "Data Anda aman" + "Sebelum melanjutkan").
     $status = teks badge (mis. "Draf · Belum lengkap"); $nomor null = belum disimpan; $tips = daftar periksa;
     $ubah = tautan ke HW-14 untuk mengganti track/peminatan (hanya selama Draf, BR-09). --}}
@props(['status', 'nomor' => null, 'keterangan' => null, 'tips' => [], 'ubah' => null])
<aside {{ $attributes->class(['flex flex-col gap-5']) }}>
    <x-pv.panel judul="Pendaftaran Anda">
        <x-pv.badge nada="warning" class="self-start">{{ $status }}</x-pv.badge>
        <div class="flex flex-col gap-2">
            <p class="text-compact text-muted">Nomor pendaftaran</p>
            <p class="text-body-sm font-bold text-ink">{{ $nomor ?? 'Dibuat saat disimpan' }}</p>
        </div>
        @if ($keterangan)<p class="text-compact leading-relaxed text-muted">{{ $keterangan }}</p>@endif
        @if ($ubah)
            <a href="{{ $ubah }}" class="self-start text-compact font-bold text-link hover:underline">Ubah track & peminatan →</a>
        @endif
    </x-pv.panel>

    <div class="flex items-start gap-3 rounded-sheet border border-line-accent bg-interactive p-5">
        <x-pv.ikon nama="info" class="mt-0.5 shrink-0 text-link" />
        <div class="flex flex-col gap-1">
            <p class="text-compact font-bold text-ink">Data Anda aman</p>
            <p class="text-compact leading-relaxed text-muted">Informasi hanya digunakan untuk proses pendaftaran dan verifikasi Polibatam. NIK disimpan terenkripsi.</p>
        </div>
    </div>

    @if ($tips)
        <x-pv.panel judul="Sebelum melanjutkan">
            <ul class="flex flex-col gap-3">
                @foreach ($tips as $tip)
                    <li class="flex items-start gap-2 text-compact text-muted"><span class="text-success" aria-hidden="true">✓</span>{{ $tip }}</li>
                @endforeach
            </ul>
        </x-pv.panel>
    @endif
</aside>
