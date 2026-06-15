<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AuthenticateApi;

/*return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )*/
return Application::configure(basePath: dirname(__DIR__))
        ->withRouting(function () {
            // Define API Routes
            Route::prefix('api')->group(function () {
                // API v1 routes
                Route::prefix('v1')->group(function () {
                    require base_path('routes/api_v1.php');
                });
    
                // API v2 routes
                // Route::prefix('v2')->group(function () {
                //     require base_path('routes/api_v2.php');
                // });
            });
    
            // Define Web Routes
            Route::middleware(['web'])->group(function () {
                require base_path('routes/web.php');
            });
    
            // Define Console Routes
            require base_path('routes/console.php');
    
            // Health Check Route
            Route::get('/up', fn () => response('OK', 200));
        })    
    ->withMiddleware(function (Middleware $middleware) {
        //
        // Add AuthenticateApi Middleware  
        $middleware->alias([
            'ApiAuthMiddleware' => AuthenticateApi::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
                'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
                'prevent-back-history' => \App\Http\Middleware\PreventBackHistory::class,
            'role'                 => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Centralised inbound ID decryption for every web request. Runs
        // before route model binding so controllers always see the
        // decoded numeric id, regardless of whether the request came from
        // a fresh "encrypted" page or a legacy plaintext URL.
        $middleware->web(append: [
            \App\Http\Middleware\DecryptRouteIds::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
