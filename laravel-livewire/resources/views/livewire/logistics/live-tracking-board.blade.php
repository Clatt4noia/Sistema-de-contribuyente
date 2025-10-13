<div class="space-y-4">
 <div wire:ignore id="live-tracking-map" class="h-80 w-full rounded-2xl border border-slate-200 bg-slate-100 "></div>

 @if(empty($markers))
 <p class="text-sm text-slate-600 ">{{ __('Sin posiciones recientes para visualizar en el mapa.') }}</p>
 @else
 <ul class="grid gap-3 text-sm text-slate-600 sm:grid-cols-2">
 @foreach ($markers as $marker)
 <li class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm ">
 <p class="font-semibold text-slate-900 ">{{ $marker['truck'] ?? __('Vehículo sin placa') }}</p>
 <p>{{ __('Pedido') }}: <span class="font-medium">{{ $marker['order'] ?? '—' }}</span></p>
 <p>{{ __('Estado') }}: <span class="capitalize">{{ __($marker['status'] ?? 'on_route') }}</span></p>
 <p>{{ __('Reportado') }}: {{ $marker['reported_at'] }}</p>
 </li>
 @endforeach
 </ul>
 @endif
</div>

<script>
 (function () {
 const markers = @json($markers);
 const mapContainerId = 'live-tracking-map';

 if (!document.getElementById(mapContainerId)) {
 return;
 }

 function ensureLeafletLoaded(callback) {
 if (window.L && window.L.map) {
 callback();
 return;
 }

 const existingScript = document.querySelector('script[data-livewire-leaflet]');

 if (!existingScript) {
 const link = document.createElement('link');
 link.rel = 'stylesheet';
 link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
 link.integrity = 'sha256-sA+z0pCfY2vOjZ0J/3p0kACIRyIoxnQJtG8a8JStjKk=';
 link.crossOrigin = '';
 document.head.appendChild(link);

 const script = document.createElement('script');
 script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
 script.integrity = 'sha256-o9N1j7kP5L7MbEuK3tXn6tB8CpI3HB+Y1Cx0KX2f5EQ=';
 script.crossOrigin = '';
 script.dataset.livewireLeaflet = 'true';
 script.onload = callback;
 document.body.appendChild(script);
 } else if (existingScript.dataset.loaded) {
 callback();
 } else {
 existingScript.addEventListener('load', () => {
 existingScript.dataset.loaded = 'true';
 callback();
 }, { once: true });
 }
 }

 function initMap() {
 if (!window.L || !markers.length) {
 return;
 }

 const container = document.getElementById(mapContainerId);

 if (!container || container.dataset.mapRendered) {
 return;
 }

 container.dataset.mapRendered = 'true';

 const map = window.L.map(mapContainerId).setView([markers[0].latitude, markers[0].longitude], 10);
 window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
 maxZoom: 18,
 attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
 }).addTo(map);

 markers.forEach((marker) => {
 window.L.marker([marker.latitude, marker.longitude])
 .addTo(map)
 .bindPopup(`<strong>${marker.truck ?? 'Vehículo'}</strong><br/>Pedido: ${marker.order ?? '—'}<br/>${marker.reported_at}`);
 });

 if (markers.length > 1) {
 const bounds = window.L.latLngBounds(markers.map((marker) => [marker.latitude, marker.longitude]));
 map.fitBounds(bounds, { padding: [20, 20] });
 }
 }

 ensureLeafletLoaded(initMap);
 })();
</script>
