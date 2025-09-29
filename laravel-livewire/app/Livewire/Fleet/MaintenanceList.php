<?php

namespace App\Livewire\Fleet;

use App\Models\Maintenance;
use App\Models\Truck;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app.sidebar', ['title' => 'Mantenimientos'])]
#[Title('Mantenimientos')]
class MaintenanceList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $truck_id = '';

    public function deleteMaintenance($id)
    {
        $maintenance = Maintenance::find($id);
        if ($maintenance) {
            $maintenance->delete();
            session()->flash('message', 'Registro de mantenimiento eliminado correctamente.');
        }
    }

    public function render()
    {
        $maintenances = Maintenance::query()
            ->with('truck')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('maintenance_type', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhereHas('truck', function ($truckQuery) {
                          $truckQuery->where('plate_number', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->truck_id, function ($query) {
                $query->where('truck_id', $this->truck_id);
            })
            ->orderBy('maintenance_date', 'desc')
            ->paginate(10);

        $trucks = Truck::orderBy('plate_number')->get();

        return view('livewire.fleet.maintenance-list', [
            'maintenances' => $maintenances,
            'trucks' => $trucks
        ]);
    }
}