<?php

namespace App\Support\Ui;

use App\Models\Dokumen;
use App\Models\DokumenVersi;
use App\Models\Pendaftaran;
use App\Models\VerifikasiDokumen;
use App\Services\NikHasher;
use App\Services\Pengaturan;
use Illuminate\Support\Facades\URL;

/**
 * Pendaftaran → array tampilan portal peserta (HW-13–16). Bentuk array = kontrak fixture resources/preview/hw-13…16.
 * Hanya membaca; aturan bisnis ada di app/Actions/Pendaftaran.
 */
final class TampilanPendaftaran
{
    public const TAHAP = ['Formulir Diajukan', 'Verifikasi Berkas', 'Masuk Kuota', 'Verifikasi Pembayaran', 'NPD Terbit'];

    /** Masa berlaku URL pratinjau berkas (NFR-07c: ≤ 15 menit). */
    public const MENIT_URL = 15;

    /** Muat relasi yang dipakai semua penyaji di bawah (sekali query per relasi). */
    public static function muat(Pendaftaran $p): Pendaftaran
    {
        return $p->load(['peminatanPeriode.peminatan.track', 'peminatanPeriode.periode', 'user', 'pesertaDidik',
            'dokumens.versiAktif.verifikasi', 'dokumens.versis.verifikasi']);
    }

    /**
     * Kartu "Pendaftaran Anda".
     *
     * @return array{nomor: string, status: string, track: string, peminatan: string, periode: string, tanggal_daftar: string, draf: bool, nama: string}
     */
    public static function ringkasan(Pendaftaran $p): array
    {
        $pp = $p->peminatanPeriode;

        return [
            'nomor' => $p->nomor_pendaftaran,
            'status' => $p->labelStatus(),
            'track' => $pp->peminatan->track->nama,
            'peminatan' => $pp->peminatan->nama,
            'periode' => $pp->periode->nama,
            'tanggal_daftar' => Format::tanggal($p->created_at),
            'draf' => $p->adalahDraf(),
            'nama' => (string) ($p->nama_lengkap ?: $p->user->name),
        ];
    }

    /**
     * FR-15 / AC-07.1: tracker 5 tahap, tahap saat ini disorot.
     *
     * @return array{langkah: list<array{label: string, state: string}>, keterangan: string}
     */
    public static function tracker(Pendaftaran $p): array
    {
        $status = $p->status->getValue();
        $aktif = match ($status) {
            'draf' => 0,
            'diajukan', 'perlu_revisi' => 1,
            'masuk_kuota' => 3, // tahap "Masuk Kuota" langsung selesai saat berkas valid (K-42)
            'npd_terbit' => 5,
            default => $p->waktu_masuk_kuota ? 3 : ($p->waktu_pengajuan ? 1 : 0), // dibatalkan: berhenti di tahap terakhir
        };

        $langkah = [];
        foreach (self::TAHAP as $i => $label) {
            $langkah[] = ['label' => $label, 'state' => $i < $aktif ? 'selesai' : ($i === $aktif ? 'aktif' : 'belum')];
        }

        $keterangan = match (true) {
            $status === 'draf' => 'Tahap 1 dari 5 · Draf, belum diajukan',
            $aktif >= 5 => 'Tahap 5 dari 5 · NPD Terbit',
            default => 'Tahap '.($aktif + 1).' dari 5 · '.$p->labelStatus(),
        };

        return ['langkah' => $langkah, 'keterangan' => $keterangan];
    }

