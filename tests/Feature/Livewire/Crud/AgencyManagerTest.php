<?php

namespace Tests\Feature\Livewire\Crud;

use Tests\TestCase;
use App\Models\Agency;
use Livewire\Livewire;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use App\Livewire\Crud\AgencyManager;

class AgencyManagerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_renders_successfully()
    {
        Livewire::test(AgencyManager::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_filters_agencies_by_name_or_code()
    {
        Agency::factory()->create(['name' => 'Alpha Agency', 'code' => 'ALPH']);
        Agency::factory()->create(['name' => 'Beta Partner', 'code' => 'BETA']);

        Livewire::test(AgencyManager::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Agency')
            ->assertDontSee('Beta Partner')
            ->set('search', 'BETA')
            ->assertSee('Beta Partner')
            ->assertDontSee('Alpha Agency');
    }

    #[Test]
    public function it_validates_agency_creation_rules()
    {
        Livewire::test(AgencyManager::class)
            ->set('name', 'Invalid Name @#') // Regex fallisce
            ->set('code', 'too-long-code')   // Max 4
            ->call('create')
            ->assertHasErrors(['name', 'code']);
    }

    #[Test]
    public function it_creates_an_agency_and_invalidates_cache()
    {
        // Usiamo 'atLeast()->once()' per essere sicuri che venga pulita, 
        // senza fallire se Livewire la chiama una seconda volta durante il ciclo di vita del test.
        Cache::shouldReceive('forget')
            ->with('agencies_list')
            ->atLeast()
            ->once();

        Livewire::test(AgencyManager::class)
            ->set('name', 'New Agency')
            ->set('code', 'NEW1')
            ->call('create')
            ->assertDispatched('notify')
            ->assertSet('showCreateForm', false);

        $this->assertDatabaseHas('agencies', [
            'name' => 'New Agency',
            'code' => 'NEW1'
        ]);
    }

    #[Test]
    public function it_can_edit_and_update_an_agency()
    {
        $agency = Agency::factory()->create(['name' => 'Old Name', 'code' => 'OLD']);

        Livewire::test(AgencyManager::class)
            ->call('edit', $agency->id)
            ->assertSet('name', 'Old Name')
            ->set('name', 'Updated Name')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertEquals('Updated Name', $agency->refresh()->name);
    }

    #[Test]
    public function it_handles_soft_deletes_and_restoration()
    {
        $agency = Agency::factory()->create(['name' => 'To Delete']);

        $component = Livewire::test(AgencyManager::class);

        // Simuliamo l'evento di conferma eliminazione (che solitamente arriva dal modale)
        $component->dispatch('confirmDeleteAgency', payload: $agency->id);

        $this->assertSoftDeleted('agencies', ['id' => $agency->id]);

        // Attiviamo la visualizzazione degli eliminati
        $component->set('showDeleted', true)
            ->assertSee('To Delete')
            ->call('restore', $agency->id);

        $this->assertDatabaseHas('agencies', [
            'id' => $agency->id,
            'deleted_at' => null
        ]);
    }

    #[Test]
    public function it_persists_search_in_url()
    {
        Livewire::withQueryParams(['search' => 'GLOBAL'])
            ->test(AgencyManager::class)
            ->assertSet('search', 'GLOBAL');
    }
}