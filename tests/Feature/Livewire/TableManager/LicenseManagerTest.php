<?php

namespace Tests\Feature\Livewire\TableManager;

use Tests\TestCase;
use App\Models\User;
use App\Models\LicenseTable;
use App\Livewire\TableManager\LicenseManager;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class LicenseManagerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_lists_available_users_correctly()
    {
        $user1 = User::factory()->create(['name' => 'Mario Rossi']);
        $user2 = User::factory()->create(['name' => 'Luigi Bianchi']);

        // Utente 1 già assegnato
        LicenseTable::factory()->create(['user_id' => $user1->id, 'date' => today()]);

        $component = Livewire::test(LicenseManager::class);

        // Accediamo alla computed property tramite l'istanza del componente
        $availableUsers = $component->instance()->availableUsers;

        $this->assertTrue($availableUsers->contains($user2));
        $this->assertFalse($availableUsers->contains($user1));
    }

    #[Test]
    public function it_can_search_available_users()
    {
        User::factory()->create(['name' => 'Alessandro']);
        User::factory()->create(['name' => 'Beatrice']);

        $component = Livewire::test(LicenseManager::class)
            ->set('search', 'Ale');

        $availableUsers = $component->instance()->availableUsers;

        $this->assertCount(1, $availableUsers);
        $this->assertEquals('Alessandro', $availableUsers->first()->name);
    }

    #[Test]
    public function it_adds_a_user_to_the_license_table()
    {
        $user = User::factory()->create();

        Livewire::test(LicenseManager::class)
            ->call('selectUser', $user->id)
            ->assertDispatched('notify', message: "Licenza aggiunta con successo.");

        $this->assertDatabaseHas('license_table', [
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'order' => 1
        ]);
    }

    #[Test]
    public function it_prevents_duplicate_entries_for_the_same_day()
    {
        $user = User::factory()->create();
        
        // Prima aggiunta
        LicenseTable::factory()->create(['user_id' => $user->id, 'date' => today()]);

        Livewire::test(LicenseManager::class)
            ->call('selectUser', $user->id)
            ->assertDispatched('notify', message: "L'utente è già in tabella.");

        // Verifichiamo che ci sia comunque un solo record nel DB
        $this->assertEquals(1, LicenseTable::where('user_id', $user->id)->count());
    }

    #[Test]
    public function it_can_remove_a_user_from_the_order()
    {
        $license = LicenseTable::factory()->create(['date' => today()]);

        Livewire::test(LicenseManager::class)
            ->call('removeUser', $license->id)
            ->assertDispatched('notify', message: "Rimosso dall'ordine.");

        $this->assertDatabaseMissing('license_table', ['id' => $license->id]);
    }

    #[Test]
    public function it_dispatches_confirmation_event_on_confirm()
    {
        // Setup: deve esserci almeno una licenza
        LicenseTable::factory()->create(['date' => today()]);

        Livewire::test(LicenseManager::class)
            ->call('confirm')
            ->assertDispatched('licensesConfirmed')
            // Verifichiamo solo il messaggio, dato che i parametri nominali possono variare
            ->assertDispatched('notify', function ($event, $params) {
                return $params['message'] === 'Ordine di servizio confermato. Buon lavoro!' && 
                       $params['type'] === 'success';
            });
    }

    #[Test]
    public function it_fails_to_confirm_if_table_is_empty()
    {
        Livewire::test(LicenseManager::class)
            ->call('confirm')
            ->assertDispatched('notify', type: 'error')
            ->assertNotDispatched('licensesConfirmed');
    }

    #[Test]
    public function it_assigns_the_correct_sequential_order_to_new_entries()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Aggiungiamo il primo
        Livewire::test(LicenseManager::class)->call('selectUser', $user1->id);
        // Aggiungiamo il secondo
        Livewire::test(LicenseManager::class)->call('selectUser', $user2->id);

        $this->assertEquals(1, LicenseTable::where('user_id', $user1->id)->first()->order);
        $this->assertEquals(2, LicenseTable::where('user_id', $user2->id)->first()->order);
    }

    #[Test]
    public function it_refreshes_available_users_after_external_change()
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $component = Livewire::test(LicenseManager::class);

        // Primo accesso: l'utente è disponibile (Livewire mette il risultato in cache)
        $this->assertTrue($component->instance()->availableUsers->contains($user));

        // Simuliamo l'inserimento esterno nel DB (es. da un altro terminale o worker)
        LicenseTable::create([
            'user_id' => $user->id,
            'date'    => today(),
            'order'   => 1
        ]);

        // Fondamentale: Forziamo Livewire a resettare la cache delle Computed Properties
        $component->call('$refresh'); 

        // Ora l'asserzione passerà perché availableUsers verrà ricalcolato
        $this->assertFalse($component->instance()->availableUsers->contains($user));
    }
}