    /**
     * AC-07.3: banner status + penjelasan (dasbor & halaman status).
     *
     * @return array{nada: string, judul: string, teks: string}
     */
    public static function banner(Pendaftaran $p): array
    {
        $batasHari = app(Pengaturan::class)->int('batas_pelunasan_formulir_hari');

        return match ($p->status->getValue()) {
            'draf' => ['nada' => 'info', 'judul' => 'Ajukan formulir sebelum '.Format::tanggal($p->peminatanPeriode->periode->tanggal_selesai),
                'teks' => self::teksKekurangan($p).' Draf yang belum diajukan otomatis dibatalkan saat periode ditutup.'],
            'diajukan' => ['nada' => 'info', 'judul' => 'Formulir sudah diajukan',
                'teks' => 'Berkas Anda menunggu verifikasi Admin SILAU. Anda akan menerima notifikasi setiap ada perubahan status.'],
            'perlu_revisi' => ['nada' => 'error', 'judul' => self::jumlahDitolak($p).' dokumen perlu diperbaiki',
                'teks' => 'Admin SILAU menolak sebagian berkas. Periksa alasannya lalu unggah ulang dokumen yang ditolak.'],
            'masuk_kuota' => ['nada' => 'success', 'judul' => 'Berkas valid dan Anda Masuk Kuota',
                'teks' => 'Lunasi tagihan formulir paling lambat '.Format::tanggal(($p->waktu_masuk_kuota ?? now())->copy()->addDays($batasHari))
                    ." agar pendaftaran tidak dibatalkan (batas {$batasHari} hari)."],
            'npd_terbit' => ['nada' => 'success', 'judul' => 'NPD terbit · '.($p->pesertaDidik->npd ?? ''),
                'teks' => 'Selamat, Anda resmi menjadi Peserta Didik. Tagihan Semester 1 dapat dilihat di menu Tagihan.'],
            default => ['nada' => 'error', 'judul' => 'Pendaftaran dibatalkan',
                'teks' => ($p->alasan_batal ? $p->alasan_batal.'. ' : '').'Anda dapat mendaftar kembali pada periode pendaftaran berikutnya.'],
        };
    }

    /**
     * Halaman status: "Langkah berikutnya".
     *
     * @return array{badge: string, teks: string, tombol: string|null, href: string|null}
     */
    public static function langkah(Pendaftaran $p): array
    {
        return match ($p->status->getValue()) {
            'draf' => ['badge' => 'Draf', 'teks' => 'Lengkapi data diri dan berkas wajib, lalu ajukan formulir dari tab Tinjau & Ajukan.',
                'tombol' => 'Lanjutkan formulir', 'href' => route('pendaftaran.formulir', $p)],
            'diajukan' => ['badge' => 'Verifikasi Berkas', 'teks' => 'Admin SILAU sedang memeriksa berkas Anda. Tidak ada yang perlu dilakukan saat ini.',
                'tombol' => null, 'href' => null],
            'perlu_revisi' => ['badge' => 'Perlu Revisi', 'teks' => 'Unggah ulang hanya dokumen yang ditolak. Dokumen Valid terkunci.',
                'tombol' => 'Buka berkas & revisi', 'href' => route('pendaftaran.berkas', $p)],
            'masuk_kuota' => ['badge' => 'Verifikasi Pembayaran Formulir', 'teks' => 'Bayar sesuai nominal tagihan (termasuk kode unik), lalu unggah bukti. NPD terbit setelah pembayaran disahkan Admin Keuangan.',
                'tombol' => 'Lihat tagihan formulir', 'href' => '#'], // STUB-FE(P05): rute HW-17/18
            'npd_terbit' => ['badge' => 'NPD Terbit', 'teks' => 'Pantau tagihan semester dan status keaktifan Anda dari dasbor.', 'tombol' => null, 'href' => null],
            default => ['badge' => 'Dibatalkan', 'teks' => 'Pendaftaran ini sudah berakhir. Bila tagihan sudah lunas, refund diproses lewat menu Refund. Anda dapat mendaftar lagi saat periode baru dibuka.', 'tombol' => null, 'href' => null],
        };
    }

