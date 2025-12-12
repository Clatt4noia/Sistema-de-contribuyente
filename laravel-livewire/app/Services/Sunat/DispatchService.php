<?php

namespace App\Services\Sunat;

use App\Models\TransportGuide;
use App\Models\Company;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company as GreenterCompany;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Report\XmlUtils;
use Illuminate\Support\Facades\Storage;
use DateTime;
use Exception;

class DispatchService
{
    protected $greenterService;

    public function __construct(GreenterService $greenterService)
    {
        $this->greenterService = $greenterService;
    }

    public function createAndSign(TransportGuide $guide)
    {
        // Obtener configuración de empresa (Emisor)
        $company = Company::where('ruc', $guide->remitente_ruc)->first();
        if (!$company) {
            throw new Exception("No se encontró configuración para el RUC remitente: {$guide->remitente_ruc}");
        }

        $see = $this->greenterService->getSee($company);
        
        // 1. Construir Objeto Despatch
        $despatch = $this->buildDespatch($guide, $company);
        
        // 2. Generar XML firmado
        $xml = $see->getXmlSigned($despatch);
        
        // 3. Guardar XML y Hash
        $guide->xml_path = 'xml/' . $despatch->getName() . '.xml';
        // Calcular hash (digest value)
        // Greenter no guarda el hash en el objeto Despatch tras firmas automáticamente expuesto,
        // pero se puede extraer del XML.
        // Ojo: TransportGuide no tiene campo 'hash' explícito en el modelo mostrado anteriormente, 
        // pero sí 'xml_path'. Si se requiere hash, se puede guardar en 'sunat_notes' o agregar columna.
        
        $guide->sunat_status = 'signed'; // O equivalente
        $guide->save();
        
        Storage::disk('public')->put($guide->xml_path, $xml);
        
        return $despatch;
    }

    public function send(TransportGuide $guide)
    {
        $company = Company::where('ruc', $guide->remitente_ruc)->first();
        if (!$company) throw new Exception("Configuración de empresa no encontrada");

        $see = $this->greenterService->getSee($company);
        $despatch = $this->buildDespatch($guide, $company);

        // Enviar a SUNAT
        $result = $see->send($despatch);
        $guide->sent_at = now();

        if ($result->isSuccess()) {
            $cdr = $result->getCdrResponse();
            $guide->cdr_path = 'cdr/R-' . $despatch->getName() . '.zip';
            Storage::disk('public')->put($guide->cdr_path, $result->getCdrZip());
            
            $guide->sunat_ticket = $cdr->getId();
            $guide->sunat_notes = $cdr->getDescription() . ' | ' . json_encode($cdr->getNotes());

            if ($cdr->getCode() === '0') {
                $guide->sunat_status = 'accepted';
                $guide->accepted_at = now();
            } else {
                $guide->sunat_status = 'rejected'; // O observado si es validación
            }
        } else {
            $error = $result->getError();
            $guide->sunat_status = 'error';
            $guide->sunat_notes = $error->getCode() . ': ' . $error->getMessage();
        }
        
        $guide->save();
        return $result;
    }

    private function buildDespatch(TransportGuide $guide, Company $companyData): Despatch
    {
        // Emisor
        $company = new GreenterCompany();
        $company->setRuc($companyData->ruc)
            ->setRazonSocial($companyData->razon_social);

        // Destinatario
        $destinatario = new Client();
        $destinatario->setTipoDoc($guide->destinatario_document_type ?? '6') // Default RUC
            ->setNumDoc($guide->destinatario_document_number)
            ->setRznSocial($guide->destinatario_name);

        // Envío (Shipment)
        $shipment = new Shipment();
        $shipment->setCodMotivoTraslado($guide->transfer_reason_code)
            ->setDesMotivoTraslado($guide->transfer_reason_description)
            ->setPesoBruto($guide->gross_weight)
            ->setUndPesoTotal($guide->gross_weight_unit ?? 'KGM')
            ->setNumBultos($guide->total_packages)
            ->setFechaTraslado(new DateTime($guide->start_transport_date->format('Y-m-d')))
            ->setModalidadTraslado($guide->transport_mode_code); // 01 Publico, 02 Privado

        // Direcciones
        $shipment->setPartida(new Direction($guide->origin_ubigeo, $guide->origin_address));
        $shipment->setLlegada(new Direction($guide->destination_ubigeo, $guide->destination_address));

        // Transportista (Si es Público - 01)
        if ($guide->transport_mode_code === '01') {
            $transportista = new Transportist();
            $transportista->setTipoDoc('6');
            $transportista->setNumDoc($guide->transportista_ruc);
            $transportista->setRznSocial($guide->transportista_name);
            $shipment->setTransportista($transportista);
        }
        
        // Vehículo / Conductor (Si es Privado - 02)
        // Implementar lógica según campos disponibles en TransportGuide (truck_id, driver_id details)
        
        // Despatch
        $despatch = new Despatch();
        $despatch->setTipoDoc('09') // Guia Remitente
            ->setSerie($guide->series)
            ->setCorrelativo($guide->correlative)
            ->setFechaEmision(new DateTime($guide->issue_date->format('Y-m-d')))
            ->setCompany($company)
            ->setDestinatario($destinatario)
            ->setEnvio($shipment);

        // Detalles
        $details = [];
        foreach ($guide->items as $item) {
            $detail = new DespatchDetail();
            $detail->setCantidad($item->quantity)
                ->setUnidad($item->unit_of_measure ?? 'NIU')
                ->setDescripcion($item->description)
                ->setCodigo($item->id); // O codigo producto si existe
            $details[] = $detail;
        }
        $despatch->setDetails($details);

        return $despatch;
    }
}
