<?php

namespace Tests\Feature\Livewire\TableManager;

use App\Livewire\TableManager\TableSplitter;
use App\Models\LicenseTable;
use App\Models\User;
use App\Models\WorkAssignment;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class TableSplitterTest extends TestCase
{
    use RefreshDatabase;

    protected function createWorkArrayFromModel(WorkAssignment $work): array
    {
        return [
            'id'                => $work->id,
            'license_table_id'  => $work->license_table_id,
            'value'             => $work->value,
            'agency'            => optional($work->agency)->name,
            'agency_name'       => optional($work->agency)->name,
            'agency_code'       => optional($work->agency)->code,
            'amount'            => $work->amount,
            'voucher'           => $work->voucher,
            'excluded'          => (bool) $work->excluded,
            'slot'              => $work->slot,
            'slots_occupied'    => $work->slots_occupied,
            'shared_from_first' => (bool) $work->shared_from_first,
            'timestamp'         => $work->timestamp->format('Y-m-d H:i:s'),
        ];
    }

    #[Test]
    public function load_matrix_populates_matrix_and_unassigned()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license1 = LicenseTable::factory()->create([
            'user_id' => $user->id,
            'date'    => today(),
            'order'   => 10
        ]);

        $work = WorkAssignment::factory()->create([
            'license_table_id' => $license1->id,
            'value'            => 'X',
            'slots_occupied'   => 1,
            'excluded'         => false,
            'timestamp'        => today()->setTime(10, 30),
            'amount'           => 90.0,
        ]);

        $license2 = LicenseTable::factory()->create([
            'user_id' => $user->id,
            'date'    => today(),
            'order'   => 20
        ]);

        $component = Livewire::test(TableSplitter::class)
            ->call('generateTable');

        $matrix = $component->get('matrix');
        $this->assertCount(2, $matrix);

        $firstRow = $matrix[0];
        $this->assertIsArray($firstRow['worksMap']);

        // NON testare lo slot esatto! Il servizio lo mette nel primo libero
        $this->assertTrue(
            collect($firstRow['worksMap'])->filter()->contains('id', $work->id),
            'Il lavoro non è stato caricato nella worksMap della prima licenza'
        );

        $this->assertIsArray($component->get('unassignedWorks'));
    }

    #[Test]
    public function generate_table_dispatches_matrix_updated_event()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        LicenseTable::factory()->create(['date' => today(), 'user_id' => $user->id]);

        Livewire::test(TableSplitter::class)
            ->call('generateTable')
            ->assertDispatched('matrix-updated');
    }

    #[Test]
    public function confirmed_remove_moves_work_to_unassigned()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = LicenseTable::factory()->create([
            'user_id' => $user->id,
            'date'    => today(),
        ]);

        $work = WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'value'            => 'X',
            'slots_occupied'   => 1,
            'timestamp'        => today()->setTime(11, 0),
            'excluded'         => false,
        ]);

        $component = Livewire::test(TableSplitter::class)
            ->call('generateTable');

        $matrix = $component->get('matrix');
        $row = $matrix[0];
        $slotWithWork = collect($row['worksMap'])->keys()->first(fn($slot) => !is_null($row['worksMap'][$slot]));

        $this->assertNotNull($slotWithWork, 'Nessun lavoro trovato nella matrice');

        $payload = [
            'licenseKey' => 0,
            'slotIndex'  => $slotWithWork,
            'work'       => $this->createWorkArrayFromModel($work),
        ];

        $component->call('confirmedRemove', $payload)
            ->assertDispatched('matrix-updated')
            ->assertDispatched('notify-success');

        $matrixAfter = $component->get('matrix');
        $this->assertNull($matrixAfter[0]['worksMap'][$slotWithWork] ?? null);

        $unassigned = $component->get('unassignedWorks');
        $this->assertTrue(collect($unassigned)->pluck('id')->contains($work->id));
    }

    #[Test]
    public function select_and_assign_work_to_empty_slot()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = LicenseTable::factory()->create([
            'user_id' => $user->id,
            'date'    => today(),
        ]);

        $work = WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'value'            => 'X',
            'slots_occupied'   => 1,
            'timestamp'        => today()->setTime(12, 0),
        ]);

        $workArray = $this->createWorkArrayFromModel($work);

        $component = Livewire::test(TableSplitter::class)
            ->call('generateTable')
            ->set('unassignedWorks', [$workArray]);

        $component->call('selectUnassignedWork', 0)
            ->assertSet('selectedWork', $workArray)
            ->assertDispatched('work-selected');

        $emptySlot = 15;
        $component->call('assignToSlot', 0, $emptySlot)
            ->assertDispatched('matrix-updated')
            ->assertDispatched('work-deselected');

        $matrix = $component->get('matrix');
        $this->assertEquals($work->id, $matrix[0]['worksMap'][$emptySlot]['id'] ?? null);
        $this->assertEmpty($component->get('unassignedWorks'));
    }

    #[Test]
    public function assign_to_slot_shows_warning_when_no_work_selected()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        LicenseTable::factory()->create(['date' => today(), 'user_id' => $user->id]);

        Livewire::test(TableSplitter::class)
            ->call('assignToSlot', 0, 5)
            ->assertDispatched('notify');
    }

    #[Test]
    public function cannot_assign_to_occupied_slot()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = LicenseTable::factory()->create([
            'user_id' => $user->id,
            'date'    => today(),
        ]);

        $occupiedWork = WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'value'            => 'X',
            'slots_occupied'   => 1,
            'timestamp'        => today()->setTime(9, 0),
        ]);

        $freeWork = WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'value'            => 'X',
            'slots_occupied'   => 1,
            'timestamp'        => today()->setTime(14, 0),
        ]);

        $component = Livewire::test(TableSplitter::class)
            ->call('generateTable')
            ->set('unassignedWorks', [$this->createWorkArrayFromModel($freeWork)])
            ->call('selectUnassignedWork', 0);

        // Trova slot occupato
        $matrix = $component->get('matrix');
        $occupiedSlot = collect($matrix[0]['worksMap'])->keys()->first(fn($s) => !is_null($matrix[0]['worksMap'][$s]));

        $component->call('assignToSlot', 0, $occupiedSlot)
            ->assertDispatched('notify');
    }

    #[Test]
    public function print_agency_report_sets_session_and_redirects()
    {
        $user = User::factory()->create(['name' => 'Mario']);
        $this->actingAs($user);

        $license = LicenseTable::factory()->create([
            'user_id' => $user->id,
            'date'    => today(),
        ]);

        $agency = Agency::factory()->create(['name' => 'Test Agency']);

        WorkAssignment::factory()->count(2)->create([
            'license_table_id' => $license->id,
            'value'            => 'A',
            'agency_id'        => $agency->id,
            'voucher'          => 'ABC123',
            'timestamp'        => today()->setTime(10, 0),
            'excluded'         => false,
        ]);

        Livewire::test(TableSplitter::class)
            ->call('printAgencyReport')
            ->assertSessionHas('pdf_generate')
            ->assertRedirect(route('generate.pdf'));

        $this->assertEquals('pdf.agency-report', session('pdf_generate.view'));
    }

    #[Test]
    public function print_split_table_sets_session_and_redirects()
    {
        $user = User::factory()->create(['name' => 'Printer']);
        $this->actingAs($user);

        $license = LicenseTable::factory()->create([
            'user_id' => $user->id,
            'date'    => today(),
        ]);

        WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'value'            => 'X',
            'timestamp'        => today()->setTime(11, 0),
            'amount'           => 180.0,
        ]);

        Livewire::test(TableSplitter::class)
            ->call('printSplitTable')
            ->assertSessionHas('pdf_generate')
            ->assertRedirect(route('generate.pdf'));

        $session = session('pdf_generate');
        $this->assertArrayHasKey('view', $session);
        $this->assertArrayHasKey('filename', $session);
    }

}
