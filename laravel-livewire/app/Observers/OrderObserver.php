<?php

namespace App\Observers;

use App\Models\Assignment;
use App\Models\Invoice;
use App\Models\Order;
use App\Notifications\OrderAssignedNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Services\Logistics\CostEstimator;
use App\Services\Logistics\InventorySyncService;
use App\Services\Logistics\OrderAssignmentService;
use App\Services\Logistics\RouteOptimizationService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class OrderObserver
{
    public function created(Order $order): void
    {
        $this->syncAssignments($order);
        $this->updateCosts($order);
        $this->syncInventory($order);
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $this->notifyClientStatusChange($order);

            if ($order->status === 'delivered') {
                $this->generateInvoice($order);
                app(InventorySyncService::class)->release($order);
            }

            if ($order->status === 'cancelled') {
                app(InventorySyncService::class)->release($order);
            }
        }

        if ($order->wasChanged(['pickup_date', 'delivery_date', 'origin_latitude', 'destination_latitude'])) {
            $this->updateRoute($order);
        }

        if ($order->wasChanged(['cargo_weight_kg', 'cargo_volume_m3', 'estimated_distance_km'])) {
            $this->updateCosts($order);
        }
    }

    protected function syncAssignments(Order $order): void
    {
        $assignment = app(OrderAssignmentService::class)->assignBestResources($order);

        if ($assignment) {
            $this->notifyAssignment($assignment, $order);
        }
    }

    protected function notifyAssignment(?Assignment $assignment, Order $order): void
    {
        if (!$assignment) {
            return;
        }

        if ($assignment->driver && $assignment->driver->email) {
            Notification::route('mail', $assignment->driver->email)
                ->notify(new OrderAssignedNotification($assignment));
        }

        if ($order->client && $order->client->email) {
            Notification::route('mail', $order->client->email)
                ->notify(new OrderAssignedNotification($assignment));
        }
    }

    protected function syncInventory(Order $order): void
    {
        $details = $order->cargo_details;
        if (is_string($details)) {
            $decoded = json_decode($details, true);
            $details = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        $items = collect(Arr::get($details, 'items', []))
            ->map(fn ($item) => [
                'sku' => Arr::get($item, 'sku'),
                'quantity' => (float) Arr::get($item, 'quantity', 0),
                'source_system' => Arr::get($item, 'source_system'),
            ])
            ->filter(fn ($item) => $item['sku'] && $item['quantity'] > 0)
            ->values()
            ->toArray();

        if ($items) {
            app(InventorySyncService::class)->reserve($order, $items);
        }
    }

    protected function updateCosts(Order $order): void
    {
        $estimation = app(CostEstimator::class)->estimate($order);

        $order->forceFill([
            'estimated_cost' => $estimation['total'],
            'cost_breakdown' => $estimation['breakdown'],
        ])->saveQuietly();
    }

    protected function updateRoute(Order $order): void
    {
        app(RouteOptimizationService::class)->createOrUpdatePlan($order);
    }

    protected function generateInvoice(Order $order): void
    {
        if ($order->invoices()->exists()) {
            return;
        }

        $estimation = $order->estimated_cost ?? 0;
        $taxRate = config('logistics.billing.tax_rate', 0.16);
        $subtotal = $estimation;
        $tax = round($subtotal * $taxRate, 2);
        $total = round($subtotal + $tax, 2);

        Invoice::create([
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'issue_date' => now(),
            'due_date' => now()->addDays(15),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => 'issued',
            'notes' => __('Factura generada automáticamente al entregar el pedido.'),
        ]);
    }

    protected function generateInvoiceNumber(): string
    {
        $prefix = config('logistics.billing.prefix', 'INV');

        return sprintf('%s-%s', $prefix, Str::upper(Str::random(8)));
    }

    protected function notifyClientStatusChange(Order $order): void
    {
        if ($order->client && $order->client->email) {
            Notification::route('mail', $order->client->email)
                ->notify(new OrderStatusChangedNotification($order, $order->getOriginal('status')));
        }

        if ($order->assignments()->with('driver')->exists()) {
            $order->loadMissing('assignments.driver');

            $order->assignments->each(function (Assignment $assignment) use ($order) {
                if ($assignment->driver && $assignment->driver->email) {
                    Notification::route('mail', $assignment->driver->email)
                        ->notify(new OrderStatusChangedNotification($order, $order->getOriginal('status')));
                }
            });
        }
    }
}
