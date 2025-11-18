<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        Volt::test('pages.auth.register')
            ->set('name', 'Mario Rossi')
            ->set('email', 'mario@example.com')
            ->set('license_number', '123456789')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertRedirect('/dashboard'); // ← ORA FUNZIONA!

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'mario@example.com',
            'license_number' => '123456789',
        ]);
    }
}
