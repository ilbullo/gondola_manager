<?php

namespace Tests\Feature\Livewire\Crud;

use App\Livewire\Crud\AgencyManager;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AgencyManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh', ['--env' => 'testing']);
    }

    #[Test]
    public function renders_successfully()
    {
        Livewire::test(AgencyManager::class)
            ->assertStatus(200)
            ->assertSee('Gestione Agenzie');
    }

    #[Test]
    public function can_create_new_agency()
    {
        Livewire::test(AgencyManager::class)
            ->set('name', 'Agenzia Test')
            ->set('code', 'TST9')
            ->call('create')
            ->assertHasNoErrors()
            ->assertSee('Agenzia creata con successo.')
            ->assertSet('showCreateForm', false);

        $this->assertDatabaseHas('agencies', [
            'name' => 'Agenzia Test',
            'code' => 'TST9',
        ]);
    }

    #[Test]
    public function can_edit_agency()
    {
        $agency = Agency::factory()->create();

        Livewire::test(AgencyManager::class)
            ->call('edit', $agency->id)
            ->assertSet('editingId', $agency->id)
            ->assertSet('name', $agency->name)
            ->assertSet('showEditForm', true);
    }

    #[Test]
    public function can_update_agency()
    {
        $agency = Agency::factory()->create();

        Livewire::test(AgencyManager::class)
            ->call('edit', $agency->id)
            ->set('name', 'Modificata')
            ->set('code', 'MD12')
            ->call('update')
            ->assertHasNoErrors()
            ->assertSee('Agenzia aggiornata con successo.');

        $this->assertDatabaseHas('agencies', [
            'id' => $agency->id,
            'name' => 'Modificata',
            'code' => 'MD12',
        ]);
    }

    #[Test]
    public function dispatches_confirm_delete_modal_with_correct_payload()
    {
        $agency = Agency::factory()->create();

        Livewire::test(AgencyManager::class)
            ->call('confirmDelete', $agency->id)
            ->assertDispatched('openConfirmModal', function ($eventName, $params) use ($agency) {
                // Livewire 3 passa i parametri in modi diversi → gestiamo tutti i casi
                $data = $params;

                // Caso 1: $params è già l'array del payload
                if (isset($params['message'])) {
                    $data = $params;
                }
                // Caso 2: $params[0] contiene il payload
                elseif (isset($params[0]) && is_array($params[0])) {
                    $data = $params[0];
                }
                // Caso 3: fallback estremo
                else {
                    return false;
                }

                return $data['message'] === 'Eliminare definitivamente questa agenzia?'
                    && $data['confirmEvent'] === 'confirmDeleteAgency'
                    && $data['payload'] === $agency->id;
            });
    }

    #[Test]
    public function can_delete_agency_via_confirmation()
    {
        $agency = Agency::factory()->create();

        Livewire::test(AgencyManager::class)
            ->dispatch('confirmDeleteAgency', $agency->id)
            ->assertSee('Agenzia eliminata con successo.');

        $this->assertSoftDeleted('agencies', ['id' => $agency->id]);
    }

    #[Test]
    public function can_restore_deleted_agency()
    {
        $agency = Agency::factory()->create();
        $agency->delete();

        Livewire::test(AgencyManager::class)
            ->call('restore', $agency->id)
            ->assertSee('Agenzia ripristinata con successo.');

        $this->assertDatabaseHas('agencies', ['id' => $agency->id, 'deleted_at' => null]);
    }

    #[Test]
    public function search_works()
    {
        Agency::factory()->create(['name' => 'Agenzia Nord', 'code' => 'NORD']);
        Agency::factory()->create(['name' => 'Agenzia Sud', 'code' => 'SUD']);

        Livewire::test(AgencyManager::class)
            ->set('search', 'Nord')
            ->assertSee('Agenzia Nord')
            ->assertDontSee('Agenzia Sud');
    }

    #[Test]
    public function can_toggle_deleted_visibility()
    {
        $active = Agency::factory()->create(['name' => 'Attiva']);
        $deleted = Agency::factory()->create(['name' => 'Cancellata']);
        $deleted->delete();

        Livewire::test(AgencyManager::class)
            ->assertSee('Attiva')
            ->assertDontSee('Cancellata')
            ->call('toggleShowDeleted')
            ->assertSee('Cancellata');
    }
}