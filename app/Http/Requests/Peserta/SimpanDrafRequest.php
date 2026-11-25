<?php

namespace App\Http\Requests\Peserta;

use App\Models\Pendaftaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * HW-15 Tab Data Diri: semua kolom boleh kosong saat Draf (AC-04.1), tetapi yang diisi harus berformat benar (NFR-35).
 * Kelengkapan dicek saat Ajukan (AjukanFormulir).
 */
class SimpanDrafRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $bersih = fn ($v) => is_string($v) ? (trim($v) === '' ? null : trim($v)) : $v;
        $data = array_map($bersih, $this->only(['nik', 'nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'no_hp', 'alamat', 'asal_sekolah', 'tahun_lulus']));

        // NIK & nomor HP boleh ditulis dengan spasi/tanda hubung (Figma: "2171 0208 0707 0003", "+62 812-3456-7890").
        foreach (['nik', 'no_hp'] as $kolom) {
            if (is_string($data[$kolom] ?? null)) {
                $data[$kolom] = preg_replace('/[\s\-]/', '', $data[$kolom]);
            }
        }

        $this->merge($data);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nik' => ['nullable', 'digits:16'],
            'nama_lengkap' => ['nullable', 'string', 'max:150'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today', 'after:1940-01-01'],
            'jenis_kelamin' => ['nullable', Rule::in(array_keys(Pendaftaran::JENIS_KELAMIN))],
            'no_hp' => ['nullable', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'asal_sekolah' => ['nullable', 'string', 'max:150'],
            'tahun_lulus' => ['nullable', 'integer', 'min:1960', 'max:'.(now()->year + 1)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus 16 digit angka sesuai KTP.',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'tanggal_lahir.after' => 'Tanggal lahir tidak valid.',
            'jenis_kelamin.in' => 'Pilih Laki-laki atau Perempuan.',
            'no_hp.regex' => 'Nomor HP harus nomor seluler Indonesia, mis. 0812xxxxxxx atau +62812xxxxxxx.',
            'tahun_lulus.*' => 'Tahun lulus harus tahun 4 digit antara 1960 dan '.(now()->year + 1).'.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'nama lengkap', 'tempat_lahir' => 'tempat lahir', 'alamat' => 'alamat', 'asal_sekolah' => 'asal sekolah',
        ];
    }
}
