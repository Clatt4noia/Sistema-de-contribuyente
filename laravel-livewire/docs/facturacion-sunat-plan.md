# Plan de Implementación SUNAT para Facturación Electrónica

## 1. Preparación de infraestructura tributaria
1. **Credenciales y certificados**
   - Solicitar a "Carlos Gabriel Transporte S.A.C." las credenciales SOL (RUC, usuario secundario y contraseña) para ambiente de homologación y producción.
   - Registrar y resguardar el certificado digital (*.pfx* o *.pem*) emitido por una entidad certificadora acreditada en Perú junto con su contraseña.
2. **Almacenamiento seguro**
   - Definir un bucket S3 o almacenamiento equivalente con cifrado en reposo para XML y CDR.
   - Activar respaldos automáticos diarios y versionado, con políticas de retención alineadas a SUNAT (mínimo 5 años).
3. **Configuración de locales**
   - Verificar que `Carbon::locale('es')` y la zona horaria `America/Lima` estén configuradas en `config/app.php` para cumplir con formatos dd/mm/yyyy.

## 2. Extensión del modelo de datos
1. **Migraciones**
   - Añadir campos SUNAT a la tabla `invoices`: `tipo_documento`, `serie`, `correlativo`, `ruc_emisor`, `ruc_receptor`, `moneda`, `igv`, `total_gravado`, `total_inafecto`, `total_exonerado`, `xml_path`, `cdr_path`, `hash`, `estado_sunat`, `fecha_envio_sunat`, `mensaje_sunat`.
   - Crear tablas catálogos: `sunat_document_types`, `sunat_tax_rates`, `sunat_error_codes`.
2. **Modelos Eloquent**
   - Actualizar `Invoice` con casts monetarios, `fillable` y relaciones hacia catálogos.
   - Implementar `InvoiceAudit` para trazabilidad y `SunatLog` para logs de comunicación.

## 3. Servicios de facturación electrónica
1. **Generación UBL 2.1**
   - Crear servicio `App\Services\Billing\SunatInvoiceBuilder` que construya XML UBL 2.1 usando catálogos SUNAT y formateo PEN.
   - Validar campos obligatorios antes de generar el XML mediante `FormRequest` dedicado (`StoreElectronicInvoiceRequest`).
2. **Firma digital**
   - Implementar `App\Services\Billing\DigitalSignatureService` que firme el XML con el certificado PFX usando `xmlseclibs`.
3. **Envío y recepción**
   - Desarrollar `App\Services\Billing\SunatSender` que encapsule SOAP/REST a SUNAT (`sendBill`, `sendSummary`, `getStatus`).
   - Registrar cada interacción en `SunatLog` con request/response y códigos de error.
4. **Procesamiento de respuesta**
   - Guardar el CDR (zip) retornado, parsear el status y actualizar `estado_sunat` (Aceptado, Rechazado, Pendiente) y `mensaje_sunat`.
   - Enviar notificaciones internas (Livewire toast, correo o Slack) cuando haya rechazo o pendiente.

## 4. Flujo operativo basado en el diagrama SUNAT
1. **Emisión (Paso 1)**
   - Operador emite comprobante desde Livewire con formularios dinámicos (selección de cliente, validación RUC, cálculo de IGV, totales en S/).
   - Se genera el XML UBL y se firma digitalmente.
2. **Envío a SUNAT (Paso 2)**
   - El sistema envía el XML firmado a SUNAT y almacena una copia en el repositorio seguro.
3. **Validación SUNAT (Paso 3)**
   - Consumir la respuesta inmediata (ticket o CDR). Si SUNAT requiere procesamiento diferido, programar un Job que consulte `getStatus`.
   - Registrar aceptación/rechazo y mensaje en `Invoice` y `SunatLog`.
4. **Entrega al cliente (Paso 4)**
   - Generar PDF del comprobante, adjuntar XML y CDR, y disponibilizar descarga en el portal de clientes.
   - Enviar correo con enlaces protegidos.

## 5. UI/UX y experiencia en Livewire
1. **Formularios dinámicos**
   - Componentes Livewire específicos (`ElectronicInvoiceForm`, `SunatStatusBadge`, `InvoiceFileDownloader`).
   - Validaciones en tiempo real con mensajes en español: "El RUC debe tener 11 dígitos", "Monto gravado obligatorio".
2. **Tableros e indicadores**
   - Actualizar dashboard de facturación con indicadores: tiempo promedio de emisión (promedio de diferencia entre creación y envío), porcentaje de errores (rechazos / total), cumplimiento de pagos y estado SUNAT.
   - Presentar líneas de tiempo y gráficos con Tailwind + Alpine.
3. **Manejo de estados**
   - Colores consistentes para estado SUNAT (Aceptado verde, Rechazado rojo, Pendiente ámbar) y mensajes flash claros.

## 6. Reportes y exportaciones
1. **Exportación PDF/Excel**
   - Utilizar `maatwebsite/excel` para exportaciones filtradas.
   - Generar PDFs con `laravel-dompdf` o `snappy` incluyendo código QR, totales en S/ y glosa SUNAT.
2. **Reportes gerenciales**
   - Programar comandos `php artisan invoices:report --frequency=mensual` que generen informes y los envíen al correo de gerencia.
3. **Auditoría**
   - Implementar `InvoiceAudit` y logging a nivel de eventos (`created`, `updated`, `sunat_status_updated`).

## 7. Diagramas, manuales y capacitación
1. **Documentación**
   - Crear manual paso a paso con capturas y flujos BPMN que reflejen el diagrama SUNAT.
   - Incluir sección de resolución de errores comunes (códigos de rechazo).
2. **Capacitación**
   - Organizar sesiones con contabilidad y operaciones para validar el prototipo y ajustar formularios.

## 8. Requisitos adicionales
1. **Accesibilidad**
   - Asegurar contraste en modo claro/oscuro y soporte de teclado.
2. **Escalabilidad**
   - Ejecutar envíos masivos mediante Jobs en colas `sunat` y supervisar con Horizon.
3. **Backups y recuperación**
   - Implementar comandos nocturnos que verifiquen integridad de XML/CDR y sincronicen con almacenamiento externo.

---

Con este plan y las credenciales SUNAT proporcionadas, podemos implementar el flujo completo mostrado en el diagrama, manteniendo buenas prácticas de Laravel 10+, Livewire y TailwindCSS. Consulte también `README-billing.md` para pasos de instalación y operación.

