<?php

namespace App\Services\Sunat;

use App\Models\Company;
use App\Models\Invoice;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company as GreenterCompany;
use Greenter\Model\Sale\Invoice as GreenterInvoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Report\XmlUtils;
use Illuminate\Support\Facades\Storage;
use DateTime;
use Exception;

class InvoiceService
{
    protected $greenterService;

    public function __construct(GreenterService $greenterService)
    {
        $this->greenterService = $greenterService;
    }

    public function send(Invoice $invoice)
    {
        // 1. Obtener Empresa Emisora
        // Intentar buscar por RUC emisor grabado en la factura
        $company = Company::where('ruc', $invoice->ruc_emisor)->first();
        
        // Fallback: Si no hay RUC emisor grabado o no existe, usar la primera empresa (Escenario Single-Tenant)
        if (!$company && $invoice->ruc_emisor) {
             throw new Exception("No existe configuración para la empresa emisora: " . $invoice->ruc_emisor);
        }
        if (!$company) {
            $company = Company::first();
            if (!$company) throw new Exception("No hay ninguna empresa configurada en el sistema.");
            
            // Actualizar invoice con el emisor real
            $invoice->ruc_emisor = $company->ruc;
            $invoice->save();
        }

        $see = $this->greenterService->getSee($company);

        // 2. Construir Objeto Invoice (Greenter)
        $greenterInvoice = $this->buildInvoice($invoice, $company);

        // 3. Enviar a SUNAT
        $result = $see->send($greenterInvoice);

        // 4. Guardar XML Firmado
        $xmlSigned = $see->getFactory()->getLastXml();
        $invoice->xml_path = 'xml/' . $greenterInvoice->getName() . '.xml';
        $invoice->hash = (new XmlUtils())->getHashSign($xmlSigned);
        Storage::disk('public')->put($invoice->xml_path, $xmlSigned);

        $invoice->sunat_sent_at = now();

        // 5. Procesar Respuesta
        if ($result->isSuccess()) {
            $cdr = $result->getCdrResponse();
            
            $invoice->cdr_path = 'cdr/R-' . $greenterInvoice->getName() . '.zip';
            Storage::disk('public')->put($invoice->cdr_path, $result->getCdrZip());
            
            $invoice->sunat_ticket = $cdr->getId();
            $invoice->sunat_response_message = $cdr->getDescription() . ' ' . json_encode($cdr->getNotes());

            if ((int)$cdr->getCode() === 0) {
                $invoice->sunat_status = 'aceptado';
                $invoice->status = 'paid'; // O el estado de negocio que corresponda
            } else {
                $invoice->sunat_status = 'observado';
            }
        } else {
            // Error de conexión o rechazo
            $error = $result->getError();
            $invoice->sunat_status = 'rechazado'; // Ojo: Revisar si es error de red vs rechazo lógico
            $invoice->sunat_response_message = $error->getCode() . ': ' . $error->getMessage();
        }

        $invoice->save();

        return $result;
    }

    private function buildInvoice(Invoice $invoice, Company $companyData): GreenterInvoice
    {
        // Emisor
        $client = new Client();
        $client->setTipoDoc(strlen($invoice->ruc_receptor) == 11 ? '6' : '1') // 6 RUC, 1 DNI (Simplificado)
            ->setNumDoc($invoice->ruc_receptor)
            ->setRznSocial($invoice->client->name ?? 'CLIENTE');

        $emisor = new GreenterCompany();
        $emisor->setRuc($companyData->ruc)
            ->setRazonSocial($companyData->razon_social)
            ->setNombreComercial($companyData->nombre_comercial)
            ->setAddress((new \Greenter\Model\Company\Address())
                ->setUbigueo($companyData->ubigeo ?? '150101')
                ->setDepartamento('LIMA')
                ->setProvincia('LIMA')
                ->setDistrito('LIMA')
                ->setDireccion($companyData->address));

        // Tipo de operación desde metadata (ej: '01' -> '0101')
        $operationType = $invoice->metadata['operation_type'] ?? '01';
        $tipoOperacion = str_pad($operationType, 2, '0', STR_PAD_LEFT) . '01'; // Formato: XXYY donde XX=tipo, YY=01
        
        $inv = new GreenterInvoice();
        $inv->setUblVersion('2.1')
            ->setTipoOperacion($tipoOperacion)
            ->setTipoDoc($invoice->document_type ?? '01')
            ->setSerie($invoice->series)
            ->setCorrelativo($invoice->correlative)
            ->setFechaEmision(new DateTime($invoice->issue_date->format('Y-m-d')))
            ->setTipoMoneda($invoice->currency)
            ->setCompany($emisor)
            ->setClient($client)
            ->setMtoOperGravadas($invoice->taxable_amount)
            ->setMtoIGV($invoice->tax)
            ->setTotalImpuestos($invoice->tax)
            ->setValorVenta($invoice->taxable_amount)
            ->setSubTotal($invoice->total)
            ->setMtoImpVenta($invoice->total);

        // Forma de pago (requerido por SUNAT desde 2024)
        // Si tiene fecha de vencimiento = Crédito, si no = Contado
        if ($invoice->due_date && $invoice->due_date->gt($invoice->issue_date)) {
            $paymentTerm = (new \Greenter\Model\Sale\PaymentTerms())
                ->setTipo('Credito')
                ->setMonto($invoice->total);
            $inv->setFormaPago($paymentTerm);
        } else {
            $paymentTerm = (new \Greenter\Model\Sale\PaymentTerms())
                ->setTipo('Contado');
            $inv->setFormaPago($paymentTerm);
        }

        // Detalles
        $items = [];
        foreach ($invoice->details as $detail) {
            $quantity = (float) $detail->quantity;
            $taxableAmount = (float) $detail->taxable_amount;
            $taxPercentage = (float) $detail->tax_percentage;
            
            // Precio unitario SIN IGV (valor unitario = base imponible / cantidad)
            $valorUnitario = $quantity > 0 ? round($taxableAmount / $quantity, 4) : 0;
            
            // Precio unitario CON IGV
            $precioUnitario = round($valorUnitario * (1 + ($taxPercentage / 100)), 4);
            
            $item = new SaleDetail();
            $item->setCodProducto($detail->metadata['sku'] ?? 'P001')
                ->setUnidad($detail->metadata['unit_code'] ?? 'ZZ')
                ->setCantidad($quantity)
                ->setDescripcion($detail->description)
                ->setMtoBaseIgv($taxableAmount)
                ->setPorcentajeIgv($taxPercentage)
                ->setIgv((float) $detail->tax_amount)
                ->setTipAfeIgv($detail->metadata['tax_exemption_reason'] ?? '10') // Gravado - Operación Onerosa
                ->setTotalImpuestos((float) $detail->tax_amount)
                ->setMtoValorVenta($taxableAmount)
                ->setMtoValorUnitario($valorUnitario)
                ->setMtoPrecioUnitario($precioUnitario);
            
            $items[] = $item;
        }

        $inv->setDetails($items);

        // Leyendas
        $legend = new Legend();
        $legend->setCode('1000')
            ->setValue((new \Luecano\NumeroALetras\NumeroALetras())->toMoney($invoice->total, 2, $invoice->currency == 'USD' ? 'DOLARES AMERICANOS' : 'SOLES', 'CENTIMOS'));
        $inv->setLegends([$legend]);

        return $inv;
    }
}
