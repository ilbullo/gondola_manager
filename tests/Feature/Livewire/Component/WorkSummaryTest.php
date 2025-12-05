<?php

namespace Tests\Feature\Livewire\Component;

use App\Livewire\Component\WorkSummary;
use App\Models\WorkAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class WorkSummaryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_starts_with_zero_counts()
    {
        Livewire::test(WorkSummary::class)
            ->assertSet('counts', [
                'N' => 0,
                'X' => 0,
                'A' => 0,
                'P' => 0,
            ])
            ->assertSet('total', 0);
    }

    #[Test]
    public function it_ignores_invalid_values_and_does_not_break()
    {
        WorkAssignment::factory()->create(['value' => null]);
        WorkAssignment::factory()->create(['value' => '']);
        WorkAssignment::factory()->create(['value' => 'I']);
        WorkAssignment::factory()->create(['value' => 1]);
        WorkAssignment::factory()->create(['value' => []]);

        Livewire::test(WorkSummary::class)
            ->call('refreshCounts')
            ->assertSet('counts', [
                'N' => 0,
                'X' => 0,
                'A' => 0,
                'P' => 0,
            ])
            ->assertSet('total', 0);
    }

    #[Test]
    public function it_does_not_fail_if_table_is_empty_after_initial_mount()
    {
        Livewire::test(WorkSummary::class)
            ->assertSet('counts', [
                'N' => 0,
                'X' => 0,
                'A' => 0,
                'P' => 0,
            ])
            ->assertSet('total', 0);
    }

    #[Test]
    public function refreshCounts_is_idempotent()
    {
        WorkAssignment::factory()->count(4)->create(['value' => 'X']);

        $component = Livewire::test(WorkSummary::class);

        $component->call('refreshCounts')
                ->assertSet('counts.X', 4);

        // richiamato di nuovo → nessun cambiamento
        $component->call('refreshCounts')
                ->assertSet('counts.X', 4);
    }

    #[Test]
    public function events_do_not_break_if_no_records_exist()
    {
        Livewire::test(WorkSummary::class)
            ->dispatch('workAssigned')
            ->assertSet('total', 0)
            ->dispatch('confirmRemoveAssignment')
            ->assertSet('total', 0)
            ->dispatch('licensesCleared')
            ->assertSet('total', 0)
            ->dispatch('tableReset')
            ->assertSet('total', 0);
    }

    #[Test]
    public function it_handles_simultaneous_events_sequence_correctly()
    {
        WorkAssignment::factory()->create(['value' => 'N']);

        $component = Livewire::test(WorkSummary::class);

        $component->dispatch('workAssigned')
                ->assertSet('counts.N', 1);

        // Cancello tutto
        WorkAssignment::truncate();

        // evento immediato
        $component->dispatch('confirmRemoveAssignment')
                ->assertSet('total', 0);

        $component->dispatch('tableReset')
                ->assertSet('total', 0);

        $component->dispatch('licensesCleared')
                ->assertSet('total', 0);
    }

    #[Test]
    public function it_counts_mixed_values_correctly_even_with_duplicates()
    {
        WorkAssignment::factory()->create(['value' => 'X']);
        WorkAssignment::factory()->create(['value' => 'X']);
        WorkAssignment::factory()->create(['value' => 'P']);
        WorkAssignment::factory()->create(['value' => 'P']);
        WorkAssignment::factory()->create(['value' => 'A']);
        WorkAssignment::factory()->create(['value' => 'N']);
        WorkAssignment::factory()->create(['value' => 'N']);

        Livewire::test(WorkSummary::class)
            ->call('refreshCounts')
            ->assertSet('counts', [
                'N' => 2,
                'X' => 2,
                'A' => 1,
                'P' => 2,
            ])
            ->assertSet('total', 7);
    }

