# Módulo de Logística

## Seguimiento en tiempo real con mapa embebido

El sistema de seguimiento en tiempo real se implementa como un componente Livewire (`App\\Livewire\\Logistics\\LiveTrackingBoard`) que recopila las últimas posiciones reportadas de cada vehículo desde el modelo `VehicleLocationUpdate`. Cada actualización almacena referencias al camión, la asignación y el pedido relacionado, así como las coordenadas geográficas, velocidad y estado reportado.

En la vista `livewire/logistics/live-tracking-board.blade.php` se dibuja un mapa interactivo utilizando Leaflet y mosaicos de OpenStreetMap. Cuando existen posiciones recientes, el componente convierte cada registro en un marcador que contiene la latitud, longitud y metadatos relevantes (vehículo, pedido, hora y estado). Al cargarse la página, un script ligero asegura que Leaflet esté disponible, genera el mapa centrado en la primera coordenada y agrega los marcadores. Si hay múltiples unidades, se ajusta automáticamente el zoom para mostrar todas las ubicaciones.

El tablero logístico expone esta información en la ruta `/dashboards/logistics/tracking`, protegida por el mismo middleware de autorización que el panel de logística. Desde allí, los coordinadores pueden supervisar en tiempo real la posición y estado de cada envío, identificar desviaciones y consultar rápidamente la información del pedido asociado.

## Notificaciones de estado del pedido (HU-14)

Para la historia de usuario **HU-14** ("Notificar automáticamente cambios en el estado del pedido"), la interfaz propuesta es una vista Livewire dedicada que sirve como bandeja de notificaciones en el módulo de logística:

- **Componente:** `App\\Livewire\\Logistics\\OrderStatusNotifications`, responsable de escuchar los eventos de actualización de estado y poblar un feed de novedades.
- **Vista:** `resources/views/livewire/logistics/order-status-notifications.blade.php`, con un panel tipo *timeline* que muestra la referencia del pedido, su estado actual (en ruta, entregado, en almacén, etc.) y la hora exacta del cambio.
- **Acceso:** ruta protegida `/dashboards/logistics/orders/notifications`, accesible desde el tablero de logística para coordinadores.

Los eventos se persisten en la tabla `order_status_updates`, relacionando pedido, asignación y el usuario que registró el cambio. La vista consume los últimos 30 eventos, ordenados de más reciente a más antiguo.

La vista despliega una tarjeta por evento de cambio de estado, ordenada de más reciente a más antiguo. Cada tarjeta incluye:

- Pedido y asignación vinculada (para entender qué camión/chofer lleva la carga).
- Estado anterior y nuevo estado, resaltando la transición con colores consistentes con el resto del módulo logístico.
- Marca de tiempo del evento y un enlace contextual para abrir el detalle del pedido.

Todas las notificaciones quedan registradas en un historial consultable (alineado con las dependencias HU-05 y HU-04), de modo que el coordinador pueda revisar la secuencia completa de movimientos del pedido sin abandonar el módulo.

### Fuentes de localización y requisitos operativos

Para que el tablero muestre posiciones en vivo es necesario contar con un flujo continuo de coordenadas. El sistema admite distintas fuentes, por lo que **no es obligatorio instalar un GPS dedicado en cada vehículo**, aunque suele ser la alternativa más precisa. Las opciones principales son:

- **Dispositivos GPS/IoT del camión**: reportan automáticamente la ubicación a través de un servicio telemático que puede integrarse con la API de ingesta (`TrackingService`). Es ideal cuando la flota ya cuenta con hardware de rastreo.
- **Aplicación móvil del conductor**: el chofer puede iniciar sesión en una app que envía su ubicación periódicamente mientras está en ruta. Este enfoque evita hardware adicional, pero depende de la batería y cobertura del dispositivo.
- **Integraciones con terceros**: si la empresa trabaja con operadores logísticos externos, se pueden consumir las posiciones desde sus APIs y normalizarlas antes de guardarlas en `VehicleLocationUpdate`.
- **Registro manual puntual**: para operaciones pequeñas, el operador puede capturar manualmente la posición (por ejemplo, al salir y al llegar). En ese caso el seguimiento no será “en tiempo real”, pero el mapa mostrará los hitos relevantes.

Independientemente de la fuente, cada actualización debe incluir al menos latitud, longitud, sello de tiempo y el identificador de la asignación o vehículo. Con esa información el componente Livewire construye los marcadores y actualiza el mapa sin necesidad de recalibrar otros módulos del sistema.
