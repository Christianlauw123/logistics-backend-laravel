<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\UseAccessTokenFromCookie;
use App\Http\Middleware\VerifyOutsiderToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(ForceJsonResponse::class);
        $middleware->api(prepend: [
            UseAccessTokenFromCookie::class,
        ]);
        $middleware->alias([
            'outsider.auth' => VerifyOutsiderToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (NotFoundHttpException  $e) {
            return response()->json([
                'message' => 'Resource not found.',
                'errors'  => [
                    'Resource not found.'
                ]
            ], 404);
        });

        $exceptions->render(function (MethodNotAllowedHttpException  $e) {
            return response()->json([
                'message' => 'Unsupported',
                'errors'  => [
                    'Unsupported.'
                ]
            ], 404);
        });


    })->create();
