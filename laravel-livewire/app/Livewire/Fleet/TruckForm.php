<?php

namespace App\Livewire\Fleet;

use App\Models\Truck;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app.sidebar', ['title' => 'Camion'])]
#[Title('Camion')]
class TruckForm extends Component
{
    public Truck $truck;
    public bool $isEdit = false;
    public array $maintenanceHistory = [];

    protected function rules(): array
    {
        return [
            'truck.plate_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('trucks', 'plate_number')->ignore($this->truck->id),
            ],
            'truck.brand' => ['required', 'string', 'max:50'],
            'truck.model' => ['required', 'string', 'max:50'],
            'truck.year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'truck.type' => ['required', 'string', 'max:50'],
            'truck.capacity' => ['nullable', 'numeric', 'min:0'],
            'truck.mileage' => ['nullable', 'integer', 'min:0'],
            'truck.status' => ['required', 'string', 'in:available,in_use,maintenance,out_of_service'],
            'truck.last_maintenance' => ['nullable', 'date'],
            'truck.next_maintenance' => ['nullable', 'date'],
            'truck.technical_details' => ['nullable', 'string'],
        ];
    }

    public function mount(?Truck $truck = null): void
    {
        if ($truck && $truck->exists) {
            $this->truck = $truck;
            $this->isEdit = true;
        } else {
            $this->truck = new Truck([
                'status' => 'available',
                'mileage' => 0,
            ]);
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
                    'status' => $maintenance->status,
                    'cost' => $maintenance->cost,
                ])
                ->toArray()
            : [];
    }

    public function save()
    {
        $validated = $this->validate();

        $this->truck->fill($validated['truck']);
        $this->truck->mileage = $this->truck->mileage ?? 0;
        $this->truck->save();

        session()->flash('message', $this->isEdit ? 'Camion actualizado correctamente.' : 'Camion creado correctamente.');

        return redirect()->route('fleet.trucks.index');
    }

    public function render()
    {
        return view('livewire.fleet.truck-form');
    }
}
