<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Listeners\UpdateLastLoginAt;
use Illuminate\Support\Facades\Event; // Importante: Importa la Facade Event
use Illuminate\Auth\Events\Login;

class AppServiceProvider extends ServiceProvider
{


    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            Login::class,
            UpdateLastLoginAt::class
        );
    }
}
