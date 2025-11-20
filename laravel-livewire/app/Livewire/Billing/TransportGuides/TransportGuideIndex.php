<?php

namespace App\Livewire\Billing\TransportGuides;

use App\Models\TransportGuide;
use App\Services\Billing\TransportGuideIssuer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class TransportGuideIndex extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public string $type = TransportGuide::TYPE_TRANSPORTISTA;
    public string $search = '';
    public string $status = '';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public bool $confirmingIssue = false;
    public ?int $selectedGuideId = null;
    public bool $processingIssue = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'dateFrom' => ['except' => null],
        'dateTo' => ['except' => null],
    ];

    public function mount(string $type = TransportGuide::TYPE_TRANSPORTISTA): void
    {
        $this->type = in_array($type, [TransportGuide::TYPE_TRANSPORTISTA, TransportGuide::TYPE_REMITENTE], true)
            ? $type
            : TransportGuide::TYPE_TRANSPORTISTA;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function confirmIssue(int $guideId): void
    {
        $this->selectedGuideId = $guideId;
        $this->confirmingIssue = true;
    }

    public function issueSelectedGuide(): void
    {
        if (! $this->selectedGuideId) {
            return;
        }

        /** @var TransportGuide $guide */
        $guide = TransportGuide::with('items')->findOrFail($this->selectedGuideId);

        $this->authorize('issue', $guide);

        $this->processingIssue = true;

        try {
            DB::transaction(function () use ($guide) {
                app(TransportGuideIssuer::class)->issue($guide);
            });

            session()->flash('message', 'Guía emitida correctamente a SUNAT.');
        } catch (\Throwable $exception) {
            $guide->forceFill([
                'sunat_status' => TransportGuide::STATUS_ERROR,
                'sunat_notes' => $exception->getMessage(),
            ])->save();

            session()->flash('error', 'No se pudo emitir la guía. ' . $exception->getMessage());
        } finally {
            $this->processingIssue = false;
            $this->confirmingIssue = false;
            $this->selectedGuideId = null;
        }
    }

    public function render()
    {
        $this->authorize('viewAny', TransportGuide::class);

        $guides = $this->queryGuides();

        return view('livewire.billing.transport-guides.index', [
            'guides' => $guides,
            'type' => $this->type,
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    protected function queryGuides(): LengthAwarePaginator
    {
        return TransportGuide::query()
            ->where('type', $this->type)
            ->when($this->search, function ($query) {
                $query->where(function ($searchQuery) {
                    $searchQuery->where('series', 'like', '%' . $this->search . '%')
                        ->orWhere('correlative', 'like', '%' . $this->search . '%')
                        ->orWhere('full_code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, fn ($query) => $query->where('sunat_status', $this->status))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('issue_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('issue_date', '<=', $this->dateTo))
            ->orderByDesc('issue_date')
            ->orderByDesc('correlative')
            ->paginate(10);
    }

    protected function statusLabels(): array
    {
        return [
            TransportGuide::STATUS_DRAFT => 'Borrador',
            TransportGuide::STATUS_PENDING => 'Pendiente',
            TransportGuide::STATUS_SENT => 'Enviado',
            TransportGuide::STATUS_ACCEPTED => 'Aceptado',
            TransportGuide::STATUS_REJECTED => 'Rechazado',
            TransportGuide::STATUS_CANCELLED => 'Anulado',
            TransportGuide::STATUS_ERROR => 'Error',
        ];
    }
}
