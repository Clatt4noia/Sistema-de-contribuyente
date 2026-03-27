<?php

namespace Database\Factories;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    protected $model = Driver::class;

    public function definition(): array
    {
        $licenseExpiration = $this->faker->dateTimeBetween('now', '+2 years');

        return [
            'name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'document_type' => $this->faker->randomElement(['1', '4']),
            'document_number' => $this->faker->unique()->numerify('########'),
            'license_number' => strtoupper($this->faker->regexify('[A-Z][0-9]{8}')), // Formato real: Letra + 8 dígitos del DNI
            'license_category' => $this->faker->randomElement(['A-IIb', 'A-IIIa', 'A-IIIb', 'A-IIIc']),
            'license_expiration' => $licenseExpiration,
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'status' => $this->faker->randomElement(['active', 'assigned', 'on_leave']),
            'notes' => $this->faker->sentence(),
            'work_schedule' => [
                [
                    'day_of_week' => 'Lunes',
                    'start_time' => '08:00',
                    'end_time' => '18:00',
                ],
                [
                    'day_of_week' => 'Martes',
                    'start_time' => '08:00',
                    'end_time' => '18:00',
                ],
            ],
        ];
    }
}
