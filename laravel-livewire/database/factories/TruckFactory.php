<?php

namespace Database\Factories;

use App\Models\Truck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Truck>
 */
class TruckFactory extends Factory
{
    protected $model = Truck::class;

    public function definition(): array
    {
        $lastMaintenance = $this->faker->dateTimeBetween('-6 months', '-1 month');

        return [
            'plate_number' => strtoupper($this->faker->bothify('???-####')),
            'brand' => $this->faker->randomElement(['Volvo', 'Scania', 'Mercedes-Benz', 'MAN']),
            'model' => $this->faker->randomElement(['FH', 'R-Series', 'Actros', 'TGX']) . ' ' . $this->faker->numberBetween(2000, 2024),
            'year' => $this->faker->numberBetween(2018, 2024),
            'type' => $this->faker->randomElement(['semi-trailer', 'straight', 'box', 'flatbed']),
            'capacity' => $this->faker->numberBetween(10, 30) * 1000,
            'mileage' => $this->faker->numberBetween(5000, 250000),
            'status' => $this->faker->randomElement(['available', 'maintenance', 'in_use']),
            'last_maintenance' => $lastMaintenance,
            'next_maintenance' => (clone $lastMaintenance)->modify('+3 months'),
            'technical_details' => $this->faker->sentence(8),
            'maintenance_interval_days' => 90,
            'maintenance_mileage_threshold' => 15000,
            'last_maintenance_mileage' => $this->faker->numberBetween(0, 200000),
        ];
    }
}
