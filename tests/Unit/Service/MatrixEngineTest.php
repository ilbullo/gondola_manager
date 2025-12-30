<?php

namespace Tests\Unit\Service;

use Tests\TestCase;
use App\Services\MatrixEngineService;
use PHPUnit\Framework\Attributes\Test;


class MatrixEngineTest extends TestCase
{
    private MatrixEngineService $engine;

    protected function setUp(): void
    {
        parent::setUp();
        // Assicuriamoci che la config sia presente per il costruttore
        config(['app_settings.matrix.total_slots' => 25]);
        $this->engine = new MatrixEngineService();
    }

    #[Test]
    public function it_finds_consecutive_free_slots_correctly()
    {
        $worksMap = array_fill(1, 25, null);
        $worksMap[1] = ['value' => 'X']; // Slot 1 occupato
        $worksMap[3] = ['value' => 'X']; // Slot 3 occupato

        // Cerchiamo 2 slot liberi. Dovrebbe saltare lo slot 1 e lo slot 3, trovando il 4.
        $startSlot = $this->engine->findConsecutiveFreeSlots($worksMap, 2);
        
        $this->assertEquals(4, $startSlot);

        // Se cerchiamo un blocco che non esiste (es. 30 slot)
        $this->assertFalse($this->engine->findConsecutiveFreeSlots($worksMap, 30));
    }

    #[Test]
    public function it_calculates_capacity_left_considering_p_works()
    {
        $license = [
            'id' => 1,
            'target_capacity' => 4,
            'worksMap' => [
                1 => ['value' => 'X'], // 1 slot occupato "commercialmente"
            ]
        ];

        // Simuliamo che esista un lavoro 'P' per questa licenza
        // Il lavoro 'P' riduce la capacità target (4 - 1 = 3)
        $allWorks = collect([
            ['license_table_id' => 1, 'value' => 'P']
        ]);

        $capacityLeft = $this->engine->getCapacityLeft($license, $allWorks);

        // Calcolo: (Target 4 - 1 lavoro P) - 1 slot già occupato in worksMap = 2
        $this->assertEquals(2, $capacityLeft);
    }

    #[Test]
    public function it_distributes_fixed_works_to_assigned_licenses()
    {
        $matrix = collect([
            ['id' => 10, 'target_capacity' => 5, 'worksMap' => array_fill(1, 25, null)]
        ]);
        
        $worksToAssign = collect([
            ['license_table_id' => 10, 'value' => 'A', 'slots_occupied' => 1]
        ]);

        $unassigned = collect();
        $allWorks = collect();

        $this->engine->distributeFixed($worksToAssign, $matrix, $unassigned, $allWorks);

        // Verifica che il lavoro sia nella worksMap della licenza 10
        $this->assertNotNull($matrix->first()['worksMap'][1]);
        $this->assertEquals('A', $matrix->first()['worksMap'][1]['value']);
        $this->assertTrue($unassigned->isEmpty());
    }

    #[Test]
    public function it_moves_to_unassigned_if_capacity_is_full()
    {
        $matrix = collect([
            ['id' => 1, 'target_capacity' => 1, 'worksMap' => [1 => ['value' => 'X']]]
        ]);
        
        $worksToAssign = collect([
            ['license_table_id' => 1, 'value' => 'A', 'slots_occupied' => 1]
        ]);

        $unassigned = collect();
        $allWorks = collect();

        $this->engine->distributeFixed($worksToAssign, $matrix, $unassigned, $allWorks);

        // La licenza era già piena (target 1, occupati 1). Il lavoro deve finire in unassigned.
        $this->assertCount(1, $unassigned);
        $this->assertEquals('A', $unassigned->first()['value']);
    }

    #[Test]
    public function it_sorts_matrix_rows_by_priority()
    {
        $matrix = collect([
            [
                'id' => 1,
                'worksMap' => [
                    1 => ['value' => 'N'], // Priorità bassa (600)
                    2 => ['value' => 'A', 'excluded' => true, 'shared_from_first' => false], // Priorità alta (100)
                ]
            ]
        ]);

        $this->engine->sortMatrixRows($matrix);

        // Dopo il sorting, il lavoro 'A' (priorità 100) deve essere allo slot 1
        $this->assertEquals('A', $matrix->first()['worksMap'][1]['value']);
        $this->assertEquals('N', $matrix->first()['worksMap'][2]['value']);
    }
}