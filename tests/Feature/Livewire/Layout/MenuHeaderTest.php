<?php

namespace Tests\Feature\Livewire\Layout;

use App\Models\User;
use App\Livewire\Layout\MenuHeader;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class MenuHeaderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_users_only_see_login_link()
    {
        Livewire::test(MenuHeader::class)
            ->assertSee('Login')
            ->assertDontSee('Home')
            ->assertDontSee('Esci');
    }

    #[Test]
    public function admin_can_see_all_authorized_links()
    {
        $admin = User::factory()->create();
        
        // Mocking dei Gate per simulare un Admin
        Gate::define('access-user-manager', fn() => true);
        Gate::define('access-agency-manager', fn() => true);
        Gate::define('access-table-manager', fn() => true);

        Livewire::actingAs($admin)
            ->test(MenuHeader::class)
            ->assertSee('Utenti')
            ->assertSee('Agenzie')
            ->assertSee('Tabella')
            ->assertSee('Esci');
    }

    #[Test]
    public function driver_cannot_see_restricted_links()
    {
        $driver = User::factory()->create();

        // Il driver può vedere solo la tabella, non gli utenti/agenzie
        Gate::define('access-user-manager', fn() => false);
        Gate::define('access-agency-manager', fn() => false);
        Gate::define('access-table-manager', fn() => true);

        Livewire::actingAs($driver)
            ->test(MenuHeader::class)
            ->assertSee('Home')
            ->assertSee('Tabella')
            ->assertDontSee('Utenti')
            ->assertDontSee('Agenzie');
    }

    #[Test]
    public function logout_redirects_to_home()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MenuHeader::class)
            ->call('logout')
            ->assertRedirect('/');
            
        $this->assertFalse(Auth::check());
    }

    #[Test]
    public function mobile_menu_toggles_correctly()
    {
        Livewire::test(MenuHeader::class)
            ->assertSet('isMenuOpen', false)
            ->call('toggleMenu')
            ->assertSet('isMenuOpen', true);
    }
}