<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add session validation middleware to web group
        $middleware->appendToGroup('web', \App\Http\Middleware\ValidateSession::class);

        // Use custom CSRF token verification middleware with exceptions
        // Exempt /logout from CSRF verification to prevent 419 errors when session/token expires
        // This is safe because logout requires authentication and doesn't expose sensitive data
        $middleware->validateCsrfTokens(except: [
            '/logout',
        ]);

        // Register custom middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Log all exceptions for monitoring
        $exceptions->reportable(function (Throwable $e) {
            \Log::error('Application exception: '.$e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        });

        // Customize error responses for better user experience
        $exceptions->renderable(function (\Illuminate\Database\QueryException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'A database error occurred. Please try again later.',
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'A database error occurred. Please try again or contact support.');
        });

        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Resource not found'], 404);
            }

            return response()->view('errors.404', [], 404);
        });
    })->create();
