<?php

namespace Tests\Feature\Livewire\Ui;

use App\Models\Agency;
use App\Livewire\Ui\AgencyModal;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AgencyModalTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_is_hidden_by_default()
    {
        Livewire::test(AgencyModal::class)
            ->assertSet('show', false)
            ->assertCount('agencies', 0);
    }

    #[Test]
    public function it_opens_and_loads_agencies_on_event()
    {
        Agency::factory()->count(3)->create();
        // Puliamo la cache per assicurarci che il test legga i dati freschi del factory
        Cache::forget('agencies_list');

        Livewire::test(AgencyModal::class)
            ->dispatch('toggleAgencyModal', visible: true)
            ->assertSet('show', true)
            ->assertCount('agencies', 3);
    }

    #[Test]
    public function it_uses_cache_to_retrieve_agencies()
    {
        Agency::factory()->create(['name' => 'Agenzia Cache']);
        
        // Simuliamo dati già presenti in cache
        Cache::put('agencies_list', collect([
            (object)['id' => 99, 'name' => 'Agenzia Mocked', 'code' => 'MOCK']
        ]));

        Livewire::test(AgencyModal::class)
            ->set('show', true)
            ->assertSee('Agenzia Mocked')
            ->assertDontSee('Agenzia Cache');
    }

    #[Test]
    public function it_dispatches_agency_selected_event_and_closes()
    {
        $agency = Agency::factory()->create();

        Livewire::test(AgencyModal::class)
            ->set('show', true)
            ->call('selectAgency', $agency->id)
            ->assertDispatched('agencySelected', agencyId: $agency->id)
            ->assertSet('show', false);
    }

    #[Test]
    public function it_resets_errors_on_close()
    {
        Livewire::test(AgencyModal::class)
            ->set('show', true)
            // Simuliamo un errore manualmente nel bag
            ->tap(fn ($component) => $component->instance()->addError('agency', 'error'))
            ->call('toggle', false)
            ->assertHasNoErrors();
    }
}