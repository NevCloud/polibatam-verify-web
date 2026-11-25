<?php

use App\Actions\Pendaftaran\AjukanFormulir;
use App\Actions\Pendaftaran\PilihPeminatan;
use App\Actions\Pendaftaran\SimpanDraf;
use App\Actions\Pendaftaran\UnggahDokumen;
use App\Models\Peminatan;
use App\Models\PeminatanPeriode;
use App\Models\Pendaftaran;
use App\Models\PeriodePendaftaran;
use App\Models\RekeningInstitusi;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Fortify\RecoveryCode;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature', 'Contract');

/** Akun uji dengan satu role (5 role, T-07). Basis data harus sudah di-seed (role ada). */
function buatPengguna(string $role = 'peserta', array $atribut = []): User
{
    $user = User::factory()->create($atribut + ['is_active' => true]);
    $user->assignRole($role);

    return $user;
}

/** Pasang TOTP terkonfirmasi; kembalikan rahasia TOTP untuk membuat kode. */
function pasang2fa(User $user): string
{
    $rahasia = app(Google2FA::class)->generateSecretKey();
    $user->forceFill([
        'two_factor_secret' => encrypt($rahasia),
        'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, fn () => RecoveryCode::generate())->all())),
        'two_factor_confirmed_at' => now(),
    ])->save();

    return $rahasia;
}

function kodeTotp(string $rahasia): string
{
    return app(Google2FA::class)->getCurrentOtp($rahasia);
}

/** P03: periode dibuka + kuota peminatan + tarif formulir berlaku (syarat BR-07). Seed 04 menyediakan track/peminatan. */
function bukaPeriode(string $kode = 'TRPL', array $periode = [], array $kuota = []): PeminatanPeriode
{
    $p = PeriodePendaftaran::create($periode + [
        'nama' => 'Gelombang Uji', 'tanggal_mulai' => now()->subDay(), 'tanggal_selesai' => now()->addMonth(), 'status' => 'dibuka',
    ]);
    $peminatan = Peminatan::where('kode', $kode)->firstOrFail();
    $pembuat = User::role('admin_track')->first() ?? buatPengguna('admin_track');
    DB::table('tarifs')->insertOrIgnore([
        'peminatan_id' => $peminatan->id, 'jenis' => 'formulir', 'semester_ke' => null, 'nominal' => 300000,
        'tanggal_efektif' => now()->subYear()->toDateString(), 'dibuat_oleh' => $pembuat->id, 'created_at' => now(), 'updated_at' => now(),
    ]);

    return PeminatanPeriode::create($kuota + [
        'periode_pendaftaran_id' => $p->id, 'peminatan_id' => $peminatan->id, 'kuota_min' => 1,
    ]);
}

/**
 * Berkas unggahan ASLI dari berkas sementara: getMimeType() membaca isi (finfo) seperti di produksi.
 * Jangan pakai UploadedFile::fake() untuk uji magic bytes — MIME-nya ditebak dari nama berkas.
 */
function berkasUji(string $nama, string $isi): UploadedFile
{
    $jalur = tempnam(sys_get_temp_dir(), 'pv');
    file_put_contents($jalur, $isi);

    return new UploadedFile($jalur, $nama, null, null, true);
}

