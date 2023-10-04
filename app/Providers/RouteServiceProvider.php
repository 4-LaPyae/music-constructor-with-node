<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api/authapi.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));


            //======================================mobile consumer======================================

            Route::middleware('api')
                ->prefix('api/mobile')
                ->group(base_path('routes/mobile/v1/authapi.php'));

            Route::middleware('api')
                ->prefix('api/mobile')
                ->group(base_path('routes/mobile/v1/applicationapi.php'));

            //======================================web consumer======================================

            Route::middleware('api')
                ->prefix('api/web')
                ->group(base_path('routes/web/v1/authapi.php'));

            Route::middleware('api')
                ->prefix('api/web')
                ->group(base_path('routes/web/v1/applicationapi.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */

    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(6000)->by($request->user()?->id ?: $request->ip());
        });

        //Your Limiter - limit 50 request per day    
        RateLimiter::for('day', function (Request $request) {
            return Limit::perDay(50)->by($request->phone)->response(function () {
                return response()->json([
                    'error' => true,
                    'authorize' => true,
                    "too_many" => true,
                    'message' => 'Too many attempts. Try again after some times.'
                ]);
            });
        });

        //Resent otp Limiter - limit 5 request per day    
        RateLimiter::for('resend-otp-day', function (Request $request) {
            return Limit::perDay(10)->by($request->phone)->response(function () {
                return response()->json([
                    'error' => true,
                    'authorize' => true,
                    "too_many" => true,
                    'message' => 'Too many attempts. Try again after some times.'
                ]);
            });
        });
    }
}
