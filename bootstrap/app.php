<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\CustomerAuth;
use App\Http\Middleware\AdminOrCustomerAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->validateCsrfTokens(except: [
            'marzpay/callback',
        ]);

        $middleware->alias([
            'AdminAuth'    => AdminAuth::class,
            'CustomerAuth' => CustomerAuth::class,
            'AdminOrCustomerAuth' => AdminOrCustomerAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

    })
    ->create();
