# Eventos de dominio para pedidos

## Orden de ejecución

- **OrderCreated** (al crear un `Order`):
  1. `AssignResourcesToOrder` asigna recursos y notifica si aplica.
  2. `ReserveOrderInventory` reserva stock si hay ítems válidos.
  3. `EstimateOrderCosts` guarda la estimación inicial de costos.
- **OrderStatusChanged** (solo cuando cambia `status`):
  1. `HandleOrderStatusChange` notifica a cliente/conductores, genera factura al entregar y libera inventario en entregas o cancelaciones.
- **OrderScheduleChanged** (pickup/delivery o coordenadas cambian):
  1. `UpdateOrderRoutePlan` recalcula el plan de ruta.
- **OrderCostParametersChanged** (peso/volumen/distancia cambian):
  1. `RecalculateOrderCostEstimation` recalcula costos.

Los listeners se ejecutan en el orden definido arriba por `app/Providers/EventServiceProvider.php`, manteniendo los payloads originales sin mutarlos antes de que lleguen a otros listeners.

## Consideraciones de idempotencia

- Los listeners usan consultas de existencia (`assignments()->exists()`, `inventoryReservations()->exists()`, `invoices()->exists()`) y `saveQuietly()` para evitar recursión o duplicados al reejecutar eventos.
- Las notificaciones solo se disparan cuando el estado realmente cambia (`wasChanged('status')`).
- La liberación de inventario y el cálculo de costos se apoyan en servicios que pueden ejecutarse de forma repetible sin alterar los payloads de eventos ni los datos de entrada originales.
