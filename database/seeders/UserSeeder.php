<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 137; $i <= 178; $i++) {
            if ($i != 140) {
                \App\Models\User::factory()->create(['license_number' => $i]);
            } else {
                \App\Models\User::factory()->create(['license_number' => $i, 'name' => "Marco Bullo", "email" => "ilbullo@gmail.com", "role" => "admin"]);
            }
        }
    }
}
