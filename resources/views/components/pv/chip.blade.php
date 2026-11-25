{{-- Chip informasi berbentuk pil (Figma "Status" di Header portal & Judul halaman): 11px Bold, latar biru muda, teks biru tautan.
     Nada success untuk chip "Peserta Didik" (design-memory §6.1). --}}
@props(['nada' => 'info'])
<span {{ $attributes->class(['inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-[5px] text-meta font-bold',
    'bg-interactive text-link' => $nada !== 'success', 'bg-success-surface text-success' => $nada === 'success']) }}>{{ $slot }}</span>
