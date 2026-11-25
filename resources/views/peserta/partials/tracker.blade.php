{{-- Progress tracker 5 tahap (FR-15, AC-07.1) dari PV/Step; garis penghubung navy setelah tahap selesai. $langkah = [{label, state}] --}}
<ol class="flex flex-col gap-3 lg:flex-row lg:items-center lg:gap-3">
    @foreach ($langkah as $i => $l)
        <li class="flex items-center gap-3 lg:contents">
            <x-pv.step :nomor="$i + 1" :label="$l['label']" :state="$l['state']" class="shrink-0" />
            @unless ($loop->last)
                <span @class(['hidden h-0.5 min-w-4 flex-1 rounded-full lg:block', 'bg-primary' => $l['state'] === 'selesai', 'bg-line' => $l['state'] !== 'selesai']) aria-hidden="true"></span>
            @endunless
        </li>
    @endforeach
</ol>
