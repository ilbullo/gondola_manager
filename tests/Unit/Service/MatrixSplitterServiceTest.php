<?php

namespace Tests\Unit\Services;

use App\Enums\DayType;
use App\Services\MatrixSplitterService;
use Illuminate\Support\Collection;
use Tests\TestCase;
use ReflectionClass;
use PHPUnit\Framework\Attributes\Test;


class MatrixSplitterServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'constants.matrix.total_slots'      => 8,
            'constants.matrix.morning_end'      => '12:00',
            'constants.matrix.afternoon_start'  => '14:00',
        ]);
    }

    /**
     * Helper per creare una licenseTable coerente
     */
    private function licenseTable(array $worksMaps): array
    {
        $total = config('constants.matrix.total_slots');

        return collect($worksMaps)->map(fn ($map, $i) => [
            'id'                => $i + 1,
            'license_table_id'  => $i + 1,
            'user'              => null,
            'turn'              => DayType::FULL->value,
            'only_cash_works'   => false,
            'wallet'            => 0,
            'slots_occupied'    => collect($map)->filter()->count(),
            'real_slots_today'  => $total,
            'target_capacity'   => $total,
            'worksMap'          => $map,
        ])->toArray();
    }

    #[Test]
public function it_preserves_fixed_agency_and_cash_works()
{
    $licenseTable = $this->licenseTable([
        [
            ['id'=>1,'value'=>'A','excluded'=>true,'license_table_id'=>1],
            null,null,null,null,null,null,null
        ],
        [
            ['id'=>2,'value'=>'X','excluded'=>true,'license_table_id'=>2],
            null,null,null,null,null,null,null
        ],
    ]);

    $service = new MatrixSplitterService($licenseTable);

    $this->assertCount(0, $service->unassignedWorks);

    $row0Values = collect($service->matrix[0]['worksMap'])
        ->filter()
        ->pluck('value')
        ->values();

    $row1Values = collect($service->matrix[1]['worksMap'])
        ->filter()
        ->pluck('value')
        ->values();

    $this->assertContains('A', $row0Values);
    $this->assertContains('X', $row1Values);
}

    #[Test]
    public function it_distributes_round_robin_cash_works()
    {
        config(['constants.matrix.total_slots' => 4]);

        $licenseTable = $this->licenseTable([
            array_fill(0, 4, null),
            array_fill(0, 4, null),
        ]);

        $service = new class($licenseTable) extends MatrixSplitterService {
            public function pendingCashWorks(): Collection
            {
                return collect([
                    ['id'=>1,'value'=>'X','timestamp'=>'2025-01-01 08:00:00'],
                    ['id'=>2,'value'=>'X','timestamp'=>'2025-01-01 09:00:00'],
                    ['id'=>3,'value'=>'X','timestamp'=>'2025-01-01 10:00:00'],
                    ['id'=>4,'value'=>'X','timestamp'=>'2025-01-01 11:00:00'],
                    ['id'=>5,'value'=>'X','timestamp'=>'2025-01-01 12:00:00'],
                    ['id'=>6,'value'=>'X','timestamp'=>'2025-01-01 13:00:00'],
                ]);
            }
        };

        $totalAssigned = $service->matrix
            ->sum(fn ($row) => collect($row['worksMap'])->filter()->count());

        $this->assertEquals(6, $totalAssigned);
        $this->assertCount(0, $service->unassignedWorks);
    }

    #[Test]
    public function it_respects_morning_turn_constraint()
    {
        config(['constants.matrix.total_slots' => 4]);

        $licenseTable = $this->licenseTable([
            array_fill(0, 4, null),
        ]);

        // forza turno mattina
        $licenseTable[0]['turn'] = DayType::MORNING->value;

        $service = new class($licenseTable) extends MatrixSplitterService {
            public function pendingCashWorks(): Collection
            {
                return collect([
                    ['id'=>1,'value'=>'X','timestamp'=>'2025-01-01 09:00:00'],
                    ['id'=>2,'value'=>'X','timestamp'=>'2025-01-01 15:00:00'],
                ]);
            }
        };

        $assigned = collect($service->matrix[0]['worksMap'])->filter()->values();

        $this->assertCount(1, $assigned);
        $this->assertEquals('09:00', substr($assigned[0]['timestamp'], 11, 5));
        $this->assertCount(1, $service->unassignedWorks);
    }

    #[Test]
    public function it_sorts_matrix_rows_correctly()
    {
        $service = new MatrixSplitterService([]);

        // Iniettiamo manualmente la matrice
        $service->matrix = collect([
            [
                'worksMap' => [
                    ['value' => 'R', 'timestamp' => '10:00'],
                    'N',
                    ['value' => 'X', 'excluded' => false],
                    ['value' => 'A', 'excluded' => true],
                    ['value' => 'A', 'shared_from_first' => true],
                    ['value' => 'P'],
                    ['value' => 'X', 'excluded' => true],
                    null,
                ],
            ],
        ]);

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('sortMatrixRows');
        $method->setAccessible(true);
        $method->invoke($service);

        $values = collect($service->matrix[0]['worksMap'])
            ->filter()
            ->map(fn ($w) => is_array($w) ? $w['value'] : $w)
            ->values()
            ->all();

        $this->assertSame(
            ['A', 'A', 'X', 'X', 'N', 'P', 'R'],
            $values
        );
    }
}
