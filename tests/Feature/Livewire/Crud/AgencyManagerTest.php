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
        // Ensure migrations are run for the test database
        $this->artisan('migrate', ['--env' => 'testing']);
    }

    #[Test]
    public function it_renders_the_component_successfully()
    {
        Livewire::test(AgencyManager::class)
            ->assertStatus(200)
            ->assertSee('Gestione Agenzie');
    }

    #[Test]
    public function it_displays_existing_agencies()
    {
        $agencies = Agency::factory()->count(3)->create();

        Livewire::test(AgencyManager::class)
            ->assertSee($agencies->first()->name)
            ->assertSee($agencies->first()->code)
            ->assertSee($agencies->last()->name)
            ->assertSee($agencies->last()->code);
    }

    #[Test]
    public function it_toggles_the_create_form()
    {
        Livewire::test(AgencyManager::class)
            ->assertSet('showCreateForm', false)
            ->call('toggleCreateForm')
            ->assertSet('showCreateForm', true)
            ->call('toggleCreateForm')
            ->assertSet('showCreateForm', false);
    }

    #[Test]
    public function it_toggles_show_deleted_agencies()
    {
        Livewire::test(AgencyManager::class)
            ->assertSet('showDeleted', false)
            ->call('toggleShowDeleted')
            ->assertSet('showDeleted', true)
            ->call('toggleShowDeleted')
            ->assertSet('showDeleted', false);
    }

    #[Test]
    public function it_creates_a_new_agency()
    {
        Livewire::test(AgencyManager::class)
            ->set('name', 'New Agency')
            ->set('code', 'NEW123')
            ->call('create')
            ->assertHasNoErrors()
            ->assertSessionHas('message', 'Agenzia creata con successo.')
            ->assertSet('showCreateForm', false)
            ->assertSet('name', null)
            ->assertSet('code', null);

        $this->assertDatabaseHas('agencies', [
            'name' => 'New Agency',
            'code' => 'NEW123',
        ]);
    }

    #[Test]
    public function it_fails_to_create_agency_with_invalid_data()
    {
        Livewire::test(AgencyManager::class)
            ->set('name', '') // Empty name
            ->set('code', 'TOOLONG123456') // Code too long
            ->call('create')
            ->assertHasErrors([
                'name' => 'required',
                'code' => 'max',
            ]);
    }

    #[Test]
    public function it_fails_to_create_agency_with_duplicate_code()
    {
        Agency::factory()->create(['code' => 'DUPE123']);

        Livewire::test(AgencyManager::class)
            ->set('name', 'Duplicate Agency')
            ->set('code', 'DUPE123')
            ->call('create')
            ->assertHasErrors(['code' => 'unique']);
    }

    #[Test]
    public function it_loads_an_agency_for_editing()
    {
        $agency = Agency::factory()->create();

        Livewire::test(AgencyManager::class)
            ->call('edit', $agency->id)
            ->assertSet('editingId', $agency->id)
            ->assertSet('name', $agency->name)
            ->assertSet('code', $agency->code)
            ->assertSet('showEditForm', true)
            ->assertSet('showCreateForm', false);
    }

    #[Test]
    public function it_updates_an_existing_agency()
    {
        $agency = Agency::factory()->create();

        Livewire::test(AgencyManager::class)
            ->call('edit', $agency->id)
            ->set('name', 'Updated Agency')
            ->set('code', 'UPD123')
            ->call('update')
            ->assertHasNoErrors()
            ->assertSessionHas('message', 'Agenzia aggiornata con successo.')
            ->assertSet('showEditForm', false)
            ->assertSet('name', null)
            ->assertSet('code', null)
            ->assertSet('editingId', null);

        $this->assertDatabaseHas('agencies', [
            'id' => $agency->id,
            'name' => 'Updated Agency',
            'code' => 'UPD123',
        ]);
    }

    #[Test]
    public function it_fails_to_update_agency_with_duplicate_code()
    {
        $agency1 = Agency::factory()->create(['code' => 'EXIST123']);
        $agency2 = Agency::factory()->create();

        Livewire::test(AgencyManager::class)
            ->call('edit', $agency2->id)
            ->set('code', 'EXIST123')
            ->call('update')
            ->assertHasErrors(['code' => 'unique']);
    }

    #[Test]
    public function it_deletes_an_agency()
    {
        $agency = Agency::factory()->create();
        $this->assertDatabaseHas('agencies', ['id' => $agency->id, 'deleted_at' => null]);

        Livewire::test(AgencyManager::class)
            ->call('delete', $agency->id)
            ->assertHasNoErrors()
            ->assertSessionHas('message', 'Agenzia eliminata con successo.');

        $this->assertSoftDeleted('agencies', ['id' => $agency->id]);
    }

    #[Test]
    public function it_restores_a_deleted_agency()
    {
        // Create and soft-delete an agency
        $agency = Agency::factory()->create();
        $agency->delete();
        $this->assertSoftDeleted('agencies', ['id' => $agency->id]);

        // Test restoration
        Livewire::test(AgencyManager::class)
            ->call('restore', $agency->id)
            ->assertHasNoErrors()
            ->assertSessionHas('message', 'Agenzia ripristinata con successo.');


        // Verify the agency is restored
        $this->assertDatabaseHas('agencies', [
            'id' => $agency->id,
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function it_searches_agencies_by_name_and_code()
    {
        Agency::factory()->create(['name' => 'Test Agency', 'code' => 'TEST123']);
        Agency::factory()->create(['name' => 'Other Agency', 'code' => 'OTH456']);

        Livewire::test(AgencyManager::class)
            ->set('search', 'Test')
            ->assertSee('Test Agency')
            ->assertSee('TEST123')
            ->assertDontSee('Other Agency')
            ->set('search', 'OTH456')
            ->assertSee('Other Agency')
            ->assertSee('OTH456')
            ->assertDontSee('Test Agency')
            ->set('search', '')
            ->assertSee('Test Agency')
            ->assertSee('Other Agency');
    }

    #[Test]
    public function it_displays_deleted_agencies_when_toggled()
    {
        $activeAgency = Agency::factory()->create(['name' => 'Active Agency']);
        $deletedAgency = Agency::factory()->create(['name' => 'Deleted Agency']);
        $deletedAgency->delete();

        Livewire::test(AgencyManager::class)
            ->assertSee('Active Agency')
            ->assertDontSee('Deleted Agency')
            ->call('toggleShowDeleted')
            ->assertSee('Active Agency')
            ->assertSee('Deleted Agency');
    }

    #[Test]
    public function it_resets_the_form_fields()
    {
        Livewire::test(AgencyManager::class)
            ->set('name', 'Test Agency')
            ->set('code', 'TEST123')
            ->set('editingId', 1)
            ->set('showEditForm', true)
            ->call('resetForm')
            ->assertSet('name', null)
            ->assertSet('code', null)
            ->assertSet('editingId', null)
            ->assertSet('showEditForm', false);
    }

    #[Test]
    public function it_dispatches_confirm_delete_event()
    {
        $agency = Agency::factory()->create();

        Livewire::test(AgencyManager::class)
            ->call('confirmDelete', $agency->id)
            ->assertDispatched('openConfirmModal', [
                'message' => 'Eliminare questa agenzia?',
                'confirmEvent' => 'confirmDeleteAgency',
                'payload' => $agency->id,
            ]);
    }

    #[Test]
    public function it_handles_confirm_delete_agency_event()
    {
        $agency = Agency::factory()->create();
        $this->assertDatabaseHas('agencies', ['id' => $agency->id, 'deleted_at' => null]);

        Livewire::test(AgencyManager::class)
            ->call('confirmDelete', $agency->id)
            ->dispatch('confirmDeleteAgency', $agency->id)
            ->assertHasNoErrors()
            ->assertSessionHas('message', 'Agenzia eliminata con successo.');

        $this->assertSoftDeleted('agencies', ['id' => $agency->id]);
    }

    #[Test]
    public function it_paginates_agencies_correctly()
    {
        Agency::factory()->count(15)->create();

        Livewire::test(AgencyManager::class)
            ->assertSee(Agency::first()->name)
            ->assertDontSee(Agency::skip(10)->first()->name)
            ->call('gotoPage', 2)
            ->assertSee(Agency::skip(10)->first()->name)
            ->assertDontSee(Agency::first()->name);
    }
}