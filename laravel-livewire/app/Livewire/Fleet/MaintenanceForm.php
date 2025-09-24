<?php

namespace App\Livewire\Fleet;

use App\Models\Maintenance;
use App\Models\Truck;
use Livewire\Component;

class MaintenanceForm extends Component
{
    public Maintenance $maintenance;
    public bool $isEdit = false;
    public $trucks = [];

    protected function rules()
    {
        return [
            'maintenance.truck_id' => 'required|exists:trucks,id',
            'maintenance.maintenance_date' => 'required|date',
            'maintenance.maintenance_type' => 'required|string|max:100',
            'maintenance.cost' => 'required|numeric|min:0',
            'maintenance.status' => 'required|string|in:scheduled,in_progress,completed,cancelled',
            'maintenance.description' => 'nullable|string',
            'maintenance.notes' => 'nullable|string',
        ];
    }

    public function mount($id = null)
    {
        $this->trucks = Truck::orderBy('plate_number')->get();
        
        if ($id) {
            $this->maintenance = Maintenance::findOrFail($id);
            $this->isEdit = true;
        } else {
            $this->maintenance = new Maintenance();
            $this->maintenance->status = 'scheduled';
            $this->maintenance->maintenance_date = now()->format('Y-m-d');
        }
    }

    public function save()
    {
        $this->validate();
        
        $this->maintenance->save();
        
        // Actualizar la fecha del último mantenimiento en el camión
        $truck = Truck::find($this->maintenance->truck_id);
        if ($truck && $this->maintenance->status === 'completed') {
            $truck->last_maintenance = $this->maintenance->maintenance_date;
            
            // Programar el próximo mantenimiento (por ejemplo, 3 meses después)
            $truck->next_maintenance = date('Y-m-d', strtotime($this->maintenance->maintenance_date . ' + 3 months'));
            
            $truck->save();
        }
        
        session()->flash('message', $this->isEdit ? 'Mantenimiento actualizado correctamente.' : 'Mantenimiento registrado correctamente.');
        
        return redirect()->route('fleet.maintenance.index');
    }

    public function render()
    {
        return view('livewire.fleet.maintenance-form');
    }
}