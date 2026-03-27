<?php

namespace App\Domains\Fleet\Livewire;

use App\Enums\Fleet\MaintenanceStatus;
use App\Enums\Fleet\TruckStatus;
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
    public ?string $lastMaintenanceDisplay = null;
    public ?string $nextMaintenanceDisplay = null;

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
        $truckStatuses = array_filter(
            TruckStatus::cases(),
            static fn (TruckStatus $status) => $status !== TruckStatus::Reserved
        );

        $truckStatusValues = array_map(static fn (TruckStatus $status) => $status->value, $truckStatuses);

        return [
            // Validamos la placa garantizando unicidad, formato y longitud.
            'form.plate_number' => [
                'required',
                'string',
                'regex:/^[A-Z][0-9][A-Z][0-9]{3}$/',
                Rule::unique('trucks', 'plate_number')->ignore($this->truck->id),
            ],
            // Reglas de integridad para cada campo del formulario.
            'form.brand' => ['required', 'string', 'max:50'],
            'form.model' => ['required', 'string', 'max:50'],
            'form.year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'form.type' => ['required', 'string', 'max:50'],
            'form.tuce_number' => ['nullable', 'string', 'regex:/^[A-Za-z0-9]+$/', 'max:15'],
            'form.is_secondary' => ['boolean'],
            'form.capacity' => ['nullable', 'numeric', 'min:0'],
            'form.mileage' => ['nullable', 'integer', 'min:0'],
            'form.status' => ['required', 'string', 'in:' . implode(',', $truckStatusValues)],
            'form.special_auth_issuer' => ['nullable', 'string', 'in:MTC'],
            'form.special_auth_number' => ['nullable', 'string', 'regex:/^[A-Za-z0-9]+$/', 'max:15'],
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
                'status' => TruckStatus::Available,
                'mileage' => 0,
            ]);
        }

        $statusValue = $this->truck->status instanceof TruckStatus
            ? $this->truck->status->value
            : ($this->truck->status ?? TruckStatus::Available->value);

        // Inicializamos el formulario a partir del modelo para que los campos
        // siempre tengan un valor consistente antes de interactuar con Livewire.
        $this->form = [
            'plate_number' => $this->truck->plate_number ?? '',
            'brand' => $this->truck->brand ?? '',
            'model' => $this->truck->model ?? '',
            'year' => $this->truck->year ?? (int) date('Y'),
            'type' => $this->truck->type ?? '',
            'tuce_number' => $this->truck->tuce_number ?? '',
            'is_secondary' => (bool) $this->truck->is_secondary,
            'special_auth_issuer' => $this->truck->special_auth_issuer ?? '',
            'special_auth_number' => $this->truck->special_auth_number ?? '',
            'capacity' => $this->truck->capacity ?? null,
            'mileage' => $this->truck->mileage ?? 0,
            'status' => $statusValue,
            'technical_details' => $this->truck->technical_details ?? '',
        ];

        if (! $this->truck->exists) {
            $this->lastMaintenanceDisplay = 'No registrado';
            $this->nextMaintenanceDisplay = 'No programado';
        } else {
            $lastDerived = $this->truck->last_maintenance_derived;
            $nextDerived = $this->truck->next_maintenance_derived;

            $this->lastMaintenanceDisplay = $lastDerived?->format('d/m/Y')
                ?? $this->truck->last_maintenance?->format('d/m/Y')
                ?? 'No registrado';

            $this->nextMaintenanceDisplay = $nextDerived?->format('d/m/Y')
                ?? $this->truck->next_maintenance?->format('d/m/Y')
                ?? 'No programado';
        }

        $this->maintenanceHistory = $this->truck->exists
            ? $this->truck->maintenances()
                ->latest('maintenance_date')
                ->take(5)
                ->get()
                ->map(fn ($maintenance) => [
                    'id' => $maintenance->id,
                    'date' => optional($maintenance->maintenance_date)->format('d/m/Y'),
                    'type' => $maintenance->maintenance_type,
                    'status' => $maintenance->status instanceof MaintenanceStatus ? $maintenance->status->value : $maintenance->status,
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
        $data['tuce_number'] = trim((string) ($data['tuce_number'] ?? '')) ?: null;
        $data['special_auth_issuer'] = trim((string) ($data['special_auth_issuer'] ?? '')) ?: null;
        $data['special_auth_number'] = trim((string) ($data['special_auth_number'] ?? '')) ?: null;
        $data['technical_details'] = trim((string) $data['technical_details']) ?: null;

        // Sincronizamos la informacion con el modelo Eloquent y persistimos.
        $this->truck->fill($data);
        $this->truck->save();

        if ($this->isEdit) {
            session()->flash('message', 'Camión actualizado correctamente.');

            return redirect()->route('fleet.trucks.index');
        }

        session()->flash('message', 'Camión creado. Ahora puedes adjuntar documentos (Certificado MTC, SOAT, etc.).');

        return redirect()->route('fleet.trucks.edit', $this->truck);
    }

    public function render()
    {
        $this->authorize('viewAny', Truck::class);

        return view('livewire.fleet.truck-form', [
            'maintenanceStatusTags' => $this->maintenanceStatusTags,
        ]);
    }
}
