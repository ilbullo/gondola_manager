<?php

namespace Tests\Feature\Console\Commands;

use App\Models\LicenseTable;
use App\Models\WorkAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ResetLicenseTableTest extends TestCase
{
    // Questo trait resetta il database di test prima di ogni esecuzione
    use RefreshDatabase;

    #[Test]
    public function it_truncates_license_and_work_assignment_tables()
    {
        // 1. Preparazione: Creiamo dei dati finti nelle tabelle
        LicenseTable::factory()->count(3)->create();
        WorkAssignment::factory()->create(['license_table_id' => LicenseTable::inRandomOrder()->first(),'slot' => 1]);
        WorkAssignment::factory()->create(['license_table_id' => LicenseTable::inRandomOrder()->first(),'slot' => 2]);
        WorkAssignment::factory()->create(['license_table_id' => LicenseTable::inRandomOrder()->first(),'slot' => 3]);
        WorkAssignment::factory()->create(['license_table_id' => LicenseTable::inRandomOrder()->first(),'slot' => 4]);
        WorkAssignment::factory()->create(['license_table_id' => LicenseTable::inRandomOrder()->first(),'slot' => 5]);

        // Verifichiamo che i dati siano effettivamente presenti
        $this->assertEquals(3, LicenseTable::count());
        $this->assertEquals(5, WorkAssignment::count());

        // 2. Esecuzione: Lanciamo il comando Artisan
        $this->artisan('app:reset-license-table')
            // Verifichiamo che l'output nel terminale sia corretto
            ->expectsOutput('Inizio truncate della tabella LicenseTable...')
            ->expectsOutput('Tabella LicenseTable svuotata con successo!')
            ->expectsOutput('Inizio truncate della tabella WorkAssigment...')
            ->expectsOutput('Tabella WorkAssigment svuotata con successo!')
            ->assertExitCode(0);

        // 3. Verifica: Le tabelle devono essere vuote (0 record)
        $this->assertEquals(0, LicenseTable::count());
        $this->assertEquals(0, WorkAssignment::count());
    }

    #[Test]
    public function it_works_correctly_even_if_tables_are_already_empty()
    {
        // Verifichiamo che il comando non dia errori se le tabelle sono già vuote
        $this->artisan('app:reset-license-table')
            ->assertExitCode(0);

        $this->assertEquals(0, LicenseTable::count());
        $this->assertEquals(0, WorkAssignment::count());
    }

    #[Test]
    public function il_comando_viene_eseguito_correttamente_alle_23()
    {
        // 1. Popoliamo le tabelle con dati finti
        LicenseTable::factory()->count(5)->create();
        WorkAssignment::factory()->create(['license_table_id' => LicenseTable::inRandomOrder()->first(),'slot' => 1]);
        WorkAssignment::factory()->create(['license_table_id' => LicenseTable::inRandomOrder()->first(),'slot' => 1]);
        WorkAssignment::factory()->create(['license_table_id' => LicenseTable::inRandomOrder()->first(),'slot' => 1]);
        WorkAssignment::factory()->create(['license_table_id' => LicenseTable::inRandomOrder()->first(),'slot' => 1]);
        WorkAssignment::factory()->create(['license_table_id' => LicenseTable::inRandomOrder()->first(),'slot' => 1]);

        // 2. Viaggiamo nel tempo fino alle 23:00
        $this->travelTo(now()->setTime(23, 0, 0));

        // 3. Eseguiamo il comando artisan
        $this->artisan('app:reset-license-table')
             ->assertSuccessful();

        // 4. Verifichiamo che le tabelle siano vuote
        $this->assertEquals(0, LicenseTable::count());
        $this->assertEquals(0, WorkAssignment::count());
    }

    #[Test]
    public function lo_scheduler_ha_pianificato_il_comando_all_ora_giusta()
    {
        // Questo test verifica se lo scheduler di Laravel "conosce" il comando alle 23:00
        $this->travelTo(now()->setTime(23, 0, 0));

        // Recuperiamo tutti i comandi pianificati e cerchiamo il nostro
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events());
        
        $commandExists = $events->contains(function ($event) {
            return str_contains($event->command, 'app:reset-license-table');
        });

        $this->assertTrue($commandExists, "Il comando non è presente nello scheduler.");
    }
}