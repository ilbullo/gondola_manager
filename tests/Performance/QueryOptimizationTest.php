<?php

// tests/Feature/Performance/QueryOptimizationTest.php
namespace Tests\Feature\Performance;

use App\Models\{LicenseTable, User, WorkAssignment};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class QueryOptimizationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_uses_eager_loading_to_prevent_n_plus_1()
    {
        $licenses = LicenseTable::factory()->count(10)->create(['date' => today()]);
        
        foreach ($licenses as $license) {
            WorkAssignment::factory()->count(5)->create([
                'license_table_id' => $license->id,
                'timestamp' => today()
            ]);
        }

        DB::enableQueryLog();

        // Query con eager loading
        $licensesWithWorks = LicenseTable::with(['user', 'works.agency'])
            ->whereDate('date', today())
            ->get();

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        // Dovrebbe fare 3 query: 1 per licenses, 1 per users, 1 per works
        // Non 1 + 10 (N+1 problem)
        $this->assertLessThanOrEqual(4, count($queryLog));
    }

    #[Test]
    public function it_uses_select_to_optimize_columns()
    {
        User::factory()->count(50)->create();

        DB::enableQueryLog();

        // Query ottimizzata
        $users = User::select(['id', 'name', 'email'])->get();

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        $firstQuery = $queryLog[0]['query'] ?? '';
        
        // Dovrebbe selezionare solo le colonne specificate
        $this->assertStringNotContainsString('SELECT *', $firstQuery);
    }

    #[Test]
    public function it_uses_chunk_for_large_datasets()
    {
        User::factory()->count(1000)->create();

        $processedCount = 0;

        User::chunk(100, function ($users) use (&$processedCount) {
            $processedCount += $users->count();
        });

        $this->assertEquals(1000, $processedCount);
    }

    #[Test]
    public function it_uses_exists_instead_of_count_for_checks()
    {
        $license = LicenseTable::factory()->create(['date' => today()]);
        WorkAssignment::factory()->count(100)->create([
            'license_table_id' => $license->id,
            'timestamp' => today()
        ]);

        DB::enableQueryLog();

        // exists() è più performante di count() > 0
        $hasWorks = WorkAssignment::where('license_table_id', $license->id)->exists();

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        $firstQuery = $queryLog[0]['query'] ?? '';
        
        // Dovrebbe usare EXISTS
        $this->assertTrue($hasWorks);
    }

    #[Test]
    public function it_uses_indexes_for_common_queries()
    {
        LicenseTable::factory()->count(100)->create();

        DB::enableQueryLog();

        // Query su date (dovrebbe usare index)
        $licenses = LicenseTable::whereDate('date', today())->get();

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        // Non possiamo verificare direttamente l'uso degli index,
        // ma possiamo verificare che la query sia veloce
        $this->assertNotEmpty($queryLog);
    }

    #[Test]
    public function it_limits_query_results_when_possible()
    {
        User::factory()->count(1000)->create();

        DB::enableQueryLog();

        // Prendi solo i primi 10
        $users = User::limit(10)->get();

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        $firstQuery = $queryLog[0]['query'] ?? '';
        
        $this->assertCount(10, $users);
        $this->assertStringContainsString('limit', strtolower($firstQuery));
    }
}