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

        $this->creaLicenze(12);
        $this->turni = $this->licenze->pluck('id')->map(fn() => 'full')->toArray();
        $this->lavoriDaRipartire = collect();
    }

    /** @test */
    public function i_lavori_fissi_non_vengono_spostati()
    {
        $licenza = $this->licenze->first();
        $this->creaLavoroFisso($licenza, 'A', slot: 10);

        $this->creaLavoriDaRipartire(15, 'A');
        $tabella = $this->ripartisci();

        $this->assertTrue(
            $this->lavoroNellaCella($tabella, $licenza->id, 10)?->excluded
        );
    }

    /** @test */
    public function non_si_assegna_piu_di_quanto_gia_fatto_in_realta()
    {
        $pochi = $this->licenze->get(0);
        $tanti = $this->licenze->get(11);

        $this->creaLavoriGiaFatti($pochi, 19, 'A');
        $this->creaLavoriGiaFatti($tanti, 4, 'A');

        $this->creaLavoriDaRipartire(40, 'A');
        $tabella = $this->ripartisci();

        $this->assertLessThanOrEqual(19, $this->contaSlotAssegnati($tabella, $pochi->id));
        $this->assertGreaterThan($this->contaSlotAssegnati($tabella, $pochi->id), $this->contaSlotAssegnati($tabella, $tanti->id));
    }

    /** @test */
    public function rispetta_il_turno_mattina_pomeriggio()
    {
        $mattina = $this->licenze->get(0);
        $pomeriggio = $this->licenze->get(1);

        $this->turni[$mattina->id] = 'morning';
        $this->turni[$pomeriggio->id] = 'afternoon';

        $this->creaLavoriDaRipartire(8, 'A', ora: '10:30');
        $this->creaLavoriDaRipartire(8, 'A', ora: '15:00');

        $tabella = $this->ripartisci();

        $this->assertFalse($this->haLavoriPomeridiani($tabella, $mattina->id));
        $this->assertFalse($this->haLavoriMattutini($tabella, $pomeriggio->id));
    }

    /** @test */
    public function esclude_le_agenzie_se_richiesto()
    {
        $esclusa = $this->licenze->get(5);
        $this->escludiA = [$esclusa->id];

        $this->creaLavoriDaRipartire(30, 'A');
        $tabella = $this->ripartisci();

        $this->assertFalse($this->haLavoriDiTipo($tabella, $esclusa->id, 'A'));
    }

    /** @test */
    public function tratta_nolo_e_perdivolta_come_contanti()
    {
        $this->creaLavoriDaRipartire(3, 'X');
        $this->creaLavoriDaRipartire(4, 'N');
        $this->creaLavoriDaRipartire(5, 'P');

        $tabella = $this->ripartisci(bancale: 180);

        $this->assertEquals(900, collect($tabella)->sum('cash_due')); // (3+4+5)=12 × 90 - 180
    }

    /** @test */
    public function assegna_prima_le_agenzie_poi_il_ripartito_dal_primo()
    {
        $primo = $this->licenze->first();

        // Lavoro che DEVE andare al primo della lista
        $this->creaLavoriDaRipartire(1, 'A', ora: '09:00', dalPrimo: true);
        $this->creaLavoriDaRipartire(25, 'A');

        $tabella = $this->ripartisci();

        $ricevuto = $this->lavoriAssegnatiA($tabella, $primo->id)
            ->contains(fn($w) => $w?->shared_from_first === true);

        $this->assertTrue($ricevuto, 'Il lavoro "dal primo" non è stato assegnato alla prima licenza');
    }

    // ===================================================================
    // HELPER – FUNZIONANO CON license_table_id E slot NOT NULL
    // ===================================================================

    private function creaLicenze(int $n = 12): void
    {
        $this->licenze = collect();
        foreach (range(1, $n) as $i) {
            $user = User::factory()->create(['license_number' => $i]);
            $lt = LicenseTable::create(['user_id' => $user->id, 'date' => today(), 'order' => $i]);
            $lt->load('user');
            $this->licenze->push($lt);
        }
    }

    private function creaLavoriGiaFatti(LicenseTable $licenza, int $quanti, string $tipo): void
    {
        WorkAssignment::factory()->count($quanti)->create([
            'license_table_id' => $licenza->id,
            'value'            => $tipo,
            'excluded'         => false,
            'slot'             => fn() => fake()->numberBetween(1, 30),
        ]);
    }

    private function creaLavoroFisso(LicenseTable $licenza, string $tipo, int $slot): void
    {
        WorkAssignment::factory()->create([
            'license_table_id' => $licenza->id,
            'value'            => $tipo,
            'slot'             => $slot,
            'excluded'         => true,
        ]);
    }

    /** CREA LAVORI DA RIPARTIRE SENZA VIOLARE NESSUN NOT NULL */
    private function creaLavoriDaRipartire(int $quanti, string $tipo, ?string $ora = null, bool $dalPrimo = false): void
    {
        $oraBase = $ora ? Carbon::today()->setTimeFromTimeString($ora) : now();
        $licenzaTemp = $this->licenze->first();

        foreach (range(1, $quanti) as $i) {
            $lavoro = WorkAssignment::factory()->create([
                'license_table_id'  => $licenzaTemp->id,
                'value'             => $tipo,
                'slot'              => 999,                    // valore temporaneo valido
                'slots_occupied'    => 1,
                'excluded'          => false,
                'shared_from_first' => $dalPrimo,
                'timestamp'         => $oraBase->copy()->addMinutes($i * 10),
            ]);

            if ($tipo === 'A') {
                $agenzia = Agency::factory()->create();
                $lavoro->agency_id = $agenzia->id;
                $lavoro->saveQuietly();
            }

            // Ora lo rendiamo "da ripartire" – il servizio lo riassegnerà
            $lavoro->license_table_id = null;
            $lavoro->slot = null;
            $lavoro->saveQuietly(); // saveQuietly() bypassa i cast/validazioni che potrebbero bloccare

            $this->lavoriDaRipartire->push($lavoro);
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

    // Helper lettura risultato
    private function lavoroNellaCella(array $tabella, int $licenzaId, int $slot)
    {
        return collect($tabella)->firstWhere('license_table_id', $licenzaId)['assignments'][$slot] ?? null;
    }

    private function contaSlotAssegnati(array $tabella, int $licenzaId): int
    {
        $riga = collect($tabella)->firstWhere('license_table_id', $licenzaId);
        return collect($riga['assignments'] ?? [])
            ->filter(fn($w) => $w instanceof WorkAssignment)
            ->sum('slots_occupied');
    }

    private function lavoriAssegnatiA(array $tabella, int $licenzaId): Collection
    {
        $riga = collect($tabella)->firstWhere('license_table_id', $licenzaId);
        return collect($riga['assignments'] ?? []);
    }

    private function haLavoriDiTipo(array $tabella, int $licenzaId, string $tipo): bool
    {
        return $this->lavoriAssegnatiA($tabella, $licenzaId)
            ->contains(fn($w) => $w instanceof WorkAssignment && $w->value === $tipo);
    }

    private function haLavoriPomeridiani(array $tabella, int $licenzaId): bool
    {
        return $this->lavoriAssegnatiA($tabella, $licenzaId)
            ->contains(fn($w) => $w?->timestamp?->format('H:i') >= '13:31');
    }

    private function haLavoriMattutini(array $tabella, int $licenzaId): bool
    {
        return $this->lavoriAssegnatiA($tabella, $licenzaId)
            ->contains(fn($w) => $w?->timestamp?->format('H:i') <= '13:30');
    }
}