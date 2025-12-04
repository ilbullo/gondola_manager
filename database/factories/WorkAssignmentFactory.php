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
        if ($value === "A") {
            $agency = Agency::factory();
        }

        return [
            'license_table_id' => LicenseTable::factory(),
            'agency_id' => $agency->id ?? null,
            'slot' => $this->faker->numberBetween(1, 25),
            'value' => $value,
            'voucher' => $this->faker->optional()->word,
            'timestamp' => now(),
            'slots_occupied' => $this->faker->numberBetween(1, 5),
            'excluded' => $value === "A" ? $this->faker->boolean : 0,
            'shared_from_first' => $value === "A" ? $this->faker->boolean : 0,
        ];
    }
}