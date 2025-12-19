<?php

namespace Database\Factories;

use App\Models\CargoType;
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

        $freight = round($this->faker->randomFloat(2, 400, 7500), 2);
        $fuel = round($freight * 0.18, 2);
        $tolls = round(max($freight * 0.05, 35), 2);
        $others = round(max($freight * 0.07, 30), 2);
        $estimatedCost = round($freight + $fuel + $tolls + $others, 2);

        $cargoTypeId = CargoType::query()->inRandomOrder()->value('id');

        return [
            'client_id' => Client::factory(),
            'cargo_type_id' => $cargoTypeId,
            'reference' => strtoupper($this->faker->bothify('ORD-#####')),
            'origin' => $this->faker->city(),
            'origin_ubigeo' => $this->faker->numerify('######'),
            'origin_address' => $this->faker->streetAddress(),
            'destination' => $this->faker->city(),
            'destination_ubigeo' => $this->faker->numerify('######'),
            'destination_address' => $this->faker->streetAddress(),
            'pickup_date' => $pickup,
            'delivery_date' => $delivery,
            'status' => $this->faker->randomElement(['pending', 'en_route', 'delivered']),
            'cargo_details' => $this->faker->sentence(6),
            'cargo_weight_kg' => $this->faker->randomFloat(2, 150, 12000),
            'cargo_volume_m3' => $this->faker->randomFloat(2, 10, 250),
            'total_packages' => $this->faker->numberBetween(1, 80),
            'destinatario_document_type' => '6',
            'destinatario_document_number' => $this->faker->numerify('###########'),
            'destinatario_name' => $this->faker->company(),
            'estimated_distance_km' => $this->faker->numberBetween(100, 1500),
            'estimated_duration_hours' => $this->faker->randomFloat(2, 2, 72),
            'estimated_cost' => $estimatedCost,
            'cost_breakdown' => [
                'freight' => $freight,
                'fuel' => $fuel,
                'tolls' => $tolls,
                'others' => $others,
                'total' => $estimatedCost,
            ],
            'notes' => $this->faker->sentence(),
        ];
    }
}
