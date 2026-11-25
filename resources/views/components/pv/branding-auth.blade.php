{{-- Panel "Branding dan informasi kampus" (Figma HW-05 1562:9251, HW-07 1562:9172, HW-08 3010:1199):
     latar interaktif, radius 28, p-10; penanda institusi · judul 38 ExtraBold · deskripsi · 3 keunggulan · catatan. --}}
@props(['judul', 'deskripsi']) {{-- `|` di judul = patahan baris (Figma) --}}
<aside {{ $attributes->class(['hidden w-full max-w-[560px] shrink-0 flex-col gap-7 rounded-panel bg-interactive p-10 lg:flex']) }}>
    <div class="flex items-center gap-2">
        <span class="h-[3px] w-6 rounded-sm bg-link" aria-hidden="true"></span>
        <p class="text-meta font-extrabold text-link">POLITEKNIK NEGERI BATAM</p>
    </div>
    <div class="flex flex-col gap-4">
        <h2 class="text-hero font-extrabold text-primary">{!! collect(explode('|', $judul))->map(fn ($b) => e($b))->implode('<br>') !!}</h2>
        <p class="text-body-sm leading-[1.75] text-muted">{{ $deskripsi }}</p>
    </div>
    <ul class="flex flex-col gap-5">
        @foreach ([
            ['book-open', 'Pembelajaran terapan', 'Kembangkan kompetensi melalui praktik dan proyek.'],
            ['handshake', 'Dekat dengan dunia industri', 'Kenali bidang yang relevan dengan kebutuhan kerja.'],
            ['layers-tumpuk', 'Pilihan bidang yang beragam', 'Temukan program sesuai minat dan rencana Anda.'],
        ] as [$ikon, $judulButir, $teks])
            <li class="flex items-start gap-3.5">
                <span class="grid size-10 shrink-0 place-items-center rounded-card bg-surface text-link"><x-pv.ikon :nama="$ikon" :ukuran="20" /></span>
                <span class="flex flex-col gap-1">
                    <span class="text-compact font-bold text-primary">{{ $judulButir }}</span>
                    <span class="text-caption leading-[1.6] text-muted">{{ $teks }}</span>
                </span>
            </li>
        @endforeach
    </ul>
    <p class="text-meta text-muted">Sistem verifikasi Microcredential Politeknik Negeri Batam</p>
</aside>
