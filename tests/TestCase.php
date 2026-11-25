<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** RefreshDatabase ikut menghapus VIEW laporan (02_view.sql) sebelum migrate:fresh. */
    protected $dropViews = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Pengaman: tes tidak pernah menyentuh basis data aplikasi.
        $db = config('database.connections.'.config('database.default').'.database');
        if (! str_ends_with((string) $db, '_test')) {
            throw new \RuntimeException("Tes harus memakai basis data *_test, bukan [{$db}].");
        }

        // Tes tidak bergantung pada hasil `npm run build`.
        $this->withoutVite();
    }
}
