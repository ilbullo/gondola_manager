<?php

namespace Database\Factories;

use App\Models\LegalAcceptance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LegalAcceptance>
 */
class LegalAcceptanceFactory extends Factory
{
    protected $model = LegalAcceptance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Crea automaticamente un utente se non fornito
            'version' => '1.0.0',
            'accepted_at' => now(),
            'ip_address' => $this->faker->ipv4,
        ];
    }

    /**
     * Stato per l'accettazione dei Termini di Servizio.
     */
    public function tos(): static
    {
        return $this->state(fn (array $attributes) => [
            'version' => config('app_settings.legal.tos_version', '1.0'),
        ]);
    }

    /**
     * Stato per simulare un'accettazione vecchia (utile per test di re-auth).
     */
    public function outdated(): static
    {
        return $this->state(fn (array $attributes) => [
            'version' => '0.0.1',
            'accepted_at' => now()->subYear(),
        ]);
    }
}