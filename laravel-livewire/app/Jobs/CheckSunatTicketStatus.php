<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\Billing\SunatSender;
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
        $this->queue = config('billing.queues.sunat', 'sunat');
    }

    public function handle(SunatSender $sender): void
    {
        $result = $sender->getStatus($this->invoice, $this->ticket);

        if (! $result['success']) {
            throw new RuntimeException('SUNAT no devolvió estado para el ticket '.$this->ticket.': '.$result['error']);
        }

        $storageDisk = config('billing.storage.disk_xml_cdr');
        $cdrDirectory = trim((string) config('billing.storage.cdr_directory'), '/');
        $fileBase = $this->invoice->numero_completo ?: $this->invoice->invoice_number;

        if (! empty($result['cdr'])) {
            $cdrPath = $cdrDirectory.'/'.str_replace('-', '_', $fileBase).'.zip';
            Storage::disk($storageDisk)->put($cdrPath, $result['cdr'], ['visibility' => 'private']);
        }

        DB::transaction(function () use ($result, $cdrDirectory, $fileBase) {
            $this->invoice->forceFill([
                'cdr_path' => ! empty($result['cdr']) ? $cdrDirectory.'/'.str_replace('-', '_', $fileBase).'.zip' : $this->invoice->cdr_path,
                'sunat_status' => data_get($result, 'parsed.is_accepted') ? 'aceptado' : 'observado',
                'sunat_response_message' => data_get($result, 'parsed.description', $this->invoice->sunat_response_message),
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
