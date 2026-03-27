<?php

namespace App\Domains\Billing\Services;

use App\Enums\Fleet\DriverStatus;
use App\Enums\Fleet\TruckStatus;
use App\Models\TransportGuide;
use App\Services\FileService;
use App\Services\GreApiService;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Despatch\Vehicle;
use Greenter\Model\Despatch\Driver;
use Greenter\Model\Sale\Document;
use Illuminate\Support\Facades\Log;
use Throwable;
use RuntimeException;

class TransportGuideIssuer
{
    protected $xmlSigned = null;
    protected $fileService;
    protected $greApiService;

    public function __construct(FileService $fileService, GreApiService $greApiService)
    {
        $this->fileService = $fileService;
        $this->greApiService = $greApiService;
    }

    public function issue(TransportGuide $transportGuide): TransportGuide
    {
        $transportGuide->loadMissing('truck', 'driver', 'client', 'items');

        $this->assertResourcesAvailable($transportGuide);
        $this->assertHasItems($transportGuide);

        set_time_limit(180); 

        $despatch = $this->buildDespatch($transportGuide);

        try {
            // Enviar siempre como Guía Transportista (31) -> Servicio GRE (REST)
            $response = $this->sendViaGRE($despatch, $transportGuide);
            $this->processResponse($transportGuide, $despatch, $response);

        } catch (Throwable $e) {
            Log::error('Error emitiendo guía', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $transportGuide->forceFill([
                'sunat_status' => TransportGuide::STATUS_ERROR,
                'sunat_notes' => 'Excepción: ' . $e->getMessage(),
            ])->save();

            throw $e;
        }

        return $transportGuide->fresh();
    }

    protected function buildDespatch(TransportGuide $guide): Despatch
    {
        // Debug inicial
        Log::info('TransportGuide Data Debug', [
            'vehicle_plate' => $guide->vehicle_plate,
            'mtc_registration' => config('greenter.company.mtc') ?? '1586716CNG',
            'driver_doc' => $guide->driver_document_number,
            'guide_type' => $guide->type,
        ]);

        $despatch = new Despatch();
        $despatch->setVersion('2.0') 
            ->setTipoDoc('31') // Guía Transportista
            ->setSerie($guide->series)
            ->setCorrelativo(str_pad((string)$guide->correlative, 8, '0', STR_PAD_LEFT))
            ->setFechaEmision($guide->issue_date instanceof \DateTimeInterface ? $guide->issue_date : new \DateTime($guide->issue_date));
        
        if ($guide->observations) {
            $despatch->setObservacion($guide->observations);
        }

        // Datos del Emisor (Transportista)
        $company = new Company();
        $address = new Address();
        
        $address->setUbigueo(config('greenter.company.address.ubigeo'))
            ->setDepartamento(config('greenter.company.address.departamento'))
            ->setProvincia(config('greenter.company.address.provincia'))
            ->setDistrito(config('greenter.company.address.distrito'))
            ->setDireccion(config('greenter.company.address.direccion'));
            
        $company->setRuc(config('greenter.company.ruc'))
            ->setRazonSocial(config('greenter.company.razonSocial'))
            ->setNombreComercial(config('greenter.company.nombreComercial'))
            ->setAddress($address);
            
        $despatch->setCompany($company);

        // Datos del Destinatario
        $destinatario = new Client();
        $destinatario->setTipoDoc($guide->destinatario_document_type ?? '6')
            ->setNumDoc($guide->destinatario_document_number)
            ->setRznSocial($guide->destinatario_name);
        $despatch->setDestinatario($destinatario);

        // Datos del Remitente (Proveedor)
        $remitente = new Client();
        $remitente->setTipoDoc($guide->remitente_document_type ?? '6')
            ->setNumDoc($guide->remitente_document_number)
            ->setRznSocial($guide->remitente_name);
        $despatch->setTercero($remitente);

        // Envío (Shipment)
        $shipment = new Shipment();
        $shipment->setCodTraslado('01') // 01 = Venta
            ->setDesTraslado('VENTA')
            ->setModTraslado('01') // 01 = Transporte Público
            ->setFecTraslado($guide->start_transport_date instanceof \DateTimeInterface ? $guide->start_transport_date : new \DateTime($guide->start_transport_date))
            ->setPesoTotal($guide->gross_weight)
            ->setUndPesoTotal($guide->gross_weight_unit ?? 'KGM')
            ->setNumBultos($guide->total_packages);

        // Direcciones
        $llegada = new Direction($guide->destination_ubigeo, $guide->destination_address);
        $partida = new Direction($guide->origin_ubigeo, $guide->origin_address);
        
        $shipment->setLlegada($llegada)
                 ->setPartida($partida);

        // Agregamos Transportista (CarrierParty) NATIVAMENTE
        $transportista = new Transportist();
        $transportista->setTipoDoc('6')
            ->setNumDoc(config('greenter.company.ruc'))
            ->setRznSocial(config('greenter.company.razonSocial'))
            ->setNroMtc(config('greenter.company.mtc') ?? '1586716CNG'); // MTC Empresa

        $shipment->setTransportista($transportista);

        // Agregamos Vehículo (TransportMeans)
        if ($guide->vehicle_plate) {
            $vehicle = new Vehicle();
            $vehicle->setPlaca($guide->vehicle_plate);
            
            if ($guide->truck) {
                $tuce = $guide->truck->tuce_number;
                if ($tuce) {
                    $vehicle->setNroCirculacion($tuce);
                }
                if ($guide->truck->special_auth_issuer) {
                    // Mapeo a Catálogo N° D-37: 06 = MTC
                    $issuerCode = strtoupper($guide->truck->special_auth_issuer) === 'MTC' ? '06' : '01';
                    $vehicle->setCodEmisor($issuerCode);
                }
                if ($guide->truck->special_auth_number) {
                    $vehicle->setNroAutorizacion($guide->truck->special_auth_number);
                }
            }
            
            $shipment->setVehiculo($vehicle);
        }

        if ($guide->secondary_vehicle_plate) {
            $secondaryVehicle = new Vehicle();
            $secondaryVehicle->setPlaca($guide->secondary_vehicle_plate);
            
            if ($guide->secondaryTruck) {
                $tuce = $guide->secondaryTruck->tuce_number;
                if ($tuce) {
                    $secondaryVehicle->setNroCirculacion($tuce);
                }
                if ($guide->secondaryTruck->special_auth_issuer) {
                    $issuerCode = strtoupper($guide->secondaryTruck->special_auth_issuer) === 'MTC' ? '06' : '01';
                    $secondaryVehicle->setCodEmisor($issuerCode);
                }
                if ($guide->secondaryTruck->special_auth_number) {
                    $secondaryVehicle->setNroAutorizacion($guide->secondaryTruck->special_auth_number);
                }
            }
            
            $shipment->vehiculoSecundario = $secondaryVehicle;
        }

        // Agregamos Chofer (DriverPerson)
        if ($guide->driver_document_number) {
            $driver = new Driver();
            $driver->setTipoDoc((strlen($guide->driver_document_number) == 8) ? '1' : ($guide->driver_document_type ?? '4'))
                ->setNroDoc($guide->driver_document_number)
                ->setNombres($guide->driver_name)
                ->setApellidos($guide->driver_last_name ?? '')
                ->setLicencia($guide->driver_license_number)
                ->setTipo('Principal'); // JobTitle
            
            $shipment->setChoferes([$driver]);
        }

        $despatch->setEnvio($shipment);

        // Detalles
        $details = [];
        foreach ($guide->items as $item) {
            $detail = new DespatchDetail();
            $detail->setCantidad($item->quantity)
                ->setUnidad($item->unit_of_measure ?? 'NIU')
                ->setDescripcion($item->description)
                ->setCodigo($item->code ?? 'GEN'); 
            $details[] = $detail;
        }

        $despatch->setDetails($details);

        return $despatch;
    }

    protected function sendViaGRE(Despatch $despatch, TransportGuide $guide): \Greenter\Model\Response\BillResult
    {
        $result = new \Greenter\Model\Response\BillResult();
        
        try {
            // Builder con Plantilla Custom UBL 2.1
            $builder = new \App\Domains\Billing\Services\Builders\CustomDespatchBuilder();
            $xmlUnsigned = $builder->build($despatch);

            try {
                $dom = new \DOMDocument('1.0');
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;
                libxml_use_internal_errors(true); 
                if ($dom->loadXML($xmlUnsigned)) {
                    $xmlFormatted = $dom->saveXML();
                    if ($xmlFormatted) {
                        $xmlUnsigned = $xmlFormatted;
                    }
                }
                libxml_clear_errors();
            } catch (\Exception $e) {
                Log::warning('XML Formatting failed: ' . $e->getMessage());
            }

            // Firmado Manual
            $signer = new \Greenter\XMLSecLibs\Sunat\SignedXml();
            $certPath = $this->fileService->getCertificatePath();
            $signer->setCertificate(file_get_contents($certPath));
            
            $this->xmlSigned = $signer->signXml($xmlUnsigned);

            Log::info('Generated XML (Final)', ['xml' => $this->xmlSigned]);
            
            $name = $despatch->getName();
            $zipPath = $this->fileService->createZip($name, $this->xmlSigned);
            
            // Envío y Sondeo vía Servicio Dedicado
            $ticket = $this->greApiService->send($zipPath, $name . '.zip');
            
            if (!$ticket) throw new RuntimeException('No se recibió ticket de SUNAT');
            
            $cdrContent = $this->greApiService->getStatus($ticket);
            
            if ($cdrContent) {
                $result->setSuccess(true);
                $cdrResponse = new \Greenter\Model\Response\CdrResponse();
                $cdrResponse->setCode('0');
                $cdrResponse->setDescription('Aceptado por SUNAT (GRE)');
                $result->setCdrResponse($cdrResponse);
                $result->setCdrZip($cdrContent);
            } else {
                throw new RuntimeException('No se recibió CDR de SUNAT');
            }

        } catch (Throwable $e) {
            Log::error('Error en sendViaGRE', ['error' => $e->getMessage()]);
            $result->setSuccess(false);
            $result->setError(new \Greenter\Model\Response\Error('GRE_ERROR', $e->getMessage()));
        } finally {
            if (isset($zipPath) && file_exists($zipPath)) @unlink($zipPath);
        }

        return $result;
    }
    
    protected function processResponse(TransportGuide $transportGuide, Despatch $despatch, $response): void
    {
       $xmlName = $despatch->getName();
       
       if ($this->xmlSigned) {
           $this->fileService->saveXml($xmlName, $this->xmlSigned);
       }
       if ($response->isSuccess() && $response->getCdrZip()) {
           $this->fileService->saveCdr($xmlName, $response->getCdrZip());
       }
       
       $transportGuide->forceFill([
           'sunat_status' => $response->isSuccess() ? TransportGuide::STATUS_ACCEPTED : TransportGuide::STATUS_ERROR,
           'sunat_notes' => $response->isSuccess() ? 'Aceptado' : ($response->getError()->getMessage() ?? 'Error'),
           'sent_at' => now(),
       ])->save();
    }
    
    protected function assertResourcesAvailable($guide): void {}
    protected function assertHasItems($guide): void {} 
}
