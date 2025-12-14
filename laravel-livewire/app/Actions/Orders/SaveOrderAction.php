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
                // dentro del data que guardas:
                'estimated_cost' => $data['estimated_cost'] ?? null,
                'referential_rate_sxtm' => $data['referential_rate_sxtm'] ?? null,
                'referential_route_key' => $data['referential_route_key'] ?? null,
                'referential_route_dest' => $data['referential_route_dest'] ?? null,
                'referential_source' => $data['referential_source'] ?? null,
                'referential_year' => $data['referential_year'] ?? null,
                

            ]);

            $order->pickup_date = !empty($form['pickup_date'] ?? null)
            ? Carbon::parse($form['pickup_date'])
            : null;

            $order->delivery_date = !empty($form['delivery_date'] ?? null)
                ? Carbon::parse($form['delivery_date'])
                : null;

            $order->delivery_window_start = !empty($form['delivery_window_start'] ?? null)
                ? Carbon::parse($form['delivery_window_start'])
                : null;

            $order->delivery_window_end = !empty($form['delivery_window_end'] ?? null)
                ? Carbon::parse($form['delivery_window_end'])
                : null;
                $order->save();

                $this->syncRoutePlan($order, $routePlan);
            });

        return $order;
    }

    protected function normalizeForm(array $form): array
    {
        $form['cargo_details'] = trim((string)($form['cargo_details'] ?? '')) ?: null;
        $form['notes']         = trim((string)($form['notes'] ?? '')) ?: null;

        $form['estimated_distance_km'] = ($form['estimated_distance_km'] ?? null) !== null
            ? (float) $form['estimated_distance_km']
            : null;

        $form['estimated_duration_hours'] = ($form['estimated_duration_hours'] ?? null) !== null
            ? (float) $form['estimated_duration_hours']
            : null;

        $form['cargo_weight_kg'] = ($form['cargo_weight_kg'] ?? null) !== null
            ? (float) $form['cargo_weight_kg']
            : null;

        $form['cargo_volume_m3'] = ($form['cargo_volume_m3'] ?? null) !== null
            ? (float) $form['cargo_volume_m3']
            : null;

        $form['estimated_cost'] = ($form['estimated_cost'] ?? null) !== null
            ? (float) $form['estimated_cost']
            : null;

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
