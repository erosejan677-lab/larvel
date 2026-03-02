<?php

namespace App\Providers;

use App\Repositories\V1\Contracts\UserRepositoryInterface;
use App\Repositories\V1\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\UrlGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        // Force HTTPS in production
        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
        }
    }
}