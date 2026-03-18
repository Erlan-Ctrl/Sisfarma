<?php

namespace App\Providers;

use App\Services\Catalog\GtinSearchClient;
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
        $this->app->singleton(GtinSearchClient::class, function (): GtinSearchClient {
            return new GtinSearchClient(
                (string) config('services.gtinsearch.base_url', 'https://www.gtinsearch.org/api'),
                config('services.gtinsearch.token') ? (string) config('services.gtinsearch.token') : null,
                (int) config('services.gtinsearch.timeout', 6),
                (int) config('services.gtinsearch.cache_days', 60),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('admin-api', function (Request $request) {
            $userId = $request->user()?->getKey();
            $key = $userId ? 'u:'.$userId : 'ip:'.$request->ip();

            // High enough for fast checkout (barcode scanning) without opening the door for abuse.
            return [
                Limit::perMinute(900)->by($key),
                Limit::perMinute(1500)->by('ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('admin-ai', function (Request $request) {
            $userId = $request->user()?->getKey();
            $key = $userId ? 'u:'.$userId : 'ip:'.$request->ip();

            // Avoid accidental loops / abuse; AI requests are expensive.
            return [
                Limit::perMinute(30)->by($key),
                Limit::perMinute(60)->by('ip:'.$request->ip()),
            ];
        });
    }
}
