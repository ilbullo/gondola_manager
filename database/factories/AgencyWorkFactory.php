<?php

namespace Database\Factories;

use App\Models\AgencyWork;
use App\Models\User;
use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgencyWorkFactory extends Factory
{
    protected $model = AgencyWork::class;

    public function definition()
    {
        return [
            'date' => $this->faker->date,
            'user_id' => User::factory(),
            'agency_id' => Agency::factory(),
            'voucher' => $this->faker->uuid,
            'amount'   => config('app_settings.works.default_amount')
        ];
    }
}