<?php

namespace Tests\Unit\Models;

use App\Models\WorkAssignment;
use App\Models\LicenseTable;
use App\Enums\WorkType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class WorkAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected LicenseTable $license;

    protected function setUp(): void
    {
        parent::setUp();
        $this->license = LicenseTable::factory()->create();
    }

    #[Test]
    public function it_enforces_maximum_slot_capacity()
    {
        $totalSlots = config('app_settings.matrix.total_slots', 25);

        // Occupiamo 24 slot
        WorkAssignment::factory()->create([
            'license_table_id' => $this->license->id,
            'slots_occupied' => $totalSlots - 1,
            'slot' => 1
        ]);

        // Proviamo ad aggiungere un lavoro da 2 slot (24 + 2 = 26 > 25)
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("superata la capacità massima");

        WorkAssignment::create([
            'license_table_id' => $this->license->id,
            'value' => WorkType::CASH->value,
            'slots_occupied' => 2,
            'slot' => 25,
            'amount' => 10.00
        ]);
    }

    #[Test]
    public function it_allows_excluded_flag_only_for_agency_or_cash_types()
    {
        // Caso valido: Tipo Agenzia (A) con excluded
        $validWork = WorkAssignment::create([
            'license_table_id' => $this->license->id,
            'value' => WorkType::AGENCY->value,
            'excluded' => true,
            'slot' => 1,
            'amount' => 50.00
        ]);
        $this->assertTrue($validWork->exists);

        // Caso non valido: Tipo Nolo (P) con excluded
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Il campo 'excluded' può essere abilitato solo per lavori di tipo 'A' o 'X'");

        WorkAssignment::create([
            'license_table_id' => $this->license->id,
            'value' => WorkType::NOLO->value,
            'excluded' => true,
            'slot' => 2,
            'amount' => 30.00
        ]);
    }

    #[Test]
    public function it_validates_work_type_via_mutator()
    {
        $work = new WorkAssignment();
        
        // Valore valido
        $work->value = 'A';
        $this->assertEquals('A', $work->value);

        // Valore non valido (diventa null per via del mutator)
        $work->value = 'INVALID_TYPE';
        $this->assertNull($work->value);
    }

    #[Test]
    public function it_provides_convenient_agency_accessors()
    {
        $agency = \App\Models\Agency::factory()->create(['name' => 'Hotel Roma', 'code' => 'HR']);
        $work = WorkAssignment::factory()->create([
            'license_table_id' => $this->license->id,
            'agency_id' => $agency->id
        ]);

        $this->assertEquals('Hotel Roma', $work->agency_name);
        $this->assertEquals('HR', $work->agency_code);
    }

    #[Test]
    public function it_maintains_financial_precision_on_large_sums()
    {
        // Creiamo 100 piccoli lavori per testare l'accumulo di arrotondamenti
        $license = LicenseTable::factory()->create();
        for ($i = 0; $i < 10; $i++) {
            WorkAssignment::factory()->create([
                'license_table_id' => $license->id,
                'amount' => 10.33,
                'value' => 'N',
                'slot' => $i + 1
            ]);
        }

        // 10.33 * 10 deve fare esattamente 103.30, non 103.2999...
        $this->assertEquals(103.30, (float)$license->refresh()->wallet);
    }

    #[Test]
    public function it_blocks_invalid_combinations_of_flags()
    {
        $license = LicenseTable::factory()->create();

        // Tentativo di mettere 'shared_from_first' su un lavoro 'N' (Cash)
        // Secondo le tue regole nel modello, questo deve fallire.
        $this->expectException(\Exception::class);
        
        WorkAssignment::create([
            'license_table_id' => $license->id,
            'value' => 'N',
            'shared_from_first' => true,
            'amount' => 50,
            'slot' => 1
        ]);
    }
}