<?php

namespace App\Observers;

use App\Domains\Fleet\Actions\SyncTruckMaintenanceSnapshot;
use App\Models\Maintenance;
use App\Models\Truck;

class MaintenanceObserver
{
    public function __construct(private readonly SyncTruckMaintenanceSnapshot $syncSnapshot)
    {
    }

    public function saved(Maintenance $maintenance): void
    {
        $this->syncAffectedTrucks($maintenance);
    }

    public function deleted(Maintenance $maintenance): void
    {
        $this->syncAffectedTrucks($maintenance);
    }

    public function restored(Maintenance $maintenance): void
    {
        $this->syncAffectedTrucks($maintenance);
    }

    protected function syncAffectedTrucks(Maintenance $maintenance): void
    {
        $truckId = (int) ($maintenance->truck_id ?? 0);

        if ($truckId > 0 && ($truck = Truck::find($truckId))) {
            $this->syncSnapshot->execute($truck);
        }

        $originalTruckId = (int) ($maintenance->getOriginal('truck_id') ?? 0);

        if ($originalTruckId > 0 && $originalTruckId !== $truckId && ($originalTruck = Truck::find($originalTruckId))) {
            $this->syncSnapshot->execute($originalTruck);
        }
    }
}

