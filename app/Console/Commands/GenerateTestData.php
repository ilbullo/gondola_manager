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
            [
                'name' => 'ROSSO GIORGIO',
                'email' => '137@dogana.it',
                'type' => 'titolare',
                'license_number' => 137,
            ],
            [
                'name' => 'MIANI MARCO',
                'email' => '138@dogana.it',
                'type' => 'titolare',
                'license_number' => 138,
            ],
            [
                'name' => 'MARIUZZO ALESSANDRO',
                'email' => '139@dogana.it',
                'type' => 'titolare',
                'license_number' => 139,
            ],
            [
                'name' => 'BULLO MARCO',
                'email' => '140@dogana.it',
                'type' => 'titolare',
                'license_number' => 140,
                'role'  => 'admin'
            ],
            [
                'name' => 'TAGLIAPIETRA GIUSEPPE',
                'email' => '141@dogana.it',
                'type' => 'titolare',
                'license_number' => 141,
            ],
            [
                'name' => 'ANDRIUZZI LUCA',
                'email' => '142@dogana.it',
                'type' => 'titolare',
                'license_number' => 142,
            ],
            [
                'name' => 'MARESCA NICOLA',
                'email' => '143@dogana.it',
                'type' => 'titolare',
                'license_number' => 143,
                'role'  => 'bancale'

            ],
            [
                'name' => 'PENGO FRANCESCO',
                'email' => '144@dogana.it',
                'type' => 'titolare',
                'license_number' => 144,
            ],
            [
                'name' => 'NARDIN GIORGIO',
                'email' => '145@dogana.it',
                'type' => 'titolare',
                'license_number' => 145,
            ],
            [
                'name' => 'ZANCHI MAURO',
                'email' => '146@dogana.it',
                'type' => 'titolare',
                'license_number' => 146,
            ],
            [
                'name' => 'TREVISAN ANDREA',
                'email' => '147@dogana.it',
                'type' => 'titolare',
                'license_number' => 147,
            ],
            [
                'name' => 'FALCER LORENZO',
                'email' => '148@dogana.it',
                'type' => 'titolare',
                'license_number' => 148,
            ],
            [
                'name' => 'VIANELLO GIANNI',
                'email' => '149@dogana.it',
                'type' => 'titolare',
                'license_number' => 149,
            ],
            [
                'name' => 'CAPOLLA CRISTIANO',
                'email' => '150@dogana.it',
                'type' => 'titolare',
                'license_number' => 150,
            ],
            [
                'name' => 'CASIMIRO SERGIO',
                'email' => '151@dogana.it',
                'type' => 'titolare',
                'license_number' => 151,
                'role'  => 'bancale'

            ],
            [
                'name' => 'RAGAZZI MARCO',
                'email' => '152@dogana.it',
                'type' => 'titolare',
                'license_number' => 152,
            ],
            [
                'name' => 'MAZZUCCATO RICCARDO',
                'email' => '153@dogana.it',
                'type' => 'titolare',
                'license_number' => 153,
            ],
            [
                'name' => 'PEDRALI MARCO',
                'email' => '154@dogana.it',
                'type' => 'titolare',
                'license_number' => 154,
            ],
            [
                'name' => 'COLLAVINI MATTIA',
                'email' => '155@dogana.it',
                'type' => 'titolare',
                'license_number' => 155,
            ],
            [
                'name' => 'SANTINI ALESSANDRO',
                'email' => '156@dogana.it',
                'type' => 'titolare',
                'license_number' => 156,
            ],
            [
                'name' => 'PAVEGGIO DANIELE',
                'email' => '157@dogana.it',
                'type' => 'titolare',
                'license_number' => 157,
            ],
            [
                'name' => 'BALLARIN ROBERTO',
                'email' => '158@dogana.it',
                'type' => 'titolare',
                'license_number' => 158,
            ],
            [
                'name' => 'GABRIELI SOPPELSA STEFANO',
                'email' => '159@dogana.it',
                'type' => 'titolare',
                'license_number' => 159,
            ],
            [
                'name' => 'TEDESCHI EROS',
                'email' => '160@dogana.it',
                'type' => 'titolare',
                'license_number' => 160,
            ],
            [
                'name' => 'PENZO FRANCESCO',
                'email' => '161@dogana.it',
                'type' => 'titolare',
                'license_number' => 161,
            ],
            [
                'name' => 'RUSSO LORENZO',
                'email' => '162@dogana.it',
                'type' => 'titolare',
                'license_number' => 162,
                'role'  => 'bancale'

            ],
            [
                'name' => 'FASAN RICCARDO',
                'email' => '163@dogana.it',
                'type' => 'titolare',
                'license_number' => 163,
            ],
            [
                'name' => 'GALANTE MATTEO',
                'email' => '164@dogana.it',
                'type' => 'titolare',
                'license_number' => 164,
            ],
            [
                'name' => 'TONELLO MASSIMO',
                'email' => '165@dogana.it',
                'type' => 'titolare',
                'license_number' => 165,
            ],
            [
                'name' => 'PERIOTTO DENIS',
                'email' => '166@dogana.it',
                'type' => 'titolare',
                'license_number' => 166,
                'role'  => 'bancale'
            ],
            [
                'name' => 'DIANA VITTORIO',
                'email' => '167@dogana.it',
                'type' => 'titolare',
                'license_number' => 167,
            ],
            [
                'name' => 'ZANCHI EUGENIO',
                'email' => '168@dogana.it',
                'type' => 'titolare',
                'license_number' => 168,
            ],
            [
                'name' => 'ZANNI UMBERTO',
                'email' => '169@dogana.it',
                'type' => 'titolare',
                'license_number' => 169,
            ],
            [
                'name' => 'MANZONI NICOLA',
                'email' => '170@dogana.it',
                'type' => 'titolare',
                'license_number' => 170,
            ],
            [
                'name' => 'FONGHER ALESSANDRO',
                'email' => '171@dogana.it',
                'type' => 'titolare',
                'license_number' => 171,
            ],
            [
                'name' => 'BATTISTON SIMONE',
                'email' => '172@dogana.it',
                'type' => 'titolare',
                'license_number' => 172,
                'role'  => 'bancale'
            ],
            [
                'name' => 'RIZZO ANDREA',
                'email' => '173@dogana.it',
                'type' => 'titolare',
                'license_number' => 173,
            ],
            [
                'name' => 'CAVAGNIS MARCO',
                'email' => '174@dogana.it',
                'type' => 'titolare',
                'license_number' => 174,
            ],
            [
                'name' => 'CAVAGNIS ALESSANDRO',
                'email' => '175@dogana.it',
                'type' => 'titolare',
                'license_number' => 175,
                'role'  => 'bancale'
            ],
            [
                'name' => 'BOLDRIN STEFANO',
                'email' => '176@dogana.it',
                'type' => 'titolare',
                'license_number' => 176,
            ],
            [
                'name' => 'CARLOTTO MAURIZIO',
                'email' => '177@dogana.it',
                'type' => 'titolare',
                'license_number' => 177,
            ],
            [
                'name' => 'TABACCO GABRIELE',
                'email' => '178@dogana.it',
                'type' => 'titolare',
                'license_number' => 178,
            ],
            [
                'name' => 'ROSSO GIACOMO',
                'email' => '516@dogana.it',
                'type' => 'sostituto',
                'license_number' => 516,
            ],
            [
                'name' => 'FABRIS ALVISE',
                'email' => '970@dogana.it',
                'type' => 'sostituto',
                'license_number' => 970,
            ],
            [
                'name' => 'ZILIO GIANMARCO',
                'email' => '952@dogana.it',
                'type' => 'sostituto',
                'license_number' => 952,
            ],
            [
                'name' => 'MARESCA GIACOMO',
                'email' => '596@dogana.it',
                'type' => 'sostituto',
                'license_number' => 596,
            ],
            [
                'name' => 'RIZZO GABRIEL',
                'email' => '517@dogana.it',
                'type' => 'sostituto',
                'license_number' => 517,
            ],
            [
                'name' => 'CAPOLLA LEONARDO',
                'email' => '563@dogana.it',
                'type' => 'sostituto',
                'license_number' => 563,
            ],
            [
                'name' => 'SANTINI SAMUEL',
                'email' => '979@dogana.it',
                'type' => 'sostituto',
                'license_number' => 979,
            ],
            [
                'name' => 'LAZZARI IVAN',
                'email' => '986@dogana.it',
                'type' => 'sostituto',
                'license_number' => 986,
            ],
            [
                'name' => 'RUMONATO ENRICO',
                'email' => '494@dogana.it',
                'type' => 'sostituto',
                'license_number' => 494,
            ],
            [
                'name' => 'TESAN MICHAEL',
                'email' => '502@dogana.it',
                'type' => 'sostituto',
                'license_number' => 502,
            ],
            [
                'name' => 'PIZZO MATTEO',
                'email' => '504@dogana.it',
                'type' => 'sostituto',
                'license_number' => 504,
            ],
            [
                'name' => 'GREIFENBERG ENRICO',
                'email' => '507@dogana.it',
                'type' => 'sostituto',
                'license_number' => 507,
            ],
            [
                'name' => 'MERLO RICCARDO',
                'email' => '541@dogana.it',
                'type' => 'sostituto',
                'license_number' => 541,
            ],
            [
                'name' => 'BON NICOLA',
                'email' => '542@dogana.it',
                'type' => 'sostituto',
                'license_number' => 542,
            ],
            [
                'name' => 'FALCER ALESSANDRO',
                'email' => '543@dogana.it',
                'type' => 'sostituto',
                'license_number' => 543,
            ],
            [
                'name' => 'BUSETTO MARCO',
                'email' => '567@dogana.it',
                'type' => 'sostituto',
                'license_number' => 567,
            ],
            [
                'name' => 'DABALA ADRIANO',
                'email' => '591@dogana.it',
                'type' => 'sostituto',
                'license_number' => 591,
            ],
            [
                'name' => 'PERIOTTO ROSSANO',
                'email' => '611@dogana.it',
                'type' => 'sostituto',
                'license_number' => 611,
            ],
            [
                'name' => 'BENEDETTI RICCARDO',
                'email' => '697@dogana.it',
                'type' => 'sostituto',
                'license_number' => 697,
            ],
            [
                'name' => 'SENO DORIANO',
                'email' => '700@dogana.it',
                'type' => 'sostituto',
                'license_number' => 700,
            ],
            [
                'name' => 'TAGLIAPIETRA ANDREA',
                'email' => '710@dogana.it',
                'type' => 'sostituto',
                'license_number' => 710,
            ],
            [
                'name' => 'MIRRA FABIO',
                'email' => '726@dogana.it',
                'type' => 'sostituto',
                'license_number' => 726,
            ],
            [
                'name' => 'MEMO LUCA',
                'email' => '792@dogana.it',
                'type' => 'sostituto',
                'license_number' => 792,
            ],
            [
                'name' => 'PERIOTTO KANGI',
                'email' => '910@dogana.it',
                'type' => 'sostituto',
                'license_number' => 910,
            ],
            [
                'name' => 'CARLOTTO GIACOMO',
                'email' => '945@dogana.it',
                'type' => 'sostituto',
                'license_number' => 945,
            ],
            [
                'name' => 'LUCIC MATTEO',
                'email' => '955@dogana.it',
                'type' => 'sostituto',
                'license_number' => 955,
            ],
            [
                'name' => 'TAGLIAPIETRA ALARICO',
                'email' => '957@dogana.it',
                'type' => 'sostituto',
                'license_number' => 957,
            ],
            [
                'name' => 'PENZO MATTIA',
                'email' => '980@dogana.it',
                'type' => 'sostituto',
                'license_number' => 980,
            ],
            [
                'name' => 'BIANCHI ALESSANDRO',
                'email' => '718@dogana.it',
                'type' => 'sostituto',
                'license_number' => 718,
            ],
            [
                'name' => 'BEPI',
                'email' => '513@dogana.it',
                'type' => 'sostituto',
                'license_number' => 513,
            ],
        ];

        // Lista agenzie [nome => codice]
        $agencies = [
            'ALBATRAVEL' => 'ALBA',
            "ITC" => "ITC",
            "GONDOLIERI TRAVEL" => "GT",
            "VENEZIA SERVICE" => "VENS",
            "BASSANI" => "BASS",
            "BUCINTORO" => "BUCI",
            "CONTIKI" => "CONT",
            "GLOBUS" => "GLOB",
            "COSMOS" => "COSM",
            "TRUMPY" => "TRUM",
            "CLEMENTSON" => "CLEM",
            "INSIGHT" => "INSI",
            "TURIVE" => "TUVE"
        ];

        $this->line('Creazione utenti e agenzie in corso...');

        // Creazione utenti
        foreach ($users as $user) {
            \App\Models\User::factory()->create($user);
        }

        // Creazione agenzie
        foreach ($agencies as $name => $code) {
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
            $start_from_first = random_int(1, 5);
            $excluded = random_int(1, 5);

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
        foreach ($cashWorks as $work) {
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
