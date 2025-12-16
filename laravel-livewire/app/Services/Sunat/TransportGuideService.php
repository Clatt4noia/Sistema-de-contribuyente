<?php

namespace App\Services\Sunat;

use App\Models\Company;
use App\Models\TransportGuide;
use Greenter\Model\Client\Client as GreenterClient;
use Greenter\Model\Company\Company as GreenterCompany;
use Greenter\Model\Company\Address as GreenterAddress;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Driver;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\XMLSecLibs\Sunat\SignedXml;
use Illuminate\Support\Facades\Storage;
use DateTime;

class TransportGuideService
{
    public function __construct(
        protected GreenterService $greenterService
    ) {
    }

    public function send(TransportGuide $guide): mixed
    {
        $guide->loadMissing('client', 'truck', 'driver', 'items');

        // 1. Obtener datos de la empresa
        $company = Company::first();
        if (!$company) {
            throw new \RuntimeException('No se encontró configuración de empresa');
        }

        // 2. Construir el objeto Despatch de Greenter
        $despatch = $this->buildDespatch($guide, $company);

        // 3. Obtener servicio Greenter configurado
        $see = $this->greenterService->getSee($company);

        // 4. Enviar a SUNAT
        $result = $see->send($despatch);

        // 5. Guardar XML Firmado
        $xmlSigned = $see->getFactory()->getLastXml();
        $guide->xml_path = 'xml/guides/' . $despatch->getName() . '.xml';
        Storage::disk('public')->put($guide->xml_path, $xmlSigned);

        $guide->sunat_sent_at = now();

        // 6. Procesar Respuesta
        if ($result->isSuccess()) {
            $cdr = $result->getCdrResponse();
            
            $guide->cdr_path = 'cdr/guides/R-' . $despatch->getName() . '.zip';
            Storage::disk('public')->put($guide->cdr_path, $result->getCdrZip());
            
            $guide->sunat_ticket = $cdr->getId();
            $guide->sunat_notes = $cdr->getDescription() . ' ' . json_encode($cdr->getNotes());

            if ((int)$cdr->getCode() === 0) {
                $guide->sunat_status = TransportGuide::STATUS_ACCEPTED;
            } else {
                $guide->sunat_status = 'observado';
            }
        } else {
            // Error de conexión o rechazo
            $error = $result->getError();
            $guide->sunat_status = TransportGuide::STATUS_REJECTED;
            $guide->sunat_notes = $error->getCode() . ': ' . $error->getMessage();
        }

        $guide->save();

        return $result;
    }

    private function buildDespatch(TransportGuide $guide, Company $companyData): Despatch
    {
        // Emisor (Remitente o Transportista según el tipo)
        $emisor = new GreenterCompany();
        $emisor->setRuc($companyData->ruc)
            ->setRazonSocial($companyData->razon_social)
            ->setAddress((new GreenterAddress())
                ->setUbigueo('150101')
                ->setDepartamento('LIMA')
                ->setProvincia('LIMA')
                ->setDistrito('LIMA')
                ->setDireccion($companyData->address ?? 'AV. PRINCIPAL 123'));

        // Destinatario
        $destinatario = new GreenterClient();
        $destinatario->setTipoDoc($guide->destinatario_document_type ?? '6')
            ->setNumDoc($guide->destinatario_document_number)
            ->setRznSocial($guide->destinatario_name);

        // Transportista
        $transportista = new Transportist();
        $transportista->setTipoDoc('6')
            ->setNumDoc($guide->transportista_ruc ?? $companyData->ruc)
            ->setRznSocial($guide->transportista_name ?? $companyData->razon_social)
            ->setPlaca($guide->vehicle_plate)
            ->setChoferTipoDoc($guide->driver_document_type ?? '1')
            ->setChoferDoc($guide->driver_document_number);

        // Envío (Shipment)
        $envio = new Shipment();
        $envio->setModTraslado($guide->transport_mode_code ?? '01')
            ->setCodTraslado($guide->transfer_reason_code ?? '01')
            ->setDesTraslado($guide->transfer_reason_description ?? 'Venta')
            ->setFecTraslado(new DateTime($guide->start_transport_date->format('Y-m-d')))
            ->setPesoTotal((float)$guide->gross_weight)
            ->setUndPesoTotal($guide->gross_weight_unit ?? 'KGM')
            ->setNumBultos((int)$guide->total_packages ?? 1)
            ->setTransportista($transportista);

        // Dirección de partida
        $partida = new Direction();
        $partida->setUbigueo($guide->origin_ubigeo)
            ->setDireccion($guide->origin_address);
        $envio->setPartida($partida);

        // Dirección de llegada
        $llegada = new Direction();
        $llegada->setUbigueo($guide->destination_ubigeo)
            ->setDireccion($guide->destination_address);
        $envio->setLlegada($llegada);

        // Detalles (ítems transportados)
        $details = [];
        foreach ($guide->items as $item) {
            $detail = new DespatchDetail();
            $detail->setCantidad((float)$item->quantity)
                ->setUnidad($item->unit_of_measure ?? 'NIU')
                ->setDescripcion($item->description)
                ->setCodigo($item->code ?? 'ITEM-' . $item->id);
            
            $details[] = $detail;
        }

        // Construir Despatch
        $despatch = new Despatch();
        $despatch->setTipoDoc($guide->document_type_code ?? '09')
            ->setSerie($guide->series)
            ->setCorrelativo($guide->correlative)
            ->setFechaEmision(new DateTime($guide->issue_date->format('Y-m-d')))
            ->setCompany($emisor)
            ->setDestinatario($destinatario)
            ->setEnvio($envio)
            ->setDetails($details);

        if ($guide->observations) {
            $despatch->setObservacion($guide->observations);
        }

        return $despatch;
    }
}
