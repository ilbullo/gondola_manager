<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateTestData extends Command
{
    private const MORNING_WORKS = 4;
    private const AFTERNOON_WORKS = 10;
    private const TOTAL_WORKS = 14;
    private const WORKERS = 16;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-table-tests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create table test fake data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Inizio creazione dati di test...');
        $this->line('Reset database in corso...');
        $this->call('migrate:fresh');

        //creo gli utenti
        $this->line('Creazione utenti in corso...');
        for ($i = 137; $i <= 178; $i++) {
            if ($i != 140) {
                \App\Models\User::factory()->create(['license_number' => $i]);
            } else {
                \App\Models\User::factory()->create(['license_number' => $i, 'name' => "Marco Bullo", "email" => "ilbullo@gmail.com", "role" => "admin"]);
            }
        }
        $this->info('Completato');

        $this->line('Creazione licenze in servizio in corso...');
        $users = \App\Models\User::inRandomOrder()->limit(self::WORKERS)->get();

        //creo a random le licenze in servizio
        foreach ($users as $index => $user) {
            \App\Models\LicenseTable::factory()->create(['user_id' => $user->id, 'order' => $index, 'date' => today()]);
        }
        $this->info('Completato.');

        //creo i servizi del mattino
        $this->line('Creazione licenze in servizio in corso...');

        for ($i = 1; $i < self::MORNING_WORKS; $i++) {
            foreach (\App\Models\LicenseTable::all() as $license) {
                \App\Models\WorkAssignment::factory()->create([
                    'license_table_id' => $license->id,
                    'agency_id' => null,
                    'slot' => $i,
                    'value' => "X",
                    'voucher' => "",
                    'timestamp' => now()->setTime(9, 00, 00)->toString(),
                    'slots_occupied' => 1,
                    'excluded' => 0,
                    'shared_from_first' => 0,
                ]);
            }
        }

        // creo i servizi del pomeriggio

        for ($i = self::MORNING_WORKS; $i <= self::AFTERNOON_WORKS; $i++) {
            foreach (\App\Models\LicenseTable::all() as $license) {
                \App\Models\WorkAssignment::factory()->create([
                    'license_table_id' => $license->id,
                    'agency_id' => null,
                    'slot' => $i,
                    'value' => "X",
                    'voucher' => "",
                    'timestamp' => now()->setTime(15, 30, 00),
                    'slots_occupied' => 1,
                    'excluded' => 0,
                    'shared_from_first' => 0,
                ]);
            }
        }

        $this->info('Completato.');

        $this->line("Creazione dei lavori agenzia in corso....");
        $this->call('db:seed');

        for ($i = 1; $i <= self::TOTAL_WORKS; $i++) {

            $start_from = random_int(1, self::WORKERS);
            $maxPossible = self::WORKERS - $start_from + 1;
            $boats = random_int(1, $maxPossible);
            $agency = \App\Models\Agency::inRandomOrder()->get()->first();
            $start_from_first = random_int(1,5);
            $excluded = random_int(1,5);
            // Aggiorna i WorkAssignment
            \App\Models\WorkAssignment::query()
                ->where('slot', $i) // Filtra per lo slot corrente del ciclo
                ->whereHas('licenseTable', function ($query) use ($start_from, $boats) {
                    // Filtra in base all'ordine della tabella licenze collegata
                    $query->whereBetween('order', [$start_from, $start_from + $boats]);
                })
                ->update([
                    'value'             => 'A',
                    'agency_id'         => $agency->id,
                    'shared_from_first' => ($start_from_first == 4 ? true : false),
                    'excluded'          => ($start_from_first == 4 && $excluded == 1) ? true : false,

                ]);
        }
        $this->info('Completato.');
                $this->line("Creazione dei lavori P e N in corso....");
        //seleziono a random le X e le sostituisco a random con P e N
        $cashWorks =\App\Models\WorkAssignment::where('value',"X")->get();
        foreach($cashWorks as $work) {
            $selector = random_int(1,15);
            if (($selector >= 1) && ($selector < 3)) {
                $work->value = "N";
                $work->save();
            }
            elseif ($selector == 7) {
                $work->value = "P";
                $work->save();
            }
        }
        $this->info('Completato.');

    }
}
