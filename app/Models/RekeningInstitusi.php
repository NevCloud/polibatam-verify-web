<?php

namespace App\Models;

use App\States\RekeningInstitusi\StatusRekeningInstitusi;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;

/**
 * Tabel `rekening_institusis` (ERD). Satu Aktif per jenis tagihan dijaga UNIQUE `kunci_aktif` (BR-70).
 *
 * @property StatusRekeningInstitusi $status
 */
class RekeningInstitusi extends Model
{
    use HasStates;

    protected $table = 'rekening_institusis';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => StatusRekeningInstitusi::class,
        ];
    }
}
