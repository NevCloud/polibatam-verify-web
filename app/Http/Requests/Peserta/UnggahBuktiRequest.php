<?php

namespace App\Http\Requests\Peserta;

/**
 * HW-18 (FR-29, BR-12, BR-72, NFR-07): berkas bukti (aturan sama dengan dokumen: magic bytes, ≤ ukuran_maks_unggah_kb)
 * + data pengiriman wajib. Nominal boleh ditulis "Rp350.284" / "350.284".
 */
class UnggahBuktiRequest extends UnggahDokumenRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($n = $this->input('nominal_transfer'))) {
            $this->merge(['nominal_transfer' => preg_replace('/\D/', '', $n) ?: null]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return parent::rules() + [
            'tanggal_transfer' => ['required', 'date', 'before_or_equal:today', 'after:2020-01-01'],
            'nominal_transfer' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'bank_pengirim' => ['required', 'string', 'max:100'],
            'nama_pengirim' => ['required', 'string', 'max:150'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return parent::messages() + [
            'tanggal_transfer.required' => 'Isi tanggal transfer.',
            'tanggal_transfer.before_or_equal' => 'Tanggal transfer tidak boleh melebihi hari ini.',
            'tanggal_transfer.*' => 'Tanggal transfer tidak valid.',
            'nominal_transfer.required' => 'Isi nominal yang Anda transfer.',
            'nominal_transfer.*' => 'Nominal transfer harus berupa angka Rupiah, mis. 350284.',
            'bank_pengirim.required' => 'Isi bank pengirim.',
            'nama_pengirim.required' => 'Isi nama pemilik rekening pengirim.',
        ];
    }
}
