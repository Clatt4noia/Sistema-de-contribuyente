<?php

namespace App\Livewire\Fleet;

use App\Models\Truck;
use Livewire\Component;

class TruckForm extends Component
{
    public Truck $truck;
    public bool $isEdit = false;

    protected function rules(): array
    {
        return [
            'truck.plate_number' => 'required|string|max:20|unique:trucks,plate_number,' . ($this->truck->id ?? ''),
            'truck.brand' => 'required|string|max:50',
            'truck.model' => 'required|string|max:50',
            'truck.year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'truck.type' => 'required|string|max:50',
            'truck.capacity' => 'nullable|numeric|min:0',
            'truck.mileage' => 'nullable|integer|min:0',
            'truck.status' => 'required|string|in:available,in_use,maintenance,out_of_service',
            'truck.last_maintenance' => 'nullable|date',
            'truck.next_maintenance' => 'nullable|date',
            'truck.technical_details' => 'nullable|string',
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
    }

    public function save()
    {
        $this->validate();

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
