<?php

namespace App\Services\Billing;

use App\Models\TransportGuide;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class TransportGuideIssuer
{
    public function __construct(
        protected DigitalSignatureService $signatureService,
    ) {
    }

    public function issue(TransportGuide $transportGuide): TransportGuide
    {
        $transportGuide->loadMissing('truck', 'driver', 'client', 'items');

        $this->assertResourcesAvailable($transportGuide);
        $this->assertHasItems($transportGuide);

        $xml = $this->buildXml($transportGuide);
        $signedXml = $this->signatureService->sign($xml);

        $storageDisk = config('billing.storage.disk_xml_cdr');
        $xmlDirectory = trim((string) config('billing.storage.xml_directory'), '/');
        $fileBase = str_replace('-', '_', $transportGuide->display_code);
        $xmlPath = $xmlDirectory.'/guides/'.$fileBase.'.xml';

        Storage::disk($storageDisk)->put($xmlPath, $signedXml, ['visibility' => 'private']);

        $transportGuide->forceFill([
            'xml_path' => $xmlPath,
            'sunat_status' => TransportGuide::STATUS_PENDING,
            'sent_at' => now(),
            'sunat_notes' => trim(implode(' ', array_filter([
                $transportGuide->sunat_notes,
                'Documento firmado y listo para envío a SUNAT.',
            ]))),
        ])->save();

        return $transportGuide->fresh();
    }

    protected function assertResourcesAvailable(TransportGuide $transportGuide): void
    {
        $truck = $transportGuide->truck;
        $driver = $transportGuide->driver;

        if (! $truck) {
            throw new RuntimeException('No se ha asignado un camión para la guía de remisión.');
        }

        if (in_array($truck->status, ['maintenance', 'out_of_service'], true)) {
            throw new RuntimeException('El camión seleccionado no está disponible por mantenimiento.');
        }

        if (! $driver) {
            throw new RuntimeException('No se ha asignado un conductor para la guía de remisión.');
        }

        if ($driver->status !== 'active') {
            throw new RuntimeException('El conductor seleccionado no está disponible.');
        }

        if (! $driver->hasValidLicenseAt($transportGuide->start_transport_date)) {
            throw new RuntimeException('La licencia del conductor no está vigente para la fecha de inicio del traslado.');
        }
    }

    protected function assertHasItems(TransportGuide $transportGuide): void
    {
        if ($transportGuide->items->isEmpty()) {
            throw new RuntimeException('La guía de remisión debe contener al menos un bien transportado.');
        }
    }

    protected function buildXml(TransportGuide $transportGuide): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        $root = $document->createElement('DespatchAdvice');
        $document->appendChild($root);

        $root->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2');
        $root->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $root->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $root->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
        $root->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');

        $this->appendExtensions($document, $root);
        $this->appendHeader($document, $root, $transportGuide);
        $this->appendParties($document, $root, $transportGuide);
        $this->appendShipment($document, $root, $transportGuide);
        $this->appendLines($document, $root, $transportGuide);

        return $document->saveXML();
    }

    protected function appendExtensions(DOMDocument $document, DOMElement $root): void
    {
        $ext = $root->appendChild($document->createElement('ext:UBLExtensions'));
        $ublExt = $ext->appendChild($document->createElement('ext:UBLExtension'));
        $ublExt->appendChild($document->createElement('ext:ExtensionContent'));
    }

    protected function appendHeader(DOMDocument $document, DOMElement $root, TransportGuide $transportGuide): void
    {
        $root->appendChild($document->createElement('cbc:UBLVersionID', '2.1'));
        $root->appendChild($document->createElement('cbc:CustomizationID', '2.0'));
        $root->appendChild($document->createElement('cbc:ID', $transportGuide->display_code));
        $root->appendChild($document->createElement('cbc:IssueDate', optional($transportGuide->issue_date)->format('Y-m-d')));
        $root->appendChild($document->createElement('cbc:IssueTime', $transportGuide->issue_time));

        $fallbackType = $transportGuide->type === TransportGuide::TYPE_REMITENTE
            ? TransportGuide::DOCUMENT_TYPE_GRE_REMITENTE
            : TransportGuide::DOCUMENT_TYPE_GRE_TRANSPORTISTA;

        $typeCode = $document->createElement('cbc:DespatchAdviceTypeCode', $transportGuide->document_type_code ?: $fallbackType);
        $typeCode->setAttribute('listAgencyName', 'PE:SUNAT');
        $typeCode->setAttribute('listName', 'Tipo de Documento');
        $root->appendChild($typeCode);

        if ($transportGuide->observations) {
            $root->appendChild($document->createElement('cbc:Note', $this->sanitizeText($transportGuide->observations)));
        }
    }

    protected function appendParties(DOMDocument $document, DOMElement $root, TransportGuide $transportGuide): void
    {
        $supplierParty = $root->appendChild($document->createElement('cac:DespatchSupplierParty'));
        $supplierPartyIdentification = $supplierParty->appendChild($document->createElement('cac:PartyIdentification'));
        $supplierId = $document->createElement('cbc:ID', $transportGuide->remitente_document_number ?: $transportGuide->remitente_ruc);
        $supplierId->setAttribute('schemeID', $transportGuide->remitente_document_type ?: '6');
        $supplierPartyIdentification->appendChild($supplierId);

        $supplierPartyLegal = $supplierParty->appendChild($document->createElement('cac:PartyLegalEntity'));
        $supplierPartyLegal->appendChild($document->createElement('cbc:RegistrationName', $this->sanitizeText($transportGuide->remitente_name)));

        $customerParty = $root->appendChild($document->createElement('cac:DeliveryCustomerParty'));
        $customerPartyIdentification = $customerParty->appendChild($document->createElement('cac:PartyIdentification'));
        $customerId = $document->createElement('cbc:ID', $transportGuide->destinatario_document_number ?: $transportGuide->destinatario_name);
        $customerId->setAttribute('schemeID', $transportGuide->destinatario_document_type ?: '6');
        $customerPartyIdentification->appendChild($customerId);

        $customerPartyLegal = $customerParty->appendChild($document->createElement('cac:PartyLegalEntity'));
        $customerPartyLegal->appendChild($document->createElement('cbc:RegistrationName', $this->sanitizeText($transportGuide->destinatario_name ?: $transportGuide->remitente_name)));
    }

    protected function appendShipment(DOMDocument $document, DOMElement $root, TransportGuide $transportGuide): void
    {
        $shipment = $root->appendChild($document->createElement('cac:Shipment'));
        $shipment->appendChild($document->createElement('cbc:ID', $transportGuide->display_code));

        $handlingCode = $document->createElement('cbc:HandlingCode', $transportGuide->transfer_reason_code);
        $handlingCode->setAttribute('listAgencyName', 'PE:SUNAT');
        $handlingCode->setAttribute('listName', 'Motivo de traslado');
        $shipment->appendChild($handlingCode);

        if ($transportGuide->transfer_reason_description) {
            $shipment->appendChild($document->createElement('cbc:HandlingInstructions', $this->sanitizeText($transportGuide->transfer_reason_description)));
        }

        $grossWeight = $document->createElement('cbc:GrossWeightMeasure', number_format((float) $transportGuide->gross_weight, 3, '.', ''));
        $grossWeight->setAttribute('unitCode', $transportGuide->gross_weight_unit ?: 'KGM');
        $shipment->appendChild($grossWeight);

        if ($transportGuide->total_packages !== null) {
            $shipment->appendChild($document->createElement('cbc:TotalTransportHandlingUnitQuantity', (string) $transportGuide->total_packages));
        }

        $shipmentStage = $shipment->appendChild($document->createElement('cac:ShipmentStage'));
        $transportMode = $document->createElement('cbc:TransportModeCode', $transportGuide->transport_mode_code);
        $transportMode->setAttribute('listAgencyName', 'PE:SUNAT');
        $transportMode->setAttribute('listName', 'Modalidad de traslado');
        $shipmentStage->appendChild($transportMode);

        $transitPeriod = $shipmentStage->appendChild($document->createElement('cac:TransitPeriod'));
        $transitPeriod->appendChild($document->createElement('cbc:StartDate', optional($transportGuide->start_transport_date)->format('Y-m-d')));
        if ($transportGuide->delivery_date) {
            $transitPeriod->appendChild($document->createElement('cbc:EndDate', optional($transportGuide->delivery_date)->format('Y-m-d')));
        }

        $carrierParty = $shipmentStage->appendChild($document->createElement('cac:CarrierParty'));
        $carrierIdentification = $carrierParty->appendChild($document->createElement('cac:PartyIdentification'));
        $carrierId = $document->createElement('cbc:ID', $transportGuide->transportista_ruc ?: Config::get('billing.sunat.ruc'));
        $carrierId->setAttribute('schemeID', '6');
        $carrierIdentification->appendChild($carrierId);

        $driverPerson = $carrierParty->appendChild($document->createElement('cac:DriverPerson'));
        $driverId = $document->createElement('cbc:ID', $transportGuide->driver_document_number);
        $driverId->setAttribute('schemeID', $transportGuide->driver_document_type);
        $driverPerson->appendChild($driverId);
        $driverPerson->appendChild($document->createElement('cbc:FirstName', $this->sanitizeText($transportGuide->driver_name)));
        $driverPerson->appendChild($document->createElement('cbc:FamilyName', ''));
        $driverPerson->appendChild($document->createElement('cbc:JobTitle', 'Conductor'));
        $driverPerson->appendChild($document->createElement('cbc:Name', $this->sanitizeText($transportGuide->driver_name)));

        $transportMeans = $shipmentStage->appendChild($document->createElement('cac:TransportMeans'));
        $roadTransport = $transportMeans->appendChild($document->createElement('cac:RoadTransport'));
        $roadTransport->appendChild($document->createElement('cbc:LicensePlateID', $transportGuide->vehicle_plate));
        if ($transportGuide->mtc_registration_number) {
            $roadTransport->appendChild($document->createElement('cbc:TransportAuthorizationCode', $transportGuide->mtc_registration_number));
        }

        $originAddress = $shipment->appendChild($document->createElement('cac:OriginAddress'));
        $originAddress->appendChild($document->createElement('cbc:ID', $transportGuide->origin_ubigeo));
        $originAddress->appendChild($document->createElement('cbc:StreetName', $this->sanitizeText($transportGuide->origin_address)));

        $deliveryAddress = $shipment->appendChild($document->createElement('cac:DeliveryAddress'));
        $deliveryAddress->appendChild($document->createElement('cbc:ID', $transportGuide->destination_ubigeo));
        $deliveryAddress->appendChild($document->createElement('cbc:StreetName', $this->sanitizeText($transportGuide->destination_address)));

        if ($transportGuide->related_invoice_number) {
            $documentReference = $shipment->appendChild($document->createElement('cac:DocumentReference'));
            $documentReference->appendChild($document->createElement('cbc:ID', $transportGuide->related_invoice_number));
        }

        if ($transportGuide->related_sender_guide_number) {
            $additionalReference = $shipment->appendChild($document->createElement('cac:AdditionalDocumentReference'));
            $additionalReference->appendChild($document->createElement('cbc:ID', $transportGuide->related_sender_guide_number));
        }

        if ($transportGuide->additional_document_reference) {
            $additionalReference = $shipment->appendChild($document->createElement('cac:AdditionalDocumentReference'));
            $additionalReference->appendChild($document->createElement('cbc:ID', $transportGuide->additional_document_reference));
        }
    }

    protected function appendLines(DOMDocument $document, DOMElement $root, TransportGuide $transportGuide): void
    {
        foreach ($transportGuide->items as $index => $item) {
            $despatchLine = $root->appendChild($document->createElement('cac:DespatchLine'));
            $despatchLine->appendChild($document->createElement('cbc:ID', (string) ($index + 1)));

            $quantity = $document->createElement('cbc:DeliveredQuantity', number_format((float) $item->quantity, 3, '.', ''));
            $quantity->setAttribute('unitCode', $item->unit_of_measure);
            $despatchLine->appendChild($quantity);

            $itemNode = $despatchLine->appendChild($document->createElement('cac:Item'));
            $itemNode->appendChild($document->createElement('cbc:Description', $this->sanitizeText($item->description)));

            if ($item->weight !== null) {
                $shipmentLine = $despatchLine->appendChild($document->createElement('cac:ShipmentLineReference'));
                $shipmentLine->appendChild($document->createElement('cbc:LineID', (string) ($index + 1)));
                $shipmentLine->appendChild($document->createElement('cbc:Weight', number_format((float) $item->weight, 3, '.', '')));
            }
        }
    }

    protected function sanitizeText(?string $value): string
    {
        return trim((string) $value);
    }
}
