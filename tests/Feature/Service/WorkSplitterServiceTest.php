<?php

namespace Tests\Feature\Services;

use App\Models\{Agency, LicenseTable, User, WorkAssignment};
use App\Services\WorkSplitterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Carbon\Carbon;

class WorkSplitterServiceTest extends TestCase
{
    use RefreshDatabase;

    private Collection $licenze;
    private Collection $lavoriDaRipartire;
    private array $escludiA = [];
    private array $turni = [];

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2025-11-21 08:00:00');

        $this->lavoriDaRipartire = collect();
        $this->creaLicenze(12);
        $this->turni = $this->licenze->pluck('id')->map(fn() => 'full')->toArray();
    }

    /** @test */
    public function i_lavori_fissi_non_vengono_spostati()
    {
        $licenza = $this->licenze->first();
        $this->creaLavoroFisso($licenza, 'A', slot: 5);

        $this->creaLavoriDaRipartire(20, 'A');
        $tabella = $this->ripartisci();

        $lavoroFisso = $this->lavoroNellaCella($tabella, $licenza->id, 5);
        $this->assertNotNull($lavoroFisso);
        $this->assertTrue($lavoroFisso->excluded);
        $this->assertEquals('A', $lavoroFisso->value);
    }

    /** @test */
    public function non_si_assegna_piu_di_quanto_gia_fatto_in_realta()
    {
        $pochi = $this->licenze->get(0);  // ha già 18 lavori reali
        $tanti = $this->licenze->get(11); // ha solo 3

        $this->creaLavoriGiaFatti($pochi, 18, 'A');
        $this->creaLavoriGiaFatti($tanti, 3, 'A');

        $this->creaLavoriDaRipartire(50, 'A');
        $tabella = $this->ripartisci();

        $dopoPochi = $this->contaLavoriAssegnati($tabella, $pochi->id);
        $dopoTanti = $this->contaLavoriAssegnati($tabella, $tanti->id);

        $this->assertLessThanOrEqual(18, $dopoPochi);
        $this->assertGreaterThan($dopoTanti, $dopoPochi);
    }

    /** @test */
    public function rispetta_il_turno_mattina_pomeriggio()
    {
        $this->turni[$this->licenze->get(0)->id] = 'morning';
        $this->turni[$this->licenze->get(1)->id] = 'afternoon';

        $this->creaLavoriDaRipartire(10, 'A', ora: '10:00');
        $this->creaLavoriDaRipartire(10, 'A', ora: '15:30');

        $tabella = $this->ripartisci();

        $mattina = $this->licenze->get(0);
        $pomeriggio = $this->licenze->get(1);

        $this->assertFalse($this->haLavoriDopoLe13_30($tabella, $mattina->id));
        $this->assertFalse($this->haLavoriPrimaDelle13_30($tabella, $pomeriggio->id));
    }

    /** @test */
    public function esclude_le_agenzie_se_richiesto()
    {
        $this->escludiA = [$this->licenze->get(3)->id];

        $this->creaLavoriDaRipartire(40, 'A');
        $tabella = $this->ripartisci();

        $this->assertFalse($this->haLavoriDiTipo($tabella, $this->escludiA[0], 'A'));
    }

    /** @test */
    public function tratta_nolo_e_perdivolta_come_contanti()
    {
        $this->creaLavoriDaRipartire(3, 'X');
        $this->creaLavoriDaRipartire(4, 'N');
        $this->creaLavoriDaRipartire(5, 'P');

        $tabella = $this->ripartisci(bancale: 180);

        $totale = collect($tabella)->sum(fn($r) => $r['cash_due'] ?? 0);
        $this->assertEquals(900, $totale); // (3+4+5) * 90 - 180
    }

    /** @test */
    public function assegna_prima_le_agenzie_poi_il_ripartito_dal_primo()
    {
        $primo = $this->licenze->first();

        // Deve andare per forza al primo
        $this->creaLavoriDaRipartire(1, 'A', dalPrimo: true);
        $this->creaLavoriDaRipartire(30, 'A');

        $tabella = $this->ripartisci();

        $haRicevuto = $this->lavoriAssegnatiA($tabella, $primo->id)
            ->contains('shared_from_first', true);

        $this->assertTrue($haRicevuto, 'Il lavoro dal primo NON è stato assegnato al primo della lista');
    }

    // ===================================================================
    // HELPER METHODS – ORA 100% STABILI E CORRETTI
    // ===================================================================

    private function creaLicenze(int $n = 12): void
    {
        $licenze = collect();
        foreach (range(1, $n) as $i) {
            $user = User::factory()->create(['license_number' => $i]);
            $lt = LicenseTable::factory()->create([
                'user_id' => $user->id,
                'date'    => today(),
                'order'   => $i,
            ]);
            $lt->load('user');
            $licenze->push($lt);
        }
        $this->licenze = $licenze->sortBy('order')->values();
    }

    private function creaLavoriGiaFatti(LicenseTable $licenza, int $quanti, string $tipo): void
    {
        WorkAssignment::factory($quanti)->create([
            'license_table_id' => $licenza->id,
            'value'            => $tipo,
            'excluded'         => false,
            'slot'             => fn() => fake()->numberBetween(1, 25),
            'slots_occupied'   => 1,
        ]);
    }

    private function creaLavoroFisso(LicenseTable $licenza, string $tipo, int $slot): void
    {
        WorkAssignment::factory()->create([
            'license_table_id' => $licenza->id,
            'value'            => $tipo,
            'slot'             => $slot,
            'excluded'         => true,
            'slots_occupied'   => 1,
        ]);
    }

    private function creaLavoriDaRipartire(int $quanti, string $tipo, ?string $ora = null, bool $dalPrimo = false): void
    {
        $base = $ora ? Carbon::today()->setTimeFromTimeString($ora) : now();
        $fakeLicense = LicenseTable::factory()->create();
        for ($i = 0; $i < $quanti; $i++) {
            $work = WorkAssignment::factory()->create([
                'license_table_id'  => $fakeLicense->id,
                'slot'              => rand(1,25),
                'value'             => $tipo,
                'slots_occupied'    => 1,
                'excluded'          => false,
                'shared_from_first' => $dalPrimo,
                'timestamp'         => $base->clone()->addMinutes($i * 8),
            ]);

            if ($tipo === 'A') {
                $work->agency_id = Agency::factory()->create()->id;
                $work->saveQuietly();
            }

            $this->lavoriDaRipartire->push($work);
        }
    }

    private function ripartisci(float $bancale = 0.0): array
    {
        return (new WorkSplitterService(
            $this->licenze,
            $this->lavoriDaRipartire,
            $this->escludiA,
            $this->turni
        ))->getSplitTable($bancale);
    }

    // Helper lettura
    private function lavoroNellaCella(array $tabella, int $licenzaId, int $slot): ?WorkAssignment
    {
        $row = collect($tabella)->firstWhere('license_table_id', $licenzaId);
        return $row['assignments'][$slot] ?? null;
    }

    private function contaLavoriAssegnati(array $tabella, int $licenzaId): int
    {
        $row = collect($tabella)->firstWhere('license_table_id', $licenzaId);
        return collect($row['assignments'] ?? [])->filter()->count();
    }

    private function lavoriAssegnatiA(array $tabella, int $licenzaId): Collection
    {
        $row = collect($tabella)->firstWhere('license_table_id', $licenzaId);
        return collect($row['assignments'] ?? []);
    }

    private function haLavoriDiTipo(array $tabella, int $licenzaId, string $tipo): bool
    {
        return $this->lavoriAssegnatiA($tabella, $licenzaId)->contains('value', $tipo);
    }

    private function haLavoriDopoLe13_30(array $tabella, int $licenzaId): bool
    {
        return $this->lavoriAssegnatiA($tabella, $licenzaId)
            ->contains(fn($w) => $w?->timestamp?->greaterThanOrEqualTo(today()->setTime(13, 31)));
    }

    private function haLavoriPrimaDelle13_30(array $tabella, int $licenzaId): bool
    {
        return $this->lavoriAssegnatiA($tabella, $licenzaId)
            ->contains(fn($w) => $w?->timestamp?->lessThanOrEqualTo(today()->setTime(13, 30)));
    }
}
