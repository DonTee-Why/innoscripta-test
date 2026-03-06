<?php

namespace App\Providers;

use App\Contracts\EventPublisher;
use App\Infrastructure\RabbitMqEventPublisher;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EventPublisher::class, RabbitMqEventPublisher::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
