<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use App\Models\RoutePlan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class OrderForm extends Component
{
    public Order $order;
    public bool $isEdit = false;
    public array $routePlan = [
        'planner' => null,
        'route_summary' => null,
        'map_url' => null,
        'route_data' => null,
    ];
    public $clients;

    protected function rules(): array
    {
        $orderId = $this->order->id ?? 'NULL';

        return [
            'order.client_id' => 'required|exists:clients,id',
            'order.reference' => 'required|string|max:50|unique:orders,reference,' . $orderId,
            'order.origin' => 'required|string|max:150',
            'order.destination' => 'required|string|max:150',
            'order.pickup_date' => 'nullable|date',
            'order.delivery_date' => 'nullable|date|after_or_equal:order.pickup_date',
            'order.status' => 'required|in:pending,en_route,delivered,cancelled',
            'order.cargo_details' => 'nullable|string',
            'order.estimated_distance_km' => 'nullable|numeric|min:0',
            'order.estimated_duration_hours' => 'nullable|numeric|min:0',
            'order.notes' => 'nullable|string',
            'routePlan.planner' => 'nullable|string|max:100',
            'routePlan.route_summary' => 'nullable|string',
            'routePlan.map_url' => 'nullable|url',
            'routePlan.route_data' => 'nullable|string',
        ];
    }

    public function mount($order = null): void
    {
        if ($order) {
            $this->order = $order->load('routePlans');
            $this->isEdit = true;

            if ($this->order->pickup_date) {
                $this->order->pickup_date = $this->order->pickup_date->format('Y-m-d\TH:i');
            }

            if ($this->order->delivery_date) {
                $this->order->delivery_date = $this->order->delivery_date->format('Y-m-d\TH:i');
            }

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
            $this->order = new Order([
                'status' => 'pending',
            ]);
        }

        $this->clients = \App\Models\Client::orderBy('business_name')->get();
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $this->order->pickup_date = $this->order->pickup_date ? Carbon::parse($this->order->pickup_date) : null;
            $this->order->delivery_date = $this->order->delivery_date ? Carbon::parse($this->order->delivery_date) : null;
            $this->order->estimated_distance_km = $this->order->estimated_distance_km ?: null;
            $this->order->estimated_duration_hours = $this->order->estimated_duration_hours ?: null;

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
        return view('livewire.orders.order-form');
    }
}
