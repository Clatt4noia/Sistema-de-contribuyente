# Laravel Greenter

**Laravel Greenter** es un paquete para emitir **comprobantes electrónicos** desde Laravel utilizando [Greenter](https://github.com/thegreenter/greenter). Permite:

* Firmar comprobantes digitalmente
* Enviarlos a SUNAT (SEE o API REST)
* Generar su representación impresa en PDF (HTML y PDF)

[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## 📚 Tabla de Contenidos

* [📦 Requisitos](#-requisitos)
* [🚀 Instalación](#-instalación)
* [⚙️ Configuración Inicial](#️-configuración-inicial)
  * [🏢 Datos de la Empresa Emisora](#-datos-de-la-empresa-emisora)
  * [🛠️ Cambiar a Producción](#️-cambiar-a-producción)
* [🧰 Uso Básico](#-uso-básico)
  * [🧾 Emisión de Comprobante Electrónico](#-emisión-de-comprobante-electrónico)
  * [🔁 Emisión Dinámica para Múltiples Empresas](#-emisión-dinámica-para-múltiples-empresas)
* [📦 Otros Tipos de Comprobantes](#-otros-tipos-de-comprobantes)
* [🎨 Generar Representación Impresa](#-generar-representación-impresa)
  * [🧾 HTML](#-html)
  * [🖨️ PDF](#️-pdf)
  * [🎨 Personalizar Plantillas](#-personalizar-plantillas)
* [🧪 Facades Disponibles](#-facades-disponibles)
* [🧱 Estructura del Paquete](#-estructura-del-paquete)
* [🔐 Seguridad Recomendada](#-seguridad-recomendada)
* [📄 Licencia](#-licencia)

## 📦 Requisitos

Este paquete requiere:

* PHP >= 8.1
* Laravel 11.x o superior
* Extensiones PHP: `soap`, `openssl`
* [wkhtmltopdf](https://wkhtmltopdf.org) (opcional, para generación de PDF)

## 🚀 Instalación

Instala el paquete con Composer:

```bash
composer require codersfree/laravel-greenter
```

Publica los archivos de configuración y recursos:

```bash
php artisan vendor:publish --tag=greenter-laravel
```

Esto generará:

* `config/greenter.php`: configuración principal del paquete
* `public/images/logo.png`: logo usado en PDFs
* `public/certs/certificate.pem`: certificado digital de prueba

## ⚙️ Configuración Inicial

### 🏢 Datos de la Empresa Emisora

En `config/greenter.php`, configura los datos de la empresa emisora:

```php
'company' => [
    'ruc' => '20000000001',
    'razonSocial' => 'GREEN SAC',
    'nombreComercial' => 'GREEN',
    'address' => [
        'ubigeo' => '150101',
        'departamento' => 'LIMA',
        'provincia' => 'LIMA',
        'distrito' => 'LIMA',
        'direccion' => 'Av. Villa Nueva 221',
    ],
]
```

### 🛠️ Cambiar a Producción

Cuando estés listo para pasar a producción, edita el archivo `config/greenter.php`, cambia el valor de `mode` a `'prod'` y reemplaza las credenciales de prueba por las credenciales reales proporcionadas por SUNAT:

```php
'mode' => 'prod',

'company' => [
    'certificate' => public_path('certs/certificate.pem'),
    'clave_sol' => [
        'user' => 'USUARIO_SOL',
        'password' => 'CLAVE_SOL',
    ],
    'credentials' => [
        'client_id' => '...',
        'client_secret' => '...',
    ],
],
```

> ⚠️ **Importante:** Nunca subas tus certificados o credenciales a tu repositorio. Usa variables de entorno.

## 🧰 Uso Básico

### 🧾 Emisión de Comprobante Electrónico

Primero define los datos del comprobante:

```php
$data = [
    "ublVersion" => "2.1",
    "tipoOperacion" => "0101", // Catálogo 51
    "tipoDoc" => "01", // Catálogo 01
    "serie" => "F001",
    "correlativo" => "1",
    "fechaEmision" => now(),
    "formaPago" => [
        'tipo' => 'Contado',
    ],
    "tipoMoneda" => "PEN", // Catálogo 02
    "client" => [
        "tipoDoc" => "6", // Catálogo 06
        "numDoc" => "20000000001",
        "rznSocial" => "EMPRESA X",
    ],
    "mtoOperGravadas" => 100.00,
    "mtoIGV" => 18.00,
    "totalImpuestos" => 18.00,
    "valorVenta" => 100.00,
    "subTotal" => 118.00,
    "mtoImpVenta" => 118.00,
    "details" => [
        [
            "codProducto" => "P001",
            "unidad" => "NIU", // Catálogo 03
            "cantidad" => 2,
            "mtoValorUnitario" => 50.00,
            "descripcion" => "PRODUCTO 1",
            "mtoBaseIgv" => 100,
            "porcentajeIgv" => 18.00,
            "igv" => 18.00,
            "tipAfeIgv" => "10",
            "totalImpuestos" => 18.00,
            "mtoValorVenta" => 100.00,
            "mtoPrecioUnitario" => 59.00,
        ],
    ],
    "legends" => [
        [
            "code" => "1000", // Catálogo 15
            "value" => "SON CIENTO DIECIOCHO CON 00/100 SOLES",
        ],
    ],
];
```

Envía el comprobante a SUNAT:

```php
use CodersFree\LaravelGreenter\Facades\Greenter;
use Illuminate\Support\Facades\Storage;

try {
    $response = Greenter::send('invoice', $data);

    $name = $response->getDocument()->getName();
    Storage::put("sunat/xml/{$name}.xml", $response->getXml());
    Storage::put("sunat/cdr/{$name}.zip", $response->getCdrZip());

    return response()->json([
        'success' => true,
        'cdrResponse' => $response->readCdr(),
        'xml' => Storage::url("sunat/xml/{$name}.xml"),
        'cdr' => Storage::url("sunat/cdr/{$name}.zip"),
    ]);
} catch (\Throwable $e) {
    return response()->json([
        'success' => false,
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
    ], 500);
}
```

### 🔁 Emisión Dinámica para Múltiples Empresas

Puedes emitir comprobantes desde distintas empresas sin cambiar archivos de configuración:

```php
$data = [ ... ]; // Datos del comprobante

$response = Greenter::setCompany([
    'ruc' => '20999999999',
    'razonSocial' => 'Otra Empresa SAC',
    'certificate' => Storage::path('certs/otro_cert.pem'),
    // Otros datos...
])->send('invoice', $data);
```

## 📦 Otros Tipos de Comprobantes

Además de facturas, puedes emitir:

| Tipo de Comprobante | Código       | Descripción                     |
|---------------------|--------------|---------------------------------|
| Factura             | `invoice`    | Factura electrónica (01)        |
| Boleta              | `invoice`    | Boleta de venta (03)            |
| Nota de Crédito     | `note`       | Nota de crédito electrónica (07)|
| Nota de Débito      | `note`       | Nota de débito electrónica (08) |
| Guía de Remisión    | `despatch`   | Guía de remisión electrónica    |
| Resumen Diario      | `summary`    | Resumen diario de boletas (RC)  |
| Comunicación de Baja| `voided`     | Comunicación de baja (RA)       |
| Retención           | `retention`  | Comprobante de retención        |
| Percepción          | `perception` | Comprobante de percepción       |

Consulta la [documentación de Greenter](https://github.com/thegreenter/greenter) para ver los campos específicos de cada uno.

---

## 🚚 Requisitos Estrictos: Guías de Remisión Transportista (GRE-T)

Para evitar rechazos y errores estructurales de formato al emitir **Guías de Remisión (Transportista / Remitente)** mediante OSE o SUNAT (como el Error 3355), el sistema exige registrar la información maestra con los siguientes formatos obligatorios:

### 1. Vehículos (Tractos y Remolques)
* **Placa del Vehículo:** 6 caracteres alfanuméricos en mayúsculas, **sin guiones** (ej. `C9F813`).
* **TUCE (Tarjeta Única de Circulación):** Código alfanumérico emitido al vehículo (ej. `15M23039620E`). Indispensable para validar la unidad principal y al menos un remolque.
* **Autorización Especial (Emisor):** Usualmente `MTC` (Mapeado internamente en el XML al Código `06` del Catálogo D-37 SUNAT).
* **N° de Autorización:** Número oficial vinculado al vehículo (ej. `151908863`).
* **Vehículos Secundarios:** El sistema distingue entre "Principal" y "Secundario" mediante la bandera `is_secondary`. Si se anexa un remolque a la guía, se inyecta la etiqueta UBL `<cac:AttachedTransportEquipment>` respetando la secuencia estandarizada.

### 2. Conductores
* **Tipo de Documento:** DNI (`1`) obligatorio para nacionales.
* **Número de Documento:** Exactamente `8` dígitos numéricos.
* **Licencia de Conducir:** Alfanumérico de `9` caracteres. Normalmente, es una letra alfabética seguida de 8 números (casi siempre el DNI). Ejemplo: `Q09794946` o `V16837629`.

### 3. Clientes (Remitentes / Destinatarios)
* **Tipo de Documento:** RUC (`6`) para el registro formal de la carga.
* **Número de RUC:** Exactamente `11` dígitos numéricos (prefijos `10` o `20`).

### 4. Detalles de la Carga (Bienes)
* **Unidad de Medida (UND):** Deben utilizarse catálogos internacionales de SUNAT (ej. `KGM` para kilogramos, `NIU` para unidades, `TNE` para toneladas).

---

## 🎨 Generar Representación Impresa

### 🧾 HTML

```php
$data = [ ... ];
$response = Greenter::send('invoice', $data);

$html = GreenterReport::generateHtml($response->getDocument());
```

### 🖨️ PDF

Es necesario tener [wkhtmltopdf](https://wkhtmltopdf.org) instalado en el sistema para generar archivos PDF. Una vez instalado, configura la ruta del ejecutable en el archivo `config/greenter.php`:

```php
'report' => [
    'bin_path' => '/usr/local/bin/wkhtmltopdf',
],
```

```php
$data = [ ... ];
$response = Greenter::send('invoice', $data);

$pdf = GreenterReport::generatePdf($response->getDocument());
Storage::put("sunat/pdf/{$name}.pdf", $pdf);
```

### ✏️ Modificar Parámetros y Opciones

**Parámetros adicionales:**

```php
$html = GreenterReport::setParams([
    'system' => [
        'logo' => public_path('images/logo.png'),
        'hash' => '',
    ],
    'user' => [
        'header' => 'Telf: <b>(01) 123456</b>',
        'extras' => [
            ['name' => 'CONDICIÓN DE PAGO', 'value' => 'Contado'],
            ['name' => 'VENDEDOR', 'value' => 'VENDEDOR PRINCIPAL'],
        ],
        'footer' => '<p>Nro Resolución: <b>123456789</b></p>',
    ]
])->generateHtml($response->getDocument());
```

**Opciones de generación:**

```php
$html = GreenterReport::setOptions([
    'no-outline',
    'viewport-size' => '1280x1024',
    'page-width' => '21cm',
    'page-height' => '29.7cm',
])->generateHtml($response->getDocument());
```

### 🎨 Personalizar Plantillas

Publica las plantillas del reporte:

```bash
php artisan vendor:publish --tag=greenter-templates
```

Ubicación por defecto:
`resources/views/vendor/laravel-greenter`

Puedes personalizar y cambiar la ruta:

```php
'report' => [
    'template' => resource_path('templates/laravel-greenter'),
],
```

## 🧪 Facades Disponibles

| Alias            | Función principal                              |
| ---------------- | ---------------------------------------------- |
| `Greenter`       | Firma y envía comprobantes electrónicos        |
| `GreenterReport` | Genera HTML o PDF de la representación impresa |

## 🧱 Estructura del Paquete

Ejemplos de métodos disponibles:

```php
Greenter::send('invoice', $data);
GreenterReport::generateHtml($document);
GreenterReport::generatePdf($document);
```

## 🔐 Seguridad Recomendada

* Usa `.env` para tus claves y certificados
* Nunca subas archivos sensibles al repositorio
* Protege rutas usando `storage_path()` o `config_path()`
* Valida los datos antes de emitir comprobantes

## 📄 Licencia

Este paquete está bajo licencia MIT.
Desarrollado por [CodersFree](https://codersfree.com)