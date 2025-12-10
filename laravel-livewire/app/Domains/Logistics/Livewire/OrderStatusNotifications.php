<?php

namespace App\Domains\Logistics\Livewire;

use App\Models\OrderStatusUpdate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

class OrderStatusNotifications extends Component
{
    use AuthorizesRequests;

    public array $notifications = [];

    public function mount($recentUpdates = null): void
    {
        $this->authorize('view-dashboard.logistics');

        $collection = $recentUpdates instanceof Collection
            ? $recentUpdates
            : collect($recentUpdates);

        if ($collection->isEmpty()) {
            $collection = OrderStatusUpdate::with([
                'order.client',
                'order.activeAssignment.truck',
                'order.activeAssignment.driver',
                'assignment.truck',
                'assignment.driver',
                'changedBy',
            ])
                ->latest('changed_at')
                ->take(30)
                ->get();
        }

        $this->notifications = $collection->map(function (OrderStatusUpdate $update) {
            $assignment = $update->assignment ?: $update->order?->activeAssignment;

            return [
                'order_id' => $update->order?->id,
                'order_reference' => $update->order?->reference,
                'client' => $update->order?->client?->business_name ?? $update->order?->client?->contact_name,
                'assignment' => $assignment?->id,
                'truck' => $assignment?->truck?->plate_number,
                'driver' => $assignment?->driver?->full_name ?? $assignment?->driver?->name,
                'previous_status' => $update->previous_status,
                'new_status' => $update->new_status,
                'changed_at' => optional($update->changed_at)->format('d/m/Y H:i'),
                'changed_by' => $update->changedBy?->name,
                'notes' => $update->notes,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.logistics.order-status-notifications')
            ->layout('components.layouts.dashboard', [
                'title' => __('Notificaciones logísticas'),
            ]);
    }
}
