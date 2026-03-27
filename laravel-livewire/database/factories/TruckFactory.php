<?php

namespace Database\Factories;

use App\Models\Truck;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Truck>
 */
class TruckFactory extends Factory
{
    protected $model = Truck::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Truck $truck) {
            $status = $truck->status instanceof \BackedEnum ? $truck->status->value : (string) $truck->status;

            if (! in_array($status, ['available', 'in_use'], true)) {
                return;
            }

            $referenceDate = now()->addDay();

            if (! $truck->requiresMaintenanceAlert($referenceDate)) {
                return;
            }

            $intervalDays = (int) ($truck->maintenance_interval_days ?: 90);
            $intervalDays = $intervalDays > 0 ? $intervalDays : 90;

            $truck->last_maintenance = Carbon::now()->subDays(max(1, $intervalDays - 30));
            $truck->next_maintenance = Carbon::parse($truck->last_maintenance)->addDays($intervalDays);
        });
    }

    public function definition(): array
    {
        $lastMaintenance = $this->faker->dateTimeBetween('-6 months', '-1 month');

        return [
            'plate_number' => strtoupper($this->faker->regexify('[A-Z][0-9][A-Z][0-9]{3}')), // Formato: C9F813
            'brand' => $this->faker->randomElement(['Volvo', 'Scania', 'Mercedes-Benz', 'MAN']),
            'model' => $this->faker->randomElement(['FH', 'R-Series', 'Actros', 'TGX']) . ' ' . $this->faker->numberBetween(2000, 2024),
            'year' => $this->faker->numberBetween(2018, 2024),
            'type' => $this->faker->randomElement(['Tractocamion', 'Camion', 'Semirremolque', 'Furgon', 'Cisterna', 'Volquete', 'Plataforma']),
            'tuce_number' => $this->faker->numerify('#########'), // 9 dígitos (ej. 151705003)
            'special_auth_issuer' => 'MTC', // Siempre MTC
            'special_auth_number' => $this->faker->numerify('#########'), // 9 dígitos (ej. 151908863)
            'capacity' => $this->faker->numberBetween(10, 30) + 0.5,
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
