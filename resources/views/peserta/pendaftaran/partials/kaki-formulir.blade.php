{{-- Kaki tab HW-15: waktu simpan terakhir + [kembali] [Simpan Draf] … [lanjut]. $kembali/$lanjut = [tab, label] | null.
     Simpan Draf selalu mengirim form Data Diri (#form-draf) dari tab mana pun. --}}
@if ($pendaftaran['draf'])
    <div class="mt-5 flex flex-col gap-4 border-t border-line pt-5">
        <p class="flex items-center gap-2 text-caption text-success"><x-pv.ikon nama="circle-check" :ukuran="14" />Draf tersimpan · {{ $disimpan }}</p>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-3">
                @if ($kembali)
                    <x-pv.button variant="secondary" x-on:click="tab = '{{ $kembali[0] }}'">{{ $kembali[1] }}</x-pv.button>
                @endif
                <x-pv.button type="submit" form="form-draf" variant="secondary" class="min-w-36">Simpan Draf</x-pv.button>
            </div>
            <x-pv.button x-on:click="tab = '{{ $lanjut[0] }}'; window.scrollTo({ top: 0, behavior: 'smooth' })" class="min-w-60">{{ $lanjut[1] }} →</x-pv.button>
        </div>
    </div>
@else
    <div class="mt-5 flex justify-end border-t border-line pt-5">
        <x-pv.button variant="secondary" x-on:click="tab = '{{ $lanjut[0] }}'">{{ $lanjut[1] }} →</x-pv.button>
    </div>
@endif
