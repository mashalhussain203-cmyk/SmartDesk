<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | Railway / Reverse Proxy vertrouwen
        |--------------------------------------------------------------------------
        |
        | Railway plaatst de Laravel-app achter een reverse proxy.
        |
        | Zonder deze instelling kan Laravel bij:
        |
        |     $request->ip()
        |
        | bijvoorbeeld het interne IP-adres van Railway zien in plaats
        | van het publieke IP-adres van de bezoeker.
        |
        | Door de Railway proxy te vertrouwen kan Laravel gebruikmaken van
        | forwarding headers zoals:
        |
        | - X-Forwarded-For
        | - X-Forwarded-Proto
        | - X-Forwarded-Host
        | - X-Forwarded-Port
        |
        | Dit is belangrijk voor onze login-security module.
        |
        */

        $middleware->trustProxies(
            at: '*'
        );
    })

    ->withExceptions(function (Exceptions $exceptions): void {

        /*
        |--------------------------------------------------------------------------
        | JSON responses voor API requests
        |--------------------------------------------------------------------------
        |
        | Bestaande Mashal-configuratie behouden.
        |
        */

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool =>
                $request->is('api/*') ||
                $request->expectsJson(),
        );
    })

    ->create();