<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div class="space-y-1">
      <h1 class="text-2xl font-semibold text-token">{{ $isEdit ? 'Editar Orden' : 'Nueva Orden' }}</h1>
      <p class="text-sm text-token">Registra la información esencial y (opcional) calcula un costo referencial según MTC.</p>
    </div>
    <a href="{{ route('orders.index') }}" class="btn btn-secondary">Volver</a>
  </div>

  @if (session()->has('message'))
    <div class="alert alert-success">{{ session('message') }}</div>
  @endif

  <div class="surface-card p-6 shadow-lg">
    <form wire:submit.prevent="save" class="space-y-6">

      {{-- 1) Datos esenciales --}}
      <div class="space-y-3">
        <h2 class="text-lg font-semibold text-token">Datos esenciales</h2>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div class="form-field">
            <label class="form-label">Cliente *</label>
            <select wire:model.defer="form.client_id" class="form-control">
              <option value="">Seleccione un cliente</option>
              @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->business_name }}</option>
              @endforeach
            </select>
            @error('form.client_id') <span class="form-error">{{ $message }}</span> @enderror
          </div>

          <div class="form-field">
            <label class="form-label">Número de referencia *</label>
            <input type="text" wire:model.defer="form.reference" class="form-control" placeholder="Ej: ORD-2025-001">
            @error('form.reference') <span class="form-error">{{ $message }}</span> @enderror
          </div>

          <div class="form-field">
            <label class="form-label">Origen *</label>
            <input type="text" wire:model.live.debounce.400ms="form.origin" class="form-control" placeholder="Ciudad / Dirección">
            @error('form.origin') <span class="form-error">{{ $message }}</span> @enderror
          </div>

          <div class="form-field">
            <label class="form-label">Destino *</label>
            <input type="text" wire:model.live.debounce.400ms="form.destination" class="form-control" placeholder="Ciudad / Dirección">
            @error('form.destination') <span class="form-error">{{ $message }}</span> @enderror
          </div>

          <div class="form-field">
            <label class="form-label">Tipo de carga</label>
            <select wire:model.defer="form.cargo_type_id" class="form-control">
              <option value="">Seleccione una opción</option>
              @foreach($cargoTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
              @endforeach
            </select>
            @error('form.cargo_type_id') <span class="form-error">{{ $message }}</span> @enderror
          </div>

          <div class="form-field">
            <label class="form-label">Estado *</label>
            <select wire:model.defer="form.status" class="form-control">
              <option value="pending">Pendiente</option>
              <option value="en_route">En ruta</option>
              <option value="delivered">Entregado</option>
              <option value="cancelled">Cancelado</option>
            </select>
            @error('form.status') <span class="form-error">{{ $message }}</span> @enderror
          </div>
        </div>
      </div>

      {{-- 2) Fechas + carga (compacto) --}}
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="form-field">
          <label class="form-label">Fecha de recojo</label>
          <input type="datetime-local" wire:model.defer="form.pickup_date" class="form-control">
          @error('form.pickup_date') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-field">
          <label class="form-label">Fecha de entrega</label>
          <input type="datetime-local" wire:model.defer="form.delivery_date" class="form-control">
          @error('form.delivery_date') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-field">
          <label class="form-label">Peso (kg)</label>
          <input type="number" step="0.01" wire:model.live.debounce.400ms="form.cargo_weight_kg" class="form-control" placeholder="Ej: 1200">
          @error('form.cargo_weight_kg') <span class="form-error">{{ $message }}</span> @enderror
          <p class="mt-1 text-xs text-token-muted">Para MTC (Anexo II) se calcula en toneladas métricas (TM).</p>
        </div>

        <div class="form-field">
          <label class="form-label">Volumen (m³)</label>
          <input type="number" step="0.01" wire:model.defer="form.cargo_volume_m3" class="form-control" placeholder="Ej: 4.5">
          @error('form.cargo_volume_m3') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-field">
          <label class="form-label">Total de bultos</label>
          <input type="number" min="1" wire:model.defer="form.total_packages" class="form-control" placeholder="Ej: 10">
          @error('form.total_packages') <span class="form-error">{{ $message }}</span> @enderror
          <p class="mt-1 text-xs text-token-muted">Se usa para autocompletar la Guía (GRE). Opcional.</p>
        </div>
      </div>

      <div class="form-field">
        <label class="form-label">Detalle de carga</label>
        <textarea rows="3" wire:model.defer="form.cargo_details" class="form-control" placeholder="Qué se transporta, observaciones, etc."></textarea>
        @error('form.cargo_details') <span class="form-error">{{ $message }}</span> @enderror
      </div>

      {{-- 2.1) Datos para Guía (SUNAT) --}}
      <details class="rounded-2xl border border-token p-4">
        <summary class="cursor-pointer select-none text-sm font-semibold text-token">
          Datos para Guía (SUNAT) - Recomendado
        </summary>

        <div class="mt-4 space-y-6">
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="form-field">
              <label class="form-label">Ubigeo origen (6 digitos)</label>
              <input type="text" maxlength="6" inputmode="numeric" wire:model.defer="form.origin_ubigeo" class="form-control" placeholder="Ej: 150101">
              @error('form.origin_ubigeo') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-field">
              <label class="form-label">Ubigeo destino (6 digitos)</label>
              <input type="text" maxlength="6" inputmode="numeric" wire:model.defer="form.destination_ubigeo" class="form-control" placeholder="Ej: 200101">
              @error('form.destination_ubigeo') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-field">
              <label class="form-label">Direccion origen</label>
              <input type="text" wire:model.defer="form.origin_address" class="form-control" placeholder="Av / Jr / Mz / Lt...">
              @error('form.origin_address') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-field">
              <label class="form-label">Direccion destino</label>
              <input type="text" wire:model.defer="form.destination_address" class="form-control" placeholder="Av / Jr / Mz / Lt...">
              @error('form.destination_address') <span class="form-error">{{ $message }}</span> @enderror
            </div>
          </div>

          <div class="rounded-xl border border-token bg-surface p-4 space-y-4">
            <div class="text-sm font-semibold text-token">Destinatario (opcional)</div>
            <p class="text-xs text-token-muted">
              Si llenas estos datos en la Orden, la Guia GRE-T puede autocompletar al destinatario aunque no exista factura.
            </p>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
              <div class="form-field">
                <label class="form-label">Tipo doc</label>
                <select wire:model.defer="form.destinatario_document_type" class="form-control">
                  <option value="">-</option>
                  <option value="6">RUC (6)</option>
                  <option value="1">DNI (1)</option>
                  <option value="4">CE (4)</option>
                  <option value="7">PAS (7)</option>
                </select>
                @error('form.destinatario_document_type') <span class="form-error">{{ $message }}</span> @enderror
              </div>

              <div class="form-field">
                <label class="form-label">Numero doc</label>
                <input type="text" wire:model.defer="form.destinatario_document_number" class="form-control">
                @error('form.destinatario_document_number') <span class="form-error">{{ $message }}</span> @enderror
              </div>

              <div class="form-field md:col-span-1">
                <label class="form-label">Razon social / Nombre</label>
                <input type="text" wire:model.defer="form.destinatario_name" class="form-control">
                @error('form.destinatario_name') <span class="form-error">{{ $message }}</span> @enderror
              </div>
            </div>
          </div>
        </div>
      </details>

      {{-- 3) Costo referencial MTC (colapsable) --}}
      <details class="rounded-2xl border border-token p-4" open>
        <summary class="cursor-pointer select-none text-sm font-semibold text-token">
          Costo referencial MTC (DS N.° 026-2024-MTC) — Opcional
        </summary>

        <div class="mt-4 space-y-4">
          <div class="rounded-xl border border-token bg-surface p-4">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
              <div>
                <div class="text-sm font-semibold text-token">
                  {{ $mtc['ok'] ? 'Tarifa encontrada' : 'Sin tarifa aplicable' }}
                </div>
                <div class="text-xs text-token-muted">
                  {{ $mtc['message'] }}
                </div>

               @if($mtc['ok'])
                <div class="mt-2 text-xs text-token">
                    <div><span class="font-semibold">Origen ingresado:</span> {{ $form['origin'] ?? '' }}</div>
                    <div><span class="font-semibold">Destino ingresado:</span> {{ $form['destination'] ?? '' }}</div>

                    <div class="mt-2"><span class="font-semibold">Ruta MTC:</span> {{ $mtc['route_key'] }}</div>
                    <div><span class="font-semibold">Destino normalizado (MTC):</span> {{ $mtc['destination'] }}</div>
                    <div><span class="font-semibold">Tarifa:</span> S/ {{ number_format((float)$mtc['rate_sxtm'], 2) }} x TM</div>
                </div>
                @endif

              </div>

              <div class="text-right">
                <div class="text-xs text-token-muted">Costo estimado</div>
                <div class="text-lg font-bold text-token">
                  @if(is_numeric($form['estimated_cost'] ?? null))
                    S/ {{ number_format((float)$form['estimated_cost'], 2) }}
                  @else
                    —
                  @endif
                </div>
              </div>
            </div>
          </div>

          <div class="form-field">
            <label class="form-label">Costo estimado (editable)</label>
            <input type="number" step="0.01" wire:model.defer="form.estimated_cost" class="form-control" placeholder="Si deseas, ajusta manualmente">
            @error('form.estimated_cost') <span class="form-error">{{ $message }}</span> @enderror
            <p class="mt-1 text-xs text-token-muted">
              Si escribes un valor manual, el sistema ya no lo pisa con el cálculo automático.
            </p>
          </div>
        </div>
      </details>

      {{-- 4) Opcionales (colapsables) --}}
      <details class="rounded-2xl border border-token p-4">
        <summary class="cursor-pointer select-none text-sm font-semibold text-token">
          Datos opcionales (coordenadas / notas / plan de ruta)
        </summary>

        <div class="mt-4 space-y-6">

          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="form-field">
              <label class="form-label">Coordenadas de origen</label>
              <div class="grid grid-cols-2 gap-3">
                <input type="number" step="0.000001" placeholder="Latitud" wire:model.defer="form.origin_latitude" class="form-control" />
                <input type="number" step="0.000001" placeholder="Longitud" wire:model.defer="form.origin_longitude" class="form-control" />
              </div>
              @error('form.origin_latitude') <span class="form-error">{{ $message }}</span> @enderror
              @error('form.origin_longitude') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-field">
              <label class="form-label">Coordenadas de destino</label>
              <div class="grid grid-cols-2 gap-3">
                <input type="number" step="0.000001" placeholder="Latitud" wire:model.defer="form.destination_latitude" class="form-control" />
                <input type="number" step="0.000001" placeholder="Longitud" wire:model.defer="form.destination_longitude" class="form-control" />
              </div>
              @error('form.destination_latitude') <span class="form-error">{{ $message }}</span> @enderror
              @error('form.destination_longitude') <span class="form-error">{{ $message }}</span> @enderror
            </div>
          </div>

          <div class="form-field">
            <label class="form-label">Notas internas</label>
            <textarea rows="3" wire:model.defer="form.notes" class="form-control"></textarea>
            @error('form.notes') <span class="form-error">{{ $message }}</span> @enderror
          </div>

          <div class="space-y-4 rounded-2xl border border-token p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <h3 class="text-sm font-semibold text-token">Plan de ruta principal (opcional)</h3>
              <span class="text-xs text-token-muted">Solo si lo usan en operación.</span>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div class="form-field">
                <label class="form-label">Planificador</label>
                <input type="text" wire:model.defer="routePlan.planner" class="form-control">
                @error('routePlan.planner') <span class="form-error">{{ $message }}</span> @enderror
              </div>

              <div class="form-field">
                <label class="form-label">URL del mapa</label>
                <input type="url" wire:model.defer="routePlan.map_url" class="form-control" placeholder="https://maps...">
                @error('routePlan.map_url') <span class="form-error">{{ $message }}</span> @enderror
              </div>
            </div>

            <div class="form-field">
              <label class="form-label">Resumen de la ruta</label>
              <textarea rows="3" wire:model.defer="routePlan.route_summary" class="form-control"></textarea>
              @error('routePlan.route_summary') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-field">
              <label class="form-label">Datos adicionales (JSON)</label>
              <textarea rows="3" wire:model.defer="routePlan.route_data" class="form-control" placeholder='{"waypoints": []}'></textarea>
              @error('routePlan.route_data') <span class="form-error">{{ $message }}</span> @enderror
            </div>
          </div>

        </div>
      </details>

      <div class="flex items-center justify-end gap-3">
        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">
          {{ $isEdit ? 'Actualizar' : 'Guardar' }}
        </button>
      </div>

    </form>
  </div>

  @if($isEdit)
    <div class="surface-card p-6 shadow-lg">
      <livewire:orders.route-plan-manager :order="$order" :key="'route-plan-'.$order->id" />
    </div>
  @endif
</div>
