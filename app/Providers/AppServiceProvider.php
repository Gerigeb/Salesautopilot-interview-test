<?php

namespace App\Providers;

use App\Services\ApiClient;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ApiClient::class, fn () => new ApiClient(
            baseUrl: config('services.salesautopilot.url'),
            username: config('services.salesautopilot.username'),
            password: config('services.salesautopilot.password'),
            cache: $this->app->make(Repository::class),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
