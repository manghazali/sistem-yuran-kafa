<?php

use App\Http\Middleware\RedirectIfNotRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RedirectIfNotRole::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'payment-gateway/callback',
        ]);
        // Percayai semua proksi (Sail nginx, terowong Cloudflare/ngrok, dll) supaya
        // Laravel hormati header X-Forwarded-Proto dan jana URL https:// yang betul.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
