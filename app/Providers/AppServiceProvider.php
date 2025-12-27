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
use Illuminate\Support\Facades\Auth;
use App\Contracts\WorkQueryInterface;
use App\Services\WorkQueryService;
use App\Contracts\MatrixEngineInterface;
use App\Services\MatrixEngineService;
use Illuminate\Support\Facades\Blade;


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
        // Diciamo a Laravel: "Ogni volta che qualcuno chiede WorkQueryInterface, dagli WorkQueryService"
        $this->app->bind(WorkQueryInterface::class, WorkQueryService::class);

        // Facciamo lo stesso per il motore della matrice
        $this->app->bind(MatrixEngineInterface::class, MatrixEngineService::class);
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
                'isAdmin'   => Auth::check() && Auth::user()->role === UserRole::ADMIN,
                'isBancale' => Auth::check() && Auth::user()->role === UserRole::BANCALE,
                'isUser'    => Auth::check() && Auth::user()->role === UserRole::USER,
            ]);
        });

        // ======================================================================
        // HELPERS
        // ======================================================================

        // Direttiva @money(1000)
        Blade::directive('money', function ($expression) {
            return "<?php echo \App\Helpers\Format::currency($expression); ?>";
        });

        // @number(100) -> 100
        Blade::directive('number', function ($expression) {
            return "<?php echo \App\Helpers\Format::number($expression); ?>";
        });

        // @date($data)
        Blade::directive('date', function ($expression) {
            return "<?php echo \App\Helpers\Format::date($expression); ?>";
        });

        // @dateTime($data)
        Blade::directive('dateTime', function ($expression) {
            return "<?php echo \App\Helpers\Format::dateTime($expression); ?>";
        });

        // @trim($testo, 20)
        Blade::directive('trim', function ($expression) {
            return "<?php echo \App\Helpers\Format::trim($expression); ?>";
        });

    }
}