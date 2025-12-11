# Arquitectura objetivo del sistema CARLOS GABRIEL TRANSPORTE S.A.C.

## 1. Objetivo y principios rectores
- **Disponibilidad y resiliencia**: componentes desacoplados por módulo (flota, logística, facturación) desplegables en contenedores independientes, con colas para tareas críticas.
- **Seguridad por diseño**: controles alineados a OWASP ASVS 2025, cifrado de datos sensibles, auditoría completa y cumplimiento con la Ley N° 29733.
- **Modularidad**: mantener dominios delimitados (bounded contexts) y contratos claros entre ellos.
- **Escalabilidad**: permitir crecimiento horizontal de módulos con colas, caché y almacenamiento distribuido.
- **Observabilidad**: métricas, trazas y logs estructurados para cada módulo.

## 2. Módulos / Bounded Contexts
### 2.1 Gestión de flota y choferes
- **Modelos clave**: `Truck`, `Driver`, `Maintenance`, `Assignment`, `MaintenanceTask`, `FleetDocument`.
- **Casos de uso**: alta/baja de unidades, disponibilidad en tiempo real, historial de mantenimientos, documentación, asignación a órdenes.
- **Componentes Livewire**: listas/formularios desacoplados (`TruckList`/`TruckForm`, etc.) + `Fleet\Report` para dashboards.
- **Servicios internos**: `FleetService` (operaciones CRUD + reglas de disponibilidad), `MaintenanceScheduler`, integración con telemática (cola de actualizaciones).
- **Persistencia**: tablas normalizadas con claves foráneas, auditoría (`fleet_activity_logs`).

### 2.2 Gestión logística y de clientes
- **Modelos clave**: `Client`, `Order`, `RoutePlan`, `ShipmentEvent`, `NotificationSetting`.
- **Casos de uso**: registro de servicios, matching camión/chofer, seguimiento en tiempo real, notificaciones a clientes, historial comercial.
- **Componentes Livewire**: `Clients\ClientList/Form`, `Orders\OrderList/Form`, `Orders\RoutePlanManager`, `Orders\ShipmentTimeline` (nuevo) para eventos en vivo.
- **Servicios**: `OrderAllocator` (resolver disponibilidad vs restricciones), `NotificationService` (correo/SMS/WhatsApp), `TrackingIngestor` (webhooks GPS).
- **Persistencia**: `orders`, `assignments`, `shipment_events`, `client_contacts`, `notifications`.

### 2.3 Facturación y pagos
- **Modelos clave**: `Invoice`, `Payment`, `InvoiceItem`, `PaymentSchedule`, `BankAccount`, `TaxRetention`.
- **Casos de uso**: emisión electrónica (SUNAT), conciliación de pagos, reportes financieros, recordatorios automáticos.
- **Componentes Livewire**: `Billing\InvoiceList/Form`, `Billing\PaymentList/Form`, `Billing\Reports` (KPIs), `Billing\PaymentScheduler`.
- **Servicios**: `InvoiceIssuer` (integración OSE/SUNAT), `PaymentGatewayService`, `ReportGenerator` (BI), `CollectionsService` (recordatorios).
- **Persistencia**: `invoices`, `invoice_items`, `payments`, `payment_schedules`, `bank_transactions`.

## 3. Roles y control de acceso
| Rol | Descripción | Módulos habilitados | Capacidades clave |
| --- | --- | --- | --- |
| `admin` | Superusuario interno | Todos | Configuración, usuarios, reportes globales.
| `logistics_manager` | Área logística (operaciones) | Flota, logística | Gestiona camiones, choferes, órdenes, rutas, reportes operativos.
| `fleet_manager` | Subrol de logística (taller/flota) | Flota | Mantenciones, disponibilidad, documentación.
| `finance_manager` | Área contabilidad/finanzas | Facturación, reportes financieros | Emite comprobantes, gestiona pagos.
| `finance_analyst` | Analista financiero | Facturación (solo lectura + conciliación) | Consulta reportes, marca pagos conciliados.
| `client` | Cliente externo | Portal de clientes | Consulta órdenes, tracking y facturas propias.

### Matriz de acceso (resumen)
- **Flota**: `admin`, `logistics_manager`, `fleet_manager` (lectura total), sólo `admin` y `fleet_manager` mutan.
- **Órdenes/Clientes**: `admin`, `logistics_manager` (completo); `finance_manager` lectura parcial para facturación; `client` sólo sus datos.
- **Facturación/Pagos**: `admin`, `finance_manager` mutan; `finance_analyst` lectura y conciliación limitada.
- **Reportes ejecutivos**: `admin`, `finance_manager`, `logistics_manager`.

