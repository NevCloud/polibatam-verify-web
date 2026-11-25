<?php

namespace App\Models;

use App\Notifications\Akun\TautanResetPassword;
use App\Notifications\Akun\VerifikasiEmail;
use App\States\PesertaDidik\Aktif;
use App\States\PesertaDidik\BelumAktif;
use App\States\PesertaDidik\Tertahan;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Tabel `users` (ERD §3.1). Satu role per akun (5 role, T-07).
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property bool $is_active
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $two_factor_confirmed_at
 * @property Carbon|null $privacy_consented_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $created_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /** Role admin: wajib 2FA (BR-59), tidak bisa dibuat lewat registrasi. */
    public const ROLE_ADMIN = ['admin_silau', 'admin_keuangan', 'admin_track', 'super_admin'];

    /** Nama resmi peran (design-memory §6). */
    public const LABEL_ROLE = [
        'peserta' => 'Calon Peserta',
        'admin_silau' => 'Admin SILAU',
        'admin_keuangan' => 'Admin Keuangan',
        'admin_track' => 'Admin Track',
        'super_admin' => 'Super Admin',
    ];

    protected $guarded = ['id'];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'privacy_consented_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function namaRole(): ?string
    {
        return $this->getRoleNames()->first();
    }

    public function adalahAdmin(): bool
    {
        return in_array($this->namaRole(), self::ROLE_ADMIN, true);
    }

    /** Label peran untuk UI; peserta dengan NPD = Peserta Didik (A-72). */
    public function labelRole(): string
    {
        $role = $this->namaRole();

        if ($role === 'peserta' && $this->pesertaDidiks()->exists()) {
            return 'Peserta Didik';
        }

        return self::LABEL_ROLE[$role] ?? '—';
    }

    /** @return HasMany<PesertaDidik, $this> */
    public function pesertaDidiks(): HasMany
    {
        return $this->hasMany(PesertaDidik::class);
    }

    /** Masih menjalani program (NPD terbit, belum selesai/dibatalkan): tidak boleh membuat pendaftaran baru. */
    public function masihPesertaDidik(): bool
    {
        return $this->pesertaDidiks()->whereIn('status', [
            BelumAktif::$name, Aktif::$name, Tertahan::$name,
        ])->exists();
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifikasiEmail);
    }

    /** @param  string  $token */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new TautanResetPassword($token));
    }
}
