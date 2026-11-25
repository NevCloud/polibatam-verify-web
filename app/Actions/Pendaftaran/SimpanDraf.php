<?php

namespace App\Actions\Pendaftaran;

use App\Models\Pendaftaran;
use App\Services\NikHasher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * HW-15 Tab Data Diri (FR-11, AC-04.1, AC-04.7, BR-62): simpan sebagian tanpa harus lengkap.
 * NIK disimpan terenkripsi (cast `encrypted`) + `nik_hash` HMAC untuk pencarian tanpa dekripsi.
 */
class SimpanDraf
{
    public function __construct(private NikHasher $nik) {}

    /** @param  array<string, mixed>  $data  hasil SimpanDrafRequest (semua kolom boleh kosong) */
    public function __invoke(Pendaftaran $pendaftaran, array $data): Pendaftaran
    {
        return DB::transaction(function () use ($pendaftaran, $data) {
            $terkunci = Pendaftaran::whereKey($pendaftaran->id)->lockForUpdate()->firstOrFail();
            self::pastikanDraf($terkunci);

            if (array_key_exists('nik', $data)) {
                $nik = $data['nik'];
                unset($data['nik']);
                $data['nik_terenkripsi'] = $nik;
                $data['nik_hash'] = $nik === null ? null : $this->nik->hash($nik);
            }

            $terkunci->update($data);

            return $terkunci;
        });
    }

    /** AC-04.4 / BR-09: setelah Diajukan (atau Draf dibatalkan karena periode ditutup) formulir hanya dapat dilihat. */
    public static function pastikanDraf(Pendaftaran $pendaftaran): void
    {
        if (! $pendaftaran->adalahDraf()) {
            throw ValidationException::withMessages([
                'formulir' => 'Formulir sudah '.mb_strtolower($pendaftaran->status->label()).' sehingga tidak dapat diubah lagi.',
            ]);
        }
    }
}
