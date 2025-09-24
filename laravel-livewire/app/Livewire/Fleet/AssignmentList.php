<?php

namespace App\Livewire\Fleet;

use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Truck;
use Livewire\Component;
use Livewire\WithPagination;

class AssignmentList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $truck_id = '';
    public $driver_id = '';

    public function deleteAssignment($id)
    {
        $assignment = Assignment::find($id);
        if ($assignment) {
            // Actualizar el estado del camión a disponible
            $truck = Truck::find($assignment->truck_id);
            if ($truck && $truck->status === 'in_use') {
                $truck->status = 'available';
                $truck->save();
            }
            
            $assignment->delete();
            session()->flash('message', 'Asignación eliminada correctamente.');
        }
    }

    public function render()
    {
        $assignments = Assignment::query()
            ->with(['truck', 'driver'])
            ->when($this->search, function ($query) {
                $query->where('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('truck', function ($q) {
                        $q->where('plate_number', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('driver', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->truck_id, function ($query) {
                $query->where('truck_id', $this->truck_id);
            })
            ->when($this->driver_id, function ($query) {
                $query->where('driver_id', $this->driver_id);
            })
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        $trucks = Truck::orderBy('plate_number')->get();
        $drivers = Driver::orderBy('name')->get();

        return view('livewire.fleet.assignment-list', [
            'assignments' => $assignments,
            'trucks' => $trucks,
            'drivers' => $drivers
        ]);
    }
}