{{-- Daftar label–nilai (Figma "Ringkasan pendaftaran", "Pendaftaran Anda", "Tinjau & ajukan"): label 13 abu, nilai 13 Medium.
     $baris = [label => nilai]; garis = pemisah antarbaris. --}}
@props(['baris', 'garis' => true])
<dl {{ $attributes->class(['flex flex-col']) }}>
    @foreach ($baris as $label => $nilai)
        <div @class(['grid grid-cols-[minmax(0,2fr)_minmax(0,3fr)] gap-4 py-2.5', 'border-b border-line' => $garis])>
            <dt class="text-compact text-muted">{{ $label }}</dt>
            <dd class="text-compact font-medium wrap-break-word text-ink">{{ $nilai }}</dd>
        </div>
    @endforeach
</dl>
