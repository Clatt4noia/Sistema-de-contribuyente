<?php

namespace App\Livewire\Fleet;

use App\Models\Truck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TruckForm extends Component
{
    use AuthorizesRequests;

    public Truck $truck;
    public bool $isEdit = false;
    public array $form = [];
    public array $maintenanceHistory = [];

    /**
     * @var array<string, string> $statusLabels
     */
    public array $statusLabels = [
        'available' => 'Disponible',
        'in_use' => 'En uso',
        'maintenance' => 'En mantenimiento',
        'out_of_service' => 'Fuera de servicio',
    ];

    /**
     * Etiquetas descriptivas para renderizar el estado del mantenimiento en la vista.
     *
     * @return array<string, array{label: string, class: string}>
     */
    public function getMaintenanceStatusTagsProperty(): array
    {
        return [
            'scheduled' => ['label' => 'Programado', 'class' => 'bg-warning-soft text-warning '],
            'in_progress' => ['label' => 'En progreso', 'class' => 'bg-accent-soft text-accent '],
            'completed' => ['label' => 'Completado', 'class' => 'bg-success-soft text-success-strong '],
            'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-danger-soft text-danger-strong '],
        ];
    }

    protected function rules(): array
    {
        return [
            // Validamos la placa garantizando unicidad, formato y longitud.
            'form.plate_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('trucks', 'plate_number')->ignore($this->truck->id),
            ],
            // Reglas de integridad para cada campo del formulario.
            'form.brand' => ['required', 'string', 'max:50'],
            'form.model' => ['required', 'string', 'max:50'],
            'form.year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'form.type' => ['required', 'string', 'max:50'],
            'form.capacity' => ['nullable', 'numeric', 'min:0'],
            'form.mileage' => ['nullable', 'integer', 'min:0'],
            'form.status' => ['required', 'string', 'in:available,in_use,maintenance,out_of_service'],
            'form.last_maintenance' => ['nullable', 'date'],
            'form.next_maintenance' => ['nullable', 'date'],
            'form.technical_details' => ['nullable', 'string'],
        ];
    }

    public function mount(?Truck $truck = null): void
    {
        if ($truck && $truck->exists) {
            $this->authorize('update', $truck);
            $this->truck = $truck;
            $this->isEdit = true;
        } else {
            $this->authorize('create', Truck::class);
            $this->truck = new Truck([
                'status' => 'available',
                'mileage' => 0,
            ]);
        }

        // Inicializamos el formulario a partir del modelo para que los campos
        // siempre tengan un valor consistente antes de interactuar con Livewire.
        $this->form = [
            'plate_number' => $this->truck->plate_number ?? '',
            'brand' => $this->truck->brand ?? '',
            'model' => $this->truck->model ?? '',
            'year' => $this->truck->year ?? (int) date('Y'),
            'type' => $this->truck->type ?? '',
            'capacity' => $this->truck->capacity ?? null,
            'mileage' => $this->truck->mileage ?? 0,
            'status' => $this->truck->status ?? 'available',
            'last_maintenance' => optional($this->truck->last_maintenance)->format('Y-m-d'),
            'next_maintenance' => optional($this->truck->next_maintenance)->format('Y-m-d'),
            'technical_details' => $this->truck->technical_details ?? '',
        ];

        $this->maintenanceHistory = $this->truck->exists
            ? $this->truck->maintenances()
                ->latest('maintenance_date')
                ->take(5)
                ->get()
                ->map(fn ($maintenance) => [
                    'id' => $maintenance->id,
                    'date' => optional($maintenance->maintenance_date)->format('d/m/Y'),
                    'type' => $maintenance->maintenance_type,
                    'status' => $maintenance->status,
                    'cost' => $maintenance->cost,
                ])
                ->toArray()
            : [];
    }

    public function save()
    {
        $this->authorize($this->isEdit ? 'update' : 'create', $this->isEdit ? $this->truck : Truck::class);

        // Validamos la data enviada desde el formulario desacoplada del modelo.
        $validated = $this->validate();
        $data = $validated['form'];

        // Normalizamos valores numericos y fechas para evitar nulos inconsistentes.
        $data['capacity'] = $data['capacity'] !== null ? (float) $data['capacity'] : null;
        $data['mileage'] = $data['mileage'] !== null ? (int) $data['mileage'] : 0;
        $data['last_maintenance'] = $data['last_maintenance'] ?: null;
        $data['next_maintenance'] = $data['next_maintenance'] ?: null;
        $data['technical_details'] = trim((string) $data['technical_details']) ?: null;

        // Sincronizamos la informacion con el modelo Eloquent y persistimos.
        $this->truck->fill($data);
        $this->truck->save();

        session()->flash('message', $this->isEdit ? 'Camion actualizado correctamente.' : 'Camion creado correctamente.');

        return redirect()->route('fleet.trucks.index');
    }

    public function render()
    {
        $this->authorize('viewAny', Truck::class);

        return view('livewire.fleet.truck-form');
    }
}
