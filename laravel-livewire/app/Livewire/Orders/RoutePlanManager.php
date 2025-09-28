<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use App\Models\RoutePlan;
use Livewire\Component;

class RoutePlanManager extends Component
{
    public Order $order;
    public string $planner = '';
    public string $route_summary = '';
    public string $map_url = '';
    public string $route_data = '';

    protected function rules(): array
    {
        return [
            'planner' => 'nullable|string|max:100',
            'route_summary' => 'required|string|min:5',
            'map_url' => 'nullable|url',
            'route_data' => 'nullable|string',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $payload = $this->route_data ? json_decode($this->route_data, true) : null;
        if ($this->route_data && json_last_error() !== JSON_ERROR_NONE) {
            $this->addError('route_data', 'El formato JSON no es valido.');
            return;
        }

        $this->order->routePlans()->create([
            'planner' => $this->planner ?: null,
            'route_summary' => $this->route_summary,
            'map_url' => $this->map_url ?: null,
            'route_data' => $payload,
        ]);

        $this->reset(['planner', 'route_summary', 'map_url', 'route_data']);
        $this->order->refresh();

        session()->flash('message', 'Ruta agregada correctamente.');
    }

    public function delete(int $routePlanId): void
    {
        $plan = $this->order->routePlans()->find($routePlanId);
        if ($plan) {
            $plan->delete();
            $this->order->refresh();
            session()->flash('message', 'Ruta eliminada correctamente.');
        }
    }

    public function render()
    {
        return view('livewire.orders.route-plan-manager', [
            'plans' => $this->order->routePlans()->latest()->get(),
        ]);
    }
}
