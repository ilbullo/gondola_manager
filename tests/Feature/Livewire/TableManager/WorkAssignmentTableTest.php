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
use App\Livewire\Ui\PdfViewerModal;

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
    public function it_opens_the_modal_and_displays_the_correct_preview_content()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['name' => 'Mario Rossi']);
        $this->actingAs($admin);

        // 1. Setup dati: Creiamo una licenza con un numero specifico
        $license = LicenseTable::factory()
            ->for(User::factory(['license_number' => '123']), 'user')
            ->create(['date' => today()]);

        $capturedData = null;

        // 2. Eseguiamo il componente Tabella e intercettiamo i dati generati
        Livewire::test(WorkAssignmentTable::class)
            ->call('refreshTable', app(\App\Services\WorkAssignmentService::class))
            ->call('printTable')
            ->assertDispatched('open-print-modal', function($name, $params) use (&$capturedData) {
                $capturedData = $params['data'] ?? $params;
                return true; 
            });

        $this->assertNotNull($capturedData, "Dati PDF non catturati.");

        // 3. Testiamo il Modale usando i dati reali
        Livewire::test(PdfViewerModal::class)
            ->dispatch('open-print-modal', data: $capturedData)
            // Correzione: usiamo 'isOpen' invece di 'show'
            ->assertSet('isOpen', true) 
            ->assertSee('123')     
            ->assertSee('Mario Rossi')
            // 4. Testiamo anche la chiusura (Solid: testiamo il ciclo di vita completo)
            ->call('close')
            ->assertSet('isOpen', false)
            ->assertSet('printData', null);
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