<?php

namespace Tests\Feature\Livewire\Ui;

use App\Livewire\Ui\WorkDetailsModal;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class WorkDetailsModalTest extends TestCase
{
    #[Test]
    public function it_populates_data_when_work_is_selected()
    {
        $work = [
            'value' => 'X',
            'amount' => 120.50,
            'slotsOccupied' => 2,
            'excluded' => true,
            'sharedFromFirst' => false
        ];

        Livewire::test(WorkDetailsModal::class)
            ->dispatch('workSelected', work: $work)
            ->assertSet('value', 'X')
            ->assertSet('amount', 120.50)
            ->assertSet('slotsOccupied', 2)
            ->assertSet('excluded', true);
    }

    #[Test]
    public function it_enforces_mutual_exclusion_between_excluded_and_shared()
    {
        $component = Livewire::test(WorkDetailsModal::class)
            ->set('isOpen', true)
            // 1. Impostiamo shared a true
            ->set('sharedFromFirst', true)
            // 2. Attiviamo excluded -> shared deve diventare false
            ->set('excluded', true)
            ->assertSet('sharedFromFirst', false)
            // 3. Riattiviamo shared -> excluded deve diventare false
            ->set('sharedFromFirst', true)
            ->assertSet('excluded', false);
    }

    #[Test]
    public function it_validates_slots_occupied_range()
    {
        Livewire::test(WorkDetailsModal::class)
            ->set('isOpen', true)
            ->set('slotsOccupied', 5) // Fuori range (max 4)
            ->call('save')
            ->assertHasErrors(['slotsOccupied' => 'in']);
    }

    #[Test]
    public function it_dispatches_update_event_on_save()
    {
        Livewire::test(WorkDetailsModal::class)
            ->set('isOpen', true)
            ->set('amount', 150)
            ->set('slotsOccupied', 3)
            ->call('save')
            ->assertDispatched('updateWorkDetails', function($name, $params) {
                // In Livewire 3, il secondo parametro ($params) contiene i dati inviati
                // Se hai inviato un array, sarà il primo elemento di $params
                $data = $params[0] ?? $params; 
                
                return $data['amount'] == 150 && $data['slotsOccupied'] == 3;
            })
            ->assertSet('isOpen', false);
    }

    #[Test]
    public function it_resets_form_on_close()
    {
        Livewire::test(WorkDetailsModal::class)
            ->set('amount', 500)
            ->set('excluded', true)
            ->call('closeModal')
            ->assertSet('amount', config('app_settings.works.default_amount'))
            ->assertSet('excluded', false)
            ->assertSet('isOpen', false);
    }
}