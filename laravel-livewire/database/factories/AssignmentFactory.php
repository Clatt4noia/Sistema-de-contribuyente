<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Truck;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        $start = Carbon::now()->addDays($this->faker->numberBetween(-5, 5));
        $end = (clone $start)->addDays($this->faker->numberBetween(1, 3));

        return [
            'truck_id' => Truck::factory(),
            'driver_id' => Driver::factory(),
            'order_id' => Order::factory(),
            'start_date' => $start,
            'end_date' => $end,
            'status' => $this->faker->randomElement(['scheduled', 'in_progress', 'completed']),
            'description' => $this->faker->sentence(6),
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
        ];
    }
}
