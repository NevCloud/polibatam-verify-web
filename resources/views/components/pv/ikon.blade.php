{{-- Ikon/* (Figma, 18px). Aset SVG dipakai sebagai mask agar warnanya ikut currentColor (aktif/putih, default/abu) tanpa mengubah aset. --}}
@props(['nama', 'ukuran' => 18])
<span aria-hidden="true" {{ $attributes->class(['inline-block shrink-0 bg-current']) }}
      style="width: {{ $ukuran }}px; height: {{ $ukuran }}px; -webkit-mask: url('{{ asset('images/ikon/'.$nama.'.svg') }}') center / contain no-repeat; mask: url('{{ asset('images/ikon/'.$nama.'.svg') }}') center / contain no-repeat;"></span>
