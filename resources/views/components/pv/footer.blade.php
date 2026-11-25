{{-- PV/Footer — portal & auth: Layar/Footer (Figma 3057:3474, tinggi 60px, 11px); publik: 3 kolom (design-memory §7.1). --}}
@props(['varian' => 'portal'])
@if ($varian === 'portal')
    <footer class="flex min-h-15 shrink-0 flex-wrap items-center justify-between gap-2 border-t border-line px-5 py-3 text-meta font-normal text-muted md:px-10">
        <span>© {{ now()->year }} Polibatam Verify. Hak cipta dilindungi.</span>
        <span>Politeknik Negeri Batam</span>
    </footer>
@else
    <footer class="border-t border-line bg-surface">
        <div class="mx-auto grid max-w-[1200px] gap-8 px-5 py-12 md:grid-cols-3">
            <div>
                <x-pv.logo />
                <p class="mt-3 text-body-sm text-muted">Sistem pendaftaran dan administrasi program Microcredential Politeknik Negeri Batam.</p>
            </div>
            <div>
                <p class="text-body-sm font-semibold">Jelajahi</p>
                <ul class="mt-3 space-y-2 text-body-sm text-muted">
                    {{-- STUB-FE(P08): rute halaman publik --}}
                    @foreach (['Beranda', 'Program', 'FAQ', 'Kontak'] as $item)
                        <li><a href="#" class="hover:text-ink">{{ $item }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <p class="text-body-sm font-semibold">Quick Support</p>
                <p class="mt-3 text-body-sm text-muted">Ada kendala saat mendaftar? Tim kami siap membantu.</p>
                <x-pv.button variant="secondary" href="#" class="mt-3">Hubungi tim dukungan</x-pv.button>
            </div>
        </div>
        <div class="border-t border-line px-5 py-4 text-center text-caption text-muted">© {{ now()->year }} Polibatam Verify. Hak cipta dilindungi.</div>
    </footer>
@endif
