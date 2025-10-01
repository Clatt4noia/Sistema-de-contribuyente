<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ClientForm extends Component
{
    use AuthorizesRequests;

    public Client $client;
    public bool $isEdit = false;

    protected function rules(): array
    {
        $clientId = $this->client->id ?? 'NULL';

        return [
            'client.business_name' => 'required|string|max:150',
            'client.tax_id' => 'required|string|max:20|unique:clients,tax_id,' . $clientId,
            'client.contact_name' => 'nullable|string|max:100',
            'client.email' => 'nullable|email|max:150',
            'client.phone' => 'nullable|string|max:30',
            'client.billing_address' => 'nullable|string|max:255',
            'client.payment_terms' => 'nullable|string|max:100',
            'client.notes' => 'nullable|string',
        ];
    }

    public function mount($client = null): void
    {
        if ($client) {
            $this->client = $client;
            $this->authorize('update', $this->client);
            $this->isEdit = true;
        } else {
            $this->authorize('create', Client::class);
            $this->client = new Client();
        }
    }

    public function save()
    {
        $this->authorize($this->isEdit ? 'update' : 'create', $this->isEdit ? $this->client : Client::class);

        $this->validate();

        $this->client->save();

        session()->flash('message', $this->isEdit ? 'Cliente actualizado correctamente.' : 'Cliente creado correctamente.');
        return redirect()->route('clients.index');
    }

    public function render()
    {
        $this->authorize('viewAny', Client::class);

        return view('livewire.clients.client-form');
    }
}
