<?php

namespace App\Livewire\Fleet;

use App\Models\Driver;
use Livewire\Component;

class DriverForm extends Component
{
    public Driver $driver;
    public bool $isEdit = false;

    protected function rules()
    {
        return [
            'driver.name' => 'required|string|max:100',
            'driver.last_name' => 'required|string|max:100',
            'driver.document_number' => 'required|string|max:20',
            'driver.license_number' => 'required|string|max:20',
            'driver.license_expiration' => 'required|date',
            'driver.phone' => 'nullable|string|max:20',
            'driver.email' => 'nullable|email|max:100',
            'driver.address' => 'nullable|string|max:255',
            'driver.status' => 'required|string|in:active,inactive,on_leave',
            'driver.notes' => 'nullable|string',
        ];
    }

    public function mount($id = null)
    {
        if ($id) {
            $this->driver = Driver::findOrFail($id);
            $this->isEdit = true;
        } else {
            $this->driver = new Driver();
            $this->driver->status = 'active';
        }
    }

    public function save()
    {
        $this->validate();
        
        $this->driver->save();
        
        session()->flash('message', $this->isEdit ? 'Chofer actualizado correctamente.' : 'Chofer registrado correctamente.');
        
        return redirect()->route('fleet.drivers.index');
    }

    public function render()
    {
        return view('livewire.fleet.driver-form');
    }
}