    /**
     * Riwayat status (halaman status). Diturunkan dari kolom waktu pendaftaran & keputusan verifikasi
     * (tidak ada tabel riwayat status di ERD, A-81).
     *
     * @return list<array{judul: string, waktu: string, teks: string}>
     */
    public static function riwayat(Pendaftaran $p): array
    {
        $status = $p->status->getValue();
        $r = [['judul' => 'Draf disimpan', 'w' => $p->created_at, 'teks' => "Nomor pendaftaran {$p->nomor_pendaftaran} dibuat."]];

        if ($p->waktu_pengajuan) {
            $r[] = ['judul' => 'Formulir diajukan', 'w' => $p->waktu_pengajuan, 'teks' => 'Formulir dan berkas wajib diajukan untuk diverifikasi.'];
        }

        $verifikasiTerakhir = VerifikasiDokumen::query()
            ->join('dokumen_versis', 'dokumen_versis.id', '=', 'verifikasi_dokumens.dokumen_versi_id')
            ->join('dokumens', 'dokumens.id', '=', 'dokumen_versis.dokumen_id')
            ->where('dokumens.pendaftaran_id', $p->id)->max('verifikasi_dokumens.created_at');

        if ($status === 'perlu_revisi') {
            $r[] = ['judul' => 'Perlu Revisi', 'w' => $verifikasiTerakhir ?? $p->updated_at, 'teks' => self::jumlahDitolak($p).' dokumen ditolak Admin SILAU. Lihat alasannya di halaman Berkas.'];
        } elseif ($verifikasiTerakhir && $p->waktu_masuk_kuota) {
            $r[] = ['judul' => 'Verifikasi berkas selesai', 'w' => $verifikasiTerakhir, 'teks' => 'Seluruh berkas wajib dinyatakan Valid oleh Admin SILAU.'];
        }

        if ($p->waktu_masuk_kuota) {
            $r[] = ['judul' => 'Masuk Kuota', 'w' => $p->waktu_masuk_kuota, 'teks' => 'Berkas valid. Lunasi tagihan formulir untuk mendapatkan NPD.'];
        }
        if ($status === 'npd_terbit' && $p->pesertaDidik) {
            $r[] = ['judul' => 'NPD Terbit', 'w' => $p->pesertaDidik->npd_terbit_pada ?? $p->updated_at, 'teks' => "Nomor Peserta Didik {$p->pesertaDidik->npd} terbit."];
        }
        if ($status === 'dibatalkan') {
            $r[] = ['judul' => $p->status->label(), 'w' => $p->updated_at, 'teks' => $p->alasan_batal ?: self::banner($p)['teks']];
        }

        return array_map(fn (array $e) => ['judul' => $e['judul'], 'waktu' => Format::tanggalJam($e['w']), 'teks' => $e['teks']], $r);
    }

    /**
     * HW-15 Tab Berkas & HW-16: satu baris per jenis dokumen (BR-11).
     *
     * @return list<array{jenis: string, label: string, wajib: bool, keterangan: string, status: string, berkas: array{nama: string, info: string, url: string}|null, alasan: string|null, bisa_unggah: bool}>
     */
    public static function berkas(Pendaftaran $p): array
    {
        $dokumens = $p->dokumens->keyBy('jenis');
        $hasil = [];

        foreach (Dokumen::JENIS as $jenis => $info) {
            /** @var Dokumen|null $d */
            $d = $dokumens->get($jenis);
            $versi = $d?->versiAktif;
            $status = match (true) {
                $versi === null => $info['wajib'] ? 'Belum terunggah' : 'Opsional',
                $p->adalahDraf() => 'Terunggah',
                default => $d->status->label(),
            };

            $hasil[] = [
                'jenis' => $jenis,
                'label' => $info['label'],
                'wajib' => $info['wajib'],
                'keterangan' => $info['keterangan'],
                'status' => $status,
                'berkas' => $versi ? self::berkasVersi($p, $versi) : null,
                'alasan' => $d && $status === 'Ditolak' ? self::alasanTerakhir($versi) : null,
                'bisa_unggah' => $p->adalahDraf() || ($p->status->getValue() === 'perlu_revisi' && $status === 'Ditolak'),
            ];
        }

        return $hasil;
    }