#[Test]
public function it_handles_corrupted_database_rows()
{
    // Creo un record valido in license_table
    $license = \App\Models\LicenseTable::factory()->create();

    // Valore valido
    WorkAssignment::factory()->create([
        'value' => 'X',
        'license_table_id' => $license->id,
    ]);

    // Record con valore nullo
    WorkAssignment::factory()->create([
        'value' => null,
        'license_table_id' => $license->id,
    ]);

    // Record con valore vuoto o non conteggiato
    WorkAssignment::factory()->create([
        'value' => '',
        'license_table_id' => $license->id,
    ]);

    $component = Livewire::test(WorkSummary::class)
        ->call('refreshCounts');

    // Solo il valore valido 'X' deve essere contato
    $component->assertSet('counts.X', 1)
              ->assertSet('total', 1);
}

 
    #[Test]
    public function it_refreshes_counts_correctly_from_database()
    {
        // Crea dati reali
        WorkAssignment::factory()->create(['value' => 'N']);
        WorkAssignment::factory()->count(3)->create(['value' => 'X']);
        WorkAssignment::factory()->count(2)->create(['value' => 'A']);
        WorkAssignment::factory()->create(['value' => 'P']);

        // Ignora valori non conteggiati
        WorkAssignment::factory()->create(['value' => 'Z']);

        Livewire::test(WorkSummary::class)
            ->call('refreshCounts')
            ->assertSet('counts', [
                'N' => 1,
                'X' => 3,
                'A' => 2,
                'P' => 1,
            ])
            ->assertSet('total', 7);
    }

    #[Test]
    public function it_refreshes_on_work_assigned_event()
    {
        $component = Livewire::test(WorkSummary::class);

        WorkAssignment::factory()->create(['value' => 'X']);

        $component->dispatch('workAssigned')
                  ->assertSet('counts.X', 1)
                  ->assertSet('total', 1);
    }

    #[Test]
    public function it_refreshes_on_confirm_remove_assignment_event()
    {
        WorkAssignment::factory()->count(5)->create(['value' => 'A']);

        $component = Livewire::test(WorkSummary::class)
            ->call('refreshCounts')
            ->assertSet('counts.A', 5);

        // Simula rimozione
        WorkAssignment::query()->delete();

        $component->dispatch('confirmRemoveAssignment')
                  ->assertSet('counts.A', 0)
                  ->assertSet('total', 0);
    }

    #[Test]
    public function it_refreshes_on_licenses_cleared_event()
    {
        WorkAssignment::factory()->create(['value' => 'P']);
        WorkAssignment::factory()->create(['value' => 'N']);

        $component = Livewire::test(WorkSummary::class)
            ->call('refreshCounts')
            ->assertSet('total', 2);

        WorkAssignment::query()->delete();

        $component->dispatch('licensesCleared')
                  ->assertSet('total', 0);
    }

    #[Test]
    public function it_refreshes_on_table_reset_event()
    {
        WorkAssignment::factory()->count(10)->create(['value' => 'X']);

        $component = Livewire::test(WorkSummary::class)
            ->assertSet('counts.X', 10);

        WorkAssignment::truncate();

        $component->dispatch('tableReset')
                  ->assertSet('counts.X', 0)
                  ->assertSet('total', 0);
    }

    #[Test]
    public function multiple_events_all_trigger_refresh()
    {
        WorkAssignment::factory()->create(['value' => 'N']);

        $component = Livewire::test(WorkSummary::class);

        $component->dispatch('workAssigned')
                  ->assertSet('counts.N', 1);

        $component->dispatch('confirmRemoveAssignment')
                  ->assertSet('counts.N', 1); // ancora presente

        WorkAssignment::query()->delete();

        $component->dispatch('licensesCleared')
                  ->assertSet('counts.N', 0);
    }

    #[Test]
    public function it_mounts_and_loads_initial_data()
    {
        WorkAssignment::factory()->create(['value' => 'A']);
        WorkAssignment::factory()->create(['value' => 'P']);

        Livewire::test(WorkSummary::class)
            ->assertSet('counts.A', 1)
            ->assertSet('counts.P', 1)
            ->assertSet('total', 2);
    }
}