<?php

namespace App\Livewire\Fleet;

use App\Models\Maintenance;
use App\Models\Truck;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app.sidebar', ['title' => 'Mantenimiento'])]
#[Title('Mantenimiento')]
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

        $truck = $this->maintenance->truck;

        if ($truck) {
            if ($this->maintenance->status === 'completed') {
                $maintenanceDate = Carbon::parse($this->maintenance->maintenance_date);
                $truck->last_maintenance = $maintenanceDate;
                $truck->next_maintenance = $maintenanceDate->copy()->addMonths(3);
            }

            if (in_array($this->maintenance->status, ['scheduled', 'in_progress'], true)) {
                $truck->status = 'maintenance';
            } elseif (in_array($this->maintenance->status, ['completed', 'cancelled'], true)) {
                $truck->status = $this->resolveTruckStatus($truck);
            }

            $truck->save();
        }

        session()->flash('message', $this->isEdit ? 'Mantenimiento actualizado correctamente.' : 'Mantenimiento registrado correctamente.');

        return redirect()->route('fleet.maintenance.index');
    }

    protected function resolveTruckStatus(Truck $truck): string
    {
        $hasPendingMaintenance = $truck->maintenances()
            ->where('id', '!=', $this->maintenance->id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->exists();

        if ($hasPendingMaintenance) {
            return 'maintenance';
        }

        $hasActiveAssignments = $truck->assignments()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($hasActiveAssignments) {
            return 'in_use';
        }

        return $truck->status === 'out_of_service' ? 'out_of_service' : 'available';
    }

    public function render()
    {
        return view('livewire.fleet.maintenance-form');
    }
}