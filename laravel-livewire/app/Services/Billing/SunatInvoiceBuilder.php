<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class SunatInvoiceBuilder
{
    /**
     * Genera el XML UBL 2.1 de la factura electrónica.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    public function build(Invoice $invoice, array $items, array $companyData, array $customerData): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->xmlStandalone = false;
        $document->formatOutput = true;

        $root = $document->createElement('Invoice');
        $document->appendChild($root);

        $root->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $root->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $root->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $root->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
        $root->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $root->setAttribute('xmlns:sac', 'urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1');

        $this->appendUblExtensions($document, $root);
        $this->appendBasicInfo($document, $root, $invoice);
        $this->appendSignature($document, $root, $companyData);
        $this->appendSupplier($document, $root, $invoice, $companyData);
        $this->appendCustomer($document, $root, $invoice, $customerData);
        $this->appendTaxes($document, $root, $invoice);
        $this->appendMonetaryTotals($document, $root, $invoice);
        $this->appendInvoiceLines($document, $root, $invoice, $items);

        return $document->saveXML();
    }

    protected function appendUblExtensions(DOMDocument $document, DOMElement $root): void
    {
        $ext = $root->appendChild($document->createElement('ext:UBLExtensions'));
        $ublExt = $ext->appendChild($document->createElement('ext:UBLExtension'));
        $content = $ublExt->appendChild($document->createElement('ext:ExtensionContent'));
        $content->appendChild($document->createElement('sac:AdditionalInformation'));
    }

    protected function appendBasicInfo(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $root->appendChild($document->createElement('cbc:UBLVersionID', '2.1'));
        $root->appendChild($document->createElement('cbc:CustomizationID', '2.0'));
        $root->appendChild($document->createElement('cbc:ID', $invoice->numero_completo ?: $invoice->invoice_number));
        $root->appendChild($document->createElement('cbc:IssueDate', optional($invoice->issue_date)->format('Y-m-d')));
        if ($invoice->due_date) {
            $root->appendChild($document->createElement('cbc:DueDate', optional($invoice->due_date)->format('Y-m-d')));
        }
        $typeCode = $document->createElement('cbc:InvoiceTypeCode', $invoice->document_type ?: '01');
        $typeCode->setAttribute('listID', '0101');
        $root->appendChild($typeCode);
        $root->appendChild($document->createElement('cbc:DocumentCurrencyCode', $invoice->currency ?? 'PEN'));
    }

    protected function appendSignature(DOMDocument $document, DOMElement $root, array $companyData): void
    {
        $signature = $root->appendChild($document->createElement('cac:Signature'));
        $signature->appendChild($document->createElement('cbc:ID', 'IDSignKG'));

        $signatoryParty = $signature->appendChild($document->createElement('cac:SignatoryParty'));
        $partyIdentification = $signatoryParty->appendChild($document->createElement('cac:PartyIdentification'));
        $partyIdentification->appendChild($document->createElement('cbc:ID', $companyData['ruc'] ?? Config::get('billing.sunat.ruc')));

        $partyName = $signatoryParty->appendChild($document->createElement('cac:PartyName'));
        $partyName->appendChild($document->createElement('cbc:Name', $this->sanitizeText($companyData['legal_name'] ?? 'Carlos Gabriel Transporte S.A.C.')));

        $digitalSignature = $signature->appendChild($document->createElement('cac:DigitalSignatureAttachment'));
        $externalReference = $digitalSignature->appendChild($document->createElement('cac:ExternalReference'));
        $externalReference->appendChild($document->createElement('cbc:URI', '#SignatureKG'));
    }

    protected function appendSupplier(DOMDocument $document, DOMElement $root, Invoice $invoice, array $companyData): void
    {
        $accountingSupplierParty = $root->appendChild($document->createElement('cac:AccountingSupplierParty'));
        $party = $accountingSupplierParty->appendChild($document->createElement('cac:Party'));

        $partyIdentification = $party->appendChild($document->createElement('cac:PartyIdentification'));
        $identifier = $invoice->ruc_emisor ?? $companyData['ruc'] ?? Config::get('billing.sunat.ruc');
        $idNode = $document->createElement('cbc:ID', $identifier);
        $idNode->setAttribute('schemeID', '6');
        $partyIdentification->appendChild($idNode);

        $partyName = $party->appendChild($document->createElement('cac:PartyName'));
        $partyName->appendChild($document->createElement('cbc:Name', $this->sanitizeText($companyData['commercial_name'] ?? $companyData['legal_name'] ?? 'Carlos Gabriel Transporte S.A.C.')));

        $legalEntity = $party->appendChild($document->createElement('cac:PartyLegalEntity'));
        $legalEntity->appendChild($document->createElement('cbc:RegistrationName', $this->sanitizeText($companyData['legal_name'] ?? 'Carlos Gabriel Transporte S.A.C.')));
    }

    protected function appendCustomer(DOMDocument $document, DOMElement $root, Invoice $invoice, array $customerData): void
    {
        $accountingCustomerParty = $root->appendChild($document->createElement('cac:AccountingCustomerParty'));
        $party = $accountingCustomerParty->appendChild($document->createElement('cac:Party'));

        $partyIdentification = $party->appendChild($document->createElement('cac:PartyIdentification'));
        $customerId = $document->createElement('cbc:ID', $invoice->ruc_receptor ?? $customerData['ruc']);
        $customerId->setAttribute('schemeID', $customerData['scheme_id'] ?? '6');
        $partyIdentification->appendChild($customerId);

        $legalEntity = $party->appendChild($document->createElement('cac:PartyLegalEntity'));
        $legalEntity->appendChild($document->createElement('cbc:RegistrationName', $this->sanitizeText($customerData['name'])));
    }

    protected function appendTaxes(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $taxTotal = $root->appendChild($document->createElement('cac:TaxTotal'));
        $taxTotalAmount = $document->createElement('cbc:TaxAmount', number_format((float) $invoice->tax, 2, '.', ''));
        $taxTotalAmount->setAttribute('currencyID', $invoice->currency ?? 'PEN');
        $taxTotal->appendChild($taxTotalAmount);

        $taxSubtotal = $taxTotal->appendChild($document->createElement('cac:TaxSubtotal'));
        $taxableAmount = $document->createElement('cbc:TaxableAmount', number_format((float) $invoice->taxable_amount, 2, '.', ''));
        $taxableAmount->setAttribute('currencyID', $invoice->currency ?? 'PEN');
        $taxSubtotal->appendChild($taxableAmount);

        $taxAmount = $document->createElement('cbc:TaxAmount', number_format((float) $invoice->tax, 2, '.', ''));
        $taxAmount->setAttribute('currencyID', $invoice->currency ?? 'PEN');
        $taxSubtotal->appendChild($taxAmount);

        $taxCategory = $taxSubtotal->appendChild($document->createElement('cac:TaxCategory'));
        $taxScheme = $taxCategory->appendChild($document->createElement('cac:TaxScheme'));
        $taxScheme->appendChild($document->createElement('cbc:ID', '1000'));
        $taxScheme->appendChild($document->createElement('cbc:Name', 'IGV'));
        $taxScheme->appendChild($document->createElement('cbc:TaxTypeCode', 'VAT'));
    }

    protected function appendMonetaryTotals(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $legalMonetaryTotal = $root->appendChild($document->createElement('cac:LegalMonetaryTotal'));
        $lineExtension = $document->createElement('cbc:LineExtensionAmount', number_format((float) $invoice->subtotal, 2, '.', ''));
        $lineExtension->setAttribute('currencyID', $invoice->currency ?? 'PEN');
        $legalMonetaryTotal->appendChild($lineExtension);

        $taxExclusive = $document->createElement('cbc:TaxExclusiveAmount', number_format((float) $invoice->subtotal, 2, '.', ''));
        $taxExclusive->setAttribute('currencyID', $invoice->currency ?? 'PEN');
        $legalMonetaryTotal->appendChild($taxExclusive);

        $taxInclusive = $document->createElement('cbc:TaxInclusiveAmount', number_format((float) $invoice->total, 2, '.', ''));
        $taxInclusive->setAttribute('currencyID', $invoice->currency ?? 'PEN');
        $legalMonetaryTotal->appendChild($taxInclusive);

        $payable = $document->createElement('cbc:PayableAmount', number_format((float) $invoice->total, 2, '.', ''));
        $payable->setAttribute('currencyID', $invoice->currency ?? 'PEN');
        $legalMonetaryTotal->appendChild($payable);
    }

    protected function appendInvoiceLines(DOMDocument $document, DOMElement $root, Invoice $invoice, array $items): void
    {
        Collection::make($items)->each(function (array $item, int $index) use ($document, $root, $invoice): void {
            $line = $root->appendChild($document->createElement('cac:InvoiceLine'));
            $line->appendChild($document->createElement('cbc:ID', (string) ($index + 1)));

            $quantity = $document->createElement('cbc:InvoicedQuantity', number_format((float) ($item['quantity'] ?? 1), 2, '.', ''));
            $quantity->setAttribute('unitCode', $item['unit_code'] ?? 'NIU');
            $line->appendChild($quantity);

            $lineExtension = $document->createElement('cbc:LineExtensionAmount', number_format((float) ($item['total'] ?? 0), 2, '.', ''));
            $lineExtension->setAttribute('currencyID', $invoice->currency ?? 'PEN');
            $line->appendChild($lineExtension);

            $pricingReference = $line->appendChild($document->createElement('cac:PricingReference'));
            $alternativeConditionPrice = $pricingReference->appendChild($document->createElement('cac:AlternativeConditionPrice'));
            $priceAmount = $document->createElement('cbc:PriceAmount', number_format((float) ($item['unit_price'] ?? 0), 2, '.', ''));
            $priceAmount->setAttribute('currencyID', $invoice->currency ?? 'PEN');
            $alternativeConditionPrice->appendChild($priceAmount);
            $alternativeConditionPrice->appendChild($document->createElement('cbc:PriceTypeCode', $item['price_type_code'] ?? '01'));

            $taxTotal = $line->appendChild($document->createElement('cac:TaxTotal'));
            $taxAmount = $document->createElement('cbc:TaxAmount', number_format((float) ($item['tax_amount'] ?? 0), 2, '.', ''));
            $taxAmount->setAttribute('currencyID', $invoice->currency ?? 'PEN');
            $taxTotal->appendChild($taxAmount);

            $taxSubtotal = $taxTotal->appendChild($document->createElement('cac:TaxSubtotal'));
            $taxableAmount = $document->createElement('cbc:TaxableAmount', number_format((float) ($item['taxable_amount'] ?? $item['total'] ?? 0), 2, '.', ''));
            $taxableAmount->setAttribute('currencyID', $invoice->currency ?? 'PEN');
            $taxSubtotal->appendChild($taxableAmount);

            $taxSubtotalAmount = $document->createElement('cbc:TaxAmount', number_format((float) ($item['tax_amount'] ?? 0), 2, '.', ''));
            $taxSubtotalAmount->setAttribute('currencyID', $invoice->currency ?? 'PEN');
            $taxSubtotal->appendChild($taxSubtotalAmount);

            $taxCategory = $taxSubtotal->appendChild($document->createElement('cac:TaxCategory'));
            $taxCategory->appendChild($document->createElement('cbc:ID', $item['tax_code'] ?? 'S'));
            $taxCategory->appendChild($document->createElement('cbc:Percent', number_format((float) ($item['tax_percentage'] ?? 18), 2, '.', '')));
            $taxExemption = $document->createElement('cbc:TaxExemptionReasonCode', $item['tax_exemption_reason'] ?? '10');
            $taxExemption->setAttribute('listName', 'Codigo de Tributo');
            $taxCategory->appendChild($taxExemption);

            $taxScheme = $taxCategory->appendChild($document->createElement('cac:TaxScheme'));
            $taxScheme->appendChild($document->createElement('cbc:ID', '1000'));
            $taxScheme->appendChild($document->createElement('cbc:Name', 'IGV'));
            $taxScheme->appendChild($document->createElement('cbc:TaxTypeCode', 'VAT'));

            $itemNode = $line->appendChild($document->createElement('cac:Item'));
            $description = $document->createElement('cbc:Description', $this->sanitizeText($item['description'] ?? 'Servicio logístico'));
            $description->setAttribute('languageID', 'es');
            $itemNode->appendChild($description);

            $sellersId = $itemNode->appendChild($document->createElement('cac:SellersItemIdentification'));
            $sellersId->appendChild($document->createElement('cbc:ID', Str::upper($item['sku'] ?? sprintf('ITEM-%s', $index + 1))));

            $price = $line->appendChild($document->createElement('cac:Price'));
            $priceAmountNode = $document->createElement('cbc:PriceAmount', number_format((float) ($item['unit_price'] ?? 0), 2, '.', ''));
            $priceAmountNode->setAttribute('currencyID', $invoice->currency ?? 'PEN');
            $price->appendChild($priceAmountNode);
        });
    }

    protected function sanitizeText(?string $value): string
    {
        return trim((string) $value);
    }
}
