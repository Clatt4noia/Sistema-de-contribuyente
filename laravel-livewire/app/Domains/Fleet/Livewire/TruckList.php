<?php

namespace App\Domains\Fleet\Livewire;

use App\Enums\Fleet\MaintenanceStatus;
use App\Enums\Fleet\TruckStatus;
use App\Models\Truck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app.sidebar', ['title' => 'Gestion de Camiones'])]
#[Title('Gestion de Camiones')]
class TruckList extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public $search = '';
    public $status = '';
    
    public function deleteTruck($id)
    {
        $truck = Truck::findOrFail($id);

        $this->authorize('delete', $truck);

        $truck->delete();
        session()->flash('message', 'Camion eliminado correctamente.');
    }

    public function render()
    {
        $this->authorize('viewAny', Truck::class);

        $query = Truck::query();
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('plate_number', 'like', '%' . $this->search . '%')
                  ->orWhere('brand', 'like', '%' . $this->search . '%')
                  ->orWhere('model', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->status) {
            $status = TruckStatus::tryFrom($this->status);

            if ($status) {
                $query->where('status', $status->value);
            }
        }
        
        $trucks = $query->withCount([
                'maintenances as pending_maintenances_count' => fn ($q) => $q->whereIn('status', [MaintenanceStatus::Scheduled->value, MaintenanceStatus::InProgress->value])
            ])
            ->orderBy('plate_number')
            ->paginate(10);
        
        $statusTotals = Truck::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $maintenanceDueSoon = Truck::query()
            ->where(function ($query) {
                $query->whereNotNull('next_maintenance')
                    ->whereDate('next_maintenance', '<=', now()->addMonth())
                    ->orWhere(function ($mileageQuery) {
                        $mileageQuery->whereColumn('mileage', '>=', DB::raw('last_maintenance_mileage + maintenance_mileage_threshold'));
                    });
            })
            ->count();

        return view('livewire.fleet.truck-list', [
            'trucks' => $trucks,
            'statusTotals' => $statusTotals,
            'maintenanceDueSoon' => $maintenanceDueSoon,
        ]);
    }
}
