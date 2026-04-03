<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Return JSON 401 for unauthenticated API requests (no redirect to login)
        $middleware->redirectGuestsTo(fn () => abort(401, 'Unauthorized'));
        $middleware->alias([
        'cache.response' => \App\Http\Middleware\CacheResponse::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $throwable): bool {
            return true;
        });

        $exceptions->render(function (Throwable $throwable, Request $request) {
            $status = 500;

            if ($throwable instanceof HttpExceptionInterface) {
                $status = $throwable->getStatusCode();
            }

            return new JsonResponse([
                'success' => false,
                'error' => class_basename($throwable),
                'message' => app()->hasDebugModeEnabled()
                    ? $throwable->getMessage()
                    : 'Server Error',
            ], $status);
        });
    })
    ->create();