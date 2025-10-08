<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\SunatLog;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Config;
use SoapClient;
use SoapFault;
use Throwable;
use ZipArchive;
use RuntimeException;

class SunatSender
{
    public function __construct(protected ?string $mode = null)
    {
        $this->mode = $mode ?: Config::get('billing.sunat.mode', 'homologation');
    }

    public function sendBill(Invoice $invoice, string $zipContent): array
    {
        $fileName = $this->buildFileName($invoice);
        $payload = [
            'fileName' => $fileName,
            'contentFile' => base64_encode($zipContent),
        ];

        $client = $this->getSoapClient('bill_service');

        try {
            $response = $client->__soapCall('sendBill', [$payload]);
            $cdr = base64_decode($response->applicationResponse ?? '', true);
            $parsed = $cdr ? $this->parseCdr($cdr) : null;

            $this->log($invoice, 'sendBill', $payload, $response, true);

            return [
                'success' => true,
                'cdr' => $cdr,
                'parsed' => $parsed,
                'ticket' => null,
            ];
        } catch (SoapFault $exception) {
            $this->log($invoice, 'sendBill', $payload, ['faultcode' => $exception->faultcode, 'faultstring' => $exception->faultstring], false);

            return [
                'success' => false,
                'cdr' => null,
                'parsed' => null,
                'ticket' => null,
                'error' => $exception->faultstring,
                'code' => $exception->faultcode,
            ];
        }
    }

    public function sendSummary(Invoice $invoice, string $zipContent): array
    {
        $fileName = $this->buildFileName($invoice, 'RC');
        $payload = [
            'fileName' => $fileName,
            'contentFile' => base64_encode($zipContent),
        ];

        $client = $this->getSoapClient('summary_service');

        try {
            $response = $client->__soapCall('sendSummary', [$payload]);
            $ticket = $response->ticket ?? null;

            $this->log($invoice, 'sendSummary', $payload, $response, true);

            return [
                'success' => true,
                'ticket' => $ticket,
            ];
        } catch (SoapFault $exception) {
            $this->log($invoice, 'sendSummary', $payload, ['faultcode' => $exception->faultcode, 'faultstring' => $exception->faultstring], false);

            return [
                'success' => false,
                'error' => $exception->faultstring,
                'code' => $exception->faultcode,
            ];
        }
    }

    public function getStatus(?Invoice $invoice, string $ticket): array
    {
        $payload = [
            'ticket' => $ticket,
        ];

        $client = $this->getSoapClient('status_service');

        try {
            $response = $client->__soapCall('getStatus', [$payload]);
            $cdr = base64_decode($response->status->content ?? '', true);
            $parsed = $cdr ? $this->parseCdr($cdr) : null;

            $this->log($invoice, 'getStatus', $payload, $response, true);

            return [
                'success' => true,
                'cdr' => $cdr,
                'parsed' => $parsed,
            ];
        } catch (SoapFault $exception) {
            $this->log($invoice, 'getStatus', $payload, ['faultcode' => $exception->faultcode, 'faultstring' => $exception->faultstring], false);

            return [
                'success' => false,
                'error' => $exception->faultstring,
                'code' => $exception->faultcode,
            ];
        }
    }

    public function parseCdr(string $cdrZip): array
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'cdr');
        file_put_contents($tempFile, $cdrZip);

        $zip = new ZipArchive();
        if ($zip->open($tempFile) !== true) {
            unlink($tempFile);
            throw new RuntimeException('No se pudo abrir el CDR devuelto por SUNAT.');
        }

        $xmlString = $zip->getFromIndex(0);
        $zip->close();
        unlink($tempFile);

        if ($xmlString === false) {
            throw new RuntimeException('No se pudo leer el contenido del CDR.');
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadXML($xmlString);

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        $responseCode = $xpath->evaluate('string(//cbc:ResponseCode)');
        $description = $xpath->evaluate('string(//cbc:Description)');

        return [
            'code' => $responseCode,
            'description' => $description,
            'is_accepted' => in_array((int) $responseCode, [0, 1], true),
        ];
    }

    protected function buildFileName(Invoice $invoice, ?string $prefix = null): string
    {
        $ruc = $invoice->ruc_emisor ?: Config::get('billing.sunat.ruc');
        $serie = $invoice->series ?: 'F001';
        $correlative = $invoice->correlative ?: str_pad((string) $invoice->getKey(), 8, '0', STR_PAD_LEFT);
        $type = $prefix ?: ($invoice->document_type ?: '01');

        return implode('-', array_filter([$ruc, $type, $serie, $correlative]));
    }

    protected function getSoapClient(string $service): SoapClient
    {
        $endpoints = Config::get('billing.sunat.endpoints.' . $this->mode);
        $endpoint = $endpoints[$service] ?? null;

        if (! $endpoint) {
            throw new RuntimeException("No se encontró el endpoint configurado para {$service} en el modo {$this->mode}.");
        }

        $options = [
            'cache_wsdl' => WSDL_CACHE_NONE,
            'trace' => true,
            'exceptions' => true,
            'stream_context' => stream_context_create([
                'http' => [
                    'header' => 'Content-Type: text/xml; charset=utf-8',
                ],
            ]),
        ];

        $credentials = [
            'login' => Config::get('billing.sunat.user'),
            'password' => Config::get('billing.sunat.password'),
        ];

        return new SoapClient($endpoint . '?wsdl', $options + $credentials);
    }

    protected function log(?Invoice $invoice, string $operation, $request, $response, bool $success): void
    {
        try {
            SunatLog::create([
                'invoice_id' => $invoice?->getKey(),
                'operation' => $operation,
                'endpoint' => $this->resolveEndpoint($operation),
                'request_payload' => json_encode($request, JSON_THROW_ON_ERROR),
                'response_payload' => json_encode($this->normalizeResponse($response), JSON_THROW_ON_ERROR),
                'status_code' => $success ? 'OK' : ($response['faultcode'] ?? null),
                'is_success' => $success,
                'executed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    protected function resolveEndpoint(string $operation): ?string
    {
        $map = [
            'sendBill' => 'bill_service',
            'sendSummary' => 'summary_service',
            'getStatus' => 'status_service',
        ];

        $service = $map[$operation] ?? null;

        if (! $service) {
            return null;
        }

        $endpoints = Config::get('billing.sunat.endpoints.' . $this->mode);

        return $endpoints[$service] ?? null;
    }

    protected function normalizeResponse($response): array
    {
        if (is_object($response)) {
            return json_decode(json_encode($response, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        }

        if (is_array($response)) {
            return $response;
        }

        return ['value' => $response];
    }
}
