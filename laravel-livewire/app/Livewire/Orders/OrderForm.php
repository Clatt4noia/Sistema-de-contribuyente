<?php

namespace App\Livewire\Orders;

use App\Models\CargoType;
use App\Models\Order;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Actions\Orders\SaveOrderAction;
use Illuminate\Support\Carbon;
use Livewire\Component;

class OrderForm extends Component
{
    use AuthorizesRequests;

    public Order $order;
    public SaveOrderAction $saveOrderAction;
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

    public function boot(SaveOrderAction $saveOrderAction): void
    {
        $this->saveOrderAction = $saveOrderAction;
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
        $updatedForm = $this->saveOrderAction->execute($this->order, $validated['form'], $this->routePlan)->toArray();

        $this->form = array_merge($this->form, [
            'cargo_details' => $updatedForm['cargo_details'],
            'notes' => $updatedForm['notes'],
            'estimated_distance_km' => $updatedForm['estimated_distance_km'],
            'estimated_duration_hours' => $updatedForm['estimated_duration_hours'],
        ]);

        session()->flash('message', $this->isEdit ? 'Pedido actualizado correctamente.' : 'Pedido registrado correctamente.');
        return redirect()->route('orders.index');
    }

    public function render()
    {
        $this->authorize('viewAny', Order::class);

        return view('livewire.orders.order-form');
    }
}
