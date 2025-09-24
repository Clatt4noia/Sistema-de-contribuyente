<?php

namespace App\Livewire\Fleet;

use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Truck;
use Livewire\Component;

class AssignmentForm extends Component
{
    public Assignment $assignment;
    public $isEdit = false;
    public $trucks = [];
    public $drivers = [];

    protected function rules()
    {
        return [
            'assignment.truck_id' => 'required|exists:trucks,id',
            'assignment.driver_id' => 'required|exists:drivers,id',
            'assignment.start_date' => 'required|date',
            'assignment.end_date' => 'nullable|date|after_or_equal:assignment.start_date',
            'assignment.status' => 'required|in:active,completed,cancelled',
            'assignment.description' => 'required|string|max:255',
            'assignment.notes' => 'nullable|string',
        ];
    }

    public function mount($id = null)
    {
        if ($id) {
            $this->assignment = Assignment::findOrFail($id);
            $this->isEdit = true;
        } else {
            $this->assignment = new Assignment();
            $this->assignment->status = 'active';
            $this->assignment->start_date = now()->format('Y-m-d');
        }

        // Cargar camiones disponibles o el camión asignado actualmente
        if ($this->isEdit) {
            $this->trucks = Truck::where('status', 'available')
                ->orWhere('id', $this->assignment->truck_id)
                ->orderBy('plate_number')
                ->get();
        } else {
            $this->trucks = Truck::where('status', 'available')
                ->orderBy('plate_number')
                ->get();
        }

        // Cargar conductores activos o el conductor asignado actualmente
        if ($this->isEdit) {
            $this->drivers = Driver::where('status', 'active')
                ->orWhere('id', $this->assignment->driver_id)
                ->orderBy('name')
                ->get();
        } else {
            $this->drivers = Driver::where('status', 'active')
                ->orderBy('name')
                ->get();
        }
    }

    public function save()
    {
        $this->validate();

        // Si es una nueva asignación, actualizar el estado del camión
        if (!$this->isEdit) {
            $truck = Truck::find($this->assignment->truck_id);
            if ($truck) {
                $truck->status = 'in_use';
                $truck->save();
            }
        }

        $this->assignment->save();

        session()->flash('message', $this->isEdit ? 'Asignación actualizada correctamente.' : 'Asignación creada correctamente.');
        return redirect()->route('fleet.assignments.index');
    }

    public function render()
    {
        return view('livewire.fleet.assignment-form');
    }
}