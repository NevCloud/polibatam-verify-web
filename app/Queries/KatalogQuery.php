<?php

namespace App\Queries;

use App\Models\Dokumen;
use App\Models\KontenCms;
use App\Models\Peminatan;
use App\Models\PeminatanPeriode;
use App\Models\PeriodePendaftaran;
use App\Models\Track;
use App\Services\TarifResolver;
use App\Support\Ui\Format;
use Illuminate\Support\Collection;

/**
 * HW-01–03 (FR-08, FR-09, BR-27, BR-55, BR-75): katalog publik dari data master — peminatan aktif di track aktif, tarif
 * versi berlaku hari ini. TIDAK ADA informasi kuota (K-42). Tanpa cache: data kecil, perubahan tarif langsung tampil (NFR-15).
 */
class KatalogQuery
{
    /** Isi bawaan bila Super Admin belum menerbitkan bagian tersebut. */
    public const KONTEN_BAWAAN = [
        'pengumuman' => ['Pengumuman', 'Pantau jadwal pendaftaran dan informasi terbaru di halaman ini.'],
        'jadwal' => ['Jadwal pendaftaran', "Verifikasi berkas berjalan bertahap setelah formulir diajukan\nPelunasan formulir paling lambat 7 hari setelah Masuk Kuota"],
        'alur' => ['5 langkah mudah, satu perjalanan baru.', "Buat akun | Daftarkan email dan siapkan akun Polibatam Verify.\nPengisian data | Lengkapi biodata, pilih track dan peminatan dalam satu registrasi.\nVerifikasi berkas admin | Unggah berkas sesuai persyaratan dan pantau hasil pemeriksaan admin.\nVerifikasi pembayaran keuangan | Setelah berkas valid, bayar tagihan formulir dan unggah bukti transfer.\nLihat hasil pendaftaran | Cek NPD dan tagihan semester melalui dasbor Anda."],
        'faq' => ['Pertanyaan yang sering ditanyakan.', "Bagaimana cara mendaftar di Polibatam Verify? | Buat akun, verifikasi email, lalu lengkapi formulir pendaftaran dan unggah berkas dari dasbor.\nDi mana saya dapat melihat jadwal pendaftaran? | Jadwal periode yang sedang dibuka tampil di beranda dan katalog peminatan.\nBagaimana memantau status verifikasi berkas? | Buka menu Status di dasbor; setiap perubahan juga dikirim lewat notifikasi dan email."],
        'kontak' => ['Kontak bantuan', "Jl. Ahmad Yani, Batam Centre, Kota Batam, Kepulauan Riau 29461\nEmail: info@polibatam.ac.id"],
    ];

    public function __construct(private TarifResolver $tarif) {}

    /** Periode yang sedang dibuka sekarang (BR-54: dibuka/ditutup otomatis sesuai tanggal). */
    public static function periodeBuka(): ?PeriodePendaftaran
    {
        return PeriodePendaftaran::where('status', 'dibuka')->where('tanggal_mulai', '<=', now())->where('tanggal_selesai', '>', now())
            ->orderBy('tanggal_selesai')->first();
    }

    /** FR-09: jadwal periode berikutnya saat pendaftaran tutup. */
    public static function periodeBerikutnya(): ?PeriodePendaftaran
    {
        return PeriodePendaftaran::where('tanggal_mulai', '>', now())->where('status', '<>', 'ditutup')->orderBy('tanggal_mulai')->first();
    }

    /** @return array{buka: bool, nama: string|null, teks: string} */
    public static function infoPeriode(): array
    {
        if ($p = self::periodeBuka()) {
            return ['buka' => true, 'nama' => $p->nama, 'teks' => 'Pendaftaran dibuka '.Format::tanggal($p->tanggal_mulai).' – '.Format::tanggal($p->tanggal_selesai)];
        }
        $n = self::periodeBerikutnya();

        return ['buka' => false, 'nama' => $n?->nama, 'teks' => $n
            ? 'Pendaftaran ditutup. Periode berikutnya ('.$n->nama.') dibuka '.Format::tanggal($n->tanggal_mulai).'.'
            : 'Pendaftaran ditutup. Jadwal periode berikutnya belum diumumkan.'];
    }

