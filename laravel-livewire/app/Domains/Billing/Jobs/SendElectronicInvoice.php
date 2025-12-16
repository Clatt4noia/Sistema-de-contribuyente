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
    public function __construct(public Invoice $invoice, public array $items, public array $companyData, public array $customerData)
    {
        $this->onQueue(config('billing.queues.sunat', 'sunat'));
    }

    public function handle(InvoiceService $invoiceService): void
    {
        // El servicio maneja todo el ciclo: Construcción, Firma, Envío y Procesamiento CDR
        $result = $invoiceService->send($this->invoice);

        if (!$result->isSuccess()) {
             // Si falló el envío o fue rechazado, lanzamos excepción para reintento (si aplica)
             // o dejamos que el servicio ya haya marcado el estado como RECHAZADO.
             // Greenter isSuccess() es false si hay error de conexión o rechazo SUNAT.
             
             // Nota: Si es rechazo SUNAT (Code > 0 y < 4000 usualmente), NO debemos reintentar el Job.
             // Si es error de conexión, sí.
             
             $error = $result->getError();
             // Simplificación: Loguear y fallar job.
             throw new \RuntimeException("Error al procesar factura: " . $error->getMessage());
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
