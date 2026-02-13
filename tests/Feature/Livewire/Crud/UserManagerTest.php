<?php

namespace Tests\Feature\Livewire\Crud;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Enums\LicenseType;
use Livewire\Livewire;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use App\Livewire\Crud\UserManager;

class UserManagerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_renders_successfully_with_enums()
    {
        Livewire::test(UserManager::class)
            ->assertStatus(200)
            ->assertViewHas('roles')
            ->assertViewHas('licenseTypes');
    }

    #[Test]
    public function it_validates_required_fields_on_creation()
    {
        Livewire::test(UserManager::class)
            ->set('editing', true)
            ->call('save')
            ->assertHasErrors(['name', 'email', 'role', 'password']);
    }

        #[Test]
    public function it_creates_a_new_user_with_hashed_password()
    {
        Livewire::test(UserManager::class)
            ->set('name', 'Mario Rossi')
            ->set('license_number','567')
            ->set('email', 'bepi@example.com')
            ->set('password', 'secret123')
            ->set('role', UserRole::USER->value)
            // Aggiungiamo il tipo per evitare l'errore di integrità DB
            ->set('type', LicenseType::OWNER->value) 
            ->call('save')
            ->assertDispatched('notify');

        $user = User::where('email', 'bepi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    #[Test]
    public function it_updates_user_without_changing_password_if_left_empty()
    {
        $user = User::factory()->create([
            'password' => bcrypt('original_password')
        ]);

        Livewire::test(UserManager::class)
            ->call('edit', $user->id)
            ->set('name', 'Mario Modificato')
            ->set('password', '') // Lasciata vuota
            ->call('save');

        $user->refresh();
        $this->assertEquals('Mario Modificato', $user->name);
        $this->assertTrue(Hash::check('original_password', $user->password));
    }

    #[Test]
    public function it_prevents_duplicate_emails_except_for_current_user()
    {
        User::factory()->create(['email' => 'duplicate@example.com']);
        $user = User::factory()->create(['email' => 'myemail@example.com']);

        Livewire::test(UserManager::class)
            ->call('edit', $user->id)
            ->set('email', 'duplicate@example.com')
            ->call('save')
            ->assertHasErrors(['email' => 'unique']);
            
        // Dovrebbe invece permettere di salvare se l'email rimane la stessa dell'utente corrente
        Livewire::test(UserManager::class)
            ->call('edit', $user->id)
            ->set('email', 'myemail@example.com')
            ->call('save')
            ->assertHasNoErrors(['email']);
    }

    #[Test]
    public function it_can_sort_users_by_name_and_email()
    {
        User::factory()->create(['name' => 'Zorro']);
        User::factory()->create(['name' => 'Abaco']);

        // Testiamo il cambio direzione: 
        // Se il default è 'name'/'asc', chiamando setSort('name') diventerà 'desc'
        Livewire::test(UserManager::class)
            ->call('setSort', 'name')
            ->assertSet('sortField', 'name')
            ->assertSet('sortDirection', 'desc')
            ->assertSeeInOrder(['Zorro', 'Abaco'])
            // Chiamando di nuovo, torna 'asc'
            ->call('setSort', 'name')
            ->assertSet('sortDirection', 'asc')
            ->assertSeeInOrder(['Abaco', 'Zorro']);
    }

    #[Test]
    public function it_prevents_self_deletion()
    {
        $admin = User::factory()->create();
        Auth::login($admin);

        Livewire::test(UserManager::class)
            ->dispatch('confirmDeleteUser', payload: $admin->id)
            ->assertDispatched('notify', function($event, $params) {
                return $params['type'] === 'error' && str_contains($params['message'], 'stesso account');
            });

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    #[Test]
    public function it_resets_form_correctly_on_create()
    {
        $component = Livewire::test(UserManager::class)
            ->set('name', 'Some Name')
            ->set('editing', true);

        $component->call('resetForm');

        $this->assertEquals('', $component->get('name'));
        $this->assertFalse($component->get('editing'));
        $this->assertEquals(0, $component->get('userId'));
    }
}