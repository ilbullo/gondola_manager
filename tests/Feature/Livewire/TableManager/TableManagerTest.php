<?php

namespace Tests\Feature\Livewire\TableManager;

use App\Livewire\TableManager\TableManager;
use App\Models\{LicenseTable, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class TableManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_mounts_and_sets_initial_status_when_licenses_exist()
    {
        LicenseTable::factory()->create(['user_id' => $this->user->id, 'date' => today()]);

        Livewire::test(TableManager::class)
            ->assertSet('hasLicenses', true)
            ->assertSet('tableConfirmed', true); // Quando haLicenses è true, tableConfirmed è true al mount
    }

    #[Test]
    public function it_mounts_and_sets_initial_status_when_no_licenses_exist()
    {
        Livewire::test(TableManager::class)
            ->assertSet('hasLicenses', false)
            ->assertSet('tableConfirmed', false);
    }

    #[Test]
    public function it_confirms_the_table_on_confirmLicenses_event()
    {
        Livewire::test(TableManager::class)
            ->set('tableConfirmed', false)
            ->dispatch('confirmLicenses')
            ->assertSet('tableConfirmed', true);
    }

    #[Test]
    public function it_enters_edit_mode_on_editLicenses_event()
    {
        Livewire::test(TableManager::class)
            ->set('tableConfirmed', true)
            ->dispatch('editLicenses')
            ->assertSet('tableConfirmed', false);
    }

    #[Test]
    public function it_clears_all_licenses_on_resetLicenses_event()
    {
        // Setup: Crea due record LicenseTable e un WorkAssignment
        $lt1 = LicenseTable::factory()->create(['user_id' => $this->user->id, 'date' => today()]);
        LicenseTable::factory()->create(['user_id' => User::factory()->create()->id, 'date' => today()]);

        // Assicura che i dati esistano prima del reset
        $this->assertDatabaseCount('license_table', 2);

        Livewire::test(TableManager::class)
            ->dispatch('resetLicenses')
            ->assertSet('hasLicenses', false)
            ->assertSet('tableConfirmed', false)
            ->assertDispatched('licensesCleared')
            ->assertDispatched('tableReset');

        // Verifica che tutti i record LicenseTable siano stati eliminati
        $this->assertDatabaseCount('license_table', 0);
    }

    #[Test]
    public function it_refreshes_license_status()
    {
        $test = Livewire::test(TableManager::class)
            ->assertSet('hasLicenses', false)
            ->assertSet('tableConfirmed', false);

        // Crea una licenza dopo il mount
        LicenseTable::factory()->create(['user_id' => $this->user->id, 'date' => today()]);

        // Chiama il refresh
        $test->call('refreshLicenseStatus')
            ->assertSet('hasLicenses', true)
            ->assertSet('tableConfirmed', true);
    }
}