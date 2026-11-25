<?php

namespace App\Models;

use App\States\Refund\StatusRefund;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;

/**
 * Tabel `refunds` (ERD).
 *
 * @property StatusRefund $status
 */
class Refund extends Model
{
    use HasStates;

    protected $table = 'refunds';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => StatusRefund::class,
            'no_rekening_terenkripsi' => 'encrypted', // AE-06, BR-50
        ];
    }

    /** AE-06: nomor rekening utuh tidak ikut serialisasi. */
    protected $hidden = ['no_rekening_terenkripsi'];
}
