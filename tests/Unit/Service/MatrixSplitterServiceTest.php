<?php

namespace Tests\Unit\Services;

use App\Enums\DayType;
use App\Services\MatrixSplitterService;
use Illuminate\Support\Collection;
use Tests\TestCase;
use ReflectionClass;

class MatrixSplitterServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'constants.matrix.total_slots' => 8,
            'constants.matrix.morning_end' => '12:00',
            'constants.matrix.afternoon_start' => '14:00',
        ]);
    }

    /** Helper per creare licenseTable */
    private function licenseTable(array $worksMaps): array
    {
        $total = config('constants.matrix.total_slots');

        return collect($worksMaps)->map(fn($map, $i) => [
            'id' => $i + 1,
            'license_table_id' => $i + 1,
            'user' => null,
            'turn' => DayType::FULL->value,
            'only_cash_works' => false,
            'slots_occupied' => 0,
            'wallet' => 0,
            'real_slots_today' => $total,
            'worksMap' => $map,
        ])->toArray();
    }

    /** @test */
    public function it_preserves_fixed_agency_and_cash_works()
    {
        $licenseTable = $this->licenseTable([
            [['value' => 'A', 'excluded' => true, 'license_table_id' => 1], null, null, null, null, null, null, null],
            [['value' => 'X', 'excluded' => true, 'license_table_id' => 2], null, null, null, null, null, null, null],
        ]);

        $service = new MatrixSplitterService($licenseTable);

        $this->assertCount(0, $service->unassignedWorks);
        $this->assertEquals('A', $service->matrix[0]['worksMap'][0]['value']);
        $this->assertEquals('X', $service->matrix[1]['worksMap'][0]['value']);
    }

    /** @test */
    public function it_distributes_round_robin_cash_works()
    {
        config(['constants.matrix.total_slots' => 4]);

        $licenseTable = $this->licenseTable([
            array_fill(0, 4, null),
            array_fill(0, 4, null),
        ]);

        // Classe anonima che simula 6 lavori cash
        $service = new class($licenseTable) extends MatrixSplitterService {
            public function pendingCashWorks(): Collection
            {
                return collect([
                    ['value' => 'R', 'timestamp' => '2025-01-01 08:00:00'],
                    ['value' => 'R', 'timestamp' => '2025-01-01 09:00:00'],
                    ['value' => 'R', 'timestamp' => '2025-01-01 10:00:00'],
                    ['value' => 'R', 'timestamp' => '2025-01-01 11:00:00'],
                    ['value' => 'R', 'timestamp' => '2025-01-01 12:00:00'],
                    ['value' => 'R', 'timestamp' => '2025-01-01 13:00:00'],
                ]);
            }
        };

        $this->assertCount(0, $service->unassignedWorks);
        $this->assertEquals(6, $service->matrix->sum(fn($r) => collect($r['worksMap'])->filter()->count()));
    }

    /** @test */
    public function it_respects_morning_turn_constraint()
    {
        config(['constants.matrix.total_slots' => 4]);

        $licenseTable = $this->licenseTable([
            array_fill(0, 4, null),
        ]);

        // Forza turno mattina
        $licenseTable[0]['turn'] = DayType::MORNING->value;

        $service = new class($licenseTable) extends MatrixSplitterService {
            public function pendingCashWorks(): Collection
            {
                return collect([
                    ['value' => 'R', 'timestamp' => '2025-01-01 09:00:00'], // OK
                    ['value' => 'R', 'timestamp' => '2025-01-01 15:00:00'], // NO
                ]);
            }
        };

        $this->assertCount(1, $service->unassignedWorks);
        $this->assertStringContainsString('09:00', $service->extractWorkTime($service->matrix[0]['worksMap'][0] ?? []));
    }

    /** @test */
    public function it_sorts_matrix_rows_correctly()
    {
        $licenseTable = $this->licenseTable([
            [
                ['value' => 'R', 'timestamp' => '10:00'],
                'N',
                ['value' => 'X', 'excluded' => false],
                ['value' => 'A', 'excluded' => true],
                ['value' => 'A', 'shared_from_first' => true],
                ['value' => 'P'],
                ['value' => 'X', 'excluded' => true],
                null,
            ],
        ]);

        $service = new MatrixSplitterService($licenseTable);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('sortMatrixRows');
        $method->setAccessible(true);
        $method->invoke($service);

        $sorted = $service->matrix[0]['worksMap'];

        $this->assertEquals('A', is_array($sorted[0]) ? $sorted[0]['value'] : $sorted[0]);
        $this->assertEquals('A', is_array($sorted[1]) ? $sorted[1]['value'] : $sorted[1]);
        $this->assertEquals('X', $sorted[2]);
        $this->assertEquals('X', $sorted[3]);
        $this->assertEquals('N', $sorted[4]);
        $this->assertEquals('P', $sorted[5]);
    }
}