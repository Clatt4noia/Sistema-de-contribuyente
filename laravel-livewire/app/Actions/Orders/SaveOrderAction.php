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
                'origin_latitude' => $this->numericOrNull($form['origin_latitude'] ?? null),
                'origin_longitude' => $this->numericOrNull($form['origin_longitude'] ?? null),

                'destination' => $form['destination'],
                'destination_latitude' => $this->numericOrNull($form['destination_latitude'] ?? null),
                'destination_longitude' => $this->numericOrNull($form['destination_longitude'] ?? null),

                'status' => $form['status'],

                'cargo_details' => $form['cargo_details'] ?? null,
                'cargo_weight_kg' => $this->numericOrNull($form['cargo_weight_kg'] ?? null),
                'cargo_volume_m3' => $this->numericOrNull($form['cargo_volume_m3'] ?? null),

                'estimated_distance_km' => $this->numericOrNull($form['estimated_distance_km'] ?? null),
                'estimated_duration_hours' => $this->numericOrNull($form['estimated_duration_hours'] ?? null),

                'notes' => $form['notes'] ?? null,

                // ✅ costo (manual o auto)
                'estimated_cost' => $this->numericOrNull($form['estimated_cost'] ?? null),

                // ✅ MTC referencial (si lo mandas desde OrderForm::save())
                'referential_rate_sxtm' => $this->numericOrNull($form['referential_rate_sxtm'] ?? null),
                'referential_route_key' => $form['referential_route_key'] ?? null,
                'referential_route_dest' => $form['referential_route_dest'] ?? null,
                'referential_source' => $form['referential_source'] ?? null,
                'referential_year' => isset($form['referential_year']) ? (int) $form['referential_year'] : null,
            ]);

            // Fechas (todas opcionales)
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
        // Strings
        $form['cargo_details'] = trim((string)($form['cargo_details'] ?? '')) ?: null;
        $form['notes']         = trim((string)($form['notes'] ?? '')) ?: null;

        // Numeric (si viene vacío o no existe => null)
        foreach ([
            'estimated_distance_km',
            'estimated_duration_hours',
            'cargo_weight_kg',
            'cargo_volume_m3',
            'estimated_cost',
            'referential_rate_sxtm',
        ] as $k) {
            $form[$k] = $this->numericOrNull($form[$k] ?? null);
        }

        // cargo_type_id puede venir '' => null
        $form['cargo_type_id'] = $form['cargo_type_id'] ?? null;

        return $form;
    }

    protected function syncRoutePlan(Order $order, array $routePlan): void
    {
        $cleanData = [
            'planner' => !empty($routePlan['planner'] ?? null) ? $routePlan['planner'] : null,
            'route_summary' => !empty($routePlan['route_summary'] ?? null) ? $routePlan['route_summary'] : null,
            'map_url' => !empty($routePlan['map_url'] ?? null) ? $routePlan['map_url'] : null,
            'route_data' => $this->decodeRouteData($routePlan['route_data'] ?? null),
        ];

        $hasData = collect($cleanData)->filter(fn ($v) => filled($v))->isNotEmpty();

        $existingPlan = $order->routePlans()->first();

        if ($hasData) {
            if ($existingPlan) $existingPlan->update($cleanData);
            else $order->routePlans()->create($cleanData);
            return;
        }

        if ($existingPlan) $existingPlan->delete();
    }

    protected function decodeRouteData(?string $payload): ?array
    {
        if (!$payload) return null;

        $decoded = json_decode($payload, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    protected function numericOrNull($value): ?float
    {
        if ($value === '' || $value === null) return null;
        if (!is_numeric($value)) return null;

        return (float) $value;
    }
}
