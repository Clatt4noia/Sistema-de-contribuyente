<?php

namespace App\Domains\Fleet\Livewire;

use App\Models\Assignment;
use App\Models\Driver;
use App\Models\DriverTraining;
use App\Models\Order;
use App\Models\Truck;
use App\Services\Fleet\AssignmentService;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app.sidebar', ['title' => 'Asignacion'])]
#[Title('Asignacion')]
class AssignmentForm extends Component
{
    use AuthorizesRequests;

    public Assignment $assignment;
    protected AssignmentService $assignmentService;
    public bool $isEdit = false;
    public array $form = [];
    public $trucks = [];
    public $drivers = [];
    public $orders = [];
    public ?Order $orderPreview = null;
    public string $mode = 'manual';
    public ?string $autoAssignAlert = null;

    public function boot(AssignmentService $assignmentService): void
    {
        $this->assignmentService = $assignmentService;
    }

    protected function rules(): array
    {
        return [
            'form.order_id' => 'required|exists:orders,id',
            'form.truck_id' => 'required|exists:trucks,id',
            'form.driver_id' => 'required|exists:drivers,id',
            'form.start_date' => 'required|date',
            'form.end_date' => 'nullable|date|after_or_equal:form.start_date',
            'form.status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'form.description' => 'required|string|max:255',
            'form.notes' => 'nullable|string',
            'mode' => 'required|in:manual,automatic',
        ];
    }

    public function mount($id = null): void
    {
        if ($id) {
            $this->assignment = Assignment::findOrFail($id);
            $this->authorize('update', $this->assignment);
            $this->isEdit = true;

            if ($this->assignment->start_date) {
                $this->assignment->start_date = $this->assignment->start_date->format('Y-m-d\TH:i');
            }

            if ($this->assignment->end_date) {
                $this->assignment->end_date = $this->assignment->end_date->format('Y-m-d\TH:i');
            }
        } else {
            $this->authorize('create', Assignment::class);
            $this->assignment = new Assignment([
                'status' => 'scheduled',
                'start_date' => now()->format('Y-m-d\TH:i'),
            ]);
        }

        $this->form = [
            'order_id' => $this->assignment->order_id ?? '',
            'truck_id' => $this->assignment->truck_id ?? '',
            'driver_id' => $this->assignment->driver_id ?? '',
            'start_date' => $this->assignment->start_date instanceof Carbon
                ? $this->assignment->start_date->format('Y-m-d\TH:i')
                : ($this->assignment->start_date ?? now()->format('Y-m-d\TH:i')),
            'end_date' => $this->assignment->end_date instanceof Carbon
                ? $this->assignment->end_date->format('Y-m-d\TH:i')
                : ($this->assignment->end_date ?? ''),
            'status' => $this->assignment->status ?? 'scheduled',
            'description' => $this->assignment->description ?? '',
            'notes' => $this->assignment->notes ?? '',
        ];

        $this->loadOptions();
    }

    public function updatedMode(): void
    {
        $this->autoAssignAlert = null;

        if ($this->mode === 'automatic') {
            $this->autoAssignResources();
        }
    }

    public function updatedFormOrderId(): void
    {
        $this->loadOptions();
    }

    public function updatedFormStatus(): void
    {
        $this->loadOptions();
    }

    public function save()
    {
        $this->authorize(
            $this->isEdit ? 'update' : 'create',
            $this->isEdit ? $this->assignment : Assignment::class
        );

        if ($this->mode === 'automatic' && empty($this->form['truck_id']) && empty($this->form['driver_id'])) {
            $this->autoAssignResources();

            if (empty($this->form['truck_id']) || empty($this->form['driver_id'])) {
                $this->addError('mode', 'No se encontraron recursos disponibles para asignacion automatica.');
                return;
            }
        }

        $validated = $this->validate();
        $this->assignmentService->save($this->assignment, $validated['form']);

        session()->flash('message', $this->isEdit ? 'Asignacion actualizada correctamente.' : 'Asignacion creada correctamente.');
        return redirect()->route('fleet.assignments.index');
    }

