<?php

namespace App\Livewire\Fleet;

use App\Models\Truck;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app.sidebar', ['title' => 'Gestion de Camiones'])]
#[Title('Gestion de Camiones')]
class TruckList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    
    public function deleteTruck($id)
    {
        $truck = Truck::find($id);
        if ($truck) {
            $truck->delete();
            session()->flash('message', 'Camion eliminado correctamente.');
        }
    }

    public function render()
    {
        $query = Truck::query();
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('plate_number', 'like', '%' . $this->search . '%')
                  ->orWhere('brand', 'like', '%' . $this->search . '%')
                  ->orWhere('model', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->status) {
            $query->where('status', $this->status);
        }
        
        $trucks = $query->withCount([
                'maintenances as pending_maintenances_count' => fn ($q) => $q->whereIn('status', ['scheduled', 'in_progress'])
            ])
            ->orderBy('plate_number')
            ->paginate(10);
        
        $statusTotals = Truck::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $maintenanceDueSoon = Truck::query()
            ->whereNotNull('next_maintenance')
            ->whereDate('next_maintenance', '<=', now()->addMonth())
            ->count();
        
        return view('livewire.fleet.truck-list', [
            'trucks' => $trucks,
            'statusTotals' => $statusTotals,
            'maintenanceDueSoon' => $maintenanceDueSoon,
        ]);
    }
}
