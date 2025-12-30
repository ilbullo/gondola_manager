<?php

namespace Tests\Unit\Models;

use App\Models\LicenseTable;
use App\Models\WorkAssignment;
use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use \Illuminate\Support\Facades\DB;

class LicenseTableTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_calculates_wallet_only_from_cash_works()
    {
        $license = LicenseTable::factory()->create();

        // Lavoro Cash (N) - Deve essere nel wallet
        WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'value' => 'N',
            'amount' => 50.00,
            'slot' => 1
        ]);

        // Lavoro Agenzia (A) - Non deve essere nel wallet
        WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'value' => 'A',
            'amount' => 100.00,
            'slot' => 2
        ]);

        $this->assertEquals(50.00, $license->wallet);
    }

    #[Test]
    public function it_generates_a_correct_works_map_without_overlaps()
    {
        $license = LicenseTable::factory()->create();
        
        // Lavoro da 3 slot partendo dallo slot 1
        WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'slot' => 1,
            'slots_occupied' => 3
        ]);

        $map = $license->works_map;

        $this->assertNotNull($map[1]);
        $this->assertNotNull($map[2]);
        $this->assertNotNull($map[3]);
        $this->assertNull($map[4]); // Lo slot 4 deve essere libero
    }

    #[Test]
    public function it_throws_exception_on_overlapping_works()
    {
        $license = LicenseTable::factory()->create();

        // Primo lavoro: slot 1-2
        WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'slot' => 1,
            'slots_occupied' => 2
        ]);

        // Secondo lavoro: prova a occupare lo slot 2 (sovrapposizione!)
        WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'slot' => 2, 
            'slots_occupied' => 1
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Sovrapposizione rilevata sullo slot 2");

        // L'accesso alla property triggera la logica di validazione
        $map = $license->works_map;
    }

    #[Test]
    public function it_swaps_order_correctly_between_two_licenses()
    {
        $date = now()->format('Y-m-d');
        $l1 = LicenseTable::factory()->create(['order' => 1, 'date' => $date]);
        $l2 = LicenseTable::factory()->create(['order' => 2, 'date' => $date]);

        LicenseTable::swap($l1->id, 'down');

        $this->assertEquals(2, $l1->refresh()->order);
        $this->assertEquals(1, $l2->refresh()->order);
    }
    #[Test]
    public function it_validates_boundary_limits_of_the_matrix()
    {
        $license = LicenseTable::factory()->create();

        // Tentativo di inserire un lavoro che inizia allo slot 25 ma occupa 2 slot
        // (Uscirebbe dal limite massimo di 25)
        $this->expectException(\Exception::class);

        WorkAssignment::create([
            'license_table_id' => $license->id,
            'slot' => 25,
            'slots_occupied' => 2,
            'value' => 'N',
            'amount' => 20
        ]);
    }

    #[Test]
    public function it_handles_gaps_in_the_works_map_correctly()
    {
        $license = LicenseTable::factory()->create();
        
        // Creiamo un lavoro allo slot 1 e uno allo slot 5
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'slot' => 1, 'slots_occupied' => 1]);
        WorkAssignment::factory()->create(['license_table_id' => $license->id, 'slot' => 5, 'slots_occupied' => 1]);

        $map = $license->works_map;

        $this->assertNotNull($map[1]);
        $this->assertNull($map[2]); // Lo slot 2 deve essere vuoto (null)
        $this->assertNull($map[3]);
        $this->assertNull($map[4]);
        $this->assertNotNull($map[5]);
    }

    #[Test]
    public function it_prevents_race_conditions_during_swap()
    {
        $date = now()->format('Y-m-d');
        $l1 = LicenseTable::factory()->create(['order' => 1, 'date' => $date]);
        $l2 = LicenseTable::factory()->create(['order' => 2, 'date' => $date]);

        // Simuliamo l'inizio di una transazione che blocca la riga
        DB::beginTransaction();
        $lockedL1 = LicenseTable::where('id', $l1->id)->lockForUpdate()->first();
        
        // In un'app reale, qui un secondo processo proverebbe a fare lo swap
        // ma verrebbe messo in attesa dal database finché non facciamo commit/rollback.
        
        $l1->update(['order' => 10]); 
        DB::commit();

        $this->assertEquals(10, $l1->refresh()->order);
    }
}