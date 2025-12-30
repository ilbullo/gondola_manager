<?php

namespace Tests\Unit\Service;

use Tests\TestCase;
use App\Services\LiquidationService;
use App\DataObjects\LiquidationResult;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;


class LiquidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Mock della configurazione per avere un prezzo unitario certo nei test
        Config::set('app_settings.works.default_amount', 90.0);
    }

    #[Test]
    public function it_calculates_correct_totals_with_standard_values()
    {
        // Simuliamo 2 lavori X (Contanti) da 90€ l'uno
        $works = [
            ['value' => 'X', 'amount' => 90, 'shared_from_first' => 0],
            ['value' => 'X', 'amount' => 90, 'shared_from_first' => 0],
            ['value' => 'N', 'amount' => 90], // Nolo (non influisce sul contante di oggi)
            ['value' => 'P', 'amount' => 0],  // Perdi volta
        ];

        $walletDiff = 10.0;  // Il conducente deve avere 10€ in più dal wallet
        $bancale = 5.0;      // Sottraiamo 5€ di costo bancale

        $result = LiquidationService::calculate($works, $walletDiff, $bancale);

        // Valore X = 90 + 90 = 180
        // Netto = 180 + 10 - 5 = 185
        $this->assertEquals(180.0, $result->money['valore_x']);
        $this->assertEquals(185.0, $result->money['netto']);
        
        // Verifica conteggi
        $this->assertEquals(1, $result->counts['n']);
        $this->assertEquals(2, $result->counts['x']);
        $this->assertEquals(1, $result->counts['p']);
    }

    #[Test]
    public function it_handles_shared_works_correcty()
    {
        $works = [
            ['value' => 'X', 'amount' => 90, 'shared_from_first' => 0],
            ['value' => 'X', 'amount' => 90, 'shared_from_first' => 1, 'voucher' => 'V-123'], // Condiviso: non paga l'utente
        ];

        $result = LiquidationService::calculate($works, 0, 0);

        // Il netto deve contare solo il primo lavoro X
        $this->assertEquals(90.0, $result->money['valore_x']);
        $this->assertEquals(1, $result->counts['shared']);
        $this->assertContains('V-123', $result->lists['shared_vouchers']);
    }

    #[Test]
    public function it_can_aggregate_multiple_liquidations()
    {
        $liq1 = new LiquidationResult(['n' => 1, 'x' => 1], ['netto' => 100.0]);
        $liq2 = new LiquidationResult(['n' => 2, 'x' => 0], ['netto' => 50.0]);

        $totals = LiquidationResult::aggregateTotals([$liq1, $liq2]);

        $this->assertEquals(3, $totals['n']);
        $this->assertEquals(1, $totals['x']);
        $this->assertEquals(150.0, $totals['netto']);
    }

    #[Test]
    public function it_is_wireable_and_preserves_data()
    {
        $original = new LiquidationResult(
            ['n' => 5], 
            ['netto' => 450.0], 
            ['agencies' => ['ABC' => 'VOUCH1']]
        );

        // Simuliamo il passaggio attraverso Livewire (Dehydration -> Hydration)
        $data = $original->toLivewire();
        $restored = LiquidationResult::fromLivewire($data);

        $this->assertEquals($original->counts['n'], $restored->counts['n']);
        $this->assertEquals($original->money['netto'], $restored->money['netto']);
        $this->assertEquals($original->lists['agencies'], $restored->lists['agencies']);
        $this->assertInstanceOf(LiquidationResult::class, $restored);
    }
}