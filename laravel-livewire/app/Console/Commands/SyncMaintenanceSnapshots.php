<?php

namespace App\Console\Commands;

use App\Domains\Fleet\Actions\SyncTruckMaintenanceSnapshot;
use App\Models\Truck;
use Illuminate\Console\Command;

class SyncMaintenanceSnapshots extends Command
{
    protected $signature = 'fleet:sync-maintenance-snapshots {--chunk=200 : Tamaño de lote}';

    protected $description = 'Recalcula last_maintenance/next_maintenance (snapshot) en todos los camiones a partir del historial de maintenances.';

    public function handle(SyncTruckMaintenanceSnapshot $syncSnapshot): int
    {
        $chunk = max((int) $this->option('chunk'), 1);
        $processed = 0;

        Truck::query()->orderBy('id')->chunkById($chunk, function ($trucks) use ($syncSnapshot, &$processed) {
            foreach ($trucks as $truck) {
                $syncSnapshot->execute($truck);
                $processed++;
            }
        });

        $this->info("Snapshots sincronizados: {$processed}");

        return self::SUCCESS;
    }
}

