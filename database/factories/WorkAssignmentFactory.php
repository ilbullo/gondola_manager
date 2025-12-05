<?php

namespace Database\Factories;

use App\Models\WorkAssignment;
use App\Models\LicenseTable;
use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkAssignmentFactory extends Factory
{
    protected $model = WorkAssignment::class;
    
    public function definition()
    {
        $value = $this->faker->randomElement(['N', 'X', 'A', 'P']);

        $agency = $value === "A"
            ? Agency::factory()
            : null;

        return [
            'license_table_id'  => LicenseTable::factory(),
            'agency_id'         => null,
            'slot'              => $this->faker->numberBetween(1, 25),
            'value'             => $this->faker->randomElement(['N', 'X', 'P']),
            'voucher'           => $this->faker->optional()->word,
            'timestamp'         => now(),
            'slots_occupied'    => $this->faker->numberBetween(1, 5),
            'excluded'          => false,
            'shared_from_first' => false,
        ];
    }

}