### Implementación técnica
- Uso de `Gate::before` para `admin`.
- Políticas por modelo (`TruckPolicy`, `OrderPolicy`, etc.) respaldadas por constantes centralizadas en `App\Models\User`.
- Validación de roles mediante enum y `Rule::enum` en formularios.
- `AuthorizesRequests` en componentes Livewire (ya integrado) + scopes filtrados por rol (ej. clientes ven sólo sus órdenes).
- Integración futura con **spatie/laravel-permission** si se requiere granularidad adicional.

## 4. Modelo de datos y migraciones
- `users` incluye columna `role` (enum string) + metadatos (`last_login_ip`, `two_factor_secret`, `consent_version`).
- Relaciones clave:
  - `orders` ↔ `assignments` (1:N) ↔ `trucks`/`drivers`.
  - `orders` ↔ `shipment_events` (tracking).
  - `invoices` ↔ `invoice_items` + `payments`.
  - `clients` ↔ `client_contacts`, `client_portal_users` (para clientes externos).
- Migraciones limpias: `2025_01_..._create_clients_table.php`, `..._create_orders_table.php`, etc., con `foreignIdFor()` y `constrained()` para integridad.
- Seeders: crear roles/usuarios base (admin, logística, finanzas), catálogos (tipos de mantenimiento, métodos de pago).
- Factories: cubrir escenarios de pruebas (órdenes pendientes, rutas activas, facturas vencidas).

## 5. Arquitectura de aplicación
- **Capa de presentación**: Livewire Volt para formularios y listados reactivos, componentes Blade compartidos (`components.layouts.dashboard`).
- **Capa de dominio**: servicios (`App\Services\Fleet`, `App\Services\Logistics`, `App\Domains\Billing\Services`) orquestan reglas y transacciones.
- **Capa de infraestructura**: repositorios para integraciones externas (SUNAT, GPS, pasarelas), jobs en cola (`ProcessInvoice`, `SyncTelematicsEvent`).
- **Eventos y listeners**: `OrderAssigned`, `InvoiceIssued`, `PaymentReceived` alimentan notificaciones, reportes y auditoría.
- **Observabilidad**: logging estructurado (Monolog JSON), métricas (Prometheus via Horizon), tracing (OpenTelemetry exporter).

## 6. Seguridad y cumplimiento
- **Autenticación**: Laravel Fortify + 2FA (TOTP/Email) para roles internos, OAuth2 para clientes.
- **Autorización**: políticas + gates, pruebas de regresión incluidas (`tests/Feature/Authorization`).
- **Protección de datos**: hashing de contraseñas (`argon2id`), cifrado de datos sensibles (`encrypt()` + columnas `binary`), registros de consentimiento y políticas de retención.
- **OWASP**: CSRF en formularios, validación estricta de entrada (`FormRequest`/`Livewire`), limitación de tasa (`ThrottleRequests`), encabezados de seguridad (`SecureHeaders` middleware), sanitización de archivos.
- **Auditoría**: tabla `activity_logs` (modelo `AuditLog`) con IP, user-agent, payload saneado. Integración con SIEM para alertas.
- **Backups y DRP**: snapshots diarios de base de datos, replicación en caliente, pruebas de restauración trimestrales.

## 7. Flujo de trabajo y pruebas
- **Pruebas**: unitarias por servicio, feature para políticas y flujos, pruebas Dusk para journeys críticos, contract tests para integraciones externas.
- **CI/CD**: pipelines con validación estática (Pint, PHPStan), pruebas, análisis de dependencias (Dependabot), escaneo SAST/DAST.
- **Entornos**: Dev (Docker Compose), Staging (contiene datos anonimizados), Producción (Kubernetes + RDS).

## 8. Roadmap de implementación
1. **Fundación**: migraciones base, seeds, enum de roles, políticas iniciales, autenticación segura.
2. **Módulo Flota**: CRUD, mantenimientos, asignaciones, reportes.
3. **Módulo Logístico**: órdenes, rutas, tracking, notificaciones.
4. **Módulo Facturación**: emisión electrónica, pagos, conciliación, reportes.
5. **Portal Cliente**: acceso a tracking, facturas, mensajes.
6. **Observabilidad & Integraciones**: telemetría, SUNAT, pasarelas de pago, automatizaciones.

## 9. Cambios inmediatos propuestos
- Centralizar roles y matrices de acceso en `App\Models\User` usando enums / constantes.
- Actualizar políticas para reflejar los nuevos roles (`logistics_manager`, `fleet_manager`, `finance_manager`, `finance_analyst`, `client`).
- Ajustar formularios (registro, administración de usuarios) para validar contra el catálogo de roles permitidos.
- Añadir pruebas de autorización diferenciando cada rol clave.
- Documentar arquitectura (este archivo) y mantenerlo actualizado con decisiones ADR.

hola
