<?php

namespace Tests\Feature\Livewire\TableManager;

use Tests\TestCase;
use App\Models\User;
use App\Models\Agency;
use App\Models\LicenseTable;
use App\Models\WorkAssignment;
use App\Livewire\TableManager\WorkAssignmentTable;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class WorkAssignmentTableTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_selected_work_when_notified_by_sidebar()
    {
        $workData = ['value' => 'A', 'agencyName' => 'Test Agency', 'slotsOccupied' => 2];

        Livewire::test(WorkAssignmentTable::class)
            ->dispatch('workSelected', work: $workData)
            ->assertSet('selectedWork', $workData)
            ->assertSet('errorMessage', '');
    }

    #[Test]
    public function it_fails_assignment_if_no_work_is_selected()
    {
        $license = LicenseTable::factory()->create();

        Livewire::test(WorkAssignmentTable::class)
            ->set('selectedWork', null)
            ->call('assignWork', $license->id, 1)
            ->assertDispatched('notify', function($event, $params) {
                // In Livewire v3 con dispatch nominale, i parametri sono spesso
                // accessibili direttamente o tramite il primo elemento dell'array
                $message = $params['message'] ?? $params[0]['message'] ?? '';
                return str_contains($message, 'Seleziona un lavoro');
            });
    }

    #[Test]
    public function it_successfully_assigns_work_and_refreshes_table()
    {
        $license = LicenseTable::factory()->create();
        $agency = Agency::factory()->create();

        $workData = [
            'value' => 'A',
            'agency_id' => $agency->id,
            'slotsOccupied' => 1
        ];

        Livewire::test(WorkAssignmentTable::class)
            ->set('selectedWork', $workData)
            ->call('assignWork', $license->id, 5) // Slot 5
            ->assertHasNoErrors()
            ->assertDispatched('notify-success')
            ->assertDispatched('workAssigned');

        $this->assertDatabaseHas('work_assignments', [
            'license_table_id' => $license->id,
            'slot' => 5,
            'value' => 'A'
        ]);
    }

    #[Test]
    public function it_handles_service_exceptions_gracefully()
    {
        $license = LicenseTable::factory()->create();

        // 1. Occupiamo gli slot 1 e 2 con un lavoro esistente
        WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'slot' => 1,
            'slots_occupied' => 2
        ]);

        // 2. Tentiamo di sovrapporre un nuovo lavoro nello slot 2
        $workData = ['value' => 'N', 'slotsOccupied' => 1];

        Livewire::test(WorkAssignmentTable::class)
            ->set('selectedWork', $workData)
            ->call('assignWork', $license->id, 2) // Slot 2 è già occupato!
            ->assertDispatched('notify', function($event, $params) {
                // Supporto sia per parametri nominali che array wrappato
                $type = $params['type'] ?? $params[0]['type'] ?? '';
                return $type === 'error';
            });
    }

    #[Test]
    public function it_can_cycle_license_turns()
    {
        $license = LicenseTable::factory()->create(['turn' => 'full']);

        Livewire::test(WorkAssignmentTable::class)
            ->call('cycleTurn', $license->id)
            ->assertHasNoErrors();

        $this->assertEquals('morning', $license->refresh()->turn->value);
    }

    #[Test]
    public function it_can_remove_an_assignment()
    {
        $license = LicenseTable::factory()->create();
        $assignment = WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'slot' => 1
        ]);

        Livewire::test(WorkAssignmentTable::class)
            ->dispatch('confirmRemoveAssignment', payload: ['licenseTableId' => $assignment->id])
            ->assertDispatched('notify-success');

        $this->assertDatabaseMissing('work_assignments', ['id' => $assignment->id]);
    }

    #[Test]
   public function it_handles_error_when_removing_non_existent_assignment()
{
    Livewire::test(WorkAssignmentTable::class)
        ->dispatch('confirmRemoveAssignment', payload: ['licenseTableId' => 99999])
        ->assertDispatched('notify', function($event, $params) {
            $message = $params['message'] ?? $params[0]['message'] ?? '';
            $type = $params['type'] ?? $params[0]['type'] ?? '';

            // Verifica che sia un errore e che contenga un riferimento al fallimento
            return $type === 'error' && (
                str_contains($message, '99999') ||
                str_contains($message, 'mancante') ||
                str_contains($message, 'No query results')
            );
        });
}

    #[Test]
    public function it_can_toggle_only_cash_status_for_a_license()
    {
        $license = LicenseTable::factory()->create(['only_cash_works' => false]);

        Livewire::test(WorkAssignmentTable::class)
            ->call('toggleOnlyCash', $license->id)
            ->assertHasNoErrors();

        $this->assertTrue($license->refresh()->only_cash_works);

        // Toggle di ritorno
        Livewire::test(WorkAssignmentTable::class)
            ->call('toggleOnlyCash', $license->id)
            ->assertHasNoErrors();

        $this->assertFalse($license->refresh()->only_cash_works);
    }

    #[Test]
    public function it_dispatches_print_event_for_browser_printing()
    {
        // 1. Setup Utente Autenticato
        $admin = User::factory()->create();
        $this->actingAs($admin);

        // 2. Test del componente
        Livewire::actingAs($admin)
            ->test(WorkAssignmentTable::class)
            // Assicuriamoci che ci siano dati nel componente se il metodo li usa
            ->set('licenses', [])
            ->call('printTable')
            // Asserzione sull'evento browser per l'iframe
            ->assertDispatched('trigger-print')
            // Asserzione sulla sessione (fondamentale per la rotta /print-report)
            ->assertSessionHas('pdf_generate');

        // Verifica finale: controlliamo che la sessione contenga effettivamente i dati della matrice
        $sessionData = session('pdf_generate');
        $this->assertArrayHasKey('view', $sessionData);
        $this->assertArrayHasKey('data', $sessionData);
    }

    #[Test]
    public function it_redirects_to_generator_for_pdf_download(): void
    {
        $admin = User::factory()->create(['name' => 'Admin']);
        /**@var User $admin */
        $this->actingAs($admin);
        LicenseTable::factory()->count(2)->create(['date' => today()]);

        Livewire::test(WorkAssignmentTable::class)
            ->call('downloadTable') // Metodo che prepara il file PDF
            ->assertRedirect(route('generate.pdf'));

        // Verifica che i dati siano stati "parcheggiati" correttamente in sessione
        $this->assertTrue(session()->has('pdf_generate'));
        $config = session()->get('pdf_generate');
        $this->assertEquals('pdf.work-assignment-table', $config['view']);
    }

    #[Test]
    public function it_resets_error_message_when_new_work_is_selected()
    {
        $component = Livewire::test(WorkAssignmentTable::class);

        // Simuliamo uno stato di errore precedente
        $component->set('errorMessage', 'Errore precedente');

        // Selezioniamo un nuovo lavoro
        $component->dispatch('workSelected', work: ['value' => 'N']);

        // L'errore deve sparire
        $this->assertEquals('', $component->get('errorMessage'));
    }
}
