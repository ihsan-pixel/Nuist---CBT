<?php

use App\Http\Middleware\ApplyExamSecurityHeaders;
use App\Http\Middleware\EnsureExamLock;
use App\Http\Middleware\ValidateSebConfigKey;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'exam.lock' => EnsureExamLock::class,
            'exam.headers' => ApplyExamSecurityHeaders::class,
            'seb.config' => ValidateSebConfigKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
