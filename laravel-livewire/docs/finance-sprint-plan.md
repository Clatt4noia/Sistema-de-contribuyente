# Plan de Sprints 4 y 5 - Módulo de Finanzas y Facturación

> Hasta el Sprint 3 se han cubierto HU1–HU12 (emisión SUNAT, almacenamiento, trazabilidad y reportes). A continuación se definen tres historias por sprint (HU13–HU18) alineadas con lo que ya está implementado: facturación electrónica, envíos a SUNAT, y guías de remisión electrónicas (GRE-T y GRE-R).

## Sprint 4: Consolidación de facturación y guías de remisión

1. **HU13: Vínculo factura ↔ guía de remisión**
   - *Descripción:* Como responsable de facturación quiero relacionar cada factura con su guía de remisión (GRE-T o GRE-R) desde el formulario de comprobantes para mantener coherencia tributaria y logística.
   - *Criterios de aceptación:*
     - En el formulario de facturas se puede buscar y asociar una GRE existente, mostrando serie, correlativo y estado SUNAT.
     - Al generar una GRE desde el módulo de guías se puede seleccionar la factura relacionada para precargar datos de cliente y destino.
     - Reporte/tabla de facturas muestra si tienen GRE vinculada y permite abrir la ficha de la guía asociada.

2. **HU14: Seguimiento SUNAT en tablero unificado**
   - *Descripción:* Como analista quiero ver en un solo tablero el estado SUNAT de facturas y GRE (aceptado, rechazado, pendiente) con filtros por fecha, serie y tipo de documento.
   - *Criterios de aceptación:*
     - Lista combinada de comprobantes y guías con badges de estado (verde/rojo/ámbar) y mensaje de respuesta SUNAT.
     - Botón de reintento para documentos pendientes/rechazados que dispare el Job de reenvío (`SendElectronicInvoice` para facturas y emisión GRE para guías).
     - Exportación a Excel/PDF con el estado SUNAT y la fecha/hora de la última consulta.

3. **HU15: Reporte de cobranzas y gastos operativos**
   - *Descripción:* Como gerente financiero quiero ver ingresos vs. gastos operativos de entregas (combustible, peajes, viáticos) junto con el estado de cobro de las facturas emitidas.
   - *Criterios de aceptación:*
     - Resumen por periodo (mes/semana/día) con totales de facturas cobradas, pendientes y gastos asociados a las rutas/entregas.
     - Filtros por cliente, ruta o vehículo, mostrando relación con la guía de remisión correspondiente.
     - Exportación a Excel/CSV para conciliación contable.

## Sprint 5: Automatización y cumplimiento operativo

1. **HU16: Envío automático de comprobantes y GRE con adjuntos**
   - *Descripción:* Como responsable de facturación quiero que al quedar aceptado por SUNAT se envíe automáticamente al cliente el PDF, XML y CDR de la factura, y opcionalmente la GRE vinculada.
   - *Criterios de aceptación:*
     - Job en cola que, tras actualizar `estado_sunat = aceptado`, dispare correo con enlaces/adjuntos al cliente y copia interna.
     - Si la factura tiene GRE asociada, incluir el PDF/XML de la guía en el mismo correo o en enlace seguro.
     - Registro en bitácora de envío (fecha, destinatarios, resultado) y reintento automático si el correo falla.

2. **HU17: Alertas de vencimiento de certificados y series**
   - *Descripción:* Como jefe de cumplimiento quiero alertas cuando el certificado digital, las series de facturas o las series GRE estén por vencer o agotarse para evitar bloqueos de emisión.
   - *Criterios de aceptación:*
     - Monitor con semáforo (verde/amarillo/rojo) que muestre días restantes de certificado y correlativos disponibles por serie.
     - Alertas por correo/Slack cuando falten 30, 15 o 7 días para vencer el certificado o cuando una serie alcance el 90% de uso.
     - Acciones registradas (renovar certificado, habilitar nueva serie) con usuario y timestamp.

3. **HU18: Auditoría integral de facturas y GRE**
   - *Descripción:* Como auditor quiero ver la trazabilidad completa de facturas y guías: generación, envíos a SUNAT, reintentos y descargas de archivos.
   - *Criterios de aceptación:*
     - Línea de tiempo por documento con eventos clave (creado, enviado, respuesta SUNAT, reenvío, envío de correo al cliente) y usuario asociado.
     - Acceso desde la ficha a XML, CDR y PDF; para GRE, incluir el XML UBL generado.
     - Exportación de la bitácora a PDF/Excel para revisiones externas.

---

Estas historias priorizan consolidar lo ya disponible (formularios de factura, Jobs de envío electrónico y módulos de GRE) y avanzar hacia mayor automatización, cumplimiento y trazabilidad.