function berkasPdf(string $nama = 'dokumen.pdf'): UploadedFile
{
    return berkasUji($nama, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n".Str::random(32));
}

function berkasJpg(string $nama = 'foto.jpg'): UploadedFile
{
    return berkasUji($nama, "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00".Str::random(64)."\xFF\xD9");
}

/** Data Diri lengkap (K-31) yang lolos SimpanDrafRequest. */
function dataDiriLengkap(): array
{
    return [
        'nik' => '2171020807070003', 'nama_lengkap' => 'Atma Fauzilla', 'tempat_lahir' => 'Batam', 'tanggal_lahir' => '2007-07-08',
        'jenis_kelamin' => 'P', 'no_hp' => '081234567890', 'alamat' => 'Jl. Raja Haji Fisabilillah No. 12, Batam',
        'asal_sekolah' => 'SMA Negeri 3 Batam', 'tahun_lulus' => (string) now()->year,
    ];
}

/** Draf milik $user dengan data & 3 berkas wajib lengkap (siap diajukan). */
function drafLengkap(User $user, PeminatanPeriode $pp): Pendaftaran
{
    $p = app(PilihPeminatan::class)($user, $pp->id);
    app(SimpanDraf::class)($p, array_merge(dataDiriLengkap(), ['nik' => '2171020807070003']));
    foreach (['ktp', 'ijazah_skl_rapor', 'skhu_nilai'] as $jenis) {
        app(UnggahDokumen::class)($p, $jenis, berkasPdf("{$jenis}.pdf"));
    }

    return $p->fresh();
}

/** P05: rekening institusi aktif untuk jenis tagihan (BR-70). */
function rekeningAktif(string $jenis = 'formulir', string $bank = 'Bank Negara Indonesia (BNI)', string $nomor = '0123456789'): RekeningInstitusi
{
    $pembuat = User::role('admin_keuangan')->first() ?? buatPengguna('admin_keuangan');

    return RekeningInstitusi::create([
        'jenis_tagihan' => $jenis, 'nama_bank' => $bank, 'nomor_rekening' => $nomor, 'atas_nama' => 'Politeknik Negeri Batam',
        'status' => 'aktif', 'dibuat_oleh' => $pembuat->id,
    ]);
}

/** P05: tarif semester ke-n berlaku untuk peminatan (BR-25). */
function tarifSemester(int $peminatanId, int $semesterKe = 1, int $nominal = 6500000): void
{
    $pembuat = User::role('admin_track')->first() ?? buatPengguna('admin_track');
    DB::table('tarifs')->insert([
        'peminatan_id' => $peminatanId, 'jenis' => 'semester', 'semester_ke' => $semesterKe, 'nominal' => $nominal,
        'tanggal_efektif' => now()->subYear()->toDateString(), 'dibuat_oleh' => $pembuat->id, 'created_at' => now(), 'updated_at' => now(),
    ]);
}

/** P05: pendaftaran Diajukan (tagihan formulir terbit lewat FormulirDiajukan) lalu opsional Masuk Kuota. */
function pendaftaranBertagihan(PeminatanPeriode $pp, bool $masukKuota = true): Pendaftaran
{
    $p = drafLengkap(buatPengguna('peserta'), $pp);
    app(AjukanFormulir::class)($p);
    if ($masukKuota) {
        DB::table('pendaftarans')->where('id', $p->id)->update(['status' => 'masuk_kuota', 'waktu_masuk_kuota' => now()]);
    }

    return $p->fresh();
}

/** P05: data pengiriman sah untuk unggah bukti (BR-72). */
function dataPengiriman(int $nominal): array
{
    return ['tanggal_transfer' => now()->toDateString(), 'nominal_transfer' => (string) $nominal, 'bank_pengirim' => 'Bank BNI', 'nama_pengirim' => 'Atma Fauzilla'];
}

/** P06: login dua tahap lewat API (password → challenge → TOTP), mengembalikan token Bearer. Password factory = "password". */
function masukApi(User $user, string $perangkat = 'Pixel Uji'): string
{
    $rahasia = $user->two_factor_secret ? decrypt($user->two_factor_secret) : pasang2fa($user);
    $tantangan = test()->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password', 'nama_perangkat' => $perangkat])
        ->assertOk()->json('data.challenge_token');

    return test()->postJson('/api/v1/auth/two-factor', ['challenge_token' => $tantangan, 'code' => kodeTotp($rahasia)])
        ->assertOk()->json('data.token');
}

/** P06: header Bearer. Guard Sanctum di-reset karena dalam satu tes ia menyimpan pengguna dari permintaan sebelumnya. */
function bearer(string $token): array
{
    app('auth')->forgetGuards();

    return ['Authorization' => "Bearer {$token}"];
}
