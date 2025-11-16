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
        return [
            'license_table_id' => LicenseTable::factory(),
            'agency_id' => Agency::factory(),
            'slot' => $this->faker->numberBetween(1, 25),
            'value' => $this->faker->word,
            'voucher' => $this->faker->optional()->word,
            'timestamp' => now(),
            'slots_occupied' => $this->faker->numberBetween(1, 5),
            'excluded' => $this->faker->boolean,
            'shared_from_first' => $this->faker->boolean,
        ];
    }
}