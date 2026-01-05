<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Models\{LicenseTable,WorkAssignment};

class ResetLicenseTable extends Command
{
    /**
     * Il nome e la firma del comando (come lo chiamerai da terminale).
     */
    protected $signature = 'app:reset-license-table';

    /**
     * La descrizione del comando.
     */
    protected $description = 'Esegue il truncate della tabella LicenseTable';

    /**
     * Esegue la logica del comando.
     */
    public function handle()
    {
        $this->line("----------------------------------------------------");
        $this->info('Orario: ' . now()->toDateTimeString());
        $this->info('Inizio truncate della tabella LicenseTable...');

        // Disabilitiamo temporaneamente i vincoli delle chiavi esterne per evitare errori
        Schema::disableForeignKeyConstraints();
        LicenseTable::truncate();
        $this->info('Tabella LicenseTable svuotata con successo!');
        
        $this->info('Inizio truncate della tabella WorkAssigment...');
        WorkAssignment::truncate();
        
        Schema::enableForeignKeyConstraints();
        $this->info('Tabella WorkAssigment svuotata con successo!');
        $this->line("----------------------------------------------------");
    }
}