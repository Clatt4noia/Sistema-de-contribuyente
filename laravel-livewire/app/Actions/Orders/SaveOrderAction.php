<?php

namespace App\Actions\Orders;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SaveOrderAction
{
    public function execute(Order $order, array $form, array $routePlan): Order
    {
        $form = $this->normalizeForm($form);

        DB::transaction(function () use ($order, $form, $routePlan) {
            $order->fill([
                'client_id' => $form['client_id'],
                'reference' => $form['reference'],
                'cargo_type_id' => $form['cargo_type_id'] ?: null,
                'origin' => $form['origin'],
                'origin_latitude' => $this->numericOrNull($form['origin_latitude']),
                'origin_longitude' => $this->numericOrNull($form['origin_longitude']),
                'destination' => $form['destination'],
                'destination_latitude' => $this->numericOrNull($form['destination_latitude']),
                'destination_longitude' => $this->numericOrNull($form['destination_longitude']),
                'status' => $form['status'],
                'cargo_details' => $form['cargo_details'],
                'cargo_weight_kg' => $this->numericOrNull($form['cargo_weight_kg']),
                'cargo_volume_m3' => $this->numericOrNull($form['cargo_volume_m3']),
                'estimated_distance_km' => $form['estimated_distance_km'],
                'estimated_duration_hours' => $form['estimated_duration_hours'],
                'notes' => $form['notes'],
            ]);

            $order->pickup_date = $form['pickup_date'] ? Carbon::parse($form['pickup_date']) : null;
            $order->delivery_date = $form['delivery_date'] ? Carbon::parse($form['delivery_date']) : null;
            $order->delivery_window_start = $form['delivery_window_start'] ? Carbon::parse($form['delivery_window_start']) : null;
            $order->delivery_window_end = $form['delivery_window_end'] ? Carbon::parse($form['delivery_window_end']) : null;

            $order->save();

            $this->syncRoutePlan($order, $routePlan);
        });

        return $order;
    }

    protected function normalizeForm(array $form): array
    {
        $form['cargo_details'] = trim((string) $form['cargo_details']) ?: null;
        $form['notes'] = trim((string) $form['notes']) ?: null;
        $form['estimated_distance_km'] = $form['estimated_distance_km'] !== null ? (float) $form['estimated_distance_km'] : null;
        $form['estimated_duration_hours'] = $form['estimated_duration_hours'] !== null ? (float) $form['estimated_duration_hours'] : null;

        return $form;
    }

    protected function syncRoutePlan(Order $order, array $routePlan): void
    {
        $cleanData = [
            'planner' => $routePlan['planner'] ?: null,
            'route_summary' => $routePlan['route_summary'] ?: null,
            'map_url' => $routePlan['map_url'] ?: null,
            'route_data' => $this->decodeRouteData($routePlan['route_data'] ?? null),
        ];

        $hasData = collect($cleanData)
            ->filter(fn ($value) => filled($value))
            ->isNotEmpty();

        $existingPlan = $order->routePlans()->first();

        if ($hasData) {
            if ($existingPlan) {
                $existingPlan->update($cleanData);
            } else {
                $order->routePlans()->create($cleanData);
            }

            return;
        }

        if ($existingPlan) {
            $existingPlan->delete();
        }
    }

    protected function decodeRouteData(?string $payload): ?array
    {
        if (!$payload) {
            return null;
        }

        $decoded = json_decode($payload, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    protected function numericOrNull($value): ?float
    {
        return $value !== '' && $value !== null ? (float) $value : null;
    }
}
