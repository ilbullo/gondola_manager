<?php

namespace Tests\Feature\Service;

use Tests\TestCase;
use App\Models\Agency;
use App\Models\LicenseTable;
use App\Models\WorkAssignment;
use App\Services\WorkAssignmentService;
use App\Enums\DayType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
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
        
        // Usiamo il driver array per i test (supporta i lock in memoria)
        config(['cache.default' => 'array']);
    }

    #[Test]
    public function it_persists_a_new_assignment_correctly(): void
    {
        $agency = Agency::factory()->create(['name' => 'Taxi Agency Venice']);
        $licenseTable = LicenseTable::factory()->create(['date' => today()]);

        $selectedWork = [
            'value' => 'A',
            'agencyName' => 'Taxi Agency Venice',
            'amount' => 120.0,
            'voucher' => 'V-TEST-001',
            'sharedFromFirst' => false
        ];

        $this->service->saveAssignment($licenseTable->id, 5, 1, $selectedWork);

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

        // Tentativo di inserimento che collide (inizia al 12)
        $this->expectException(\Exception::class);
        
        // Usiamo la stringa aggiornata o una sottostringa univoca
        $this->expectExceptionMessage('è già occupato o si sovrappone');

        $this->service->saveAssignment($licenseTable->id, 12, 1, $selectedWork);
    }

    #[Test]
    public function it_handles_license_turn_cycling_logic(): void
    {
        $licenseTable = LicenseTable::factory()->create(['turn' => DayType::FULL]);

        $this->service->cycleLicenseTurn($licenseTable->id);
        $this->assertEquals(DayType::MORNING, $licenseTable->refresh()->turn);

        $this->service->cycleLicenseTurn($licenseTable->id);
        $this->assertEquals(DayType::AFTERNOON, $licenseTable->refresh()->turn);
        
        $this->service->cycleLicenseTurn($licenseTable->id);
        $this->assertEquals(DayType::FULL, $licenseTable->refresh()->turn);
    }

    #[Test]
    public function it_successfully_deletes_a_work_assignment()
    {
        $license = LicenseTable::factory()->create(['date' => today()]);
        $work = WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'slot' => 1,
            'slots_occupied' => 1,
            'timestamp' => now(),
        ]);

        $result = $this->service->deleteAssignment($work->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('work_assignments', ['id' => $work->id]);
    }

    #[Test]
    public function it_prepares_clean_data_for_pdf_export(): void
    {
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
        
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'amount' => 50.50, 'slot' => 1, 'timestamp' => now()]);
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'amount' => 20.00, 'slot' => 2, 'timestamp' => now()]);
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'amount' => 30.00, 'slot' => 3, 'timestamp' => now()]);

        $total = $this->service->getLicenseTotal($license->id);
        
        $this->assertEquals(100.50, $total);
    }

    #[Test]
    public function it_prevents_race_conditions_using_atomic_locks()
    {
        $license = LicenseTable::factory()->create();
        $workData = ['value' => 'A', 'amount' => 100, 'agencyName' => 'Agency Test'];
        $lockKey = "save-assignment-license-{$license->id}-" . today()->format('Y-m-d');
        
        $lock = Cache::lock($lockKey, 10);
        $lock->get(); 

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Un altro utente sta aggiornando questa licenza');

        $this->service->saveAssignment($license->id, 5, 1, $workData);
    }

    #[Test]
    public function it_prevents_concurrent_turn_cycling()
    {
        $license = LicenseTable::factory()->create(['turn' => DayType::MORNING]);
        $lockKey = "cycle-turn-license-{$license->id}";
        
        $lock = Cache::lock($lockKey, 5);
        $lock->get();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Aggiornamento turno in corso...');

        $this->service->cycleLicenseTurn($license->id);
    }

    #[Test]
    public function it_prevents_deletion_if_assignment_is_locked()
    {
        $license = LicenseTable::factory()->create();
        $work = WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'slot' => 1,
            'slots_occupied' => 1,
        ]);
        
        $lockKey = "action-assignment-{$work->id}";
        $lock = Cache::lock($lockKey, 5);
        $lock->get();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Impossibile eliminare: operazione in corso su questo lavoro.');

        $this->service->deleteAssignment($work->id);
    }

    #[Test]
    public function it_allows_assignment_immediately_after_another(): void
    {
        $licenseTable = LicenseTable::factory()->create(['date' => today()]);

        // Lavoro che finisce allo slot 10
        WorkAssignment::factory()->create([
            'license_table_id' => $licenseTable->id,
            'slot'             => 9,
            'slots_occupied'   => 2, // Slot 9 e 10
            'timestamp'        => now(),
        ]);

        // Nuovo lavoro che inizia allo slot 11 (Libero)
        $this->service->saveAssignment($licenseTable->id, 11, 1, ['value' => 'X', 'amount' => 90]);

        $this->assertDatabaseCount('work_assignments', 2);
    }

    #[Test]
    public function it_allows_assignment_immediately_before_another(): void
    {
        $licenseTable = LicenseTable::factory()->create(['date' => today()]);

        // Lavoro che inizia allo slot 10
        WorkAssignment::factory()->create([
            'license_table_id' => $licenseTable->id,
            'slot'             => 10,
            'slots_occupied'   => 2, // Slot 10 e 11
            'timestamp'        => now(),
        ]);

        // Nuovo lavoro che finisce allo slot 9 (Libero)
        $this->service->saveAssignment($licenseTable->id, 8, 2, ['value' => 'X', 'amount' => 90]);

        $this->assertDatabaseCount('work_assignments', 2);
    }
}