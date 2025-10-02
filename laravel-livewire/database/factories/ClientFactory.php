<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'business_name' => $this->faker->company(),
            'tax_id' => $this->faker->unique()->bothify('############'),
            'contact_name' => $this->faker->name(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'billing_address' => $this->faker->address(),
            'payment_terms' => $this->faker->randomElement(['30 días', '45 días', 'Contado']),
            'notes' => $this->faker->sentence(),
        ];
    }
}
