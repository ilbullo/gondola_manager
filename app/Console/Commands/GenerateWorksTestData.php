<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LicenseTable;
use App\Models\WorkAssigment;
use App\Models\WorkAssignment;
use Illuminate\Queue\Worker;
use Illuminate\Support\Facades\DB;
use function Symfony\Component\Clock\now;

class GenerateWorksTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-works 
                        {licenze : Numero totale di licenze attive} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un numero di 11 lavori per le licenze attive per testare la gestione lavori.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        WorkAssignment::truncate();
        LicenseTable::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $licenze = (int) $this->argument('licenze') ?? 10;

        $licenses = \App\Models\User::inRandomOrder()->take($licenze)->get();
        foreach($licenses as $key => $license) {
            LicenseTable::factory()->create([
                'user_id'      => $license->id,
                'date'         => today(),
                'order'        => $key,
            ]);
        }

        $licenseTable = LicenseTable::all();

        foreach($licenseTable as $license) {
            // Creazione lavori di test per ogni licenza
            $this->createMorningWorkForLicense($license,'GT',1,1);
            $this->createAfternoonWorkForLicense($license,'ITC',1,2,'afternoon');
            $this->createAgencyWorkForLicense($license,'ALBA',1,3);
            $this->createCashWorkForLicense($license,1,5);
            $this->createNPWorkForLicense($license,'N',1,4);
            $this->createSharableFirstWorkForLicense($license,'BASS',1,7);
            $this->createFixedWorkForLicense($license,'ALBA',1,6);
            $this->createFixedCashWorkForLicense($license,1,8);
            $this->createCashWorkForLicense($license,1,9);
            $this->createAgencyWorkForLicense($license,'ALBA',1,10);
            $this->createCashWorkForLicense($license,1,11);



        }        
        
    }

    private function createWorkForLicense($payload)
    {
        WorkAssignment::factory()->create([
            'license_table_id'  => $payload['license']->id ?? LicenseTable::create(['date' => today()])->id,
            'agency_id'         => $payload['agency'] ?? null,
            'slot'              => $payload['slot'],
            'value'             => $payload['value'],
            'voucher'           => $payload['voucher'] ?? null,
            'timestamp'         => $payload['time'] == 'morning' ? now()->setTime(9,0) : now()->setTime(15,0),
            'slots_occupied'    => $payload['slots_occupied'] ?? 1,
            'excluded'          => $payload['excluded'] ?? false,
            'shared_from_first' => $payload['shared_from_first'] ?? false,
        ]);
    }

    private function createMorningWorkForLicense($license,$agency_code,$slots_occupied,$slot) {
        $this->createAgencyWorkForLicense($license,$agency_code,$slots_occupied,$slot,'morning');
    }

    public function createAfternoonWorkForLicense($license,$agency_code,$slots_occupied,$slot) {
        $this->createAgencyWorkForLicense($license,$agency_code,$slots_occupied,$slot,'afternoon');
    }

    private function createAgencyWorkForLicense($license,$agency_code,$slots_occupied,$slot,$period='morning') {
        $this->createWorkForLicense([
            'license'           => $license,
            'agency'            => \App\Models\Agency::where('code',$agency_code ?? 'ALBA')->get()->first()->id,
            'slots_occupied'    => $slots_occupied,
            'value'             => 'A',
            'slot'              => $slot,
            'time'              => $period
        ]);
    }

    private function createCashWorkForLicense($license,$slots_occupied,$slot,$period='morning') {
        $this->createWorkForLicense([
            'license'           => $license,
            'agency'            => null,
            'slots_occupied'    => $slots_occupied,
            'value'             => 'X',
            'slot'              => $slot,
            'time'              => $period
        ]);
    }

    private function createNPWorkForLicense($license,$value,$slots_occupied,$slot,$period='morning') {
        $this->createWorkForLicense([
            'license'           => $license,
            'agency'            => null,
            'slots_occupied'    => $slots_occupied,
            'value'             => $value,
            'slot'              => $slot,
            'time'              => $period
        ]);
    }

    private function createSharableFirstWorkForLicense($license,$agency_code,$slots_occupied,$slot,$period='morning') {
        $this->createWorkForLicense([
            'license'           => $license,
            'agency'            => \App\Models\Agency::where('code',$agency_code ?? 'ALBA')->get()->first(),
            'slots_occupied'    => $slots_occupied,
            'value'             => 'A',
            'slot'              => $slot,
            'time'              => $period,
            'shared_from_first' => true,
        ]);
    }

    private function createFixedWorkForLicense($license,$agency_code,$slots_occupied,$slot,$period='morning') {
        $this->createWorkForLicense([
            'license'           => $license,
            'agency'            => \App\Models\Agency::where('code',$agency_code ?? 'ALBA')->get()->first()->id,
            'slots_occupied'    => $slots_occupied,
            'value'             => 'A',
            'slot'              => $slot,
            'time'              => $period,
            'excluded'          => true,
        ]);
    }

    private function createFixedCashWorkForLicense($license,$slots_occupied,$slot,$period='morning') {
        $this->createWorkForLicense([
            'license'           => $license,
            'agency'            => null,
            'slots_occupied'    => $slots_occupied,
            'value'             => 'X',
            'slot'              => $slot,
            'time'              => $period,
            'excluded'          => true,
        ]);
    }

}
