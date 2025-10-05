<?php

namespace App\Livewire\ClientPortal;

use App\Models\Order;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class OrderTracker extends Component
{
    use AuthorizesRequests;

    public $orders;
    public array $windowUpdates = [];

    public function mount(): void
    {
        $this->authorize('view-dashboard.client');
        $this->loadOrders();
    }

    public function render()
    {
        return view('livewire.client-portal.order-tracker');
    }

    public function loadOrders(): void
    {
        $email = auth()->user()->email;

        $this->orders = Order::with(['client', 'routePlans', 'assignments.truck'])
            ->whereHas('client', fn ($query) => $query->where('email', $email))
            ->orderByDesc('created_at')
            ->get();

        $this->orders->each(function (Order $order) {
            $this->windowUpdates[$order->id] = [
                'delivery_window_start' => optional($order->delivery_window_start)->format('Y-m-d\TH:i'),
                'delivery_window_end' => optional($order->delivery_window_end)->format('Y-m-d\TH:i'),
                'notes' => $order->notes,
            ];
        });
    }

    public function updateWindow(int $orderId): void
    {
        $email = auth()->user()->email;
        $order = Order::with('client')->findOrFail($orderId);

        if (! $order->client || $order->client->email !== $email) {
            abort(403);
        }

        $payload = $this->windowUpdates[$orderId] ?? [];

        $data = Validator::make($payload, [
            'delivery_window_start' => 'required|date',
            'delivery_window_end' => 'required|date|after_or_equal:delivery_window_start',
            'notes' => 'nullable|string|max:500',
        ])->validate();

        $order->update([
            'delivery_window_start' => $data['delivery_window_start'],
            'delivery_window_end' => $data['delivery_window_end'],
            'notes' => $data['notes'] ?? $order->notes,
        ]);

        $this->windowUpdates[$orderId] = [];
        $this->loadOrders();
        session()->flash('message', __('Ventana de entrega actualizada correctamente.'));
    }
}
