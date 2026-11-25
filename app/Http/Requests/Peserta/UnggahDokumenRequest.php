<?php

namespace App\Http\Requests\Peserta;

use App\Services\Pengaturan;
use Illuminate\Foundation\Http\FormRequest;

/**
 * BR-12, NFR-07(a), AC-05.2: PDF/JPG/PNG ≤ ukuran_maks_unggah_kb (pengaturans).
 * `mimetypes` membaca tipe MIME dari isi berkas (magic bytes via finfo), `extensions` memeriksa nama;
 * berkas berekstensi palsu (mis. .exe diganti .pdf) gagal di `mimetypes`.
 */
class UnggahDokumenRequest extends FormRequest
{
    public const PESAN = 'Format berkas harus PDF, JPG, atau PNG dengan ukuran maksimal :ukuran.';

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'berkas' => ['required', 'file', 'max:'.$this->batasKb(), 'mimetypes:application/pdf,image/jpeg,image/png', 'extensions:pdf,jpg,jpeg,png'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $pesan = str_replace(':ukuran', self::ukuranTeks($this->batasKb()), self::PESAN);

        return [
            'berkas.required' => 'Pilih berkas yang akan diunggah.',
            'berkas.file' => 'Berkas gagal diunggah. Coba pilih ulang berkasnya.',
            'berkas.uploaded' => $pesan,
            'berkas.max' => $pesan,
            'berkas.mimetypes' => $pesan,
            'berkas.extensions' => $pesan,
        ];
    }

    public static function ukuranTeks(int $kb): string
    {
        return $kb % 1024 === 0 ? ($kb / 1024).' MB' : $kb.' KB';
    }

    private function batasKb(): int
    {
        return app(Pengaturan::class)->int('ukuran_maks_unggah_kb');
    }
}
