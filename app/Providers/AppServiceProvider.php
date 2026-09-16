<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Observers\CategoryObserver;
use App\Observers\ProductObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(100)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perDay(5)->by($request->ip())->response(function () {
                return response()->json([
                    'message' => 'لقد تجاوزت عدد محاولات الدخول المسموحة، يرجى المحاولة بعد دقيقة.'
                ], 429);
            });
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(1)->by($request->ip());
        });
    }
}
