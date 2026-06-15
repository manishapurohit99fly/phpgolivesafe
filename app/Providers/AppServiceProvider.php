<?php

namespace App\Providers;

use App\Models\SiteSetting;
use App\Services\SiteSettingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    private const RATE_LIMIT_MESSAGE = "Too many attempts detected. Please try again later.";

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

         if (Schema::hasTable('site_settings')) {

            View::composer('*', function ($view) {               
                $siteSetting = app(SiteSettingService::class)->getSettings();

                $view->with([
                    'currentUserInfo' => auth()->user(),
                    'siteSetting'     => $siteSetting,
                ]);
            });
            
            $siteSetting = app(SiteSettingService::class)->getSettings();
            app()->instance('siteSettings', $siteSetting);


            Schema::defaultStringLength(191);
           
        RateLimiter::for('otp_limit', function (Request $request) {
            return Limit::perMinute(40, 5)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' =>  self::RATE_LIMIT_MESSAGE,
                ], 429);
            });
        });

        RateLimiter::for('signup_limit', function (Request $request) {
            return Limit::perMinutes(3, 5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' =>  self::RATE_LIMIT_MESSAGE,
                    ], 429);
                });
        });

        RateLimiter::for('forgot_limit', function (Request $request) {
            
            return Limit::perMinutes(3, 3)
                ->by($request->ip() . '|' . strtolower((string) $request->input('email')))
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' =>  self::RATE_LIMIT_MESSAGE,
                    ], 429);
                });
        });

        RateLimiter::for('api_global', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' =>  self::RATE_LIMIT_MESSAGE,
                    ], 429);
                });
        });

        }
    }
}
