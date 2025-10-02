<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $pickup = $this->faker->dateTimeBetween('-1 week', '+1 week');
        $delivery = (clone $pickup)->modify('+'. $this->faker->numberBetween(1, 5) .' days');

        return [
            'client_id' => Client::factory(),
            'reference' => strtoupper($this->faker->bothify('ORD-#####')),
            'origin' => $this->faker->city(),
            'destination' => $this->faker->city(),
            'pickup_date' => $pickup,
            'delivery_date' => $delivery,
            'status' => $this->faker->randomElement(['pending', 'en_route', 'delivered']),
            'cargo_details' => $this->faker->sentence(6),
            'estimated_distance_km' => $this->faker->numberBetween(100, 1500),
            'estimated_duration_hours' => $this->faker->randomFloat(2, 2, 72),
            'notes' => $this->faker->sentence(),
        ];
    }
}
