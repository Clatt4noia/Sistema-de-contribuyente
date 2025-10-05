<?php

namespace App\Livewire\Logistics;

use App\Models\VehicleLocationUpdate;
use Illuminate\Support\Collection;
use Livewire\Component;

class LiveTrackingBoard extends Component
{
    public array $markers = [];

    public function mount($latestTracking = null): void
    {
        $collection = $latestTracking instanceof Collection
            ? $latestTracking
            : collect($latestTracking);

        if ($collection->isEmpty()) {
            $collection = VehicleLocationUpdate::with(['truck', 'assignment.order'])
                ->latest('reported_at')
                ->take(10)
                ->get();
        }

        $this->markers = $collection->map(function (VehicleLocationUpdate $update) {
            return [
                'truck' => $update->truck?->plate_number,
                'order' => $update->assignment?->order?->reference,
                'latitude' => $update->latitude,
                'longitude' => $update->longitude,
                'status' => $update->status,
                'reported_at' => optional($update->reported_at)->format('d/m/Y H:i'),
            ];
        })->filter(fn ($marker) => $marker['latitude'] && $marker['longitude'])->values()->toArray();
    }

    public function render()
    {
        return view('livewire.logistics.live-tracking-board');
    }
}