    /**
     * FR-17 / AC-08.4: seluruh versi + keputusan + alasan.
     *
     * @return list<array{dokumen: string, versi: string, berkas: string, status: string, alasan: string|null}>
     */
    public static function riwayatVersi(Pendaftaran $p): array
    {
        $baris = [];
        foreach ($p->dokumens as $d) {
            $jumlah = $d->versis->count();
            foreach ($d->versis as $i => $v) {
                $keputusan = $v->verifikasi->sortByDesc('id')->first();
                $baris[] = [
                    'dokumen' => $d->label(),
                    'versi' => 'v'.($jumlah - $i),
                    'berkas' => $v->nama_asli.' · '.Format::tanggal($v->diunggah_pada),
                    'status' => match (true) {
                        $keputusan !== null => $keputusan->keputusan === 'valid' ? 'Valid' : 'Ditolak',
                        $v->id === $d->versi_aktif_id => $p->adalahDraf() ? 'Terunggah' : 'Menunggu Verifikasi',
                        default => 'Diganti',
                    },
                    'alasan' => $keputusan?->alasan,
                ];
            }
        }

        return $baris;
    }

    /**
     * Ringkasan data diri untuk Tinjau & Ajukan / Biodata (NIK tersamar, BR-62).
     *
     * @return array<string, string>
     */
    public static function dataDiri(Pendaftaran $p): array
    {
        $kosong = '—';

        return [
            'Nama lengkap' => $p->nama_lengkap ?: $kosong,
            'NIK' => $p->nik_terenkripsi ? NikHasher::samarkan($p->nik_terenkripsi) : $kosong,
            'Tempat, tanggal lahir' => trim(($p->tempat_lahir ?? '').($p->tanggal_lahir ? ', '.Format::tanggal($p->tanggal_lahir) : ''), ', ') ?: $kosong,
            'Jenis kelamin' => Pendaftaran::JENIS_KELAMIN[$p->jenis_kelamin ?? ''] ?? $kosong,
            'Nomor HP' => $p->no_hp ?: $kosong,
            'Alamat' => $p->alamat ?: $kosong,
            'Asal sekolah' => $p->asal_sekolah ?: $kosong,
            'Tahun lulus' => $p->tahun_lulus ? (string) $p->tahun_lulus : $kosong,
        ];
    }

    /** @return array{nama: string, info: string, url: string} */
    private static function berkasVersi(Pendaftaran $p, DokumenVersi $v): array
    {
        $jenisBerkas = ['application/pdf' => 'PDF', 'image/jpeg' => 'JPG', 'image/png' => 'PNG'][$v->mime] ?? strtoupper($v->mime);
        $ukuran = $v->ukuran >= 1048576 ? number_format($v->ukuran / 1048576, 1, ',', '.').' MB' : max(1, (int) round($v->ukuran / 1024)).' KB';

        return [
            'nama' => $v->nama_asli,
            'info' => "{$jenisBerkas} · {$ukuran} · ".Format::wib($v->diunggah_pada)->translatedFormat('j M Y'),
            'url' => URL::temporarySignedRoute('pendaftaran.berkas.lihat', now()->addMinutes(self::MENIT_URL), ['pendaftaran' => $p, 'versi' => $v->id]),
        ];
    }

    private static function alasanTerakhir(?DokumenVersi $v): ?string
    {
        return $v?->verifikasi->where('keputusan', 'ditolak')->sortByDesc('id')->first()?->alasan;
    }

    private static function jumlahDitolak(Pendaftaran $p): int
    {
        return $p->dokumens->filter(fn (Dokumen $d) => $d->status->getValue() === 'ditolak')->count();
    }

    private static function teksKekurangan(Pendaftaran $p): string
    {
        $k = $p->kekurangan();
        $bagian = array_filter([
            $k['data'] ? 'lengkapi '.mb_strtolower(implode(', ', $k['data'])) : null,
            $k['berkas'] ? 'unggah '.implode(', ', $k['berkas']) : null,
        ]);

        return $bagian ? ucfirst(implode(' dan ', $bagian)).', lalu ajukan dari tab Tinjau & Ajukan.' : 'Data dan berkas wajib sudah lengkap. Ajukan dari tab Tinjau & Ajukan.';
    }
}
