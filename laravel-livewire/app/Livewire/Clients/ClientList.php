<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class ClientList extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteClient(int $clientId): void
    {
        $client = Client::withCount(['orders', 'invoices'])->find($clientId);
        if (!$client) {
            return;
        }

        if ($client->orders_count > 0 || $client->invoices_count > 0) {
            session()->flash('error', 'No se puede eliminar el cliente porque tiene pedidos o facturas asociadas.');
            return;
        }

        $client->delete();
        session()->flash('message', 'Cliente eliminado correctamente.');
        $this->resetPage();
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, function ($query) {
                $query->where(function ($searchQuery) {
                    $searchQuery->where('business_name', 'like', '%' . $this->search . '%')
                        ->orWhere('tax_id', 'like', '%' . $this->search . '%')
                        ->orWhere('contact_name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('business_name')
            ->paginate(10);

        return view('livewire.clients.client-list', [
            'clients' => $clients,
        ]);
    }
}
