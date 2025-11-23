# Pruebas y CI local

## Ejecutar suites críticas

Los flujos sensibles (órdenes, guías, facturación y tableros) ahora cuentan con pruebas unitarias/feature. Para ejecutarlas localmente:

```bash
php artisan test --filter=OrderAssignmentServiceTest
php artisan test --filter=RouteOptimizationServiceTest
php artisan test --filter=TransportGuideIssuerTest
php artisan test --filter=SunatDashboardExportControllerTest
```

## Ejecutar la misma rutina que CI

El pipeline `tests` de GitHub Actions corre los comandos:

```bash
npm run build
./vendor/bin/phpunit
```

Use los mismos pasos para verificar cambios antes de un push. Si necesita probar todas las suites con base de datos limpia:

```bash
php artisan test
```
