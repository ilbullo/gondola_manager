<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Listeners\UpdateLastLoginAt;
use Illuminate\Auth\Events\Login;

class AppServiceProvider extends ServiceProvider
{

    protected $listen = [
        // ...altri eventi
        Login::class => [
            UpdateLastLoginAt::class,
        ],
    ];

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
        //
    }
}
