<?php

namespace App\Services\Logistics\Tracking;

use App\Models\Assignment;
use App\Models\RouteIncident;
use App\Models\VehicleLocationUpdate;
use Illuminate\Support\Facades\Log;

class TrackingService
{
    public function recordLocation(array $payload): VehicleLocationUpdate
    {
        $assignment = Assignment::find($payload['assignment_id'] ?? null);
        $update = VehicleLocationUpdate::create([
            'assignment_id' => optional($assignment)->id,
            'truck_id' => $payload['truck_id'],
            'latitude' => $payload['latitude'],
            'longitude' => $payload['longitude'],
            'speed_kph' => $payload['speed_kph'] ?? null,
            'reported_at' => $payload['reported_at'] ?? now(),
            'status' => $payload['status'] ?? 'on_route',
            'raw_payload' => $payload,
        ]);

        $this->evaluateDeviation($update);

        return $update;
    }

    protected function evaluateDeviation(VehicleLocationUpdate $update): void
    {
        if ($update->status === 'off_route') {
            RouteIncident::create([
                'assignment_id' => $update->assignment_id,
                'type' => 'route_deviation',
                'severity' => 'medium',
                'description' => __('Desviación detectada para el vehículo :truck', [
                    'truck' => optional($update->truck)->plate_number,
                ]),
                'reported_at' => now(),
                'metadata' => $update->raw_payload,
            ]);
        }

        if ($update->status === 'delayed') {
            Log::notice('Retraso detectado en ruta', [
                'assignment_id' => $update->assignment_id,
                'truck_id' => $update->truck_id,
            ]);
        }
    }
}
