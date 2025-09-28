<?php

namespace App\Livewire\Fleet;

use App\Models\Truck;
use Livewire\Component;
use Livewire\WithPagination;

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
        
        $trucks = $query->paginate(10);
        
        return view('livewire.fleet.truck-list', [
            'trucks' => $trucks
        ]);
    }
}
