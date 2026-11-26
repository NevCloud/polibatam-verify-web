<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Tabel `konten_cms` (FR-10, BR-55): satu baris `draf` dan satu baris `terbit` per bagian landing page.
 * Isi teks polos (ditampilkan ter-escape; tidak ada HTML dari CMS). Tarif & kuota TIDAK berasal dari sini (AC-29.4).
 *
 * @property int $id
 * @property string $bagian
 * @property string $judul
 * @property string $isi
 * @property string $status draf / terbit
 * @property Carbon|null $diterbitkan_pada
 * @property int $diubah_oleh
 * @property Carbon|null $updated_at
 */
class KontenCms extends Model
{
    protected $table = 'konten_cms';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** Bagian landing page + petunjuk format isi (satu butir per baris; "A | B" untuk pasangan). */
    public const BAGIAN = [
        'pengumuman' => ['label' => 'Pengumuman', 'format' => 'Satu kalimat pengumuman per baris.'],
        'jadwal' => ['label' => 'Jadwal', 'format' => 'Catatan jadwal, satu per baris. Tanggal buka/tutup periode diambil otomatis dari data periode.'],
        'alur' => ['label' => 'Alur pendaftaran', 'format' => 'Satu langkah per baris: Judul langkah | keterangan singkat.'],
        'faq' => ['label' => 'FAQ', 'format' => 'Satu pertanyaan per baris: Pertanyaan? | Jawaban.'],
        'kontak' => ['label' => 'Kontak bantuan', 'format' => 'Satu baris per kanal (alamat, email, WhatsApp, jam layanan).'],
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['diterbitkan_pada' => 'datetime'];
    }

    /** @return list<string> baris tidak kosong */
    public function baris(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $this->isi) ?: []), fn (string $b) => $b !== ''));
    }

    /** @return list<array{0: string, 1: string}> pasangan "A | B" */
    public function pasangan(): array
    {
        return array_map(fn (string $b) => array_pad(array_map('trim', explode('|', $b, 2)), 2, ''), $this->baris());
    }
}
