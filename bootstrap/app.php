<?php

use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Application;
use App\Http\Middleware\CheckSecAccess;
use App\Http\Middleware\CheckAdminAccess;
use App\Http\Middleware\CheckMemberAccess;
use App\Http\Middleware\CheckDiretoriaAccess;
use App\Http\Middleware\CheckPresbiterioAccess;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'access-member-panel' => CheckMemberAccess::class,
            'access-sec-panel' => CheckSecAccess::class,
            'access-presbiterio-panel' => CheckPresbiterioAccess::class,
            'access-diretoria-panel' => CheckDiretoriaAccess::class,
            'access-admin-panel' =>CheckAdminAccess::class,
        ]);
    })
    ->withExceptions(function () {
        //
    })
    ->withProviders([
        AuthServiceProvider::class,
        \Barryvdh\DomPDF\ServiceProvider::class,
    ])
    ->create();
