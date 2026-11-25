{{-- PV/Dialog (konfirmasi + isian; daftar-halaman-web "Dialog konfirmasi + alasan wajib"). Memakai <dialog> bawaan:
     fokus terkunci & Esc menutup tanpa JS tambahan. Buka: onclick="document.getElementById('{id}').showModal()".
     Tanpa sumber Figma (A-80): gaya diambil dari token kartu (radius 16, garis, p-6). --}}
@props(['id', 'judul', 'deskripsi' => null])
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-judul" {{ $attributes->class(['m-auto w-[calc(100%-2rem)] max-w-[480px] rounded-sheet border border-line bg-surface p-0 text-ink shadow-xl backdrop:bg-ink/40']) }}>
    <div class="flex flex-col gap-5 p-6">
        <header class="flex items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h2 id="{{ $id }}-judul" class="text-title font-bold text-primary">{{ $judul }}</h2>
                @if ($deskripsi)<p class="text-body-sm text-muted">{{ $deskripsi }}</p>@endif
            </div>
            <form method="dialog">
                <button type="submit" class="grid size-8 place-items-center rounded-control text-muted hover:bg-canvas hover:text-ink" aria-label="Tutup">
                    <x-pv.ikon nama="x" :ukuran="16" />
                </button>
            </form>
        </header>
        {{ $slot }}
    </div>
</dialog>
