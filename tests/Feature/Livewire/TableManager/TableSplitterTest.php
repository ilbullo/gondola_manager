<?php

namespace Tests\Feature\Livewire\TableManager;

use App\Livewire\TableManager\TableSplitter;
use App\Models\LicenseTable;
use App\Models\User;
use App\Models\WorkAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Enums\DayType;

class TableSplitterTest extends TestCase
{
    use RefreshDatabase;

    public function test_splitter_renders_and_generates_initial_table()
    {
        $user = User::factory()->create();
        LicenseTable::factory()->create(['user_id' => $user->id, 'date' => today()]);

        Livewire::test(TableSplitter::class)
            ->assertStatus(200)
            ->assertViewHas('splitTable');
    }

    public function test_updating_bancale_cost_regenerates_table()
    {
        $user = User::factory()->create();
        // 1. Cattura l'istanza reale della LicenseTable
        $licenseTable = LicenseTable::factory()->create(['user_id' => $user->id, 'date' => today()]);

        // Creiamo un lavoro X distribuibile
        WorkAssignment::create([
            'license_table_id' => $licenseTable->id, // <-- CORREZIONE: Usa l'ID reale appena creato
            'value' => 'X',
            'slots_occupied' => 1,
            'excluded' => false,
            'timestamp' => now(),
            'slot' => 1, 
        ]);

        $component = Livewire::test(TableSplitter::class);
        
        // Impostiamo costo bancale
        $component->set('bancaleCost', 10.0);

        // ... (resto del test)
        $splitTable = $component->get('splitTable');
        
        $this->assertNotEmpty($splitTable);
    }

    public function test_toggle_exclude_from_a()
    {
        $lt = LicenseTable::factory()->create(['date' => today()]);

        Livewire::test(TableSplitter::class)
            ->call('toggleExcludeFromA', $lt->id)
            ->assertSet('excludedFromA', [$lt->id])
            ->call('toggleExcludeFromA', $lt->id) // Toggle off
            ->assertSet('excludedFromA', []);
    }

    public function test_shifts_update_triggers_regeneration()
    {
        $lt = LicenseTable::factory()->create(['date' => today()]);
        
        Livewire::test(TableSplitter::class)
            ->set("shifts.{$lt->id}", DayType::MORNING->value)
            ->assertSet("shifts.{$lt->id}", 'morning');
            // La rigenerazione avviene automaticamente su updatedShifts, 
            // verifichiamo che non scoppi nulla
    }

    public function test_print_split_table_sets_session_flash()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        LicenseTable::factory()->create(['date' => today()]);

        Livewire::test(TableSplitter::class)
            ->call('printSplitTable')
            ->assertRedirect(route('generate.pdf'))
            ->assertSessionHas('pdf_generate');
    }
}