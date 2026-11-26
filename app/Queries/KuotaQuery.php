<?php

namespace App\Queries;

use App\Models\PeminatanPeriode;
use App\Models\PeriodePendaftaran;
use App\Support\Ui\Format;
use Illuminate\Support\Facades\DB;

/**
 * HW-36 & HW-37 (FR-48, FR-49, BR-23, BR-75): kuota minimum dan keterisian per peminatan pada satu periode.
 * Hanya untuk admin; tidak pernah dipakai di halaman peserta/publik (BR-75).
 */
class KuotaQuery
{
    public const HARI_PERINGATAN = 7;

    /** Periode dari `?periode=`; bawaan: yang sedang dibuka, lalu yang terbaru. */
    public static function periode(mixed $id): ?PeriodePendaftaran
    {
        return PeriodePendaftaran::find((int) $id)
            ?? PeriodePendaftaran::where('status', 'dibuka')->orderBy('tanggal_selesai')->first()
            ?? PeriodePendaftaran::orderByDesc('tanggal_mulai')->first();
    }

    /** @return array<int, string> */
    public static function daftarPeriode(): array
    {
        return PeriodePendaftaran::orderByDesc('tanggal_mulai')->pluck('nama', 'id')->all();
    }

    /** Sisa hari sampai periode ditutup (dibulatkan ke atas); 0 bila sudah lewat. */
    public static function sisaHari(PeriodePendaftaran $periode): int
    {
        return max(0, (int) ceil(now()->diffInSeconds($periode->tanggal_selesai, false) / 86400));
    }

    /**
     * Baris per peminatan yang dibuka pada periode: jumlah per status + penanda.
     *
     * @return list<array{id: int, peminatan_id: int, peminatan: string, kode: string, track: string, kuota_min: int, diajukan: int, masuk_kuota: int, npd_terbit: int, persen: int|null, penanda: string, nada: string, dibatalkan: bool}>
     */
    public static function baris(PeriodePendaftaran $periode): array
    {
        $hitung = DB::table('pendaftarans')
            ->join('peminatan_periodes as pp', 'pp.id', '=', 'pendaftarans.peminatan_periode_id')
            ->where('pp.periode_pendaftaran_id', $periode->id)
            ->whereIn('pendaftarans.status', ['diajukan', 'masuk_kuota', 'npd_terbit'])
            ->groupBy('pendaftarans.peminatan_periode_id', 'pendaftarans.status')
            ->selectRaw('pendaftarans.peminatan_periode_id as id, pendaftarans.status, COUNT(*) as n')
            ->get()->groupBy('id');
        $ditutup = $periode->status === 'ditutup' || now()->greaterThanOrEqualTo($periode->tanggal_selesai);
        $dekat = self::sisaHari($periode) <= self::HARI_PERINGATAN;

        return PeminatanPeriode::where('periode_pendaftaran_id', $periode->id)->with('peminatan.track')->get()
            ->sortBy(fn (PeminatanPeriode $pp) => $pp->peminatan->track->nama.'|'.$pp->peminatan->nama)
            ->map(function (PeminatanPeriode $pp) use ($hitung, $ditutup, $dekat) {
                $n = fn (string $status) => (int) ($hitung->get($pp->id)?->firstWhere('status', $status)->n ?? 0);
                $npd = $n('npd_terbit');
                [$penanda, $nada] = match (true) {
                    $pp->status === 'dibatalkan' => ['Dibatalkan', 'neutral'],
                    $npd >= $pp->kuota_min => ['Tercapai', 'success'],
                    $ditutup => ['Di bawah minimum', 'error'],         // dievaluasi Super Admin (K-42, P10)
                    $dekat => ['Peringatan', 'warning'],                // AC-24.2: ≤ 7 hari & NPD < minimum
                    default => ['Berjalan', 'info'],
                };

                return [
                    'id' => $pp->id,
                    'peminatan_id' => $pp->peminatan_id,
                    'peminatan' => $pp->peminatan->nama,
                    'kode' => $pp->peminatan->kode,
                    'track' => $pp->peminatan->track->nama,
                    'kuota_min' => $pp->kuota_min,
                    'diajukan' => $n('diajukan'),
                    'masuk_kuota' => $n('masuk_kuota'),
                    'npd_terbit' => $npd,
                    'persen' => $pp->kuota_min > 0 ? (int) round($npd / $pp->kuota_min * 100) : null,
                    'penanda' => $penanda,
                    'nada' => $nada,
                    'dibatalkan' => $pp->status === 'dibatalkan',
                ];
            })->values()->all();
    }

    /** @return array{nama: string, rentang: string, sisa_hari: int, ditutup: bool} */
    public static function ringkasPeriode(PeriodePendaftaran $periode): array
    {
        return [
            'nama' => $periode->nama,
            'rentang' => Format::tanggal($periode->tanggal_mulai).' – '.Format::tanggal($periode->tanggal_selesai),
            'sisa_hari' => self::sisaHari($periode),
            'ditutup' => $periode->status === 'ditutup' || now()->greaterThanOrEqualTo($periode->tanggal_selesai),
        ];
    }
}
