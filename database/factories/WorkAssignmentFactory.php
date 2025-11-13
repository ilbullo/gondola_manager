<?php

namespace Database\Factories;

use App\Models\WorkAssignment;
use App\Models\User;
use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkAssignmentFactory extends Factory
{
    protected $model = WorkAssignment::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'agency_id' => Agency::factory(),
            'slot' => $this->faker->numberBetween(1, 5),
            'value' => $this->faker->word,
            'voucher' => $this->faker->uuid,
            'timestamp' => $this->faker->dateTime,
            'slots_occupied' => $this->faker->numberBetween(1, 3),
        ];
    }
}