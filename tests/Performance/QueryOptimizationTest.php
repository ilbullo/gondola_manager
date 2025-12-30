<?php

namespace Tests\Performance;

use App\Models\{LicenseTable, User, WorkAssignment};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class QueryOptimizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST 1: Prevenzione problema N+1
     * Spiegazione: Quando carichi 10 licenze, non vuoi che Laravel faccia 
     * 10 query separate per trovare gli utenti e 10 per i lavori.
     * Deve usare "Eager Loading" (il metodo with()).
     */
    #[Test]
    public function it_uses_eager_loading_to_prevent_n_plus_1()
    {
        // Prepariamo 10 licenze con 5 lavori ciascuna
        $licenses = LicenseTable::factory()->count(10)->create(['date' => today()]);
        foreach ($licenses as $license) {
            WorkAssignment::factory()->count(5)->create([
                'license_table_id' => $license->id,
            ]);
        }

        DB::enableQueryLog();

        // Query OTTIMIZZATA: Carica tutto in 3-4 query totali
        $licensesWithWorks = LicenseTable::with(['user', 'works.agency'])
            ->whereDate('date', today())
            ->get();

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        // Se non usassi "with", farebbe circa 21 query (1 + 10 utenti + 10 lavori).
        // Con "with", ne bastano 4.
        $this->assertLessThanOrEqual(5, count($queryLog), "Rilevato possibile problema N+1: troppe query eseguite!");
    }

    /**
     * TEST 2: Selezione colonne specifiche
     * Spiegazione: Non scaricare "SELECT *" se ti servono solo 3 campi. 
     * Risparmia memoria RAM sul server.
     */
    #[Test]
    public function it_uses_select_to_optimize_columns()
    {
        User::factory()->count(5)->create();

        DB::enableQueryLog();
        User::select(['id', 'name', 'email'])->get();
        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        $firstQuery = $queryLog[0]['query'];
        
        // Verifica che la query non contenga il carattere jolly *
        $this->assertStringNotContainsString('*', $firstQuery);
        $this->assertStringContainsString('"name"', $firstQuery);
    }

    /**
     * TEST 3: Elaborazione a blocchi (Chunk)
     * Spiegazione: Se hai 10.000 utenti, non caricarli tutti insieme o il server esplode.
     * Caricali 100 alla volta.
     */
    #[Test]
    public function it_uses_chunk_for_large_datasets()
    {
        User::factory()->count(150)->create();

        $processedCount = 0;

        // Processa 100 record alla volta
        User::chunk(100, function ($users) use (&$processedCount) {
            $processedCount += $users->count();
        });

        $this->assertEquals(150, $processedCount);
    }

    /**
     * TEST 4: Uso di EXISTS invece di COUNT
     * Spiegazione: Per sapere se esiste un lavoro, COUNT(*) deve contare tutto. 
     * EXISTS si ferma appena ne trova uno. È molto più veloce.
     */
    #[Test]
    public function it_uses_exists_instead_of_count_for_checks()
    {
        $license = LicenseTable::factory()->create();
        WorkAssignment::factory()->create(['license_table_id' => $license->id]);

        DB::enableQueryLog();
        $hasWorks = WorkAssignment::where('license_table_id', $license->id)->exists();
        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        $sql = strtolower($queryLog[0]['query']);
        
        // Verifica che Laravel stia usando la logica "limit 1" o "exists"
        $this->assertTrue($hasWorks);
        $this->assertStringContainsString('limit 1', $sql);
    }

    /**
     * TEST 5: Limite dei risultati
     * Spiegazione: Mai chiedere al database più record di quelli che puoi mostrare.
     */
    #[Test]
    public function it_limits_query_results_when_possible()
    {
        User::factory()->count(20)->create();

        DB::enableQueryLog();
        $users = User::limit(10)->get();
        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(10, $users);
        $this->assertStringContainsString('limit 10', strtolower($queryLog[0]['query']));
    }
}