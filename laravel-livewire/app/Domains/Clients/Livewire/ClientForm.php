<?php

namespace App\Domains\Clients\Livewire;

use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ClientForm extends Component
{
    use AuthorizesRequests;

    public Client $client;
    public bool $isEdit = false;
    public array $form = [];

    protected function rules(): array
    {
        $clientId = $this->client->id ?? 'NULL';

        return [
            // Datos generales del cliente.
            'form.business_name' => ['required', 'string', 'max:150'],
            'form.tax_id' => ['required', 'string', 'max:20', 'unique:clients,tax_id,' . $clientId],
            'form.contact_name' => ['nullable', 'string', 'max:100'],
            'form.email' => ['nullable', 'email', 'max:150'],
            'form.phone' => ['nullable', 'string', 'max:30'],
            'form.billing_address' => ['nullable', 'string', 'max:255'],
            'form.payment_terms' => ['nullable', 'string', 'max:100'],
            'form.notes' => ['nullable', 'string'],
        ];
    }

    public function mount($client = null): void
    {
        // 1) Detectamos si estamos editando o creando para aplicar la política correcta.
        if ($client) {
            $this->client = $client;
            $this->authorize('update', $this->client);
            $this->isEdit = true;
        } else {
            $this->authorize('create', Client::class);
            $this->client = new Client();
        }

        // 2) Sincronizamos el formulario desacoplado para evitar problemas
        //    con el enlace directo al modelo al crear nuevos registros.
        $this->form = [
            'business_name' => $this->client->business_name ?? '',
            'tax_id' => $this->client->tax_id ?? '',
            'contact_name' => $this->client->contact_name ?? '',
            'email' => $this->client->email ?? '',
            'phone' => $this->client->phone ?? '',
            'billing_address' => $this->client->billing_address ?? '',
            'payment_terms' => $this->client->payment_terms ?? '',
            'notes' => $this->client->notes ?? '',
        ];
    }

    public function save()
    {
        $this->authorize($this->isEdit ? 'update' : 'create', $this->isEdit ? $this->client : Client::class);

        // 3) Validamos y limpiamos datos antes de persistir.
        $validated = $this->validate();
        $data = $validated['form'];

        // 4) Aseguramos que campos opcionales lleguen como null para mantener la BD limpia.
        foreach (['contact_name', 'email', 'phone', 'billing_address', 'payment_terms', 'notes'] as $optionalField) {
            $data[$optionalField] = trim((string) ($data[$optionalField] ?? '')) ?: null;
        }

        // 5) Ajustamos el RUC eliminando espacios en blanco accidentales.
        $data['tax_id'] = trim($data['tax_id']);

        // 6) Persistimos usando fill para respetar la protección de asignación masiva.
        $this->client->fill($data);
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
