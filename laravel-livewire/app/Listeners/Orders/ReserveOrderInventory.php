<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderCreated;
use App\Services\Logistics\InventorySyncService;
use Illuminate\Support\Arr;

class ReserveOrderInventory
{
    public function __construct(private InventorySyncService $inventorySyncService)
    {
    }

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if ($order->inventoryReservations()->exists()) {
            return;
        }

        $items = $this->normalizeItems($order->cargo_details);

        if (empty($items)) {
            return;
        }

        $this->inventorySyncService->reserve($order, $items);
    }

    /**
     * @param  array<int, mixed>|string|null  $details
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeItems($details): array
    {
        if (is_string($details)) {
            $decoded = json_decode($details, true);
            $details = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        return collect(Arr::get($details, 'items', []))
            ->map(fn ($item) => [
                'sku' => Arr::get($item, 'sku'),
                'quantity' => (float) Arr::get($item, 'quantity', 0),
                'source_system' => Arr::get($item, 'source_system'),
            ])
            ->filter(fn ($item) => $item['sku'] && $item['quantity'] > 0)
            ->values()
            ->toArray();
    }
}
