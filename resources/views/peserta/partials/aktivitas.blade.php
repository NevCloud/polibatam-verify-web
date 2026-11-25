{{-- Kartu "Aktivitas terbaru" HW-13 = notifikasi in-app terbaru (FR-53). $notifikasi = [{judul, waktu, dibaca, href}] --}}
<x-pv.panel judul="Aktivitas terbaru">
    <x-slot:aksi><a href="{{ route('notifikasi') }}" class="text-caption font-bold text-link hover:underline">Lihat semua</a></x-slot:aksi>
    @forelse ($notifikasi as $n)
        <a href="{{ $n['href'] }}" class="group flex items-start gap-3">
            <span @class(['mt-1.5 size-2 shrink-0 rounded-full', 'bg-link' => ! $n['dibaca'], 'bg-line-strong' => $n['dibaca']]) aria-hidden="true"></span>
            <span class="flex flex-col gap-0.5">
                <span class="text-compact font-medium text-ink group-hover:underline">{{ $n['judul'] }}</span>
                <span class="text-meta text-muted">{{ $n['waktu'] }}</span>
            </span>
        </a>
    @empty
        <p class="text-compact text-muted">Belum ada aktivitas.</p>
    @endforelse
</x-pv.panel>
