<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Component\WorkSummary;
use App\Enums\WorkType;
use App\Services\WorkAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

class WorkSummaryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_calculates_initial_counts_correctly_from_passed_licenses()
    {
        // Prepariamo una matrice finta (mock data) coerente con LicenseResource
        $licenses = [
            [
                'id' => 1,
                'worksMap' => [
                    ['value' => 'N', 'id' => 10],
                    ['value' => 'N', 'id' => 11],
                    ['value' => 'A', 'id' => 12],
                ]
            ]
        ];

        // Testiamo il componente passando le licenze tramite mount
        Livewire::test(WorkSummary::class, ['licenses' => $licenses])
            ->assertSet('counts.N', 2)
            ->assertSet('counts.A', 1)
            ->assertSet('total', 3)
            ->assertSee('2') // Verifica render HTML per N
            ->assertSee('1'); // Verifica render HTML per A
    }

    #[Test]
    public function it_refreshes_when_receiving_matrix_updated_event_with_payload()
    {
        $component = Livewire::test(WorkSummary::class);

        // Stato iniziale vuoto
        $component->assertSet('total', 0);

        // Simuliamo l'evento con il payload delle licenze (come fa il padre)
        $updatedLicenses = [
            [
                'id' => 1,
                'worksMap' => [
                    ['value' => 'X', 'id' => 99]
                ]
            ]
        ];

        $component->dispatch('matrix-updated', licenses: $updatedLicenses)
                  ->assertSet('counts.X', 1)
                  ->assertSet('total', 1);
    }

    #[Test]
    public function it_can_refresh_correctly_when_receiving_new_data()
    {
        // 1. Definiamo i dati finti (mock data)
        $mockData = [
            [
                'worksMap' => [
                    ['value' => 'P', 'id' => 50]
                ]
            ]
        ];

        // 2. Invece di forzare un evento che il componente non ha (refreshTableBoard)
        // Testiamo la logica di aggiornamento tramite l'evento che il componente 
        // SICURAMENTE ascolta o tramite il refresh dei parametri.
        
        $component = Livewire::test(WorkSummary::class);

        // Simuliamo l'arrivo dei dati (che è quello che succede quando il padre aggiorna)
        $component->dispatch('matrix-updated', licenses: $mockData)
                ->assertSet('counts.P', 1)
                ->assertSet('total', 1);
    }

    #[Test]
    public function it_resets_counts_correctly_when_receiving_empty_matrix()
    {
        $initialLicenses = [
            ['worksMap' => [['value' => 'N', 'id' => 1]]]
        ];

        Livewire::test(WorkSummary::class, ['licenses' => $initialLicenses])
            ->assertSet('total', 1)
            ->dispatch('matrix-updated', licenses: []) // Reset tramite evento vuoto
            ->assertSet('total', 0)
            ->assertSet('counts.N', 0);
    }
}