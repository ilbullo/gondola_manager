<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateTestData extends Command
{
    // Costanti per la creazione di dati di test
    private const MORNING_WORKS = 4;
    private const AFTERNOON_WORKS = 10;
    private const TOTAL_WORKS = 14;
    private const WORKERS = 16;

    /**
     * Nome e signature del comando Artisan.
     *
     * @var string
     */
    protected $signature = 'app:create-table-tests';

    /**
     * Descrizione del comando.
     *
     * @var string
     */
    protected $description = 'Crea dati di test fittizi per utenti, agenzie e lavori.';

    /**
     * Esecuzione del comando.
     */
    public function handle()
    {
        $this->info('Inizio creazione dati di test...');

        // Resetta il database
        $this->line('Reset database in corso...');
        $this->call('migrate:fresh');

        // Lista utenti da creare [license_number, nome completo]
        $users = [
            [137,"ROSSO GIORGIO"], [138,"MIANI MARCO"], [139,"MARIUZZO ALESSANDRO"], 
            [140,"BULLO MARCO"], [141,"TAGLIAPIETRA GIUSEPPE"], [142,"ANDRIUZZI LUCA"], 
            [143,"MARESCA NICOLA"], [144,"PENGO FRANCESCO"], [145,"NARDIN GIORGIO"], 
            [146,"ZANCHI MAURO"], [147,"TREVISAN ANDREA"], [148,"FALCER LORENZO"], 
            [149,"VIANELLO GIANNI"], [150,"CAPOLLA CRISTIANO"], [151,"CASIMIRO SERGIO"], 
            [152,"RAGAZZI MARCO"], [153,"MAZZUCCATO RICCARDO"], [154,"PEDRALI MARCO"], 
            [155,"COLLAVINI MATTIA"], [156,"SANTINI ALESSANDRO"], [157,"PAVEGGIO DANIELE"], 
            [158,"BALLARIN ROBERTO"], [159,"GABRIELI SOPPELSA STEFANO"], [160,"TEDESCHI EROS"], 
            [161,"PENZO FRANCESCO"], [162,"RUSSO LORENZO"], [163,"FASAN RICCARDO"], 
            [164,"GALANTE MATTEO"], [165,"TONELLO MASSIMO"], [166,"PERIOTTO DENIS"], 
            [167,"DIANA VITTORIO"], [168,"ZANCHI EUGENIO"], [169,"ZANNI UMBERTO"], 
            [170,"MANZONI NICOLA"], [171,"FONGHER ALESSANDRO"], [172,"BATTISTON SIMONE"], 
            [173,"RIZZO ANDREA"], [174,"CAVAGNIS MARCO"], [175,"CAVAGNIS ALESSANDRO"], 
            [176,"BOLDRIN STEFANO"], [177,"CARLOTTO MAURIZIO"], [178,"TABACCO GABRIELE"]
        ];

        // Lista agenzie [nome => codice]
        $agencies = [
            'ALBATRAVEL' => 'ALBA', "ITC" => "ITC", "GONDOLIERI TRAVEL" => "GT", 
            "VENEZIA SERVICE" => "VENS", "BASSANI" => "BASS", "BUCINTORO" => "BUCI", 
            "CONTIKI" => "CONT", "GLOBUS" => "GLOB", "COSMOS" => "COSM", 
            "TRUMPY" => "TRUM", "CLEMENTSON" => "CLEM", "INSIGHT" => "INSI", 
            "TURIVE" => "TUVE"
        ];

        $this->line('Creazione utenti e agenzie in corso...');

        // Creazione utenti
        foreach($users as $user){
            // Determina il ruolo
            if (in_array($user[0], [143, 172, 166, 162])) {
                $role = \App\Enums\UserRole::BANCALE->value;
            } elseif ($user[0] == 140) {
                $role = \App\Enums\UserRole::ADMIN->value;
            } else {
                $role = \App\Enums\UserRole::USER->value;
            }

            \App\Models\User::factory()->create([
                'license_number' => $user[0],
                'name'           => $user[1],
                'email'          => $user[0]. '@dogana.it',
                'role'           => $role
            ]);
        }

        // Creazione agenzie
        foreach($agencies as $name => $code) {
            \App\Models\Agency::factory()->create([
                'name' => $name,
                'code' => $code
            ]);
        }

        $this->info('Utenti e agenzie creati con successo.');

        // Creazione licenze per utenti selezionati casualmente
        $this->line('Creazione licenze in servizio in corso...');
        $users = \App\Models\User::inRandomOrder()->limit(self::WORKERS)->get();
        foreach ($users as $index => $user) {
            \App\Models\LicenseTable::factory()->create([
                'user_id' => $user->id,
                'order' => $index,
                'date' => today()
            ]);
        }
        $this->info('Licenze create con successo.');

        // Creazione lavori del mattino
        $this->line('Creazione lavori del mattino in corso...');
        for ($i = 1; $i < self::MORNING_WORKS; $i++) {
            foreach (\App\Models\LicenseTable::all() as $license) {
                \App\Models\WorkAssignment::factory()->create([
                    'license_table_id' => $license->id,
                    'agency_id' => null,
                    'slot' => $i,
                    'value' => "X",
                    'voucher' => "",
                    'timestamp' => now()->setTime(9, 0, 0),
                    'slots_occupied' => 1,
                    'excluded' => false,
                    'shared_from_first' => false,
                ]);
            }
        }

        // Creazione lavori del pomeriggio
        $this->line('Creazione lavori del pomeriggio in corso...');
        for ($i = self::MORNING_WORKS; $i <= self::AFTERNOON_WORKS; $i++) {
            foreach (\App\Models\LicenseTable::all() as $license) {
                \App\Models\WorkAssignment::factory()->create([
                    'license_table_id' => $license->id,
                    'agency_id' => null,
                    'slot' => $i,
                    'value' => "X",
                    'voucher' => "",
                    'timestamp' => now()->setTime(15, 30, 0),
                    'slots_occupied' => 1,
                    'excluded' => false,
                    'shared_from_first' => false,
                ]);
            }
        }
        $this->info('Lavori mattina e pomeriggio creati.');

        // Creazione lavori agenzia
        $this->line("Creazione dei lavori agenzia in corso...");
        $this->call('db:seed');

        for ($i = 1; $i <= self::TOTAL_WORKS; $i++) {
            $start_from = random_int(1, self::WORKERS);
            $maxPossible = self::WORKERS - $start_from + 1;
            $boats = random_int(1, $maxPossible);
            $agency = \App\Models\Agency::inRandomOrder()->first();
            $start_from_first = random_int(1,5);
            $excluded = random_int(1,5);

            \App\Models\WorkAssignment::query()
                ->where('slot', $i)
                ->whereHas('licenseTable', function ($query) use ($start_from, $boats) {
                    $query->whereBetween('order', [$start_from, $start_from + $boats]);
                })
                ->update([
                    'value'             => 'A',
                    'agency_id'         => $agency->id,
                    'shared_from_first' => ($start_from_first == 4),
                    'excluded'          => ($start_from_first == 4 && $excluded == 1),
                ]);
        }
        $this->info('Lavori agenzia creati.');

        // Sostituzione casuale di alcuni lavori X con P o N
        $this->line("Creazione dei lavori P e N in corso...");
        $cashWorks = \App\Models\WorkAssignment::where('value', "X")->get();
        foreach($cashWorks as $work) {
            $selector = random_int(1, 15);
            if ($selector >= 1 && $selector < 3) {
                $work->value = "N";
                $work->save();
            } elseif ($selector == 7) {
                $work->value = "P";
                $work->save();
            }
        }
        $this->info('Lavori P e N creati.');

        $this->info('Generazione dati di test completata.');
    }
}
