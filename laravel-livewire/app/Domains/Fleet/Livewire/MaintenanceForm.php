<?php

namespace App\Domains\Fleet\Livewire;

use App\Enums\Fleet\AssignmentStatus;
use App\Enums\Fleet\MaintenanceStatus;
use App\Enums\Fleet\TruckStatus;
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
        $maintenanceStatuses = array_map(static fn (MaintenanceStatus $status) => $status->value, MaintenanceStatus::cases());

        return [
            'form.truck_id' => 'required|exists:trucks,id',
            'form.maintenance_date' => 'required|date',
            'form.maintenance_type' => 'required|string|max:100',
            'form.cost' => 'required|numeric|min:0',
            'form.status' => ['required', 'string', 'in:' . implode(',', $maintenanceStatuses)],
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
            $this->maintenance->status = MaintenanceStatus::Scheduled;
            $this->maintenance->maintenance_date = now()->format('Y-m-d');
        }

        $statusValue = $this->maintenance->status instanceof MaintenanceStatus
            ? $this->maintenance->status->value
            : ($this->maintenance->status ?? MaintenanceStatus::Scheduled->value);

        $this->form = [
            'truck_id' => $this->maintenance->truck_id ?? '',
            'maintenance_date' => optional($this->maintenance->maintenance_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'maintenance_type' => $this->maintenance->maintenance_type ?? '',
            'cost' => $this->maintenance->cost ?? 0,
            'status' => $statusValue,
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
            'status' => MaintenanceStatus::from($data['status']),
            'odometer' => $data['odometer'] ?? null,
            'description' => $data['description'],
            'notes' => $data['notes'],
        ]);

        $this->maintenance->save();

        $truck = $this->maintenance->truck;

        if ($truck) {
            if ($this->maintenance->status === MaintenanceStatus::Completed) {
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

            if (in_array($this->maintenance->status, [MaintenanceStatus::Scheduled, MaintenanceStatus::InProgress], true)) {
                $truck->status = TruckStatus::Maintenance;
            } elseif (in_array($this->maintenance->status, [MaintenanceStatus::Completed, MaintenanceStatus::Cancelled], true)) {
                $truck->status = $this->resolveTruckStatus($truck);
            }

            $truck->save();
        }

        session()->flash('message', $this->isEdit ? 'Mantenimiento actualizado correctamente.' : 'Mantenimiento registrado correctamente.');

        return redirect()->route('fleet.maintenance.index');
    }

    protected function resolveTruckStatus(Truck $truck): TruckStatus
    {
        $hasPendingMaintenance = $truck->maintenances()
            ->where('id', '!=', $this->maintenance->id)
            ->whereIn('status', [MaintenanceStatus::Scheduled->value, MaintenanceStatus::InProgress->value])
            ->exists();

        if ($hasPendingMaintenance) {
            return TruckStatus::Maintenance;
        }

        $hasActiveAssignments = $truck->assignments()
            ->whereNotIn('status', [AssignmentStatus::Completed->value, AssignmentStatus::Cancelled->value])
            ->exists();

        if ($hasActiveAssignments) {
            return TruckStatus::InUse;
        }

        return $truck->status === TruckStatus::OutOfService ? TruckStatus::OutOfService : TruckStatus::Available;
    }

    public function render()
    {
        $this->authorize('viewAny', Maintenance::class);

        return view('livewire.fleet.maintenance-form');
    }
}
