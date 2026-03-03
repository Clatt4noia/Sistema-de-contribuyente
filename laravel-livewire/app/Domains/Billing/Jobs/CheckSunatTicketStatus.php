<?php

namespace App\Domains\Billing\Jobs;

use App\Models\Invoice;
use CodersFree\LaravelGreenter\Facades\Greenter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CheckSunatTicketStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public string $queue;

    public function __construct(public Invoice $invoice, public string $ticket)
    {
        $this->queue = config('greenter.queues.sunat', 'sunat');
    }

    public function handle(): void
    {
        $result = Greenter::getStatus($this->ticket);

        if (! $result->isSuccess()) {
             // Si es error de conexión, el job fallará y reintentará.
             // Si es error de ticket no encontrado O rechazado por sunat, lanzamos excepción.
             throw new RuntimeException('SUNAT Error Ticket '.$this->ticket.': ' . $result->getError()->getMessage());
        }

        $cdrResponse = $result->getCdrResponse();
        
        $storageDisk = config('greenter.storage.disk_xml_cdr');
        $cdrDirectory = trim((string) config('greenter.storage.cdr_directory'), '/');
        $fileBase = $this->invoice->numero_completo ?: $this->invoice->invoice_number;

        if ($result->getCdrZip()) {
            $cdrPath = $cdrDirectory.'/'.str_replace('-', '_', $fileBase).'.zip';
            Storage::disk($storageDisk)->put($cdrPath, $result->getCdrZip(), ['visibility' => 'private']);
        }

        DB::transaction(function () use ($cdrResponse, $cdrDirectory, $fileBase, $result) {
            $cdrPath = $result->getCdrZip() ? $cdrDirectory.'/'.str_replace('-', '_', $fileBase).'.zip' : $this->invoice->cdr_path;
            
            $isAccepted = $cdrResponse && (int)$cdrResponse->getCode() === 0;

            $this->invoice->forceFill([
                'cdr_path' => $cdrPath,
                'sunat_status' => $isAccepted ? 'aceptado' : 'observado',
                'sunat_response_message' => $cdrResponse ? $cdrResponse->getDescription() : $this->invoice->sunat_response_message,
                'status' => $isAccepted ? 'paid' : $this->invoice->status, // Opcional, actualizar status negocio
            ])->save();
        });
    }

    public function failed(Throwable $exception): void
    {
        Log::warning('No se pudo validar el ticket SUNAT', [
            'invoice_id' => $this->invoice->getKey(),
            'ticket' => $this->ticket,
            'message' => $exception->getMessage(),
        ]);

        $this->invoice->forceFill([
            'sunat_status' => 'observado',
            'sunat_response_message' => $exception->getMessage(),
        ])->save();
    }
}
