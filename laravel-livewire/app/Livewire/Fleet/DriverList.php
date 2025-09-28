<?php

namespace App\Livewire\Fleet;

use App\Models\Driver;
use Livewire\Component;
use Livewire\WithPagination;

class DriverList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';

    public function deleteDriver($id): void
    {
        $driver = Driver::find($id);
        if ($driver) {
            $driver->delete();
            session()->flash('message', 'Chofer eliminado correctamente.');
        }
    }

    public function render()
    {
        $drivers = Driver::query()
            ->with(['schedules', 'evaluations'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('document_number', 'like', '%' . $this->search . '%')
                      ->orWhere('license_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.fleet.driver-list', [
            'drivers' => $drivers,
        ]);
    }
}
