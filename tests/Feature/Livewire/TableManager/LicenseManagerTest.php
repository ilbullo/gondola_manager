<?php

namespace Tests\Feature\Livewire\TableManager;

use App\Livewire\TableManager\LicenseManager;
use App\Models\{LicenseTable, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class LicenseManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $user1;
    private User $user2;
    private User $user3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::factory()->create(['license_number' => '11']);
        $this->user2 = User::factory()->create(['license_number' => '12']);
        $this->user3 = User::factory()->create(['license_number' => '13']);
    }

    #[Test]

    public function it_mounts_and_loads_data()
    {
        LicenseTable::factory()->create(['user_id' => $this->user1->id, 'date' => today(), 'order' => 1]);

        Livewire::test(LicenseManager::class)
            ->assertSet('selectedUsers', function ($users) {
                return count($users) === 1 && $users[0]['user_id'] === $this->user1->id;
            })
            ->assertSet('availableUsers', function ($users) {
                // Dovrebbe contenere user2 e user3, ma non user1
                return count($users) === 2 && collect($users)->pluck('id')->contains($this->user2->id);
            });
    }

    // --- Selezione e Rimozione ---

    #[Test]

    public function it_selects_a_user_and_adds_a_license_table_record()
    {
        $this->assertDatabaseCount('license_table', 0);

        Livewire::test(LicenseManager::class)
            ->call('selectUser', $this->user2->id)
            ->assertDispatched('toggleLoading', true)
            ->assertDispatched('toggleLoading', false);

        $this->assertDatabaseHas('license_table', [
            'user_id' => $this->user2->id,
            'date' => today(),
            'order' => 1,
        ]);

        // Verifica che l'utente sia passato da "disponibile" a "selezionato"
        Livewire::test(LicenseManager::class)
            ->assertSet('selectedUsers', function ($users) {
                return count($users) === 1 && $users[0]['user_id'] === $this->user2->id;
            })
            ->assertSet('availableUsers', function ($users) {
                return !collect($users)->pluck('id')->contains($this->user2->id);
            });
    }

    
    #[Test]

    public function it_removes_a_license_table_record()
    {
        $licenseTable = LicenseTable::factory()->create(['user_id' => $this->user3->id, 'date' => today(), 'order' => 1]);

        Livewire::test(LicenseManager::class)
            ->call('removeUser', $licenseTable->id)
            ->assertDispatched('toggleLoading', true)
            ->assertDispatched('toggleLoading', false);

        $this->assertModelMissing($licenseTable);
        
        // Verifica che l'utente sia tornato tra gli "available"
        Livewire::test(LicenseManager::class)
            ->assertSet('selectedUsers', [])
            ->assertSet('availableUsers', function ($users) {
                return collect($users)->pluck('id')->contains($this->user3->id);
            });
    }

    // --- Ordinamento ---

    #[Test]

    public function it_updates_the_order_of_selected_users()
    {
        $lt1 = LicenseTable::factory()->create(['user_id' => $this->user1->id, 'date' => today(), 'order' => 1]);
        $lt2 = LicenseTable::factory()->create(['user_id' => $this->user2->id, 'date' => today(), 'order' => 2]);

        $orderedIds = [
            ['value' => $lt2->id], // user2 ora è 1°
            ['value' => $lt1->id], // user1 ora è 2°
        ];

        Livewire::test(LicenseManager::class)
            ->call('updateOrder', $orderedIds)
            ->assertDispatched('toggleLoading', true)
            ->assertDispatched('toggleLoading', false)
            ->assertSee('Ordine aggiornato con successo!')
            ->assertSet('selectedUsers', function ($users) use ($lt1, $lt2) {
                return $users[0]['id'] === $lt2->id && $users[0]['order'] === 1 &&
                       $users[1]['id'] === $lt1->id && $users[1]['order'] === 2;
            });

        $this->assertDatabaseHas('license_table', ['id' => $lt2->id, 'order' => 1]);
        $this->assertDatabaseHas('license_table', ['id' => $lt1->id, 'order' => 2]);
    }

    // --- Conferma ---

    #[Test]

        public function it_confirms_the_selection_and_dispatches_event()
    {
        // 1. Setup: Creiamo almeno una licenza per oggi
        // (Altrimenti il metodo confirm() si ferma e mostra un errore)
        $user = \App\Models\User::factory()->create();
        
        \App\Models\LicenseTable::create([
            'user_id' => $user->id,
            'date'    => today(),
            'order'   => 1,
        ]);

        // 2. Esecuzione del Test
        \Livewire\Livewire::test(\App\Livewire\TableManager\LicenseManager::class)
            // Assicuriamoci che il componente carichi i dati (mount)
            ->call('confirm') 
            
            // 3. Asserzioni
            // Verifica che non ci siano errori nel componente
            ->assertSet('errorMessage', '') 
            
            // Verifica che l'evento 'confirmLicenses' sia stato emesso
            ->assertDispatched('confirmLicenses')
            
            // === IL FIX È QUI ===
            // Invece di controllare manualmente l'array di sessione,
            // usiamo l'asserzione integrata che gestisce tutto automaticamente.
            ->assertSee('Selezione confermata con successo!');
    }

    #[Test]

    public function it_prevents_confirmation_with_no_selected_users()
    {
        Livewire::test(LicenseManager::class)
            ->call('confirm')
            ->assertSet('errorMessage', 'Seleziona almeno un utente prima di confermare.')
            ->assertNotDispatched('confirmLicenses');
    }
}