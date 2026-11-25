<?php

namespace App\Actions\Tagihan;

use App\Models\Pendaftaran;
use App\Models\PesertaDidik;
use App\Models\RekeningInstitusi;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\AuditLogger;
use App\States\RekeningInstitusi\Aktif;
use App\States\RekeningInstitusi\Nonaktif;
use Illuminate\Support\Facades\DB;

/**
 * FR-67, BR-70, BR-71, A-62: rekening institusi tujuan transfer. Satu Aktif per jenis tagihan (UNIQUE kunci_aktif);
 * mengaktifkan rekening lain menonaktifkan yang lama. Tidak ada hapus (pv_app tanpa DELETE; rekening terpakai wajib disimpan).
 */
class KelolaRekening
{
    public function __construct(private AuditLogger $audit, private TerbitkanTagihan $tagihan) {}

    /** @param  array{jenis_tagihan: string, nama_bank: string, nomor_rekening: string, atas_nama: string}  $data */
    public function simpan(array $data, ?RekeningInstitusi $rekening, User $admin): RekeningInstitusi
    {
        return DB::transaction(function () use ($data, $rekening, $admin) {
            if ($rekening) {
                $sebelum = $rekening->only(array_keys($data));
                $rekening->update($data + ['diubah_oleh' => $admin->id]);
                $this->audit->catat('ubah_rekening', 'rekening_institusi', $rekening->id, sebelum: $sebelum, sesudah: $data, pelaku: $admin);

                return $rekening;
            }

            $baru = RekeningInstitusi::create($data + ['status' => Nonaktif::class, 'dibuat_oleh' => $admin->id]);
            $this->audit->catat('tambah_rekening', 'rekening_institusi', $baru->id, sesudah: $data, pelaku: $admin);

            return $baru;
        });
    }

    public function ubahStatus(RekeningInstitusi $rekening, bool $aktif, User $admin): RekeningInstitusi
    {
        DB::transaction(function () use ($rekening, $aktif, $admin) {
            $r = RekeningInstitusi::whereKey($rekening->id)->lockForUpdate()->firstOrFail();
            if ($aktif === ($r->status->getValue() === 'aktif')) {
                return;
            }

            if ($aktif) {
                RekeningInstitusi::where('jenis_tagihan', $r->jenis_tagihan)->where('status', 'aktif')->lockForUpdate()->get()
                    ->each(fn (RekeningInstitusi $lama) => $lama->forceFill(['diubah_oleh' => $admin->id])->status->transitionTo(Nonaktif::class));
            }
            $r->forceFill(['diubah_oleh' => $admin->id])->status->transitionTo($aktif ? Aktif::class : Nonaktif::class);
            $this->audit->catat($aktif ? 'aktifkan_rekening' : 'nonaktifkan_rekening', 'rekening_institusi', $r->id,
                sesudah: ['status' => $aktif ? 'aktif' : 'nonaktif', 'jenis_tagihan' => $r->jenis_tagihan], pelaku: $admin);
        });

        if ($aktif) {
            $this->terbitkanTertunda($rekening->jenis_tagihan);
        }

        return $rekening->refresh();
    }

    /** A-87: tagihan yang tertunda karena belum ada rekening aktif diterbitkan begitu rekening diaktifkan. */
    private function terbitkanTertunda(string $jenis): void
    {
        if ($jenis === 'formulir') {
            Pendaftaran::whereIn('status', ['diajukan', 'perlu_revisi', 'masuk_kuota'])
                ->whereDoesntHave('tagihans', fn ($q) => $q->where('jenis', 'formulir')->where('status', '<>', 'dibatalkan'))
                ->with('peminatanPeriode.peminatan', 'user')->get()
                ->each(fn (Pendaftaran $p) => $this->tagihan->formulir($p));

            return;
        }

        PesertaDidik::whereIn('status', ['belum_aktif', 'aktif', 'tertahan'])
            ->whereNotExists(fn ($q) => $q->from('tagihans')->whereColumn('tagihans.peserta_didik_id', 'peserta_didiks.id')
                ->whereColumn('tagihans.semester_ke', 'peserta_didiks.semester_berjalan'))
            ->with('pendaftaran.peminatanPeriode.peminatan', 'pendaftaran.user')->get()
            ->each(fn (PesertaDidik $pd) => $this->tagihan->semester($pd, $pd->semester_berjalan));
    }
}
