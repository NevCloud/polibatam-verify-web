{{-- "Pilihan autentikasi" (Figma 1562:9208): segmen Masuk | Daftar Akun, latar interaktif, p-[5px], radius 10. --}}
@props(['aktif' => 'masuk'])
<nav class="flex gap-1.5 rounded-menu bg-interactive p-[5px]" aria-label="Masuk atau daftar">
    @foreach (['masuk' => ['Masuk', route('login')], 'daftar' => ['Daftar Akun', route('register')]] as $kunci => [$label, $href])
        <a href="{{ $href }}" @if ($aktif === $kunci) aria-current="page" @endif
           @class(['flex h-[38px] flex-1 items-center justify-center rounded-control text-compact',
               'border border-line-accent bg-surface font-extrabold text-primary' => $aktif === $kunci,
               'font-semibold text-muted hover:text-primary' => $aktif !== $kunci])>{{ $label }}</a>
    @endforeach
</nav>
