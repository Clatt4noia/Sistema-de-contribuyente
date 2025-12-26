<?php

namespace App\Services\Logistics;

use App\Models\Order;
use App\Models\RoutePlan;
use Illuminate\Support\Arr;

class RouteOptimizationService
{
    public function createOrUpdatePlan(Order $order, array $stops = []): RoutePlan
    {
        $orderedStops = $this->optimizeStops($order, $stops);
        $routeSummary = $this->buildRouteSummary($order, $orderedStops);
        $routeSummaryText = sprintf(
            'Distancia aprox. %s km · %s min',
            $routeSummary['distance_km'] ?? 0,
            $routeSummary['duration_minutes'] ?? 0
        );

        return $order->routePlans()->updateOrCreate(
            ['id' => optional($order->routePlans()->latest()->first())->id],
            [
                'planner' => auth()->user()->name ?? 'system',
                'route_summary' => $routeSummaryText,
                'map_url' => $this->buildMapUrl($order, $orderedStops),
                'route_data' => [
                    'stops' => $orderedStops,
                    'distance_km' => $routeSummary['distance_km'],
                    'duration_minutes' => $routeSummary['duration_minutes'],
                ],
            ]
        );
    }

    protected function optimizeStops(Order $order, array $stops): array
    {
        $stopsCollection = collect($stops)->map(function ($stop) {
            return [
                'name' => Arr::get($stop, 'name'),
                'latitude' => (float) Arr::get($stop, 'latitude'),
                'longitude' => (float) Arr::get($stop, 'longitude'),
            ];
        })->filter(fn ($stop) => $stop['latitude'] && $stop['longitude']);

        if ($stopsCollection->isEmpty()) {
            return [];
        }

        $origin = [
            'latitude' => $order->origin_latitude,
            'longitude' => $order->origin_longitude,
        ];

        $sorted = $stopsCollection->sortBy(function ($stop) use ($origin) {
            return $this->haversineDistance($origin['latitude'], $origin['longitude'], $stop['latitude'], $stop['longitude']);
        })->values();

        return $sorted->toArray();
    }

    protected function buildRouteSummary(Order $order, array $stops): array
    {
        $points = array_merge([
            [
                'latitude' => $order->origin_latitude,
                'longitude' => $order->origin_longitude,
            ],
        ], $stops, [
            [
                'latitude' => $order->destination_latitude,
                'longitude' => $order->destination_longitude,
            ],
        ]);

        $distance = 0;
        $duration = 0;
        for ($i = 0; $i < count($points) - 1; $i++) {
            $segmentDistance = $this->haversineDistance(
                $points[$i]['latitude'],
                $points[$i]['longitude'],
                $points[$i + 1]['latitude'],
                $points[$i + 1]['longitude']
            );
            $distance += $segmentDistance;
            $duration += $segmentDistance / 60 * 60; // assume 60km/h
        }

        return [
            'distance_km' => round($distance, 2),
            'duration_minutes' => round($duration, 0),
        ];
    }

    protected function buildMapUrl(Order $order, array $stops): ?string
    {
        $apiKey = config('services.maps.google_api_key');

        if ($apiKey) {
            $waypoints = collect($stops)->map(function ($stop) {
                return $stop['latitude'].','.$stop['longitude'];
            })->implode('|');

            return sprintf(
                'https://www.google.com/maps/embed/v1/directions?key=%s&origin=%s,%s&destination=%s,%s%s',
                $apiKey,
                $order->origin_latitude,
                $order->origin_longitude,
                $order->destination_latitude,
                $order->destination_longitude,
                $waypoints ? '&waypoints='.$waypoints : ''
            );
        }

        return null;
    }

    protected function haversineDistance($latFrom, $lonFrom, $latTo, $lonTo): float
    {
        $earthRadius = 6371; // km
        $latFrom = deg2rad($latFrom);
        $lonFrom = deg2rad($lonFrom);
        $latTo = deg2rad($latTo);
        $lonTo = deg2rad($lonTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
