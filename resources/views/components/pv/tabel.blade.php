{{-- PV/Tabel (Figma HW-44 "User Management Table Card" 1562:6935): kartu putih bergaris, radius 8, kepala 12 SemiBold
     kapital di latar interaktif, baris dipisah garis. $kolom = label kolom; label diawali '>' = rata kanan.
     Slot = baris <tr>; slot `kaki` = ringkasan + paginasi. --}}
@props(['kolom'])
<div {{ $attributes->class(['overflow-hidden rounded-control border border-line bg-surface']) }}>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-left">
            <thead class="border-b border-line bg-interactive/70">
                <tr>
                    @foreach ($kolom as $k)
                        <th scope="col" @class(['px-6 py-3.5 text-caption font-semibold tracking-[0.6px] text-muted uppercase', 'text-right' => str_starts_with($k, '>')])>{{ ltrim($k, '>') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-line">{{ $slot }}</tbody>
        </table>
    </div>
    @isset($kaki)
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line px-6 py-3.5">{{ $kaki }}</div>
    @endisset
</div>
