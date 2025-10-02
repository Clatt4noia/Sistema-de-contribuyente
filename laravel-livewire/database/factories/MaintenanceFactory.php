<?php

namespace Database\Factories;

use App\Models\Maintenance;
use App\Models\Truck;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Maintenance>
 */
class MaintenanceFactory extends Factory
{
    protected $model = Maintenance::class;

    public function definition(): array
    {
        $date = Carbon::now()->addDays($this->faker->numberBetween(-60, 30));

        return [
            'truck_id' => Truck::factory(),
            'maintenance_date' => $date,
            'maintenance_type' => $this->faker->randomElement(['Preventivo', 'Correctivo', 'Inspección']),
            'cost' => $this->faker->randomFloat(2, 100, 5000),
            'status' => $this->faker->randomElement(['scheduled', 'in_progress', 'completed']),
            'description' => $this->faker->sentence(8),
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
        ];
    }
}
