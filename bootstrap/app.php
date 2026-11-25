<?php

use App\Http\Middleware\BatasiPermintaanSensitif;
use App\Http\Middleware\JagaSesi;
use App\Support\Http\ArahPortal;
use App\Support\Http\PenanganGalat;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            if (config('app.preview')) {
                Route::middleware('web')->group(base_path('routes/preview.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [BatasiPermintaanSensitif::class, JagaSesi::class]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn (Request $request) => ArahPortal::untuk($request->user()));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        PenanganGalat::daftarkan($exceptions);
    })->create();
