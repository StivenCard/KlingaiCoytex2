<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\KlingAi\KlingApiService;
use App\Services\KlingAi\KlingAuthService;
use App\Services\KlingAi\ImageProcessingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ImageProcessingService::class, function () {
            return new ImageProcessingService();
        });

        $this->app->bind(KlingAuthService::class, function () {
            return new KlingAuthService();
        });

        $this->app->bind(KlingApiService::class, function ($app) {
            return new KlingApiService(
                $app->make(KlingAuthService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
