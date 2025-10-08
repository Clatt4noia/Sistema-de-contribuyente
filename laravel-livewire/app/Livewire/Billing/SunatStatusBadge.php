<?php

namespace App\Livewire\Billing;

use Livewire\Component;

class SunatStatusBadge extends Component
{
    public string $status = 'pendiente';
    public ?string $message = null;

    public function mount(string $status, ?string $message = null): void
    {
        $this->status = $status;
        $this->message = $message;
    }

    public function render()
    {
        $variant = match ($this->status) {
            'aceptado' => 'emerald',
            'rechazado' => 'rose',
            'observado' => 'amber',
            default => 'slate',
        };

        return view('livewire.billing.sunat-status-badge', [
            'variant' => $variant,
            'status' => $this->status,
            'message' => $this->message,
        ]);
    }
}
