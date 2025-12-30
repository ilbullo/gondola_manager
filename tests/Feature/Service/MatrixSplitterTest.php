<?php

namespace Tests\Feature\Service;

use Tests\TestCase;
use App\Services\MatrixSplitterService;
use App\Contracts\WorkQueryInterface;
use App\Contracts\MatrixEngineInterface;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;


class MatrixSplitterTest extends TestCase
{
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
}