<?php

namespace App\Support\Preview;

use App\Support\Ui\PortalContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

/**
 * Rute /_preview (PRD §4.1). Fixture `resources/preview/hw-XX.php` = kontrak data view:
 * kunci `view`, `peran`, `pengguna`, lalu satu entri per state (`default`, `kosong`, ...).
 */
class PreviewController
{
    public const KUNCI_CADANGAN = ['view', 'peran', 'pengguna', 'menu'];

    public function index(): View
    {
        $halaman = [];

        foreach (self::semuaFixture() as $id => $fixture) {
            $halaman[$id] = self::state($fixture);
        }

        return view('preview.index', ['halaman' => $halaman]);
    }

    public function show(string $halaman, string $state = 'default'): View
    {
        $fixture = self::fixture($halaman);
        abort_unless(array_key_exists($state, $fixture) && ! in_array($state, self::KUNCI_CADANGAN, true), 404);

        return view(self::view($fixture, $state), self::data($fixture, $state));
    }

    /**
     * View untuk satu state: state boleh menimpa `view`/`menu` bila satu HW punya beberapa layar
     * (HW-13 dasbor + halaman status, A-69).
     *
     * @param  array<string, mixed>  $fixture
     */
    public static function view(array $fixture, string $state): string
    {
        return $fixture[$state]['view'] ?? $fixture['view'];
    }

    /**
     * Data yang diberikan ke view untuk satu state; dipakai juga oleh tes kontrak.
     *
     * @param  array<string, mixed>  $fixture
     * @return array<string, mixed>
     */
    public static function data(array $fixture, string $state): array
    {
        $data = $fixture[$state];
        $menu = $data['menu'] ?? $fixture['menu'] ?? null;
        unset($data['view'], $data['menu']);

        // State galat validasi: kunci `errors` = [field => pesan] menjadi $errors seperti setelah redirect back.
        if (isset($data['errors'])) {
            $data['errors'] = (new ViewErrorBag)->put('default', new MessageBag($data['errors']));
        }

        if (! empty($fixture['pengguna'])) {
            $data['portal'] = PortalContext::dariFixture($fixture['pengguna'], $menu);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $fixture
     * @return list<string>
     */
    public static function state(array $fixture): array
    {
        return array_values(array_diff(array_keys($fixture), self::KUNCI_CADANGAN));
    }

    /** @return array<string, array<string, mixed>> */
    public static function semuaFixture(): array
    {
        $semua = [];

        // Jalur relatif file (bukan resource_path) agar bisa dipanggil sebelum aplikasi boot, mis. di tes kontrak.
        foreach (glob(dirname(__DIR__, 3).'/resources/preview/h[wm]-*.php') ?: [] as $berkas) {
            $semua[basename($berkas, '.php')] = require $berkas;
        }
        ksort($semua);

        return $semua;
    }

    /** @return array<string, mixed> */
    public static function fixture(string $halaman): array
    {
        abort_unless(preg_match('/^h[wm]-\d{2}$/', $halaman) === 1, 404);
        $berkas = resource_path("preview/{$halaman}.php");
        abort_unless(is_file($berkas), 404);

        return require $berkas;
    }
}
