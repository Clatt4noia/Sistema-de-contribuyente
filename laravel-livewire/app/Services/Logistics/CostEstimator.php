<?php

namespace App\Services\Logistics;

use App\Models\Order;

class CostEstimator
{
    public function estimate(Order $order): array
    {
        $distance = $order->estimated_distance_km ?? $this->estimateDistance($order);
        $baseRate = config('logistics.costs.base_rate_per_km', 1.25);
        $weightRate = config('logistics.costs.weight_rate_per_kg', 0.05);
        $volumeRate = config('logistics.costs.volume_rate_per_m3', 0.1);
        $handlingFee = config('logistics.costs.handling_fee', 25.0);

        $distanceCost = $distance * $baseRate;
        $weightCost = ($order->cargo_weight_kg ?? 0) * $weightRate;
        $volumeCost = ($order->cargo_volume_m3 ?? 0) * $volumeRate;
        $hazardFee = ($order->cargoType && $order->cargoType->is_hazardous)
            ? config('logistics.costs.hazard_fee', 75)
            : 0;

        $total = round($distanceCost + $weightCost + $volumeCost + $handlingFee + $hazardFee, 2);

        return [
            'total' => $total,
            'breakdown' => [
                'distance' => round($distanceCost, 2),
                'weight' => round($weightCost, 2),
                'volume' => round($volumeCost, 2),
                'handling' => $handlingFee,
                'hazard' => $hazardFee,
            ],
            'distance_km' => round($distance, 2),
        ];
    }

    protected function estimateDistance(Order $order): float
    {
        if ($order->origin_latitude && $order->destination_latitude) {
            $calculator = app(RouteOptimizationService::class);

            return $calculator->createOrUpdatePlan($order)->route_data['distance_km'] ?? 0.0;
        }

        return 0.0;
    }
}
