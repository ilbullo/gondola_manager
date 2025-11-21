<?php

namespace Tests\Feature\Livewire\TableManager;

use App\Livewire\TableManager\WorkAssignmentTable;
use App\Models\Agency;
use App\Models\LicenseTable;
use App\Models\User;
use App\Models\WorkAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkAssignmentTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_mounts_correctly()
    {
        $user = User::factory()->create();
        LicenseTable::factory()->create(['user_id' => $user->id, 'date' => today()]);

        Livewire::test(WorkAssignmentTable::class)
            ->assertStatus(200)
            ->assertSee($user->license_number);
    }

    public function test_can_assign_single_slot_work()
    {
        $licenseTable = LicenseTable::factory()->create(['date' => today()]);
        
        Livewire::test(WorkAssignmentTable::class)
            ->set('selectedWork', ['value' => 'X', 'amount' => 90, 'slotsOccupied' => 1])
            ->call('assignWork', $licenseTable->id, 1)
            ->assertDispatched('workAssigned')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('work_assignments', [
            'license_table_id' => $licenseTable->id,
            'slot' => 1,
            'value' => 'X',
            'slots_occupied' => 1
        ]);
    }

    public function test_can_assign_multi_slot_work_and_prevents_overlap()
    {
        $licenseTable = LicenseTable::factory()->create(['date' => today()]);

        $component = Livewire::test(WorkAssignmentTable::class)
            ->set('selectedWork', ['value' => 'A', 'agencyName' => 'TestAgency', 'slotsOccupied' => 3]);

        // 1. Assegna lavoro da 3 slot nello slot 1 (occupa 1, 2, 3)
        $component->call('assignWork', $licenseTable->id, 1)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('work_assignments', [
            'license_table_id' => $licenseTable->id,
            'slot' => 1,
            'slots_occupied' => 3
        ]);

        // 2. Tenta di assegnare nello slot 2 (dovrebbe fallire per sovrapposizione)
        $component->set('selectedWork', ['value' => 'X', 'slotsOccupied' => 1])
            ->call('assignWork', $licenseTable->id, 2)
            ->assertSee('Lo slot è già occupato o si sovrappone'); // Controllo messaggio errore

        // 3. Assegna nello slot 4 (libero)
        $component->call('assignWork', $licenseTable->id, 4)
            ->assertHasNoErrors();
    }

    public function test_can_remove_assignment()
    {
        $licenseTable = LicenseTable::factory()->create(['date' => today()]);
        
        // Creiamo un assignment
        WorkAssignment::create([
            'license_table_id' => $licenseTable->id,
            'slot' => 5,
            'value' => 'X',
            'slots_occupied' => 1,
            'timestamp' => now()
        ]);

        Livewire::test(WorkAssignmentTable::class)
            ->dispatch('confirmRemoveAssignment', ['licenseTableId' => $licenseTable->id, 'slot' => 5]);

        $this->assertDatabaseMissing('work_assignments', [
            'license_table_id' => $licenseTable->id,
            'slot' => 5
        ]);
    }

    public function test_removing_multi_slot_assignment_clears_block()
    {
        $licenseTable = LicenseTable::factory()->create(['date' => today()]);
        
        // Assignment di 3 slot partendo dallo slot 2
        WorkAssignment::create([
            'license_table_id' => $licenseTable->id,
            'slot' => 2,
            'value' => 'A',
            'slots_occupied' => 3, // Occupa 2, 3, 4
            'timestamp' => now()
        ]);

        // Simuliamo la rimozione cliccando sullo slot 2
        Livewire::test(WorkAssignmentTable::class)
            ->dispatch('confirmRemoveAssignment', ['licenseTableId' => $licenseTable->id, 'slot' => 2]);

        // Verifica che sia stato cancellato
        $this->assertDatabaseMissing('work_assignments', [
            'license_table_id' => $licenseTable->id,
            'slot' => 2
        ]);
        
        // Verifica che non ci siano rimasugli (anche se il DB delete è a cascata o per ID, 
        // la logica del componente usa whereBetween per sicurezza logica, testiamo che non ci siano errori)
        $this->assertDatabaseCount('work_assignments', 0);
    }
}