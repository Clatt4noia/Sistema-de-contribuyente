<?php

namespace App\Domains\Billing\Livewire;

use App\Domains\Billing\Jobs\SendElectronicInvoice;
use App\Models\Invoice;
use App\Models\TransportGuide;
use App\Domains\Billing\Services\TransportGuideIssuer;
use App\Domains\Billing\Support\SunatStatusAggregator;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Throwable;

class SunatUnifiedDashboard extends Component
{
    use AuthorizesRequests;

    public string $dateFrom;
    public string $dateTo;
    public string $series = '';
    public string $documentType = 'all';
    public string $sunatStatus = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Invoice::class);

        $this->dateFrom = Carbon::now()->subDays(30)->toDateString();
        $this->dateTo = Carbon::now()->toDateString();
    }

    public function retry(string $type, int $id): void
    {
        if ($type === 'invoice') {
            $this->retryInvoice($id);
            return;
        }

        if ($type === 'gre') {
            $this->retryGuide($id);
        }
    }

    public function render()
    {
        return view('livewire.billing.sunat-unified-dashboard', [
            'rows' => $this->rows,
            'exportQuery' => http_build_query($this->currentFilters()),
        ])->layout('components.layouts.dashboard', [
            'title' => __('Seguimiento SUNAT'),
        ]);
    }

    #[Computed]
    public function rows()
    {
        return app(SunatStatusAggregator::class)->forFilters($this->currentFilters());
    }

    /**
     * @return array<string, string>
     */
    protected function currentFilters(): array
    {
        return [
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'series' => $this->series,
            'document_type' => $this->documentType,
            'sunat_status' => $this->sunatStatus,
        ];
    }

    protected function retryInvoice(int $invoiceId): void
    {
        $invoice = Invoice::with('client')->findOrFail($invoiceId);

        $this->authorize('update', $invoice);

        $items = $invoice->metadata['items'] ?? [];

        if (empty($items)) {
            $this->addError('sunat', 'La factura no tiene ítems configurados para reenviar a SUNAT.');
            return;
        }

        $companyData = [
            'ruc' => $invoice->ruc_emisor,
            'legal_name' => Config::get('app.name', 'Carlos Gabriel Transporte S.A.C.'),
            'commercial_name' => Config::get('app.name', 'Carlos Gabriel Transporte S.A.C.'),
        ];

        $customerData = [
            'ruc' => $invoice->ruc_receptor,
            'scheme_id' => '6',
            'name' => $invoice->client?->business_name ?? 'Cliente sin razón social',
        ];

        SendElectronicInvoice::dispatch($invoice, $items, $companyData, $customerData)
            ->onQueue(config('greenter.queues.sunat', 'sunat'));

        session()->flash('message', 'Se reenviará el comprobante a SUNAT. Revisa el estado en unos minutos.');
    }

    protected function retryGuide(int $guideId): void
    {
        /** @var TransportGuide $guide */
        $guide = TransportGuide::with('items')->findOrFail($guideId);

        $this->authorize('issue', $guide);

        try {
            DB::transaction(function () use ($guide) {
                app(TransportGuideIssuer::class)->issue($guide);
            });

            session()->flash('message', 'Se reenviará la guía de remisión a SUNAT.');
        } catch (Throwable $e) {
            $guide->forceFill([
                'sunat_status' => TransportGuide::STATUS_ERROR,
                'sunat_notes' => $e->getMessage(),
            ])->save();

            session()->flash('error', 'No fue posible reenviar la guía: '.$e->getMessage());
        }
    }
}
