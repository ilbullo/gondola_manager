<?php

namespace Tests\Feature\Livewire\Ui;

use App\Livewire\Ui\WorkDetailsModal;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class WorkDetailsModalTest extends TestCase
{
    #[Test]
    public function it_starts_closed_with_default_values()
    {
        Livewire::test(WorkDetailsModal::class)
            ->assertSet('isOpen', false)
            ->assertSet('amount', 90)
            ->assertSet('slotsOccupied', 1)
            ->assertSet('excluded', false);
    }

    #[Test]
    public function it_opens_via_event()
    {
        Livewire::test(WorkDetailsModal::class)
            ->dispatch('openWorkDetailsModal')
            ->assertSet('isOpen', true);
    }

    #[Test]
    public function it_updates_fields_from_work_selected_event()
    {
        Livewire::test(WorkDetailsModal::class)
            ->dispatch('workSelected', [
                'amount' => 150.50,
                'slotsOccupied' => 3,
                'excluded' => true,
            ])
            ->assertSet('amount', 150.50)
            ->assertSet('slotsOccupied', 3)
            ->assertSet('excluded', true);
    }

    #[Test]
    public function it_validates_and_emits_save_event()
    {
        Livewire::test(WorkDetailsModal::class)
            ->set('isOpen', true)
            ->set('amount', 200)
            ->set('slotsOccupied', 4)
            ->set('excluded', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('updateWorkDetails', [
                'amount' => 200,
                'slotsOccupied' => 4,
                'excluded' => true,
            ])
            ->assertSet('isOpen', false);
    }

    #[Test]
    public function it_fails_validation_with_invalid_data()
    {
        Livewire::test(WorkDetailsModal::class)
            ->set('isOpen', true)
            ->set('amount', -10)
            ->set('slotsOccupied', 99)
            ->call('save')
            ->assertHasErrors([
                'amount' => 'min',
                'slotsOccupied' => 'in',
            ]);
    }

    #[Test]
    public function close_resets_form_and_hides_modal()
    {
        Livewire::test(WorkDetailsModal::class)
            ->set('isOpen', true)
            ->set('amount', 999)
            ->call('closeModal')
            ->assertSet('isOpen', false)
            ->assertSet('amount', 90);
    }
}