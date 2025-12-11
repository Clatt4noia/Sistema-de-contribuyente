# Plan de arquitectura modular por dominios

## Objetivo
Organizar el código en dominios con alta cohesión y bajo acoplamiento siguiendo PSR-4 y convenciones Laravel/Livewire. Las rutas públicas, vistas y comportamiento deben permanecer idénticos tras el refactor.

## Estructura propuesta
```
app/
  Domains/
    Auth/
      Actions/
      Http/Controllers/
      Http/Requests/
      Livewire/
      Models/
      Policies/
    Billing/
      Actions/
      Http/Controllers/
      Http/Requests/
      Jobs/
      Livewire/
      Models/
      Policies/
      Services/
      Support/
    Fleet/
    Logistics/
    Orders/
    Settings/
    ClientPortal/
resources/views/
  auth/
  billing/
  fleet/
  logistics/
  orders/
  settings/
  client-portal/
resources/views/livewire/
  auth/
  billing/
  fleet/
  logistics/
  orders/
  settings/
  client-portal/
```

## Convenciones de namespaces
- Controladores: `App\\Domains\\<Domain>\\Http\\Controllers`
- Actions/UseCases: `App\\Domains\\<Domain>\\Actions`
- Form requests: `App\\Domains\\<Domain>\\Http\\Requests`
- Jobs: `App\\Domains\\<Domain>\\Jobs`
- Servicios: `App\\Domains\\<Domain>\\Services`
- Soporte/helpers: `App\\Domains\\<Domain>\\Support`
- Livewire: `App\\Domains\\<Domain>\\Livewire` (y subcarpetas por feature)
- Modelos (cuando se muevan): `App\\Domains\\<Domain>\\Models`

## Ubicación por dominio
- **Auth**: acciones de sesión (p.ej. `Logout`), controladores de verificación/registro, solicitudes de autenticación, componentes Livewire/Volt (`resources/views/livewire/auth`), políticas de usuario.
- **Billing**: controladores de facturación y guías, actions de emisión electrónica, jobs SUNAT, servicios de firma/emisión, agregadores de estado, Livewire de facturas/pagos/guias, requests de filtros, vistas en `resources/views/pages/billing` y `resources/views/livewire/billing`.
- **Fleet / Logistics / Orders / Settings / ClientPortal**: mantener en `app/Domains/<Domain>` respetando la misma jerarquía cuando se aborden en PRs posteriores.

## Pasos de migración seguros
1. **Inventario**: listar archivos por dominio y dependencias (rutas, Livewire aliases, bindings, vistas que llamen `view()` o `<livewire:...>`).
2. **Mover por capa**: trasladar Actions/Controllers/Requests/Jobs/Services/Support del dominio al árbol `app/Domains/<Domain>/...` ajustando namespaces y `use`.
3. **Actualizar referencias**: en rutas, componentes, tests y providers (incluyendo registros manuales de Livewire) para que apunten al nuevo namespace sin cambiar nombres de rutas ni paths.
4. **Vistas Livewire**: mantener nombres de componentes y plantillas, solo ajustar `render()`/`view()` si cambia la carpeta destino.
5. **Autoload**: no se requiere cambio en `composer.json` (PSR-4 `App\\` cubre `app/Domains`), pero ejecutar `composer dump-autoload` tras el refactor.
6. **Verificación**: buscar namespaces antiguos, limpiar imports huérfanos y confirmar que las rutas siguen resolviendo las clases movidas.

## Alcance de este PR
- Aplicar el esquema solo a **Auth** y **Billing**:
  - Auth: mover `Logout` a `App\\Domains\\Auth\\Actions` y actualizar referencias; mantener rutas y vistas Volt intactas.
  - Billing: mover Actions, Requests, Jobs, Services y Support a `App\\Domains\\Billing\\...`, actualizando Livewire, controladores, tests y provider de componentes sin cambiar lógica ni URLs.
- Dominios Fleet, Logistics, Orders, Settings y ClientPortal se migrarán en PRs posteriores siguiendo este mismo plan.
