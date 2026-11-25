<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Autentikasi diperlukan.',
                    'data' => [],
                ], 401);
            }
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($request->is('api/*')) {
                $status = $exception->getStatusCode();
                $message = match ($status) {
                    404 => 'Endpoint API tidak ditemukan.',
                    405 => 'Metode HTTP tidak diizinkan untuk endpoint ini.',
                    429 => 'Terlalu banyak permintaan. Silakan coba kembali.',
                    default => 'Permintaan API tidak dapat diproses.',
                };

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => [],
                ], $status);
            }
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (
                $request->is('api/*')
                && ! $exception instanceof AuthenticationException
                && ! $exception instanceof HttpExceptionInterface
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memproses permintaan API.',
                    'data' => [],
                ], 500);
            }
        });
    })->create();
