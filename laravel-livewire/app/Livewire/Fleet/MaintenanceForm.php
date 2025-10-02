<?php

namespace App\Livewire\Fleet;

use App\Models\Maintenance;
use App\Models\Truck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app.sidebar', ['title' => 'Mantenimiento'])]
#[Title('Mantenimiento')]
class MaintenanceForm extends Component
{
    use AuthorizesRequests;

    public Maintenance $maintenance;
    public bool $isEdit = false;
    public array $form = [];
    public $trucks = [];

    protected function rules()
    {
        return [
            'form.truck_id' => 'required|exists:trucks,id',
            'form.maintenance_date' => 'required|date',
            'form.maintenance_type' => 'required|string|max:100',
            'form.cost' => 'required|numeric|min:0',
            'form.status' => 'required|string|in:scheduled,in_progress,completed,cancelled',
            'form.odometer' => 'nullable|integer|min:0',
            'form.description' => 'nullable|string',
            'form.notes' => 'nullable|string',
        ];
    }

    public function mount($id = null)
    {
        if ($id) {
            $this->maintenance = Maintenance::findOrFail($id);
            $this->authorize('update', $this->maintenance);
            $this->isEdit = true;
        } else {
            $this->authorize('create', Maintenance::class);
            $this->maintenance = new Maintenance();
            $this->maintenance->status = 'scheduled';
            $this->maintenance->maintenance_date = now()->format('Y-m-d');
        }

        $this->form = [
            'truck_id' => $this->maintenance->truck_id ?? '',
            'maintenance_date' => optional($this->maintenance->maintenance_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'maintenance_type' => $this->maintenance->maintenance_type ?? '',
            'cost' => $this->maintenance->cost ?? 0,
            'status' => $this->maintenance->status ?? 'scheduled',
            'odometer' => $this->maintenance->odometer ?? null,
            'description' => $this->maintenance->description ?? '',
            'notes' => $this->maintenance->notes ?? '',
        ];

        $this->trucks = Truck::orderBy('plate_number')->get();
    }

    public function save()
    {
        $this->authorize($this->isEdit ? 'update' : 'create', $this->isEdit ? $this->maintenance : Maintenance::class);

        $validated = $this->validate();

        $data = $validated['form'];
        $data['description'] = trim((string) $data['description']) ?: null;
        $data['notes'] = trim((string) $data['notes']) ?: null;

        $this->maintenance->fill([
            'truck_id' => $data['truck_id'],
            'maintenance_date' => Carbon::parse($data['maintenance_date']),
            'maintenance_type' => $data['maintenance_type'],
            'cost' => $data['cost'],
            'status' => $data['status'],
            'odometer' => $data['odometer'] ?? null,
            'description' => $data['description'],
            'notes' => $data['notes'],
        ]);

        $this->maintenance->save();

        $truck = $this->maintenance->truck;

        if ($truck) {
            if ($this->maintenance->status === 'completed') {
                $maintenanceDate = Carbon::parse($this->maintenance->maintenance_date);
                $truck->last_maintenance = $maintenanceDate;
                $intervalDays = max((int) ($truck->maintenance_interval_days ?? 90), 1);
                $truck->next_maintenance = $maintenanceDate->copy()->addDays($intervalDays);

                if (! empty($data['odometer'])) {
                    $truck->last_maintenance_mileage = $data['odometer'];

                    if ($data['odometer'] > ($truck->mileage ?? 0)) {
                        $truck->mileage = $data['odometer'];
                    }
                }
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
        $this->authorize('viewAny', Maintenance::class);

        return view('livewire.fleet.maintenance-form');
    }
}