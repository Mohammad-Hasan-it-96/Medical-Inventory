<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ModeratorMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RepMiddleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Override the framework default of route('login') → use this project's login route.
        $middleware->redirectGuestsTo(fn () => route('auth.login'));

        $middleware->web(append: [
            SetLocale::class,
        ]);

        // Register custom middleware
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'moderator' => ModeratorMiddleware::class,
            'rep' => RepMiddleware::class,
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // 401 Unauthenticated ─ API: JSON envelope │ Web: redirect to login
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            return redirect()->guest(route('auth.login'));
        });

        // 403 Forbidden ─ API: JSON envelope │ Web: 403 view
        $exceptions->render(function (AccessDeniedHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Forbidden.',
                ], 403);
            }
            return response()->view('errors.403', ['exception' => $e], 403);
        });

        // 404 Not Found ─ API: JSON envelope │ Web: 404 view
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                ], 404);
            }
            return response()->view('errors.404', ['exception' => $e], 404);
        });
    })
    ->create();
