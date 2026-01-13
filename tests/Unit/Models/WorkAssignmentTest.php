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
        // 1. Cambiato in \Exception (come da tuo errore nel modello)
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Errore Spaziale: la durata del lavoro eccede il limite del tabellone");

        $license = LicenseTable::factory()->create();

        // Questo fa finire il lavoro allo slot 26, scatenando l'eccezione
        WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'slot' => 22,
            'slots_occupied' => 5
        ]);
    }

    #[Test]
    public function it_allows_excluded_flag_only_for_agency_or_cash_types()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Integrità violata: il campo 'excluded'/'shared_from_first'");

        $license = LicenseTable::factory()->create();

        // Tentativo illegale su tipo 'N'
        WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'value' => 'N',
            'excluded' => true
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
            'agency_id' => $agency->id,
            'slot' => 1,  // Inizia all'1
            'slots_occupied'      => 1,  // Dura solo 1 slot
        ]);

        $this->assertEquals('Hotel Roma', $work->agency_name);
        $this->assertEquals('HR', $work->agency_code);
    }

    #[Test]
    public function it_maintains_financial_precision_on_large_sums()
    {
        $license = LicenseTable::factory()->create();

        // Usiamo un valore alto ma compatibile con un decimal(8,2) o (10,2)
        // 9999.99 è una scommessa sicura e verifica comunque i decimali
        $largeAmount = 9999.99;

        $work = WorkAssignment::factory()->create([
            'license_table_id' => $license->id,
            'amount' => $largeAmount,
            'slots_occupied' => 1,
            'slot' => 1,
            'value' => 'X'
        ]);

        // fresh() ricarica il modello dal database per essere sicuri
        // che il casting di Eloquent non ci stia ingannando
        $this->assertEquals($largeAmount, (float) $work->fresh()->amount);
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
