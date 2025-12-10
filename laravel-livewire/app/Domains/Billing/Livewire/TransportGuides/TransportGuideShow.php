<?php

namespace App\Domains\Billing\Livewire\TransportGuides;

use App\Models\TransportGuide;
use App\Services\Billing\TransportGuideIssuer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

// Usar el type de la guía para mostrar encabezados GRE-T/GRE-R y regresar al índice adecuado.
class TransportGuideShow extends Component
{
    use AuthorizesRequests;

    public TransportGuide $transportGuide;
    public string $type = TransportGuide::TYPE_TRANSPORTISTA;
    public bool $confirmingIssue = false;
    public bool $processingIssue = false;

    public function mount(TransportGuide $transportGuide): void
    {
        $this->transportGuide = $transportGuide->load('items', 'client');
        $this->authorize('view', $this->transportGuide);
        $this->type = in_array($transportGuide->type, [TransportGuide::TYPE_TRANSPORTISTA, TransportGuide::TYPE_REMITENTE], true)
            ? $transportGuide->type
            : TransportGuide::TYPE_TRANSPORTISTA;
    }

    public function confirmIssue(): void
    {
        $this->authorize('issue', $this->transportGuide);
        $this->confirmingIssue = true;
    }

    public function issueGuide(): void
    {
        $this->authorize('issue', $this->transportGuide);

        $this->processingIssue = true;

        try {
            DB::transaction(function () {
                app(TransportGuideIssuer::class)->issue($this->transportGuide);
            });

            session()->flash('message', 'Guía emitida correctamente a SUNAT.');
        } catch (\Throwable $exception) {
            $this->transportGuide->forceFill([
                'sunat_status' => TransportGuide::STATUS_ERROR,
                'sunat_notes' => $exception->getMessage(),
            ])->save();

            session()->flash('error', 'No se pudo emitir la guía. ' . $exception->getMessage());
        } finally {
            $this->processingIssue = false;
            $this->confirmingIssue = false;
            $this->transportGuide->refresh();
        }
    }

    public function render()
    {
        $this->authorize('view', $this->transportGuide);

        return view('livewire.billing.transport-guides.show');
    }
}
