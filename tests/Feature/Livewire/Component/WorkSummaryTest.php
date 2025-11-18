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