    /** @return array<string, string> kode → nama track aktif yang punya peminatan aktif */
    public static function track(): array
    {
        return Track::where('is_active', true)->whereHas('peminatans', fn ($q) => $q->where('is_active', true))->orderBy('nama')->pluck('nama', 'kode')->all();
    }

    /**
     * Kartu katalog (HW-02).
     *
     * @return list<array<string, mixed>>
     */
    public function daftar(?string $trackKode = null): array
    {
        $dibuka = PeminatanPeriode::terbuka()->pluck('peminatan_id')->all();

        return $this->aktif()->when($trackKode, fn ($c) => $c->filter(fn (Peminatan $p) => $p->track->kode === $trackKode))
            ->map(function (Peminatan $p) use ($dibuka) {
                $semester = array_filter(array_map(fn (int $n) => $this->tarif->untuk($p->id, 'semester', $n)?->nominal, range(1, $p->durasi_semester)));

                return [
                    'kode' => $p->kode, 'nama' => $p->nama, 'track' => $p->track->nama, 'track_kode' => $p->track->kode, 'durasi' => $p->durasi_semester,
                    'formulir' => ($f = $this->tarif->untuk($p->id, 'formulir')) ? Format::rupiah($f->nominal) : null,
                    'semester' => match (true) {
                        $semester === [] => null,
                        min($semester) === max($semester) => Format::rupiah(min($semester)),
                        default => Format::rupiah(min($semester)).' – '.Format::rupiah(max($semester)),
                    },
                    'dibuka' => in_array($p->id, $dibuka, true),
                    'href' => route('katalog.show', $p->kode),
                ];
            })->values()->all();
    }

    /**
     * Detail peminatan (HW-03); null bila tidak aktif (AC-01.3).
     *
     * @return array<string, mixed>|null
     */
    public function detail(string $kode): ?array
    {
        $p = $this->aktif()->first(fn (Peminatan $p) => $p->kode === $kode);
        if (! $p) {
            return null;
        }
        $baris = ['Formulir pendaftaran' => $this->tarif->untuk($p->id, 'formulir')];
        foreach (range(1, $p->durasi_semester) as $n) {
            $baris["Semester {$n}"] = $this->tarif->untuk($p->id, 'semester', $n);
        }

        return [
            'kode' => $p->kode, 'nama' => $p->nama, 'track' => $p->track->nama, 'durasi' => $p->durasi_semester,
            'tarif' => array_map(fn (string $label) => [
                'komponen' => $label,
                'nominal' => $baris[$label] ? Format::rupiah($baris[$label]->nominal) : null,
                'sejak' => $baris[$label] ? Format::tanggal($baris[$label]->tanggal_efektif) : null,
            ], array_keys($baris)),
            'berkas' => array_values(array_map(fn (array $j) => $j['label'].($j['wajib'] ? ' (wajib)' : ' (opsional)'), Dokumen::JENIS)),
            'dibuka' => PeminatanPeriode::terbuka()->where('peminatan_id', $p->id)->exists(),
        ];
    }

    /**
     * Konten landing page per bagian: terbit (publik) atau draf (pratinjau Super Admin, AC-29.2), dengan isi bawaan sebagai cadangan.
     *
     * @return array<string, array{judul: string, baris: list<string>, pasangan: list<array{0: string, 1: string}>}>
     */
    public static function konten(bool $draf = false): array
    {
        $baris = KontenCms::whereIn('status', $draf ? ['draf', 'terbit'] : ['terbit'])->get()
            ->sortBy(fn (KontenCms $k) => $k->status === 'draf' ? 1 : 0)->keyBy('bagian'); // draf menimpa terbit saat pratinjau

        $hasil = [];
        foreach (self::KONTEN_BAWAAN as $bagian => [$judul, $isi]) {
            $k = $baris->get($bagian) ?? new KontenCms(['bagian' => $bagian, 'judul' => $judul, 'isi' => $isi]);
            $hasil[$bagian] = ['judul' => $k->judul, 'baris' => $k->baris(), 'pasangan' => $k->pasangan()];
        }

        return $hasil;
    }

    /** @return Collection<int, Peminatan> */
    private function aktif()
    {
        return Peminatan::with('track')->where('is_active', true)->whereHas('track', fn ($q) => $q->where('is_active', true))->get()
            ->sortBy(fn (Peminatan $p) => $p->track->nama.'|'.$p->nama)->values();
    }
}
