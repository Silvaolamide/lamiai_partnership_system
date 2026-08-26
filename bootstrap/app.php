<?php

use App\Http\Middleware\EnsureBusinessApproved;
use App\Http\Middleware\EnsurePartnerApproved;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Routes loaded here are not automatically placed in Laravel's
            // web middleware group. Explicitly apply it so sessions, CSRF,
            // shared validation errors, and other web middleware are available.
            Route::middleware('web')->group(function (): void {
                require __DIR__.'/../routes/auth.php';
                require __DIR__.'/../routes/admin.php';
            });
        },
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'business.approved' => EnsureBusinessApproved::class,
            'partner.approved' => EnsurePartnerApproved::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
