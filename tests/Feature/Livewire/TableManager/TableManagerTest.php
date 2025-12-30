<?php

namespace Tests\Feature\Livewire\TableManager;

use Tests\TestCase;
use App\Models\LicenseTable;
use App\Livewire\TableManager\TableManager;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class TableManagerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_initializes_correctly_with_existing_licenses(): void
    {
        // Setup: Creiamo una licenza per oggi
        LicenseTable::factory()->create(['date' => today()]);

        Livewire::test(TableManager::class)
            ->assertSet('hasLicenses', true)
            ->assertSet('tableConfirmed', true)
            ->assertSet('isRedistributed', false);
    }

    #[Test]
    public function it_enters_redistribution_mode_on_event(): void
    {
        Livewire::test(TableManager::class)
            ->dispatch('callRedistributeWorks')
            ->assertSet('isRedistributed', true)
            ->assertDispatched('redistributeWorks');
    }

    #[Test]
    public function it_handles_the_workflow_transitions_correctly(): void
    {
        $component = Livewire::test(TableManager::class);

        // 1. Conferma tabella
        $component->dispatch('licensesConfirmed')
            ->assertSet('tableConfirmed', true)
            ->assertSet('isRedistributed', false);

        // 2. Passa a modalità modifica
        $component->dispatch('editLicenses')
            ->assertSet('tableConfirmed', false);

        // 3. Torna a tabella assegnazione da splitter
        $component->set('isRedistributed', true)
            ->dispatch('goToAssignmentTable')
            ->assertSet('isRedistributed', false)
            ->assertSet('tableConfirmed', false); // Resta invariato
    }

    #[Test]
    public function it_clears_all_licenses_and_dispatches_events(): void
    {
        // Setup: creiamo licenze che devono essere cancellate
        LicenseTable::factory()->count(3)->create(['date' => today()]);
        
        $this->assertDatabaseCount('license_table', 3);

        Livewire::test(TableManager::class)
            ->dispatch('resetLicenses')
            ->assertSet('hasLicenses', false)
            ->assertSet('tableConfirmed', false)
            ->assertDispatched('licensesCleared')
            ->assertDispatched('tableReset');

        // Verifica fisica sul database
        $this->assertDatabaseCount('license_table', 0);
    }

    #[Test]
    public function it_refreshes_status_when_requested(): void
    {
        $component = Livewire::test(TableManager::class)
            ->assertSet('hasLicenses', false);

        // Creiamo una licenza "alle spalle" del componente
        LicenseTable::factory()->create(['date' => today()]);

        // Eseguiamo il refresh manuale
        $component->call('refreshLicenseStatus')
            ->assertSet('hasLicenses', true);
    }
}