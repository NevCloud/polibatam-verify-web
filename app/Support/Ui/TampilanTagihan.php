<?php

namespace App\Support\Ui;

use App\Models\BuktiPembayaran;
use App\Models\Tagihan;
use App\Services\CekVersiData;
use App\Services\Pengaturan;
use Illuminate\Support\Facades\URL;

/** Tagihan → array tampilan (HW-17, HW-18, HW-28, HW-29, HW-43). Bentuk = kontrak fixture hw-17/18/28/29/43. */
final class TampilanTagihan
{
    /**
     * @return array{kode: string, jenis: string, nominal: string, jatuh_tempo: string, status: string, bisa_bayar: bool, href: string}
     */
    public static function baris(Tagihan $t, string $rute = 'tagihan.show'): array
    {
        return [
            'kode' => $t->kode_referensi,
            'jenis' => $t->labelJenis(),
            'nominal' => Format::rupiah($t->nominal_total),
            'jatuh_tempo' => self::jatuhTempo($t),
            'status' => $t->status->label(),
            'bisa_bayar' => in_array($t->status->getValue(), ['belum_bayar', 'ditolak'], true),
            'href' => route($rute, $t),
        ];
    }

    /** BR-35: semester tanpa jatuh tempo = "Menunggu penetapan"; formulir dibatasi 7 hari setelah Masuk Kuota (BR-21). */
    public static function jatuhTempo(Tagihan $t): string
    {
        return match (true) {
            $t->jatuh_tempo !== null => Format::tanggal($t->jatuh_tempo),
            $t->jenis === 'semester' => 'Menunggu penetapan',
            default => app(Pengaturan::class)->int('batas_pelunasan_formulir_hari').' hari setelah Masuk Kuota',
        };
    }

    /**
     * Rincian + rekening snapshot (HW-18, HW-29).
     *
     * @return array<string, mixed>
     */
    public static function rincian(Tagihan $t): array
    {
        $pp = $t->pendaftaran->peminatanPeriode;

        return self::baris($t) + [
            'versi' => CekVersiData::versi($t),
            'jenis_kode' => $t->jenis,
            'nominal_dasar' => Format::rupiah($t->nominal_dasar),
            'kode_unik' => sprintf('%03d', $t->kode_unik),
            'nominal_total_angka' => $t->nominal_total,
            'track' => $pp->peminatan->track->nama,
            'peminatan' => $pp->peminatan->nama,
            'nomor_pendaftaran' => $t->pendaftaran->nomor_pendaftaran,
            'status_pendaftaran' => $t->pendaftaran->labelStatus(),
            'nama' => (string) ($t->pendaftaran->nama_lengkap ?: $t->user->name),
            'lunas_pada' => $t->lunas_pada ? Format::tanggalJam($t->lunas_pada) : null,
            'rekening' => $t->rekening_nomor ? ['bank' => $t->rekening_bank, 'nomor' => $t->rekening_nomor, 'atas_nama' => $t->rekening_atas_nama] : null,
        ];
    }

    /**
     * Riwayat bukti + data pengiriman (BR-72). $rute = rute pratinjau bertanda tangan (peserta / admin).
     *
     * @return list<array<string, mixed>>
     */
    public static function bukti(Tagihan $t, string $rute): array
    {
        return $t->buktis->map(fn (BuktiPembayaran $b) => [
            'diunggah' => Format::tanggalJam($b->diunggah_pada),
            'tanggal_transfer' => $b->tanggal_transfer ? Format::tanggal($b->tanggal_transfer) : '—',
            'nominal_transfer' => $b->nominal_transfer !== null ? Format::rupiah($b->nominal_transfer) : '—',
            'selisih' => $b->nominal_transfer !== null && $b->nominal_transfer !== $t->nominal_total,
            'bank_pengirim' => $b->bank_pengirim ?? '—',
            'nama_pengirim' => $b->nama_pengirim ?? '—',
            'keputusan' => ['menunggu' => 'Menunggu Verifikasi', 'diterima' => 'Lunas', 'ditolak' => 'Ditolak'][$b->keputusan] ?? $b->keputusan,
            'alasan' => $b->alasan,
            'gambar' => str_starts_with($b->mime, 'image/'),
            'url' => URL::temporarySignedRoute($rute, now()->addMinutes(TampilanPendaftaran::MENIT_URL), ['tagihan' => $t, 'bukti' => $b->id]),
        ])->values()->all();
    }
}
