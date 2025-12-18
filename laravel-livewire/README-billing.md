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

### Configuración del Certificado Digital

El certificado digital es **obligatorio** para firmar los documentos electrónicos antes de enviarlos a SUNAT.

- `BILLING_CERTIFICATE_PATH`: ruta **absoluta** al certificado digital `.pfx` o `.pem`.
  - Ejemplo Windows: `C:/certificados/certificado.pfx`
  - Ejemplo Linux: `/var/www/certificados/certificado.pfx`
  - **Importante:** Usa barras diagonales `/` en lugar de barras invertidas `\`
  
- `BILLING_CERTIFICATE_PASSPHRASE`: contraseña del certificado.
  - Si el certificado no tiene contraseña, deja el valor vacío: `BILLING_CERTIFICATE_PASSPHRASE=""`
  - Si la contraseña contiene espacios o caracteres especiales, enciérrala entre comillas dobles

### Otras Variables

- `BILLING_SUNAT_USER` y `BILLING_SUNAT_PASSWORD`: credenciales SOL.
- `BILLING_STORAGE_DRIVER`: `local` o `s3` según el repositorio de XML/CDR.

**📖 Para una guía completa sobre cómo obtener y configurar el certificado digital, consulta [docs/guia-certificado-digital.md](docs/guia-certificado-digital.md)**

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

## Solución de Problemas (Troubleshooting)

### Error: "No se encontró el certificado digital configurado"

Este error aparece cuando el sistema no puede localizar el archivo del certificado digital.

**Soluciones:**
1. Verifica que `BILLING_CERTIFICATE_PATH` esté configurado en `.env`
2. Verifica que la ruta sea absoluta y use barras diagonales `/`
3. Confirma que el archivo existe: `Test-Path "C:/ruta/al/certificado.pfx"` (PowerShell)
4. Verifica los permisos de lectura del archivo
5. Limpia la caché de configuración: `php artisan config:clear`

### Error: "No fue posible interpretar el certificado PFX"

**Soluciones:**
1. Verifica que la contraseña en `BILLING_CERTIFICATE_PASSPHRASE` sea correcta
2. Asegúrate de que el certificado no esté corrupto
3. Confirma que el formato sea `.pfx` (PKCS#12) compatible

### Verificar la Configuración del Certificado

```powershell
# Usando Laravel Tinker
php artisan tinker

# Ejecutar en Tinker:
config('billing.certificate.path')
file_exists(config('billing.certificate.path'))
```

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
