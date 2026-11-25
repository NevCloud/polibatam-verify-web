<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Tabel `audit_logs` (ERD §3.18): append-only. Tulis HANYA lewat App\Services\AuditLogger (AE-10);
 * UPDATE/DELETE ditolak trigger dan GRANT.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $aksi
 * @property string $hasil
 * @property string|null $alasan
 * @property string|null $ip_address
 * @property Carbon $created_at
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'audit_logs';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'nilai_sebelum' => 'array',
            'nilai_sesudah' => 'array',
        ];
    }
}
