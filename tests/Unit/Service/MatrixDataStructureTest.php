<?php

namespace Tests\Unit\Service;

use Tests\TestCase;
use App\DataObjects\MatrixTable;
use App\DataObjects\LicenseRow;
use App\DataObjects\LiquidationResult;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;

class MatrixDataStructureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('app_settings.works.default_amount', 90.0);
    }

    #[Test]
    public function license_row_refresh_calculates_correct_liquidation()
    {
        // Prepariamo una riga con un nolo (N) e un contante (X)
        // Il nolo (90€) meno il wallet (100€) genera un wallet_diff di -10€
        $row = new LicenseRow(
            user: ['license_number' => '10'],
            id: 1,
            target_capacity: 4,
            only_cash_works: false,
            wallet: 100.0,
            worksMap: [
                ['value' => 'N', 'amount' => 90],
                ['value' => 'X', 'amount' => 90, 'shared_from_first' => 0]
            ]
        );

        $row->refresh(bancaleCost: 5.0);

        $this->assertEquals(2, $row->slots_occupied);
        $this->assertInstanceOf(LiquidationResult::class, $row->liquidation);

        // Calcolo: X(90) + Diff(-10) - Bancale(5) = 75
        $this->assertEquals(75.0, $row->liquidation->money['netto']);
    }

    #[Test]
    public function matrix_table_refresh_all_updates_every_row()
    {
        $row1 = new LicenseRow(['ln' => '1'], 1, 4, false, 0, [['value' => 'X', 'amount' => 90, 'shared_from_first' => 0]]);
        $row2 = new LicenseRow(['ln' => '2'], 2, 4, false, 0, [['value' => 'X', 'amount' => 90, 'shared_from_first' => 0]]);

        $table = new MatrixTable(collect([$row1, $row2]));

        // Eseguiamo refresh globale con costo bancale 10
        $table->refreshAll(10.0);

        $this->assertEquals(80.0, $row1->liquidation->money['netto']);
        $this->assertEquals(80.0, $row2->liquidation->money['netto']);
    }

    #[Test]
    public function deep_wireable_integrity_test()
    {
        // Creiamo una struttura completa
        $row = new LicenseRow(['license_number' => 'A1'], 1, 4, false, 0, [['value' => 'X', 'amount' => 90, 'shared_from_first' => 0]]);
        $row->refresh(0); // Genera l'oggetto LiquidationResult interno

        $originalTable = new MatrixTable(collect([$row]));

        // Simula il "viaggio" verso il browser e ritorno (Dehydration -> Hydration)
        $dehydrated = $originalTable->toLivewire();
        $hydratedTable = MatrixTable::fromLivewire($dehydrated);

        // Verifiche di integrità
        $this->assertCount(1, $hydratedTable->rows);

        $firstRow = $hydratedTable->rows->first();
        $this->assertInstanceOf(LicenseRow::class, $firstRow);

        // Verifica la REIDRATAZIONE PROFONDA (l'oggetto più interno)
        $this->assertInstanceOf(LiquidationResult::class, $firstRow->liquidation);
        $this->assertEquals(90.0, $firstRow->liquidation->money['netto']);
    }
}
