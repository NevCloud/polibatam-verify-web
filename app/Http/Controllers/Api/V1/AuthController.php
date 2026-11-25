<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Akun\MasukMobile;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Autentikasi
 *
 * Login dua tahap (FR-60, BR-59, NFR-47): password → `challenge_token` → kode TOTP → token Bearer 12 jam per perangkat.
 */
class AuthController extends ApiController
{
    /**
     * Login (tahap 1)
     *
     * Selalu mengembalikan `challenge_token` (2FA wajib untuk admin, K-33). Hanya Admin SILAU & Admin Keuangan; role lain 403.
     *
     * @unauthenticated
     *
     * @bodyParam email string required Email akun admin. Example: silau@polibatam-verify.test
     * @bodyParam password string required Password akun.
     * @bodyParam nama_perangkat string required Nama perangkat (satu token per perangkat). Example: Pixel 8 Bayu
     */
    public function login(Request $request, MasukMobile $masuk): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'nama_perangkat' => ['required', 'string', 'max:100'],
        ], [], ['nama_perangkat' => 'nama perangkat']);
        $hasil = $masuk->tantangan($data['email'], $data['password'], $data['nama_perangkat']);

        return $this->ok('Masukkan kode autentikator.', [
            'challenge_token' => $hasil['challenge_token'],
            'kedaluwarsa_pada' => self::iso($hasil['kedaluwarsa_pada']),
        ]);
    }

    /**
     * Verifikasi kode TOTP (tahap 2)
     *
     * Mengembalikan token Sanctum yang berlaku 12 jam. Gagal berturut-turut mengunci akun seperti di web (BR-03).
     *
     * @unauthenticated
     *
     * @bodyParam challenge_token string required Token dari tahap 1 (berlaku 5 menit).
     * @bodyParam code string Kode 6 digit dari aplikasi autentikator. Wajib bila `recovery_code` kosong. Example: 123456
     * @bodyParam recovery_code string Recovery code sekali pakai.
     */
    public function duaFaktor(Request $request, MasukMobile $masuk): JsonResponse
    {
        $data = $request->validate([
            'challenge_token' => ['required', 'string', 'max:100'],
            'code' => ['required_without:recovery_code', 'nullable', 'string', 'max:20'],
            'recovery_code' => ['nullable', 'string', 'max:100'],
        ], ['code.required_without' => 'Masukkan kode autentikator atau recovery code.']);
        $hasil = $masuk->selesaikan($data['challenge_token'], $data['code'] ?? null, $data['recovery_code'] ?? null);

        return $this->ok('Berhasil masuk.', [
            'token' => $hasil['token'],
            'token_type' => 'Bearer',
            'kedaluwarsa_pada' => self::iso($hasil['kedaluwarsa_pada']),
            'pengguna' => self::profil($hasil['user']),
        ]);
    }

    /**
     * Logout
     *
     * Mencabut token perangkat ini.
     */
    public function logout(Request $request, AuditLogger $audit): Response
    {
        $user = self::admin($request);
        $user->currentAccessToken()->delete();
        $audit->catat('logout', 'user', $user->id, pelaku: $user);

        return response()->noContent();
    }

    /** Profil & role */
    public function me(Request $request): JsonResponse
    {
        return $this->ok('Profil berhasil dimuat.', self::profil(self::admin($request)));
    }

    /** @return array<string, mixed> */
    private static function profil(User $user): array
    {
        return ['id' => $user->id, 'nama' => $user->name, 'email' => $user->email, 'role' => $user->namaRole(), 'label_role' => $user->labelRole()];
    }
}
