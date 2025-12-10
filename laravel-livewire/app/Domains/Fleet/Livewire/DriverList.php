<?php

namespace App\Domains\Fleet\Livewire;

use App\Models\Driver;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app.sidebar', ['title' => 'Gestion de Choferes'])]
#[Title('Gestion de Choferes')]
class DriverList extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public $search = '';
    public $status = '';

    public function deleteDriver($id): void
    {
        $driver = Driver::findOrFail($id);

        $this->authorize('delete', $driver);

        $driver->delete();
        session()->flash('message', 'Chofer eliminado correctamente.');
    }

    public function render()
    {
        $this->authorize('viewAny', Driver::class);

        $drivers = Driver::query()
            ->with(['schedules', 'evaluations', 'trainings'])
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
