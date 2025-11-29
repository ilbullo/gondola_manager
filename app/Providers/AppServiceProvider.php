<?php

namespace App\Providers;

use App\Listeners\UpdateLastLoginAt;
use App\Models\{Agency, AgencyWork, LicenseTable, User, WorkAssignment};
use App\Policies\{AgencyPolicy, AgencyWorkPolicy, LicenseTablePolicy, UserPolicy, WorkAssignmentPolicy};
use App\Enums\UserRole;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;


/** FIX FOR REGISTER POLICY INTELLIPHASE VS EDITOR
 * @method void registerPolicies()
 */ 

class AppServiceProvider extends AuthServiceProvider
{

    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class         => UserPolicy::class,
        Agency::class       => AgencyPolicy::class,
        LicenseTable::class => LicenseTablePolicy::class,
        WorkAssignment::class => WorkAssignmentPolicy::class,
        AgencyWork::class   => AgencyWorkPolicy::class,
    ];

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
        // Registra automaticamente tutte le policy
        $this->registerPolicies();

        // ===================================================================
        // GATES PERSONALIZZATI
        // ===================================================================

        Gate::define('access-table-manager', fn(User $user): bool =>
            $user->role === UserRole::ADMIN || $user->role === UserRole::BANCALE
        );

        Gate::define('access-agency-manager', fn(User $user): bool =>
            $user->role === UserRole::ADMIN || $user->role === UserRole::BANCALE
        );

        Gate::define('access-user-manager', fn(User $user): bool =>
            $user->role === UserRole::ADMIN
        );

        Gate::define('edit-own-profile', fn(User $user, ?User $target = null): bool =>
            ($target ?? $user)->is($user)
        );

        Gate::define('is-admin', fn(User $user): bool =>
            $user->role === UserRole::ADMIN
        );

        Gate::define('is-bancale', fn(User $user): bool =>
            $user->role === UserRole::BANCALE
        );

        // ===================================================================
        // EVENT LISTENERS
        // ===================================================================

        Event::listen(Login::class, UpdateLastLoginAt::class);

        view()->composer('*', function ($view) {
        $view->with([
                'isAdmin'   => auth()->check() && auth()->user()->role === UserRole::ADMIN,
                'isBancale' => auth()->check() && auth()->user()->role === UserRole::BANCALE,
                'isUser'    => auth()->check() && auth()->user()->role === UserRole::USER,
            ]);
        });

    }
}