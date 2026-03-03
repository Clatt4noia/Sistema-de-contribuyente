<?php

namespace App\Domains\Billing\Jobs;

use App\Models\Invoice;
use CodersFree\LaravelGreenter\Facades\Greenter;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\Invoice as GreenterInvoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\PaymentTerms;
use Greenter\Model\Sale\SaleDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Luecano\NumeroALetras\NumeroALetras;
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
        $this->onQueue(config('greenter.queues.sunat', 'sunat'));
    }

    public function handle(): void
    {
        try {
            // Configurar empresa dinámica si es diferente a la default
            // Se asume que $this->companyData trae la info necesaria
            $this->configureCompany();

            $greenterInvoice = $this->buildInvoice();

            $response = Greenter::send('invoice', $greenterInvoice);

            $this->processResponse($response, $greenterInvoice->getName());

        } catch (Throwable $e) {
            $this->failJob($e);
            throw $e;
        }
    }

    protected function configureCompany(): void
    {
        // Si los datos de la compañía del job difieren del .env/config, cambiamos el contexto
        // Nota: Laravel Greenter permite setCompany.
        // Mapeamos $this->companyData a la estructura que espera setCompany
        
        $currentRuc = config('greenter.company.ruc');
        $jobRuc = $this->companyData['ruc'] ?? null;

        if ($jobRuc && $jobRuc !== $currentRuc) {
             // Construir configuración
             $config = [
                'ruc' => $jobRuc,
                'razonSocial' => $this->companyData['legal_name'] ?? '',
                'nombreComercial' => $this->companyData['commercial_name'] ?? '',
                'address' => [
                     'ubigeo' => $this->companyData['address']['ubigeo'] ?? '150101', // Fallback
                     'direccion' => $this->companyData['address']['direccion'] ?? 'AV. UNKNOWN',
                     'departamento' => $this->companyData['address']['departamento'] ?? 'LIMA',
                     'provincia' => $this->companyData['address']['provincia'] ?? 'LIMA',
                     'distrito' => $this->companyData['address']['distrito'] ?? 'LIMA',
                ],
                // Importante: Certificado y Credenciales SOL deben ser dinámicos si la empresa cambia.
                // Si este sistema es multi-tenant real, deberíamos obtener esto de una BDD o Secrets.
                // Por ahora, asumimos que si cambia, comparte certificado o se pasa en companyData.
                'certificate' => $this->companyData['certificate_path'] ?? config('greenter.company.certificate'),
                'clave_sol' => [
                    'user' => $this->companyData['sol_user'] ?? config('greenter.company.clave_sol.user'),
                    'password' => $this->companyData['sol_pass'] ?? config('greenter.company.clave_sol.password'),
                ]
             ];
             
             Greenter::setCompany($config);
        }
    }

    protected function buildInvoice(): GreenterInvoice
    {
        $blueClient = new Client();
        $blueClient->setTipoDoc($this->customerData['scheme_id'] ?? '6')
            ->setNumDoc($this->customerData['ruc'])
            ->setRznSocial($this->customerData['name']);
            
        // Emisor (Greenter lo toma de la config seteada, pero el objeto Invoice requiere setCompany si se quiere explicitar en el XML?)
        // Greenter\Model\Sale\Invoice -> setCompany(Company $company)
        // Laravel Greenter inyecta la compañia al enviar? NO. Laravel Greenter configura el Factory.
        // Pero el Objeto Invoice necesita tener la compañía seteada para generar el XML XmlBuilder.
        
        // Construimos el objeto Company para el Invoice
        $emisor = new Company();
        $emisor->setRuc($this->companyData['ruc'])
            ->setRazonSocial($this->companyData['legal_name'] ?? '')
            ->setNombreComercial($this->companyData['commercial_name'] ?? '')
            ->setAddress((new Address())
                ->setUbigueo($this->companyData['address']['ubigeo'] ?? '150101')
                ->setDireccion($this->companyData['address']['direccion'] ?? 'AV. DEFAULT')
                ->setDepartamento('LIMA')
                ->setProvincia('LIMA')
                ->setDistrito('LIMA'));

        $inv = new GreenterInvoice();
        $inv->setUblVersion('2.1')
            ->setTipoOperacion($this->invoice->metadata['operation_type'] ?? '0101') // Asegurar formato 4 digitos si es necesario? Service hacia str_pad.
            ->setTipoDoc($this->invoice->document_type)
            ->setSerie($this->invoice->series)
            ->setCorrelativo($this->invoice->correlative)
            ->setFechaEmision($this->invoice->issue_date)
            ->setTipoMoneda($this->invoice->currency)
            ->setCompany($emisor)
            ->setClient($blueClient)
            ->setMtoOperGravadas((float) $this->invoice->taxable_amount)
            ->setMtoIGV((float) $this->invoice->tax)
            ->setTotalImpuestos((float) $this->invoice->tax)
            ->setValorVenta((float) $this->invoice->taxable_amount)
            ->setSubTotal((float) $this->invoice->total)
            ->setMtoImpVenta((float) $this->invoice->total);
            
         // Ajuste Tipo Operacion
         $opType = $this->invoice->metadata['operation_type'] ?? '01';
         if (strlen($opType) === 2) {
             $inv->setTipoOperacion($opType . '01');
         } else {
             $inv->setTipoOperacion($opType);
         }

        // Forma de Pago
        if ($this->invoice->due_date && $this->invoice->due_date->gt($this->invoice->issue_date)) {
            $payment = new PaymentTerms();
            $payment->setTipo('Credito')
                ->setMonto((float) $this->invoice->total);
            $inv->setFormaPago($payment);
             $inv->setCuotas([
                (new \Greenter\Model\Sale\Cuota())
                ->setMonto((float) $this->invoice->total)
                ->setFechaPago($this->invoice->due_date)
            ]);
        } else {
            $payment = new PaymentTerms();
            $payment->setTipo('Contado');
            $inv->setFormaPago($payment);
        }

        // Detalles
        $details = [];
        foreach ($this->items as $itemData) {
            $qty = (float) ($itemData['quantity'] ?? 1);
            $unitPrice = (float) ($itemData['unit_price'] ?? 0); // Precio Unitario (p.e. 100)
            // Asumimos que los items ya vienen calculados o recalculamos?
            // El array $items trae 'taxable_amount', 'tax_amount', 'total'.
            
            // Greenter espera:
            // MtoValorUnitario (Sin IGV)
            // MtoPrecioUnitario (Con IGV)
            
            $taxPercent = (float) ($itemData['tax_percentage'] ?? 18);
            $taxAmount = (float) ($itemData['tax_amount'] ?? 0);
            $total = (float) ($itemData['total'] ?? 0);
            $taxable = (float) ($itemData['taxable_amount'] ?? 0);
            
            // Re-inferir unitarios si es necesario
            $valorUnitario = $qty > 0 ? round($taxable / $qty, 4) : 0;
            $precioUnitario = $qty > 0 ? round($total / $qty, 4) : 0;

            $detail = new SaleDetail();
            $detail->setCodProducto($itemData['sku'] ?? 'GEN')
                ->setUnidad($itemData['unit_code'] ?? 'NIU')
                ->setCantidad($qty)
                ->setDescripcion($itemData['description'] ?? 'Item')
                ->setMtoBaseIgv($taxable)
                ->setPorcentajeIgv($taxPercent)
                ->setIgv($taxAmount)
                ->setTipAfeIgv($itemData['tax_exemption_reason'] ?? '10')
                ->setTotalImpuestos($taxAmount)
                ->setMtoValorVenta($taxable)
                ->setMtoValorUnitario($valorUnitario)
                ->setMtoPrecioUnitario($precioUnitario);

            $details[] = $detail;
        }
        $inv->setDetails($details);

        // Leyenda
        $legend = new Legend();
        $legend->setCode('1000')
            ->setValue((new NumeroALetras())->toMoney($this->invoice->total, 2, $this->invoice->currency == 'USD' ? 'DOLARES AMERICANOS' : 'SOLES', 'CENTIMOS'));
        $inv->setLegends([$legend]);

        return $inv;
    }

    protected function processResponse($response, string $filename): void
    {
        $disk = config('greenter.storage.disk_xml_cdr', 'public');
        
        $xmlPath = null;
        if ($response->getXml()) {
            $xmlPath = config('greenter.storage.xml_directory', 'xml') . '/' . $filename . '.xml';
            Storage::disk($disk)->put($xmlPath, $response->getXml());
            
            // Hash (si fuera necesario, extraer del XML)
            // $this->invoice->hash = ...
        }

        $cdrPath = null;
        if ($response->getCdrZip()) {
            $cdrPath = config('greenter.storage.cdr_directory', 'cdr') . '/R-' . $filename . '.zip';
            Storage::disk($disk)->put($cdrPath, $response->getCdrZip());
        }

        $cdr = $response->getCdrResponse();
        $isAccepted = $response->isSuccess() && $cdr && (int) $cdr->getCode() === 0;

        $notes = $cdr ? "[{$cdr->getCode()}] {$cdr->getDescription()}" : ($response->getError()?->getMessage() ?? 'Enviado');
        
        if (!$response->isSuccess()) {
             $notes = $response->getError()?->getCode() . ': ' . $response->getError()?->getMessage();
        }

        $this->invoice->forceFill([
            'xml_path' => $xmlPath,
            'cdr_path' => $cdrPath,
            'sunat_ticket' => $cdr?->getId(),
            'sunat_status' => $isAccepted ? 'aceptado' : ($response->isSuccess() ? 'observado' : 'rechazado'),
            'sunat_response_message' => $notes,
            'sunat_sent_at' => now(),
             // Actualizar estado de negocio si es aceptado
             'status' => $isAccepted ? 'paid' : $this->invoice->status, // O mantener issued?
        ])->save();
        
        if (!$response->isSuccess() && !$cdr) {
             // Si falló conexión o error bloqueante
             throw new \RuntimeException("Greenter Error: " . $notes);
        }
    }

    protected function failJob(Throwable $e): void
    {
        Log::error('Fallo el envío de factura electrónica', [
            'invoice_id' => $this->invoice->getKey(),
             'error' => $e->getMessage(),
        ]);
    }
    
    public function failed(Throwable $exception): void
    {
        $this->failJob($exception);

        $this->invoice->forceFill([
            'sunat_status' => 'rechazado',
            'sunat_response_message' => 'Fallo definitivo: ' . $exception->getMessage(),
        ])->save();
    }
}
