<?php

namespace App\Models;

use App\States\PesertaDidik\StatusPesertaDidik;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\HasStates;

/**
 * Tabel `peserta_didiks` (ERD).
 *
 * @property StatusPesertaDidik $status
 * @property int $semester_berjalan
 * @property string $npd
 * @property Carbon|null $npd_terbit_pada
 */
class PesertaDidik extends Model
{
    use HasStates;

    protected $table = 'peserta_didiks';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => StatusPesertaDidik::class,
            'npd_terbit_pada' => 'datetime',
        ];
    }

    /** "Belum Aktif Semester 2", "Aktif Semester 1", dst. (design-memory §6.2). */
    public function labelStatus(): string
    {
        $label = $this->status->label();

        return str_ends_with($label, 'Semester') ? "{$label} {$this->semester_berjalan}" : $label;
    }
}
