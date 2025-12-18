<?php

namespace App\Services\Logistics;

use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Truck;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderAssignmentService
{
    public function assignBestResources(Order $order): ?Assignment
    {
        return DB::transaction(function () use ($order) {
            $truck = $this->selectTruck($order);
            $driver = $this->selectDriver($order, $truck);

            if (!$truck || !$driver) {
                Log::warning('No se encontraron recursos disponibles para la Orden', [
                    'order_id' => $order->id,
                ]);

                return null;
            }

            $assignment = Assignment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'truck_id' => $truck->id,
                    'driver_id' => $driver->id,
                    'start_date' => $order->pickup_date ?? now(),
                    'end_date' => $order->delivery_date,
                    'status' => 'scheduled',
                    'description' => __('Asignación automática generada para el Orden :reference', [
                        'reference' => $order->reference,
                    ]),
                ]
            );

            $truck->update(['status' => 'reserved']);
            $driver->update(['status' => 'assigned']);

            return $assignment;
        });
    }

    protected function selectTruck(Order $order): ?Truck
    {
        $query = Truck::query()->operational()->whereIn('status', ['available', 'active', 'reserved']);

        if ($order->cargoType) {
            $query->whereHas('cargoTypes', fn ($q) => $q->where('cargo_type_id', $order->cargo_type_id));
        }

        if ($order->cargo_weight_kg) {
            $query->where('capacity', '>=', $order->cargo_weight_kg);
        }

        return $query->orderBy('status')->orderBy('mileage')->first();
    }

    protected function selectDriver(Order $order, ?Truck $truck): ?Driver
    {
        if (!$truck) {
            return null;
        }

        $availableDrivers = Driver::query()
            ->where('status', 'available')
            ->with('schedules')
            ->get();

        $filtered = $availableDrivers->filter(function (Driver $driver) use ($order) {
            if (!$order->pickup_date) {
                return true;
            }

            $pickup = $order->pickup_date->copy()->setTimezone('UTC');
            $pickupDay = $pickup->isoWeekday();

            $hasShift = $driver->schedules->first(function ($schedule) use ($pickup, $pickupDay) {
                if ((int) $schedule->day_of_week !== $pickupDay) {
                    return false;
                }

                $start = CarbonInterval::createFromDateString($schedule->start_time)->totalMinutes;
                $end = CarbonInterval::createFromDateString($schedule->end_time)->totalMinutes;
                $pickupMinutes = $pickup->hour * 60 + $pickup->minute;

                return $pickupMinutes >= $start && $pickupMinutes <= $end;
            });

            return (bool) $hasShift;
        });

        return $filtered->sortBy('id')->first();
    }
}
