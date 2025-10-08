<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\Billing\DigitalSignatureService;
use App\Services\Billing\SunatInvoiceBuilder;
use App\Services\Billing\SunatSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class SendElectronicInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public string $queue;

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(public Invoice $invoice, public array $items, public array $companyData, public array $customerData)
    {
        $this->queue = config('billing.queues.sunat', 'sunat');
    }

    public function handle(SunatInvoiceBuilder $builder, DigitalSignatureService $signatureService, SunatSender $sender): void
    {
        $invoice = $this->invoice->fresh();
        $xml = $builder->build($invoice, $this->items, $this->companyData, $this->customerData);
        $signedXml = $signatureService->sign($xml);

        $hash = base64_encode(hash('sha256', $signedXml, true));
        $zipContent = $this->zipSignedXml($signedXml, $invoice);

        $storageDisk = config('billing.storage.disk_xml_cdr');
        $xmlDirectory = trim((string) config('billing.storage.xml_directory'), '/');
        $cdrDirectory = trim((string) config('billing.storage.cdr_directory'), '/');
        $fileBase = $invoice->numero_completo ?: $invoice->invoice_number;

        $xmlPath = $xmlDirectory.'/'.str_replace('-', '_', $fileBase).'.xml';
        Storage::disk($storageDisk)->put($xmlPath, $signedXml, ['visibility' => 'private']);

        $result = $sender->sendBill($invoice, $zipContent);

        DB::transaction(function () use ($invoice, $result, $hash, $xmlPath, $storageDisk, $cdrDirectory, $fileBase) {
            $status = match (true) {
                ! $result['success'] => 'rechazado',
                ! empty($result['ticket']) => 'pendiente',
                default => 'aceptado',
            };

            $invoice->forceFill([
                'hash' => $hash,
                'xml_path' => $xmlPath,
                'sunat_sent_at' => now(),
                'sunat_status' => $status,
                'sunat_response_message' => $result['parsed']['description'] ?? $result['error'] ?? null,
                'sunat_ticket' => $result['ticket'] ?? null,
            ])->save();

            if (! empty($result['cdr'])) {
                $cdrPath = $cdrDirectory.'/'.str_replace('-', '_', $fileBase).'.zip';
                Storage::disk($storageDisk)->put($cdrPath, $result['cdr'], ['visibility' => 'private']);

                $invoice->forceFill([
                    'cdr_path' => $cdrPath,
                    'sunat_status' => ($result['parsed']['is_accepted'] ?? false) ? 'aceptado' : 'observado',
                ])->save();
            }
        });

        if (! $result['success'] && empty($result['ticket'])) {
            throw new RuntimeException('SUNAT rechazó el comprobante: '.$result['error']);
        }

        if (! empty($result['ticket'])) {
            CheckSunatTicketStatus::dispatch($invoice, $result['ticket'])
                ->onQueue($this->queue)
                ->delay(now()->addMinutes(5));
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

    protected function zipSignedXml(string $signedXml, Invoice $invoice): string
    {
        $tempDirectory = sys_get_temp_dir();
        $fileBase = $invoice->numero_completo ?: $invoice->invoice_number;
        $slug = Str::slug($fileBase, '_');
        $xmlFile = $tempDirectory.'/'.$slug.'.xml';
        $zipFile = $tempDirectory.'/'.$slug.'.zip';

        file_put_contents($xmlFile, $signedXml);

        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No fue posible generar el archivo ZIP para SUNAT.');
        }

        $zip->addFile($xmlFile, basename($xmlFile));
        $zip->close();

        $content = file_get_contents($zipFile) ?: '';

        unlink($xmlFile);
        unlink($zipFile);

        return $content;
    }
}
