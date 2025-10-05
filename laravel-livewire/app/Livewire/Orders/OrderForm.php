<?php

namespace App\Livewire\Orders;

use App\Models\CargoType;
use App\Models\Order;
use App\Models\RoutePlan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class OrderForm extends Component
{
    use AuthorizesRequests;

    public Order $order;
    public bool $isEdit = false;
    public array $form = [];
    public array $routePlan = [
        'planner' => null,
        'route_summary' => null,
        'map_url' => null,
        'route_data' => null,
    ];
    public $clients;
    public $cargoTypes;

    protected function rules(): array
    {
        $orderId = $this->order->id ?? 'NULL';

        return [
            'form.client_id' => 'required|exists:clients,id',
            'form.reference' => 'required|string|max:50|unique:orders,reference,' . $orderId,
            'form.cargo_type_id' => 'nullable|exists:cargo_types,id',
            'form.origin' => 'required|string|max:150',
            'form.origin_latitude' => 'nullable|numeric|between:-90,90',
            'form.origin_longitude' => 'nullable|numeric|between:-180,180',
            'form.destination' => 'required|string|max:150',
            'form.destination_latitude' => 'nullable|numeric|between:-90,90',
            'form.destination_longitude' => 'nullable|numeric|between:-180,180',
            'form.pickup_date' => 'nullable|date',
            'form.delivery_date' => 'nullable|date|after_or_equal:form.pickup_date',
            'form.delivery_window_start' => 'nullable|date|after_or_equal:form.pickup_date',
            'form.delivery_window_end' => 'nullable|date|after_or_equal:form.delivery_window_start',
            'form.status' => 'required|in:pending,en_route,delivered,cancelled',
            'form.cargo_details' => 'nullable|string',
            'form.cargo_weight_kg' => 'nullable|numeric|min:0',
            'form.cargo_volume_m3' => 'nullable|numeric|min:0',
            'form.estimated_distance_km' => 'nullable|numeric|min:0',
            'form.estimated_duration_hours' => 'nullable|numeric|min:0',
            'form.notes' => 'nullable|string',
            'routePlan.planner' => 'nullable|string|max:100',
            'routePlan.route_summary' => 'nullable|string',
            'routePlan.map_url' => 'nullable|url',
            'routePlan.route_data' => 'nullable|string',
        ];
    }

    public function mount($order = null): void
    {
        if ($order) {
            $this->order = $order;
            $this->authorize('update', $this->order);
            $this->order->load('routePlans');
            $this->isEdit = true;

            $firstPlan = $this->order->routePlans->first();
            if ($firstPlan) {
                $this->routePlan = [
                    'planner' => $firstPlan->planner,
                    'route_summary' => $firstPlan->route_summary,
                    'map_url' => $firstPlan->map_url,
                    'route_data' => $firstPlan->route_data ? json_encode($firstPlan->route_data) : null,
                ];
            }
        } else {
            $this->authorize('create', Order::class);
            $this->order = new Order([
                'status' => 'pending',
            ]);
        }

        $this->form = [
            'client_id' => $this->order->client_id ?? '',
            'reference' => $this->order->reference ?? '',
            'cargo_type_id' => $this->order->cargo_type_id ?? '',
            'origin' => $this->order->origin ?? '',
            'origin_latitude' => $this->order->origin_latitude,
            'origin_longitude' => $this->order->origin_longitude,
            'destination' => $this->order->destination ?? '',
            'destination_latitude' => $this->order->destination_latitude,
            'destination_longitude' => $this->order->destination_longitude,
            'pickup_date' => optional($this->order->pickup_date)->format('Y-m-d\TH:i'),
            'delivery_date' => optional($this->order->delivery_date)->format('Y-m-d\TH:i'),
            'delivery_window_start' => optional($this->order->delivery_window_start)->format('Y-m-d\TH:i'),
            'delivery_window_end' => optional($this->order->delivery_window_end)->format('Y-m-d\TH:i'),
            'status' => $this->order->status ?? 'pending',
            'cargo_details' => $this->order->cargo_details ?? '',
            'cargo_weight_kg' => $this->order->cargo_weight_kg,
            'cargo_volume_m3' => $this->order->cargo_volume_m3,
            'estimated_distance_km' => $this->order->estimated_distance_km ?? null,
            'estimated_duration_hours' => $this->order->estimated_duration_hours ?? null,
            'notes' => $this->order->notes ?? '',
        ];

        $this->clients = \App\Models\Client::orderBy('business_name')->get();
        $this->cargoTypes = CargoType::orderBy('name')->get();
    }

    public function save()
    {
        $this->authorize($this->isEdit ? 'update' : 'create', $this->isEdit ? $this->order : Order::class);

        $validated = $this->validate();
        $data = $validated['form'];

        // Normalizamos campos libres antes de persistirlos y reflejamos el resultado en el estado del formulario.
        $data['cargo_details'] = trim((string) $data['cargo_details']) ?: null;
        $data['notes'] = trim((string) $data['notes']) ?: null;
        $data['estimated_distance_km'] = $data['estimated_distance_km'] !== null ? (float) $data['estimated_distance_km'] : null;
        $data['estimated_duration_hours'] = $data['estimated_duration_hours'] !== null ? (float) $data['estimated_duration_hours'] : null;

        $this->form = array_merge($this->form, [
            'cargo_details' => $data['cargo_details'],
            'notes' => $data['notes'],
            'estimated_distance_km' => $data['estimated_distance_km'],
            'estimated_duration_hours' => $data['estimated_duration_hours'],
        ]);

        DB::transaction(function () {
            $this->order->fill([
                'client_id' => $this->form['client_id'],
                'reference' => $this->form['reference'],
                'cargo_type_id' => $this->form['cargo_type_id'] ?: null,
                'origin' => $this->form['origin'],
                'origin_latitude' => $this->form['origin_latitude'] !== '' ? (float) $this->form['origin_latitude'] : null,
                'origin_longitude' => $this->form['origin_longitude'] !== '' ? (float) $this->form['origin_longitude'] : null,
                'destination' => $this->form['destination'],
                'destination_latitude' => $this->form['destination_latitude'] !== '' ? (float) $this->form['destination_latitude'] : null,
                'destination_longitude' => $this->form['destination_longitude'] !== '' ? (float) $this->form['destination_longitude'] : null,
                'status' => $this->form['status'],
                'cargo_details' => $this->form['cargo_details'] ?: null,
                'cargo_weight_kg' => $this->form['cargo_weight_kg'] !== '' ? (float) $this->form['cargo_weight_kg'] : null,
                'cargo_volume_m3' => $this->form['cargo_volume_m3'] !== '' ? (float) $this->form['cargo_volume_m3'] : null,
                'estimated_distance_km' => $this->form['estimated_distance_km'] !== null ? (float) $this->form['estimated_distance_km'] : null,
                'estimated_duration_hours' => $this->form['estimated_duration_hours'] !== null ? (float) $this->form['estimated_duration_hours'] : null,
                'notes' => $this->form['notes'] ?: null,
            ]);

            $this->order->pickup_date = $this->form['pickup_date'] ? Carbon::parse($this->form['pickup_date']) : null;
            $this->order->delivery_date = $this->form['delivery_date'] ? Carbon::parse($this->form['delivery_date']) : null;
            $this->order->delivery_window_start = $this->form['delivery_window_start'] ? Carbon::parse($this->form['delivery_window_start']) : null;
            $this->order->delivery_window_end = $this->form['delivery_window_end'] ? Carbon::parse($this->form['delivery_window_end']) : null;

            $this->order->save();

            $this->syncRoutePlan();
        });

        session()->flash('message', $this->isEdit ? 'Pedido actualizado correctamente.' : 'Pedido registrado correctamente.');
        return redirect()->route('orders.index');
    }

    protected function syncRoutePlan(): void
    {
        $cleanData = [
            'planner' => $this->routePlan['planner'] ?: null,
            'route_summary' => $this->routePlan['route_summary'] ?: null,
            'map_url' => $this->routePlan['map_url'] ?: null,
            'route_data' => $this->decodeRouteData($this->routePlan['route_data'] ?? null),
        ];

        $hasData = collect($cleanData)
            ->filter(fn ($value) => filled($value))
            ->isNotEmpty();

        $existingPlan = $this->order->routePlans()->first();

        if ($hasData) {
            if ($existingPlan) {
                $existingPlan->update($cleanData);
            } else {
                $this->order->routePlans()->create($cleanData);
            }
        } elseif ($existingPlan) {
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

    public function render()
    {
        $this->authorize('viewAny', Order::class);

        return view('livewire.orders.order-form');
    }
}
