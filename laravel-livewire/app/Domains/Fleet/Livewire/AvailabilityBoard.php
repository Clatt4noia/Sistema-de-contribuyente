<?php

namespace App\Domains\Fleet\Livewire;

use App\Enums\Documents\DocumentComputedStatus;
use App\Enums\Fleet\AssignmentStatus;
use App\Enums\Fleet\DriverStatus;
use App\Enums\Fleet\TruckStatus;
use App\Models\Driver;
use App\Models\Truck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app.sidebar', ['title' => 'Disponibilidad de recursos'])]
#[Title('Disponibilidad de recursos')]
class AvailabilityBoard extends Component
{
    use AuthorizesRequests;

    public string $vehicleSearch = '';
    public string $vehicleStatus = '';
    public string $driverSearch = '';
    public string $driverStatus = '';

    public function render()
    {
        $this->authorize('viewAny', Truck::class);
        $this->authorize('viewAny', Driver::class);

        $truckStats = Truck::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $driverStats = Driver::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $trucks = Truck::query()
            ->withCount(['assignments as active_assignments_count' => function ($query) {
                $query->whereNotIn('status', [AssignmentStatus::Completed->value, AssignmentStatus::Cancelled->value]);
            }])
            ->with(['documents' => function ($query) {
                $query->orderBy('expires_at');
            }])
            ->when($this->vehicleStatus, function ($query) {
                $status = TruckStatus::tryFrom($this->vehicleStatus);

                if ($status) {
                    $query->where('status', $status->value);
                }
            })
            ->when($this->vehicleSearch, function ($query) {
                $term = '%' . trim($this->vehicleSearch) . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('plate_number', 'like', $term)
                        ->orWhere('brand', 'like', $term)
                        ->orWhere('model', 'like', $term);
                });
            })
            ->orderBy('status')
            ->orderBy('plate_number')
            ->take(12)
            ->get()
            ->map(function (Truck $truck) {
                $truck->alert_level = $truck->maintenanceAlertLevel();
                $truck->document_alerts = $truck->documents
                    ->filter(fn ($document) => in_array($document->computed_status, [DocumentComputedStatus::EXPIRING, DocumentComputedStatus::EXPIRED], true))
                    ->take(2);

                return $truck;
            });

        $drivers = Driver::query()
            ->with(['assignments' => function ($query) {
                $query->whereNotIn('status', [AssignmentStatus::Completed->value, AssignmentStatus::Cancelled->value])->latest('start_date');
            }, 'trainings', 'documents' => function ($query) {
                $query->orderBy('expires_at');
            }])
            ->when($this->driverStatus, function ($query) {
                $status = DriverStatus::tryFrom($this->driverStatus);

                if ($status) {
                    $query->where('status', $status->value);
                }
            })
            ->when($this->driverSearch, function ($query) {
                $term = '%' . trim($this->driverSearch) . '%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('license_number', 'like', $term);
                });
            })
            ->orderBy('status')
            ->orderBy('name')
            ->take(12)
            ->get()
            ->map(function (Driver $driver) {
                $driver->next_assignment = $driver->assignments->first();
                $driver->valid_trainings = $driver->trainings->filter(fn ($training) => ! $training->expires_at || $training->expires_at->isFuture());
                $driver->document_alerts = $driver->documents
                    ->filter(fn ($document) => in_array($document->computed_status, [DocumentComputedStatus::EXPIRING, DocumentComputedStatus::EXPIRED], true))
                    ->take(2);

                return $driver;
            });

        return view('livewire.fleet.availability-board', [
            'truckStats' => $truckStats,
            'driverStats' => $driverStats,
            'trucks' => $trucks,
            'drivers' => $drivers,
        ]);
    }
}
