<?php

namespace App\Services;

use App\Exceptions\SudahDiverifikasi;
use App\Exceptions\TransisiTidakSah;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\State;

/**
 * Optimistic locking keputusan verifikasi (BR-61, FR-61, AE-09; K-36):
 * UPDATE … WHERE id = ? AND updated_at = <versi yang dilihat admin> AND status = <status yang dilihat>.
 * 0 baris terubah → data sudah diputuskan pihak lain → SudahDiverifikasi (HTTP 409).
 */
class CekVersiData
{
    /** Versi = updated_at TIMESTAMP(6) apa adanya dari DB; dikirim ke klien dan dibawa balik sebagai `versi`. */
    public static function versi(Model $model): ?string
    {
        $nilai = $model->getRawOriginal('updated_at');

        return $nilai === null ? null : (string) $nilai;
    }

    /**
     * Ubah status lewat state machine + optimistic lock dalam satu UPDATE.
     *
     * @template TModel of Model
     *
     * @param  TModel  $model
     * @param  class-string<State<TModel>>  $ke
     * @param  array<string, mixed>  $data  kolom lain yang ikut diubah
     * @return string versi baru
     */
    public function transisi(Model $model, string $ke, ?string $versi, array $data = [], string $kolom = 'status'): string
    {
        /** @var State<Model> $sekarang */
        $sekarang = $model->{$kolom};

        if (! $sekarang->canTransitionTo($ke)) {
            throw TransisiTidakSah::dari(class_basename($model), $sekarang->getValue(), $ke::getMorphClass());
        }

        return $this->perbarui($model, $versi, [$kolom => $ke::getMorphClass()] + $data, [$kolom => $sekarang->getValue()]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $syarat  kondisi tambahan (mis. status yang dilihat admin)
     * @return string versi baru
     */
    public function perbarui(Model $model, ?string $versi, array $data, array $syarat = []): string
    {
        $baru = now('UTC')->format('Y-m-d H:i:s.u');

        $terubah = $model->newQuery()
            ->whereKey($model->getKey())
            ->where('updated_at', $versi)
            ->where($syarat)
            ->toBase()
            ->update($data + ['updated_at' => $baru]);

        if ($terubah !== 1) {
            throw new SudahDiverifikasi;
        }

        // forceFill (bukan setRawAttributes) agar cache objek status ikut diperbarui.
        $model->forceFill($data + ['updated_at' => $baru])->syncOriginal();

        return $baru;
    }
}
