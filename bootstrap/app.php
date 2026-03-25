<?php

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        RedirectIfAuthenticated::redirectUsing(function () {
            $user = Auth::user();
            if ($user?->isAdmin()) {
                return route('admin.dashboard');
            } elseif ($user?->isConsultant()) {
                return route('consultant.dashboard');
            }
            return route('user.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (TokenMismatchException $e) {
            return redirect()->route('login')->with('error', 'セッションが切れました。再度ログインしてください。');
        });
    })->create();
