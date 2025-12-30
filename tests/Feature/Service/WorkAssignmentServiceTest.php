<?php

namespace Tests\Feature\Service;

use Tests\TestCase;
use App\Models\User;
use App\Models\Agency;
use App\Models\LicenseTable;
use App\Models\WorkAssignment;
use App\Services\WorkAssignmentService;
use App\Enums\DayType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;

class WorkAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WorkAssignmentService();
        
        // Mock della configurazione per avere un valore prevedibile nei test
        Config::set('app_settings.works.default_amount', 90.0);
    }

    #[Test]
    public function it_persists_a_new_assignment_correctly(): void
    {
        // Setup: usiamo le factory per creare il contesto
        $agency = Agency::factory()->create(['name' => 'Taxi Agency Venice']);
        $licenseTable = LicenseTable::factory()->create(['date' => today()]);

        $selectedWork = [
            'value' => 'A',
            'agencyName' => 'Taxi Agency Venice',
            'amount' => 120.0,
            'voucher' => 'V-TEST-001',
            'sharedFromFirst' => false
        ];

        // Esecuzione
        $this->service->saveAssignment($licenseTable->id, 5, 1, $selectedWork);

        // Verifica
        $this->assertDatabaseHas('work_assignments', [
            'license_table_id' => $licenseTable->id,
            'agency_id'        => $agency->id,
            'slot'             => 5,
            'amount'           => 120.0,
            'voucher'          => 'V-TEST-001'
        ]);
    }

    #[Test]
    public function it_blocks_overlapping_assignments_in_the_same_slot_range(): void
    {
        $licenseTable = LicenseTable::factory()->create(['date' => today()]);

        // Creiamo un lavoro esistente che occupa gli slot 10, 11, 12 (3 slot)
        WorkAssignment::factory()->create([
            'license_table_id' => $licenseTable->id,
            'slot'             => 10,
            'slots_occupied'   => 3,
            'timestamp'        => now(),
        ]);

        $selectedWork = ['value' => 'X', 'amount' => 90];

        // Tentativo di inserimento che collide (inizia al 12, dove il precedente non è ancora finito)
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Lo slot è già occupato');

        $this->service->saveAssignment($licenseTable->id, 12, 1, $selectedWork);
    }

    #[Test]
    public function it_handles_license_turn_cycling_logic(): void
    {
        // Creiamo con l'Enum direttamente (Laravel gestirà il salvataggio del valore)
        $licenseTable = LicenseTable::factory()->create(['turn' => DayType::FULL]);

        // Ciclo 1: FULL -> MORNING
        $this->service->cycleLicenseTurn($licenseTable->id);
        // Confrontiamo l'oggetto restituito dal refresh() con l'Enum previsto
        $this->assertEquals(DayType::MORNING, $licenseTable->refresh()->turn);

        // Ciclo 2: MORNING -> AFTERNOON
        $this->service->cycleLicenseTurn($licenseTable->id);
        $this->assertEquals(DayType::AFTERNOON, $licenseTable->refresh()->turn);
        
        // Ciclo 3: AFTERNOON -> FULL
        $this->service->cycleLicenseTurn($licenseTable->id);
        $this->assertEquals(DayType::FULL, $licenseTable->refresh()->turn);
    }

    #[Test]
    public function it_successfully_deletes_a_work_assignment()
    {
        // 1. Setup con data odierna (coerente con i filtri del Service)
        $today = now()->format('Y-m-d');
        $license = LicenseTable::factory()->create(['date' => $today]);

        // 2. Creiamo il lavoro in uno slot "sicuro"
        $work = WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'slot' => 1,
            'slots_occupied' => 1,
            'timestamp' => now(), // Necessario perché il service filtra per whereDate('timestamp', today())
        ]);

        $service = app(WorkAssignmentService::class);

        // 3. Chiamiamo il metodo corretto: deleteAssignment()
        $result = $service->deleteAssignment($work->id);

        // 4. Verifiche
        $this->assertTrue($result);
        $this->assertDatabaseMissing('work_assignments', ['id' => $work->id]);
    }

    #[Test]
    public function it_prepares_clean_data_for_pdf_export(): void
    {
        // Simuliamo la struttura che arriva dalla LicenseResource
        $mockLicenses = [
            [
                'user' => ['name' => 'Mario Rossi', 'license_number' => '42'],
                'worksMap' => array_fill(1, 25, null)
            ]
        ];

        $pdfData = $this->service->preparePdfData($mockLicenses);

        $this->assertIsArray($pdfData);
        $this->assertEquals('Mario Rossi', $pdfData[0]['name']);
        $this->assertCount(25, $pdfData[0]['worksMap']);
    }

    #[Test]
    public function it_calculates_the_total_amount_for_a_license_on_a_given_day()
    {
        $license = LicenseTable::factory()->create();
        
        // Creiamo tre assegnazioni con importi diversi
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'amount' => 50.50, 'slot' => 1]);
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'amount' => 20.00, 'slot' => 2]);
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'amount' => 30.00, 'slot' => 3]);

        $total = $this->service->getLicenseTotal($license->id);
        
        $this->assertEquals(100.50, $total);
    }
}