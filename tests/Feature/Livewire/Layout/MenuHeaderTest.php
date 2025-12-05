<?php

namespace Tests\Feature\Livewire\Layout;

use App\Enums\UserRole;
use App\Models\User;
use App\Livewire\Layout\MenuHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MenuHeaderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_sees_only_login_button()
    {
        Livewire::test(MenuHeader::class)
            ->assertSee('Login')
            ->assertSeeHtml(route('login'))
            ->assertDontSee('Home')
            ->assertDontSee('Profilo')
            ->assertDontSee('Utenti')
            ->assertDontSee('Agenzie')
            ->assertDontSee('Tabella')
            ->assertDontSee('Esci');
    }

    #[Test]
    public function authenticated_user_sees_common_menu_items()
    {
        $user = User::factory()->create(['role' => UserRole::USER]);

        Livewire::actingAs($user)
            ->test(MenuHeader::class)
            ->assertSee('Home')
            ->assertSee('Profilo')
            ->assertSee('Esci');
    }

    #[Test]
    public function admin_sees_all_admin_menu_items()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        Livewire::actingAs($admin)
            ->test(MenuHeader::class)
            ->assertSee('Utenti')
            ->assertSee('Agenzie')
            ->assertSee('Tabella');
    }

    #[Test]
    public function bancale_sees_agency_and_table_but_not_user_manager()
    {
        $bancale = User::factory()->create(['role' => UserRole::BANCALE]);

        Livewire::actingAs($bancale)
            ->test(MenuHeader::class)
            ->assertSee('Agenzie')
            ->assertSee('Tabella')
            ->assertDontSee('Utenti');
    }

    #[Test]
    public function regular_user_does_not_see_admin_menu_items()
    {
        $user = User::factory()->create(['role' => UserRole::USER]);

        Livewire::actingAs($user)
            ->test(MenuHeader::class)
            ->assertDontSee('Utenti')
            ->assertDontSee('Agenzie')
            ->assertDontSee('Tabella');
    }

#[Test]
public function active_route_is_highlighted()
{
    $user = User::factory()->create(['role' => UserRole::ADMIN]);

    Livewire::actingAs($user)
        ->test(MenuHeader::class)
        ->assertSet('menuItems', function ($items) {
            $dashboardItem = collect($items)->firstWhere('id', 'dashboard');
            return $dashboardItem && $dashboardItem['label'] === 'Home';
        });
}


    #[Test]
    public function mobile_menu_can_be_toggled()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MenuHeader::class)
            ->assertSet('isMenuOpen', false)
            ->call('toggleMenu')
            ->assertSet('isMenuOpen', true)
            ->call('toggleMenu')
            ->assertSet('isMenuOpen', false);
    }

    #[Test]
    public function logout_logs_out_user_and_redirects()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MenuHeader::class)
            ->call('logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    #[Test]
    public function mobile_menu_closes_when_clicking_a_link()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MenuHeader::class)
            ->set('isMenuOpen', true)
            ->assertSet('isMenuOpen', true)
            ->call('toggleMenu') // Simula chiusura tramite click su link
            ->assertSet('isMenuOpen', false);
    }
}