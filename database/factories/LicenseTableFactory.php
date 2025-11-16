<?php

namespace Database\Factories;

use App\Models\LicenseTable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseTableFactory extends Factory
{
    protected $model = LicenseTable::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'date'    => $this->faker->date,
            'order'   => $this->faker->numberBetween(1, 25)
        ];
    }
}