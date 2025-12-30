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

    #[Test]
    public function it_prevents_race_conditions_using_atomic_locks()
    {
        // 1. Forza l'uso di un driver cache che supporta i lock (array va bene per i test singoli)
        config(['cache.default' => 'array']);
        
        $license = LicenseTable::factory()->create();
        $service = app(WorkAssignmentService::class);
        $workData = [
            'value' => 'A',
            'amount' => 100,
            'agencyName' => 'Agency Test'
        ];

        // 2. Genera la chiave ESATTAMENTE come fa il Service
        // Nota: Il Service usa "save-assignment-license-..."
        $lockKey = "save-assignment-license-{$license->id}-" . today()->format('Y-m-d');
        
        // 3. Acquisisci il lock PRIMA di chiamare il service
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 10);
        $lock->get(); 

        // 4. Verifica che il Service lanci l'eccezione
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Un altro utente sta aggiornando questa licenza. Riprova tra pochi istanti.');

        // 5. Questa chiamata deve fallire perché il lock è occupato
        $service->saveAssignment($license->id, 5, 1, $workData);
    }

    #[Test]
    public function it_prevents_concurrent_turn_cycling()
    {
        config(['cache.default' => 'array']);
        $license = LicenseTable::factory()->create(['turn' => \App\Enums\DayType::MORNING]);
        $service = app(WorkAssignmentService::class);

        // Chiave di lock specifica per il cambio turno di QUELLA licenza
        $lockKey = "cycle-turn-license-{$license->id}";
        
        // Simulo che un processo stia già cambiando il turno
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);
        $lock->get();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Aggiornamento turno in corso...');

        // Deve fallire perché il lock è occupato
        $service->cycleLicenseTurn($license->id);
    }

    #[Test]
    public function it_prevents_deletion_if_assignment_is_locked()
    {
        config(['cache.default' => 'array']);
        
        // 1. Creiamo prima la licenza, poi il lavoro associato
        $license = LicenseTable::factory()->create();
        $work = WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'slot' => 1,            // Inizia all'inizio
            'slots_occupied' => 1,  // Dura solo uno slot
        ]);
        
        $service = app(WorkAssignmentService::class);

        // 2. Usiamo la chiave di lock coerente con l'ID del lavoro
        $lockKey = "action-assignment-{$work->id}";
        
        // 3. Simuliamo un lock attivo
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 5);
        $lock->get();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Impossibile eliminare: operazione in corso su questo lavoro.');

        // 4. Deve fallire
        $service->deleteAssignment($work->id);
    }
}