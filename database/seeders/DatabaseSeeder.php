<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        /*User::factory()->create([
            'name' => 'Test User',
            'email' => 'ilbullo@gmail.com',
            'password'  =>'password',
            'license_number'=> 140
        ]);*/

        //User::factory(40)->create();

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

        foreach($agencies as $name => $code) {
            \App\Models\Agency::factory()->create(['name' => $name,'code' => $code]);
        }
        
    }
}
