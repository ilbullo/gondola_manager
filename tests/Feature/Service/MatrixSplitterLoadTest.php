<?php

declare(strict_types=1);

namespace Tests\Feature\Service;

use Tests\TestCase;
use App\Models\LicenseTable;
use App\Models\WorkAssignment;
use App\Services\MatrixSplitterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;


class MatrixSplitterLoadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test di stress per verificare le performance con alto carico.
     * 100 Licenze x 10 Lavori ciascuna = 1.000 WorkAssignments.
     * 
     */
    #[Test]
    public function it_handles_high_load_efficiently()
    {
        $date = '2025-12-30'; // Usiamo una stringa fissa
        $licenseCount = 200;
        $worksPerLicense = 20;

        // 1. Creiamo un utente di riferimento (necessario per la foreign key)
        $user = \App\Models\User::first() ?: \App\Models\User::factory()->create();

        // 2. SETUP LICENZE (Uso Eloquent per velocità e controllo)
        for ($i = 1; $i <= $licenseCount; $i++) {
            $license = \App\Models\LicenseTable::create([
                'user_id'         => $user->id,
                'date'            => $date,
                'only_cash_works' => false,
                'turn'            => 'full',
                'order'           => $i, // Incremento manuale, zero conflitti
            ]);

            // 3. SETUP LAVORI per ogni licenza
            for ($j = 1; $j <= $worksPerLicense; $j++) {
                \App\Models\WorkAssignment::create([
                    'license_table_id'  => $license->id,
                    'value'             => ($j % 2 == 0) ? 'A' : 'X', // Alterniamo i tipi
                    'timestamp'         => "$date 10:00:00",
                    'amount'            => 50.00,
                    'slot'              => $j,
                    'slots_occupied'    => 1,
                    'excluded'          => false,
                    'shared_from_first' => false,
                ]);
            }
        }

        // --- DA QUI IN POI IL TEST PROCEDE NORMALMENTE ---

        $inputLicenses = \App\Models\LicenseTable::where('date', $date)->with('works')->get();

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $splitter = app(\App\Services\MatrixSplitterService::class);
        $result = $splitter->execute($inputLicenses);

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = round(($endTime - $startTime), 4);
        $memoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2);

        $this->assertCount($licenseCount, $result);
        
        fwrite(STDOUT, "\n--- RISULTATI LOAD TEST (NO FACTORY) ---");
        fwrite(STDOUT, "\nTempo Esecuzione: {$executionTime}s");
        fwrite(STDOUT, "\nMemoria Utilizzata: {$memoryUsed}MB");
        fwrite(STDOUT, "\n----------------------------------------\n");
    }

    /**
     * Verifica che non ci sia il problema delle query N+1.
     * 
     */
    #[Test]
    public function it_does_not_execute_excessive_queries_during_splitting()
    {
        $date = today()->addMonth(2);
        $licenses = LicenseTable::factory()->count(20)->create(['date' => $date]);
        
        foreach ($licenses as $lic) {
            // Creiamo 5 lavori per licenza in posizioni fisse e sicure
            for ($slot = 1; $slot <= 5; $slot++) {
                WorkAssignment::factory()->create([
                    'license_table_id' => $lic->id,
                    'slot' => $slot,
                    'slots_occupied' => 1,
                    'value' => 'A'
                ]);
            }
        }

        DB::enableQueryLog();

        $splitter = app(MatrixSplitterService::class);
        $splitter->execute($licenses);

        $queryCount = count(DB::getQueryLog());

        // Assert: con 20 licenze, se lo splitter è ottimizzato, 
        // non dovrebbe superare le 40-50 query totali (dipende dai tuoi logger/audit)
        $this->assertLessThan(50, $queryCount, "Troppe query rilevate ($queryCount). Problema N+1!");
    }
}