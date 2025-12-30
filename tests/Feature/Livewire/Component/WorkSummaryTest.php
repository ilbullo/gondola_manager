<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Component\WorkSummary;
use App\Models\LicenseTable;
use App\Models\WorkAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class WorkSummaryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_calculates_initial_counts_correctly_on_mount()
    {
        $license = LicenseTable::factory()->create();

        // Creiamo lavori di vario tipo per oggi
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'value' => 'N', 'timestamp' => today(), 'slot' => 1]);
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'value' => 'N', 'timestamp' => today(), 'slot' => 2]);
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'value' => 'A', 'timestamp' => today(), 'slot' => 3]);

        Livewire::test(WorkSummary::class)
            ->assertSet('counts.N', 2)
            ->assertSet('counts.A', 1)
            ->assertSet('counts.X', 0)
            ->assertSet('total', 3)
            ->assertSee('2') // Verifica che il numero appaia nel HTML
            ->assertSee('Totale:');
    }

    #[Test]
    public function it_refreshes_when_notified_by_events()
    {
        $license = LicenseTable::factory()->create();
        $component = Livewire::test(WorkSummary::class);

        // All'inizio tutto a zero
        $component->assertSet('total', 0);

        // Simuliamo l'aggiunta di un lavoro nel database
        WorkAssignment::factory()->create([
            'license_table_id' => $license->id, 
            'value' => 'X', 
            'timestamp' => today(),
            'slot' => 1
        ]);

        // Scateniamo l'evento 'workAssigned' come se arrivasse dal TableSplitter
        $component->dispatch('workAssigned');

        // Verifichiamo che il componente abbia reagito
        $component->assertSet('counts.X', 1)
                  ->assertSet('total', 1);
    }

    #[Test]
    public function it_only_counts_assignments_from_today()
    {
        $license = LicenseTable::factory()->create();

        // Lavoro di ieri (non deve essere contato)
        WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'value' => 'N',
            'timestamp' => today()->subDay(),
            'slot' => 1
        ]);

        // Lavoro di oggi
        WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'value' => 'N',
            'timestamp' => today(),
            'slot' => 2
        ]);

        Livewire::test(WorkSummary::class)
            ->assertSet('counts.N', 1)
            ->assertSet('total', 1);
    }

    #[Test]
    public function it_resets_to_zero_on_table_reset_event()
    {
        $license = LicenseTable::factory()->create();
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'value' => 'P', 'timestamp' => today()]);

        $component = Livewire::test(WorkSummary::class);
        $component->assertSet('total', 1);

        // Simuliamo la cancellazione fisica dei dati
        WorkAssignment::query()->delete();

        // Lanciamo l'evento di reset
        $component->dispatch('tableReset');

        $component->assertSet('total', 0)
                  ->assertSet('counts.P', 0);
    }
}