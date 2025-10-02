<?php

namespace Database\Factories;

use App\Models\DriverTraining;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DriverTraining>
 */
class DriverTrainingFactory extends Factory
{
    protected $model = DriverTraining::class;

    public function definition(): array
    {
        $issuedAt = $this->faker->dateTimeBetween('-2 years', 'now');
        $expiresAt = (clone $issuedAt)->modify('+1 year');

        return [
            'name' => $this->faker->randomElement([
                'Seguridad vial avanzada',
                'Manejo defensivo',
                'Transporte de materiales peligrosos',
                'Primeros auxilios',
            ]),
            'provider' => $this->faker->company(),
            'issued_at' => $issuedAt,
            'expires_at' => $expiresAt,
            'hours' => $this->faker->numberBetween(8, 40),
            'status' => $this->faker->randomElement(['valid', 'expired', 'in_progress']),
            'certificate_url' => null,
        ];
    }
}
