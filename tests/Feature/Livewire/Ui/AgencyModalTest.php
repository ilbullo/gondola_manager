<?php

namespace Tests\Feature\Livewire\Ui;

use App\Livewire\Ui\AgencyModal;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class AgencyModalTest extends TestCase
{
    #[Test]
    public function it_starts_closed_with_empty_agencies()
    {
        Livewire::test(AgencyModal::class)
            ->assertSet('show', false)
            ->assertSet('agencies', [])
            ->assertSee('Nessuna agenzia disponibile'); // vista mostra questo quando vuoto
    }

    #[Test]
    public function it_opens_and_populates_agencies_via_event()
    {
        $agencies = [
            ['id' => 1, 'name' => 'Agenzia Roma'],
            ['id' => 2, 'name' => 'Agenzia Milano'],
        ];

        Livewire::test(AgencyModal::class)
            ->dispatch('toggleAgencyModal', true, $agencies)
            ->assertSet('show', true)
            ->assertSet('agencies', $agencies)
            ->assertSee('Agenzia Roma')
            ->assertSee('Agenzia Milano');
    }

    #[Test]
    public function it_closes_and_clears_data()
    {
        $initialAgencies = [
            ['id' => 10, 'name' => 'Agenzia Test'],
            ['id' => 20, 'name' => 'Agenzia Prova'],
        ];

        Livewire::test(AgencyModal::class)
            ->dispatch('toggleAgencyModal', true, $initialAgencies)
            ->assertSet('show', true)
            ->assertSee('Agenzia Test')
            ->call('close')
            ->assertSet('show', false)
            ->assertSet('agencies', [])
            ->assertSee('Nessuna agenzia disponibile');
    }
}