    public function render()
    {
        $this->authorize('viewAny', Assignment::class);

        return view('livewire.fleet.assignment-form');
    }

    protected function loadOptions(): void
    {
        $this->authorize('viewAny', Order::class);
        $this->authorize('viewAny', Truck::class);
        $this->authorize('viewAny', Driver::class);

        $orderId = $this->form['order_id'] ?? null;
        $start = isset($this->form['start_date']) ? Carbon::parse($this->form['start_date']) : now();
        $end = isset($this->form['end_date']) && $this->form['end_date'] ? Carbon::parse($this->form['end_date']) : $start->copy();

        $this->orders = Order::query()
            ->where(function ($query) use ($orderId) {
                $query->whereIn('status', ['pending', 'en_route']);
                if ($orderId) {
                    $query->orWhere('id', $orderId);
                }
            })
            ->orderBy('pickup_date')
            ->get();

        $this->trucks = Truck::query()
            ->with('maintenances')
            ->where(function ($query) {
                $query->whereIn('status', ['available', 'in_use']);
                if (! empty($this->form['truck_id'])) {
                    $query->orWhere('id', $this->form['truck_id']);
                }
            })
            ->orderBy('plate_number')
            ->get()
            ->filter(function (Truck $truck) use ($start, $end) {
                if ($this->assignment->truck_id === $truck->id) {
                    return true;
                }

                if ($truck->requiresMaintenanceAlert($start)) {
                    return false;
                }

                $hasPendingMaintenance = $truck->maintenances
                    ->filter(fn ($maintenance) => in_array($maintenance->status, ['scheduled', 'in_progress'], true))
                    ->first(fn ($maintenance) => $maintenance->maintenance_date && $maintenance->maintenance_date->between($start->copy()->startOfDay(), $end->copy()->endOfDay()));

                return ! $hasPendingMaintenance;
            })
            ->values();

        $this->drivers = Driver::query()
            ->with('trainings')
            ->where(function ($query) {
                $query->whereIn('status', ['active', 'assigned']);
                if (! empty($this->form['driver_id'])) {
                    $query->orWhere('id', $this->form['driver_id']);
                }
            })
            ->orderBy('name')
            ->get()
            ->filter(function (Driver $driver) use ($start, $end) {
                if ($this->assignment->driver_id === $driver->id) {
                    return true;
                }

                if (! $driver->hasValidLicenseAt($start)) {
                    return false;
                }

                if (! $driver->isAvailableBetween($start, $end, $this->assignment->id)) {
                    return false;
                }

                return $driver->trainings->contains(fn (DriverTraining $training) => ! $training->expires_at || $training->expires_at->greaterThanOrEqualTo($start));
            })
            ->values();

        // Guardamos una vista previa del pedido seleccionado para alimentar el resumen lateral.
        $this->orderPreview = $this->orders->firstWhere('id', (int) ($this->form['order_id'] ?? 0));
    }

    public function autoAssignResources(): void
    {
        $this->autoAssignAlert = null;

        $start = isset($this->form['start_date']) ? Carbon::parse($this->form['start_date']) : now();
        $end = isset($this->form['end_date']) && $this->form['end_date'] ? Carbon::parse($this->form['end_date']) : $start->copy();

        $truck = $this->assignmentService->findAvailableTruck($this->assignment, $start, $end);
        $driver = $this->assignmentService->findAvailableDriver($this->assignment, $start, $end);

        if (! $truck || ! $driver) {
            $this->autoAssignAlert = 'No hay camiones o choferes disponibles en el rango seleccionado.';
            return;
        }

        $this->form['truck_id'] = $truck->id;
        $this->form['driver_id'] = $driver->id;
    }
}
