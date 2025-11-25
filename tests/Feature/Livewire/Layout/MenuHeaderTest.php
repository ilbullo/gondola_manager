<?php

namespace Tests\Feature\Livewire\Layout;

use App\Enums\UserRole;
use App\Models\User;
use App\Livewire\Layout\MenuHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MenuHeaderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
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

    /** @test */
    public function authenticated_user_sees_common_menu_items()
    {
        $user = User::factory()->create(['role' => UserRole::USER]);

        Livewire::actingAs($user)
            ->test(MenuHeader::class)
            ->assertSee('Home')
            ->assertSee('Profilo')
            ->assertSee('Esci');
    }

    /** @test */
    public function admin_sees_all_admin_menu_items()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        Livewire::actingAs($admin)
            ->test(MenuHeader::class)
            ->assertSee('Utenti')
            ->assertSee('Agenzie')
            ->assertSee('Tabella');
    }

    /** @test */
    public function bancale_sees_agency_and_table_but_not_user_manager()
    {
        $bancale = User::factory()->create(['role' => UserRole::BANCALE]);

        Livewire::actingAs($bancale)
            ->test(MenuHeader::class)
            ->assertSee('Agenzie')
            ->assertSee('Tabella')
            ->assertDontSee('Utenti');
    }

    /** @test */
    public function regular_user_does_not_see_admin_menu_items()
    {
        $user = User::factory()->create(['role' => UserRole::USER]);

        Livewire::actingAs($user)
            ->test(MenuHeader::class)
            ->assertDontSee('Utenti')
            ->assertDontSee('Agenzie')
            ->assertDontSee('Tabella');
    }

    /** @test */
    public function active_route_is_highlighted()
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);

        // Simula di essere sulla route dashboard
        $this->get(route('dashboard'));

        Livewire::actingAs($user)
            ->test(MenuHeader::class)
            ->assertSeeHtml('bg-indigo-50 text-indigo-700')
            ->assertSee('Home');
    }

    /** @test */
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

    /** @test */
    public function logout_logs_out_user_and_redirects()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MenuHeader::class)
            ->call('logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    /** @test */
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