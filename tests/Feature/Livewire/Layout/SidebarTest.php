<?php

namespace Tests\Feature\Livewire\Layout;

use App\Models\Agency;
use App\Livewire\Layout\Sidebar;
use App\Enums\WorkType;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SidebarTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_initializes_with_default_values()
    {
        Livewire::test(Sidebar::class)
            ->assertSet('workType', '')
            ->assertSet('excluded', false)
            ->assertSet('sharedFromFirst', false)
            ->assertSet('amount', config('app_settings.works.default_amount'));
    }

    #[Test]
    public function it_enforces_mutual_exclusion_between_excluded_and_shared()
    {
        $component = Livewire::test(Sidebar::class);

        // Se attivo 'excluded', 'sharedFromFirst' deve diventare false
        $component->set('sharedFromFirst', true)
            ->call('toggleExcluded')
            ->assertSet('excluded', true)
            ->assertSet('sharedFromFirst', false);

        // Viceversa
        $component->call('toggleShared')
            ->assertSet('sharedFromFirst', true)
            ->assertSet('excluded', false);
    }

    #[Test]
    public function it_dispatches_event_when_work_type_is_selected()
    {
        Livewire::test(Sidebar::class)
            ->call('setWorkType', WorkType::CASH->value)
            ->assertDispatched('workSelected', function ($name, $params) {
                // In Livewire 3: $name è la stringa 'workSelected'
                // $params[0] è l'array associativo dei dati
                return $params[0]['value'] === WorkType::CASH->value;
            });
    }

    #[Test]
    public function it_triggers_agency_modal_when_agency_type_is_selected()
    {
        Livewire::test(Sidebar::class)
            ->call('setWorkType', WorkType::AGENCY->value)
            ->assertDispatched('toggleAgencyModal', true);
    }

    #[Test]
    public function it_updates_state_when_an_agency_is_selected_externally()
    {
        $agency = Agency::factory()->create(['name' => 'Venezia Taxi']);

        Livewire::test(Sidebar::class)
            ->dispatch('agencySelected', agencyId: $agency->id)
            ->assertSet('agencyId', $agency->id)
            ->assertSet('agencyName', 'Venezia Taxi')
            ->assertSet('workType', WorkType::AGENCY->value)
            ->assertDispatched('workSelected');
    }

    #[Test]
    public function it_resets_selection_correctly()
    {
        Livewire::test(Sidebar::class)
            ->set('workType', 'X')
            ->set('voucher', 'TEST-123')
            ->call('setWorkType', 'clear')
            ->assertSet('workType', '')
            ->assertSet('voucher', '');
    }

    #[Test]
    public function it_syncs_to_table_on_voucher_update()
    {
        // Importante: impostiamo prima un workType per assicurarci che 
        // l'evento venga emesso con uno stato valido
        Livewire::test(Sidebar::class)
            ->call('setWorkType', WorkType::CASH->value)
            ->set('voucher', 'GIFT-CARD')
            ->assertDispatched('workSelected', function ($name, $params) {
                // Verifichiamo l'ultimo dispatch avvenuto (quello del voucher)
                return ($params[0]['voucher'] ?? '') === 'GIFT-CARD';
            });
    }
}