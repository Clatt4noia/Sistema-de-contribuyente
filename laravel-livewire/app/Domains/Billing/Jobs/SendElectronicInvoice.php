<?php

namespace App\Domains\Billing\Jobs;

use App\Services\Sunat\InvoiceService;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendElectronicInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        public Invoice $invoice,
        public array $items,
        public array $companyData,
        public array $customerData
    ) {
        // ❌ NO definas $this->queue aquí (ni declares public string $queue)
        // La cola se define al dispatch: ->onQueue(...)
    }

    public function handle(InvoiceService $invoiceService): void
    {
        $result = $invoiceService->send($this->invoice);

        if (!$result->isSuccess()) {
            $error = $result->getError();
            throw new \RuntimeException("Error al procesar factura: " . ($error?->getMessage() ?? 'Sin detalle'));
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Fallo el envío de factura electrónica', [
            'invoice_id' => $this->invoice->getKey(),
            'message' => $exception->getMessage(),
        ]);

        $this->invoice->forceFill([
            'sunat_status' => 'rechazado',
            'sunat_response_message' => $exception->getMessage(),
        ])->save();
    }
}
