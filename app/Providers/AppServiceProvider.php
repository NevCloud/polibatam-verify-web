<?php

namespace App\Providers;

use App\Services\AuditLogger;
use App\Services\Pengaturan;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\ViewErrorBag;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Satu salinan per request/job agar perubahan pengaturan terbaca di request berikutnya.
        $this->app->scoped(Pengaturan::class);
        // Satu instans per request/job: menyimpan status GET_LOCK rantai audit.
        $this->app->scoped(AuditLogger::class);

        // Telescope: paket dev, KHUSUS LOKAL (PRD BE-0). Tidak di-auto-discover (composer.json dont-discover),
        // jadi di produksi (composer install --no-dev) kelasnya tidak ada dan tidak pernah dimuat.
        if ($this->app->environment('local') && class_exists(TelescopeServiceProvider::class)) {
            $this->app->register(TelescopeServiceProvider::class);
            $this->loadMigrationsFrom(database_path('migrations/lokal'));

            // AE-06/NFR-32: NIK & nomor rekening tidak boleh tersimpan utuh, termasuk di catatan debug Telescope.
            Telescope::hideRequestParameters([
                '_token', 'password', 'password_confirmation', 'nik', 'nomor_rekening', 'two_factor_secret', 'code', 'recovery_code',
            ]);
            Telescope::hideRequestHeaders(['cookie', 'x-csrf-token', 'x-xsrf-token', 'authorization']);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Kunci rantai audit dilepas setelah rollback (afterCommit tidak terpanggil saat rollback).
        Event::listen(TransactionRolledBack::class, function (TransactionRolledBack $e): void {
            app(AuditLogger::class)->setelahRollback($e->connection->transactionLevel());
        });

        // $errors selalu ada (preview & tes kontrak tanpa middleware web); ShareErrorsFromSession menimpanya di rute web.
        View::share('errors', new ViewErrorBag);

        // Riwayat keamanan HW-12 / HW-44 (A-78): logout ikut tercatat di audit log.
        Event::listen(Logout::class, function (Logout $e): void {
            app(AuditLogger::class)->catat('logout', 'user', $e->user->getAuthIdentifier(), pelaku: $e->user);
        });
    }
}
