<?php

namespace Tests\Feature\Livewire\TableManager;

use App\Livewire\TableManager\WorkAssignmentTable;
use App\Models\{Agency, LicenseTable, WorkAssignment, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class WorkAssignmentTableTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private LicenseTable $licenseTable;
    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['license_number' => '123']);
        $this->licenseTable = LicenseTable::factory()->create([
            'user_id' => $this->user->id,
            'date'    => today(),
            'order'   => 1
        ]);
        $this->agency = Agency::factory()->create(['name' => 'Test Agency', 'code' => 'TA']);
    }

    #[Test]

    public function it_mounts_and_refreshes_the_table()
    {
        Livewire::test(WorkAssignmentTable::class)
            ->assertSet('licenses', function ($licenses) {
                return count($licenses) > 0;
            });
    }

    // --- Assegnazione del Lavoro ---

    #[Test]

    public function it_prevents_assignment_without_selected_work()
    {
        Livewire::test(WorkAssignmentTable::class)
            ->call('assignWork', $this->licenseTable->id, 1)
            ->assertSet('errorMessage', 'Seleziona un lavoro valido dalla sidebar.');
    }

    #[Test]

    public function it_prevents_assignment_with_invalid_work_type()
    {
        Livewire::test(WorkAssignmentTable::class)
            ->set('selectedWork', ['value' => 'Z'])
            ->call('assignWork', $this->licenseTable->id, 1)
            ->assertSet('errorMessage', 'Tipo di lavoro non valido.');
    }

    #[Test]

    public function it_assigns_a_valid_work_A()
    {
        Livewire::test(WorkAssignmentTable::class)
            ->set('selectedWork', [
                'value' => 'A',
                'slotsOccupied' => 2,
                'agencyName' => 'Test Agency'
            ])
            ->call('assignWork', $this->licenseTable->id, 5)
            ->assertDispatched('workAssigned')
            ->assertSet('errorMessage', '');

        $this->assertDatabaseHas('work_assignments', [
            'license_table_id' => $this->licenseTable->id,
            'slot' => 5,
            'value' => 'A',
            'slots_occupied' => 2,
            'agency_id' => $this->agency->id,
            'timestamp' => today(),
        ]);
    }

    #[Test]

    public function it_assigns_a_valid_work_X()
    {
        Livewire::test(WorkAssignmentTable::class)
            ->set('selectedWork', ['value' => 'X', 'slotsOccupied' => 1])
            ->call('assignWork', $this->licenseTable->id, 1)
            ->assertDispatched('workAssigned');

        $this->assertDatabaseHas('work_assignments', [
            'license_table_id' => $this->licenseTable->id,
            'slot' => 1,
            'value' => 'X',
            'slots_occupied' => 1,
        ]);
    }

    #[Test]

    public function it_prevents_assignment_when_slot_is_occupied()
    {
        WorkAssignment::create([
            'license_table_id' => $this->licenseTable->id,
            'slot' => 2,
            'value' => 'X',
            'slots_occupied' => 3,
            'timestamp' => today(),
        ]);

        // Tentativo di assegnare a slot 4, che è occupato (slot 2, 3, 4)
        Livewire::test(WorkAssignmentTable::class)
            ->set('selectedWork', ['value' => 'P', 'slotsOccupied' => 1])
            ->call('assignWork', $this->licenseTable->id, 4)
            ->assertSet('errorMessage', 'Lo slot è già occupato o si sovrappone.');

        // Tentativo di assegnare con un overlap (slot 1, 2)
        Livewire::test(WorkAssignmentTable::class)
            ->set('selectedWork', ['value' => 'P', 'slotsOccupied' => 2])
            ->call('assignWork', $this->licenseTable->id, 1)
            ->assertSet('errorMessage', 'Lo slot è già occupato o si sovrappone.');
    }


    // --- Rimozione del Lavoro ---

    #[Test]

    public function it_opens_the_confirm_remove_modal()
    {
        Livewire::test(WorkAssignmentTable::class)
            ->call('openConfirmRemove', $this->licenseTable->id, 5)
            ->assertDispatched('openConfirmModal', [
                'message'      => 'Vuoi rimuovere il lavoro da questa cella?',
                'confirmEvent' => 'confirmRemoveAssignment',
                'payload'      => ['licenseTableId' => $this->licenseTable->id, 'slot' => 5],
            ]);
    }

    #[Test]

    public function it_removes_a_work_assignment_and_its_slots()
    {
        // Assegna un lavoro che occupa 3 slot (5, 6, 7)
        WorkAssignment::create([
            'license_table_id' => $this->licenseTable->id,
            'slot' => 5,
            'value' => 'X',
            'slots_occupied' => 3,
            'timestamp' => today(),
        ]);

        $this->assertDatabaseCount('work_assignments', 1);

        // Chiamo la rimozione passando lo slot iniziale (5)
        Livewire::test(WorkAssignmentTable::class)
            ->call('removeAssignment', ['licenseTableId' => $this->licenseTable->id, 'slot' => 5]);

        $this->assertDatabaseCount('work_assignments', 0);
        $this->assertDatabaseMissing('work_assignments', [
            'license_table_id' => $this->licenseTable->id,
            'slot' => 5,
            'timestamp' => today(),
        ]);
    }

    #[Test]

    public function it_handles_remove_assignment_with_missing_data()
    {
        Livewire::test(WorkAssignmentTable::class)
            ->call('removeAssignment', ['licenseTableId' => $this->licenseTable->id])
            ->assertSet('errorMessage', 'Dati mancanti per rimuovere l\'assegnazione.');
    }
}