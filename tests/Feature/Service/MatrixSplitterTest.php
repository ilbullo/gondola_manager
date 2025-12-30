<?php

namespace Tests\Feature\Service;

use Tests\TestCase;
use App\Services\MatrixSplitterService;
use App\Contracts\WorkQueryInterface;
use App\Contracts\MatrixEngineInterface;
use Illuminate\Support\Collection;
use Mockery;
use App\Models\LicenseTable;
use App\Models\WorkAssignment;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;


class MatrixSplitterTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Contracts\WorkQueryInterface&\Mockery\MockInterface */
    private MockInterface $queryServiceMock;
    /** @var \App\Contracts\MatrixEngineInterface&\Mockery\MockInterface */
    private MockInterface $engineServiceMock;
    private MatrixSplitterService $splitter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queryServiceMock = Mockery::mock(WorkQueryInterface::class);
        $this->engineServiceMock = Mockery::mock(MatrixEngineInterface::class);

        // CONFIGURAZIONE DI BASE PER EVITARE NULL POINTERS
        // Ogni volta che il service chiede i lavori, diamo una collezione (anche vuota)
        $this->queryServiceMock->shouldReceive('allWorks')->andReturn(collect())->byDefault();
        $this->queryServiceMock->shouldReceive('unsharableWorks')->andReturn(collect())->byDefault();
        $this->queryServiceMock->shouldReceive('sharableFirstAgencyWorks')->andReturn(collect())->byDefault();
        $this->queryServiceMock->shouldReceive('pendingMorningAgencyWorks')->andReturn(collect())->byDefault();
        $this->queryServiceMock->shouldReceive('pendingAfternoonAgencyWorks')->andReturn(collect())->byDefault();
        $this->queryServiceMock->shouldReceive('sharableFirstCashWorks')->andReturn(collect())->byDefault();
        $this->queryServiceMock->shouldReceive('pendingNWorks')->andReturn(collect())->byDefault();
        $this->queryServiceMock->shouldReceive('pendingCashWorks')->andReturn(collect())->byDefault();
        $this->queryServiceMock->shouldReceive('pendingPWorks')->andReturn(collect())->byDefault();
        
        // L'engine non deve fare nulla di default
        $this->engineServiceMock->shouldReceive('distributeFixed')->byDefault();
        $this->engineServiceMock->shouldReceive('distribute')->byDefault();

        $this->splitter = new MatrixSplitterService(
            $this->queryServiceMock,
            $this->engineServiceMock
        );
    }

    #[Test]
    public function it_orchestrates_the_splitting_workflow_in_correct_order()
    {
        $inputData = collect([['id' => 1]]);

        $workA = ['value' => 'A', 'license_table_id' => 1];
        $workX = ['value' => 'X', 'license_table_id' => 1];
        $workN = ['value' => 'N', 'license_table_id' => 1];

        $this->queryServiceMock->shouldReceive('prepareMatrix')
            ->once()
            ->andReturn(collect([
                ['id' => 1, 'worksMap' => array_fill(1, 25, null)]
            ]));

        $this->queryServiceMock->shouldReceive('unsharableWorks')
            ->andReturn(collect([$workA, $workX]));
            
        $this->queryServiceMock->shouldReceive('pendingNWorks')
            ->andReturn(collect([$workN]));

        // Aspettative di chiamata
        $this->engineServiceMock->shouldReceive('distributeFixed')->times(3);
        $this->engineServiceMock->shouldReceive('distribute')->atLeast()->once();

        // Esecuzione
        $result = $this->splitter->execute($inputData);

        // ASSERZIONE ESPLICITA: Verifica che il risultato sia una collezione e contenga la licenza
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(1, $result->first()['id']);
    }

    #[Test]
    public function it_compacts_the_matrix_removing_gaps()
    {
        // 1. Mockiamo prepareMatrix per restituire una riga con buchi
        $this->queryServiceMock->shouldReceive('prepareMatrix')->andReturn(collect([
            [
                'id' => 1, 
                'worksMap' => [
                    1 => ['value' => 'X'], 
                    2 => null, 
                    3 => ['value' => 'A'],
                    4 => null,
                    5 => ['value' => 'N']
                ]
            ]
        ]));

        // 2. Esecuzione
        $result = $this->splitter->execute(collect([['id' => 1]]));

        // Verifichiamo che la collezione non sia vuota
        $this->assertNotEmpty($result);
        
        $compactedMap = $result->first()['worksMap'];
        
        // Verifica slittamento
        $this->assertEquals('X', $compactedMap[1]['value']);
        $this->assertEquals('A', $compactedMap[2]['value']);
        $this->assertEquals('N', $compactedMap[3]['value']);
        $this->assertNull($compactedMap[4]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_guarantees_total_mass_conservation_during_splitting()
    {
        // 1. SETUP
        $date = today();
        $licenses = collect();
        
        // Creiamo 10 licenze con ordini da 1 a 10
        for ($i = 1; $i <= 10; $i++) {
            $licenses->push(LicenseTable::factory()->create([
                'date' => $date,
                'order' => $i,
                'turn' => 'full'
            ]));
        }
        
        $works = collect();
        $types = ['A', 'X', 'N', 'P'];
        
        // Contatori per gli slot di ogni licenza per evitare sovrapposizioni
        $slotCounters = [];
        foreach ($licenses as $lic) {
            $slotCounters[$lic->id] = 1;
        }

        // 2. CREAZIONE LAVORI (10 per tipo = 40 totali)
        foreach ($types as $type) {
            for ($j = 0; $j < 10; $j++) {
                $targetLicense = $licenses[$j % 10];
                $currentSlot = $slotCounters[$targetLicense->id];
                $slotCounters[$targetLicense->id]++;

                // Creiamo il lavoro associandolo alla licenza
                $works->push(WorkAssignment::factory()->create([
                    'license_table_id' => $targetLicense->id, 
                    'value' => $type,
                    'timestamp' => $date->copy()->setHour(rand(8, 18)),
                    'amount' => 50.00,
                    'slot' => $currentSlot,
                    'slots_occupied' => 1,
                    'excluded' => false
                ]));
            }
        }

        $totalInitialCount = $works->count();
        $totalInitialAmount = (float) $works->sum('amount');

        // 3. ESECUZIONE SPLITTER
        $splitter = app(MatrixSplitterService::class);
        $matrixResult = $splitter->execute($licenses);
        
        // 4. VERIFICA QUANTITÀ (Record totali)
        $worksInMatrix = $matrixResult->pluck('worksMap')
            ->flatMap(fn($map) => collect($map)->filter())
            ->count();
        
        $worksInUnassigned = $splitter->unassignedWorks->count();
        
        $this->assertEquals(
            $totalInitialCount, 
            $worksInMatrix + $worksInUnassigned, 
            "Massa record persa! Iniziali: $totalInitialCount, Finali: " . ($worksInMatrix + $worksInUnassigned)
        );

        // 5. VERIFICA ECONOMICA (Somma euro)
        $amountInMatrix = (float) $matrixResult->pluck('worksMap')
            ->flatMap(fn($map) => collect($map)->filter())
            ->sum('amount');
            
        $amountInUnassigned = (float) $splitter->unassignedWorks->sum('amount');

        $this->assertEquals(
            $totalInitialAmount, 
            $amountInMatrix + $amountInUnassigned, 
            "Massa economica persa! Attesa: {$totalInitialAmount}, Ottenuta: " . ($amountInMatrix + $amountInUnassigned)
        );
    }
}