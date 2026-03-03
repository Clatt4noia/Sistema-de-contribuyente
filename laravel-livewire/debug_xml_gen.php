<?php
require 'vendor/autoload.php';

use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Vehicle;
use Greenter\Model\Despatch\Driver;
use Greenter\Model\Sale\Document;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Client\Client;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Xml\Builder\FeBuilder;
use Greenter\Xml\Builder\DespatchBuilder;

$despatch = new Despatch();
$despatch->setTipoDoc('31')
    ->setSerie('V001')
    ->setCorrelativo('1')
    ->setFechaEmision(new DateTime());

$company = new Company();
$company->setRuc('20123456789')->setRazonSocial('TEST')->setAddress(new Address());
$despatch->setCompany($company);
$despatch->setDestinatario((new Client())->setTipoDoc('6')->setNumDoc('20000000002')->setRznSocial('DEST'));
$despatch->setTercero((new Client())->setTipoDoc('6')->setNumDoc('20000000003')->setRznSocial('REMITENTE'));

$shipment = new Shipment();
$shipment->setCodTraslado('01')->setModTraslado('01');
$vehicle = new Vehicle();
$vehicle->setPlaca('ABC-123');
$shipment->setVehiculo($vehicle);
$driver = new Driver();
$driver->setTipo('Principal')->setNroDoc('12345678');
$shipment->setChoferes([$driver]);

$despatch->setEnvio($shipment);

$doc = new Document();
$doc->setTipoDoc('09')->setNroDoc('T001-1');
$despatch->setAddDocs([$doc]);

$detail = new DespatchDetail();
$detail->setCantidad(1)->setDescripcion('ITEM');
$despatch->setDetails([$detail]);

$see = new \Greenter\See();
$see->setCertificate('-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDQq...
-----END PRIVATE KEY-----
-----BEGIN CERTIFICATE-----
MIIDxzCCAq+gAwIBAgIJAP...
-----END CERTIFICATE-----'); // Dummy cert for internal XML gen test

$xml = $see->getXmlSigned($despatch);
echo $xml;
