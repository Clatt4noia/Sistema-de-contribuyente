# Módulo de facturación electrónica SUNAT

Este directorio describe la configuración e implementación del módulo 3 de facturación electrónica para **Carlos Gabriel Transporte S.A.C.** siguiendo el plan oficial.

## Instalación de dependencias

```bash
composer install
npm install
```

Si ya existe la instalación, ejecute:

```bash
composer update guzzlehttp/guzzle robrichards/xmlseclibs
```

> **Nota:** habilite las extensiones `ext-soap` y `ext-zip` en PHP para poder consumir los servicios SUNAT y manipular CDR.

## Variables de entorno

Actualice el archivo `.env` con las claves agregadas en `.env.example` (prefijo `BILLING_`).

- `BILLING_CERTIFICATE_PATH`: ruta absoluta al certificado digital `.pfx` o `.pem`.
- `BILLING_CERTIFICATE_PASSPHRASE`: contraseña del certificado.
- `BILLING_SUNAT_USER` y `BILLING_SUNAT_PASSWORD`: credenciales SOL.
- `BILLING_STORAGE_DRIVER`: `local` o `s3` según el repositorio de XML/CDR.

## Migraciones y seeders

```bash
php artisan migrate --seed
```

Se crean los catálogos SUNAT (tipos de documentos, impuestos y códigos de error) y las tablas de auditoría.

## Colas y Horizon

- La cola `sunat` procesa el envío y verificación de comprobantes.
- Ejecute Horizon para monitorear:

```bash
php artisan horizon
```

Para procesamiento sin Horizon:

```bash
php artisan queue:work --queue=sunat,default
```

## Emisión de comprobantes

1. Configure y emita una factura desde `Facturación > Emitir SUNAT`.
2. Se genera el XML UBL, se firma con el certificado digital y se envía a SUNAT.
3. Los archivos XML, CDR y PDF quedan disponibles en los botones de descarga.
4. Los estados se actualizan automáticamente (pendiente, aceptado, observado o rechazado).

## Reportes

Use el comando:

```bash
php artisan invoices:report --frequency=mensual
```

Genera un archivo Excel con el detalle de facturas. El comando queda programado para el primer día de cada mes a las 07:00.

## Webhook SUNAT

Endpoint expuesto: `POST /api/sunat/status-callback`. Registra los eventos en la tabla `sunat_logs`.

## Pruebas

```bash
php artisan test --testsuite=Unit
```

Los tests cubren:
- Construcción del XML UBL.
- Firma digital con certificados PFX.
- Parseo del CDR ZIP devuelto por SUNAT.

## Consideraciones de seguridad

- Los certificados no se almacenan en el repositorio.
- Los logs enmascaran contraseñas y se almacenan en `sunat_logs`.
- Las descargas de XML/CDR/PDF requieren URLs firmadas y autenticación.

Para más detalle consulte `docs/facturacion-sunat-plan.md`.

## Checklist de preproducción

1. `composer install` y `npm install`
2. Configurar las variables `BILLING_*` en `.env`
3. `php artisan key:generate` (si aplica) y `php artisan migrate --seed`
4. `php artisan storage:link` para exponer archivos públicos necesarios
5. Cargar el certificado digital en la ruta definida por `BILLING_CERTIFICATE_PATH`
6. Ejecutar `php artisan test --testsuite=Unit`
7. Levantar Horizon (`php artisan horizon`) o un worker `php artisan queue:work --queue=sunat,default`
8. Emitir una factura de prueba en homologación y verificar que genere XML, CDR y PDF
