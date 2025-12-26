{{-- Mostrar dinámicamente GRE-T o GRE-R en el título y en las etiquetas de serie/tipo de documento según $type. No duplicar la vista; usar condiciones simples. --}}
<div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    @php
        $isTransportista = $type === \App\Models\TransportGuide::TYPE_TRANSPORTISTA;
        $greLabel = $isTransportista ? 'GRE-T' : 'GRE-R';
        $guideLabel = $isTransportista ? 'transportista' : 'remitente';
        $backRoute = $isTransportista ? route('billing.transport-guides.index') : route('billing.remitter-guides.index');
    @endphp

    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <p class="text-sm font-semibold text-accent">{{ $greLabel }}</p>
            <h1 class="text-2xl font-bold text-token">
                {{ $isEdit ? "Editar guía de $guideLabel ($greLabel)" : "Nueva guía de $guideLabel ($greLabel)" }}
            </h1>
            <p class="text-sm text-token-muted">Completa los datos exigidos por SUNAT para emitir la {{ $greLabel }}.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ $backRoute }}" class="btn btn-secondary">Volver</a>
            @if($isEdit && $transportGuide->sunat_status === \App\Models\TransportGuide::STATUS_DRAFT)
                @can('issue', $transportGuide)
                    <a href="{{ route('billing.transport-guides.show', $transportGuide) }}" class="btn btn-primary">Emitir</a>
                @endcan
            @endif
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    @error('save')
        <div class="alert alert-danger" role="alert">
            <p>{{ $message }}</p>
        </div>
    @enderror

    <form wire:submit.prevent="save" class="space-y-6">

        {{-- 1) Identificación + Servicio (GRE-T) --}}
        <div class="surface-card rounded-xl border border-token p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-semibold text-token">
                @if($isTransportista)
                    Identificación y datos del servicio
                @else
                    Identificación de la guía
                @endif
            </h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="form-label" for="series">Serie ({{ $greLabel }})</label>
                    <input id="series" type="text"
                           wire:model.defer="form.series"
                           class="form-control @error('form.series') is-invalid @enderror"
                           placeholder="{{ $isTransportista ? 'V001' : 'T001' }}">
                    @error('form.series') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="correlative">Correlativo</label>
                    <input id="correlative" type="number" min="1"
                           wire:model.defer="form.correlative"
                           class="form-control @error('form.correlative') is-invalid @enderror">
                    @error('form.correlative') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="document_type_code">Tipo de documento (fijo)</label>
                    <input id="document_type_code" type="text"
                           wire:model.defer="form.document_type_code"
                           class="form-control @error('form.document_type_code') is-invalid @enderror"
                           readonly>
                    @error('form.document_type_code') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                @if($isTransportista)
                    <div class="md:col-span-3">
                        <label class="form-label" for="assignment_id">Asignación / Servicio (recomendado)</label>
                        <select id="assignment_id"
                                wire:model.live="form.assignment_id"
                                class="form-control @error('form.assignment_id') is-invalid @enderror">
                            <option value="">Opcional</option>
                            @foreach($assignments as $assignment)
                                @php
                                    $assignmentOrderLabel = $assignment->order
                                        ? trim($assignment->order->reference.' - '.$assignment->order->origin.' -> '.$assignment->order->destination)
                                        : null;

                                    $assignmentLabel = $assignmentOrderLabel ?: ($assignment->description
                                        ?: trim(implode(' / ', array_filter([
                                            optional($assignment->truck)->plate_number,
                                            optional($assignment->driver)->name,
                                        ]))));
                                @endphp
                                <option value="{{ $assignment->id }}">
                                    #{{ $assignment->id }} - {{ $assignmentLabel ?: 'Servicio' }} ({{ $assignment->status->label() }})
                                </option>
                            @endforeach
                        </select>
                        @error('form.assignment_id') <p class="form-error">{{ $message }}</p> @enderror

                        <p class="form-help">
                            Al elegir una asignación, el sistema intentará autollenar: <b>cliente/remitente</b>, <b>destinatario</b> (si hay factura),
                            <b>factura relacionada</b> (si existe para la orden), y <b>vehículo/conductor</b>. Puedes corregir lo que falte.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- 2) Cliente y partes --}}
        <div class="surface-card rounded-xl border border-token p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-semibold text-token">
                @if($isTransportista)
                    Cliente y partes (Remitente / Destinatario)
                @else
                    Partes (Remitente / Destinatario)
                @endif
            </h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-3">
                    <label class="form-label" for="client_id">
                        @if($isTransportista)
                            Cliente (Remitente - Dueño de la mercancía)
                        @else
                            Cliente (Destinatario)
                        @endif
                    </label>
                    <select id="client_id"
                            wire:model.live="form.client_id"
                            class="form-control @error('form.client_id') is-invalid @enderror">
                        <option value="">Seleccione cliente</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->business_name }}</option>
                        @endforeach
                    </select>
                    @error('form.client_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                @if($isTransportista)
                    {{-- GRE-T: Remitente = cliente --}}
                    <div>
                        <label class="form-label" for="remitente_document_number">RUC Remitente</label>
                        <input id="remitente_document_number" type="text"
                               wire:model="form.remitente_document_number"
                               class="form-control bg-surface-muted" readonly>
                        @error('form.remitente_document_number') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label" for="remitente_name">Razón social Remitente</label>
                        <input id="remitente_name" type="text"
                               wire:model="form.remitente_name"
                               class="form-control bg-surface-muted" readonly>
                        @error('form.remitente_name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                @endif

                {{-- Destinatario (siempre visible; en GRE-T idealmente viene de factura u otra fuente confiable) --}}
                <div>
                    <label class="form-label" for="destinatario_document_number">RUC destinatario</label>
                    <input id="destinatario_document_number" type="text"
                           wire:model="form.destinatario_document_number"
                           class="form-control">
                    @error('form.destinatario_document_number') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="form-label" for="destinatario_name">Razón social destinatario</label>
                    <input id="destinatario_name" type="text"
                           wire:model="form.destinatario_name"
                           class="form-control">
                    @error('form.destinatario_name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-3">
                    <label class="form-label" for="observations">Observaciones</label>
                    <textarea id="observations"
                              wire:model.defer="form.observations"
                              class="form-control @error('form.observations') is-invalid @enderror"></textarea>
                    @error('form.observations') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- 3) Documentos relacionados --}}
        <div class="surface-card rounded-xl border border-token p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-semibold text-token">Documentos relacionados</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="form-label" for="related_invoice_id">Factura relacionada</label>
                    <select id="related_invoice_id"
                            wire:model.live="form.related_invoice_id"
                            class="form-control @error('form.related_invoice_id') is-invalid @enderror">
                        <option value="">Opcional</option>
                        @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}">
                                {{ $invoice->invoice_number }} - {{ $invoice->client->business_name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('form.related_invoice_id') <p class="form-error">{{ $message }}</p> @enderror

                    <p class="form-help">
                        Si la orden tiene una factura, se debería autoseleccionar aquí. Si no, puedes elegirla manualmente.
                    </p>
                </div>

                <div>
                    <label class="form-label" for="related_invoice_number">Número factura/guía remitente</label>
                    <input id="related_invoice_number" type="text"
                           wire:model.defer="form.related_invoice_number"
                           class="form-control @error('form.related_invoice_number') is-invalid @enderror">
                    @error('form.related_invoice_number') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="related_sender_guide_number">
                        @if($isTransportista)
                            Guía del remitente (GRE-R)
                        @else
                            Guía del remitente
                        @endif
                    </label>
                    <input id="related_sender_guide_number" type="text"
                           wire:model.defer="form.related_sender_guide_number"
                           class="form-control @error('form.related_sender_guide_number') is-invalid @enderror"
                           placeholder="{{ $isTransportista ? 'Ej: T001-00001234' : '' }}">
                    @error('form.related_sender_guide_number') <p class="form-error">{{ $message }}</p> @enderror

                    @if($isTransportista)
                        <p class="form-help">
                            Si existe una GRE-R registrada para la orden, se intentará autollenar. Si el cliente te la dio, regístrala aquí.
                        </p>
                    @endif
                </div>

                <div class="md:col-span-3">
                    <label class="form-label" for="additional_document_reference">Documento adicional</label>
                    <input id="additional_document_reference" type="text"
                           wire:model.defer="form.additional_document_reference"
                           class="form-control @error('form.additional_document_reference') is-invalid @enderror">
                    @error('form.additional_document_reference') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- 4) Datos de traslado --}}
        <div class="surface-card rounded-xl border border-token p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-semibold text-token">Datos de traslado</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="form-label" for="transfer_reason_code">Motivo de traslado</label>
                    <input id="transfer_reason_code" type="text"
                           wire:model.defer="form.transfer_reason_code"
                           class="form-control @error('form.transfer_reason_code') is-invalid @enderror"
                           placeholder="Catálogo 20">
                    @error('form.transfer_reason_code') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="transfer_reason_description">Descripción del motivo</label>
                    <input id="transfer_reason_description" type="text"
                           wire:model.defer="form.transfer_reason_description"
                           class="form-control @error('form.transfer_reason_description') is-invalid @enderror">
                    @error('form.transfer_reason_description') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="transport_mode_code">Modalidad de transporte</label>
                    <input id="transport_mode_code" type="text"
                           wire:model.defer="form.transport_mode_code"
                           class="form-control @error('form.transport_mode_code') is-invalid @enderror"
                           placeholder="01 público / 02 privado">
                    @error('form.transport_mode_code') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="start_transport_date">Fecha inicio traslado</label>
                    <input id="start_transport_date" type="date"
                           wire:model.defer="form.start_transport_date"
                           class="form-control @error('form.start_transport_date') is-invalid @enderror">
                    @error('form.start_transport_date') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="delivery_date">Fecha de entrega</label>
                    <input id="delivery_date" type="date"
                           wire:model.defer="form.delivery_date"
                           class="form-control @error('form.delivery_date') is-invalid @enderror">
                    @error('form.delivery_date') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input id="scheduled_transshipment" type="checkbox"
                           wire:model.defer="form.scheduled_transshipment"
                           class="form-checkbox">
                    <label class="form-label" for="scheduled_transshipment">Transbordo programado</label>
                </div>

                <div>
                    <label class="form-label" for="gross_weight">Peso bruto total (KGM)</label>
                    <input id="gross_weight" type="number" step="0.001" min="0"
                           wire:model.defer="form.gross_weight"
                           class="form-control @error('form.gross_weight') is-invalid @enderror">
                    @error('form.gross_weight') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="total_packages">Total de bultos</label>
                    <input id="total_packages" type="number" min="1"
                           wire:model.defer="form.total_packages"
                           class="form-control @error('form.total_packages') is-invalid @enderror">
                    @error('form.total_packages') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <h3 class="text-sm font-semibold text-token">Punto de partida</h3>

                    <label class="form-label" for="origin_ubigeo">Ubigeo</label>
                    <input id="origin_ubigeo" type="text"
                           wire:model.defer="form.origin_ubigeo"
                           class="form-control @error('form.origin_ubigeo') is-invalid @enderror"
                           maxlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="Ej: 150101">
                    @error('form.origin_ubigeo') <p class="form-error">{{ $message }}</p> @enderror

                    <label class="form-label" for="origin_address">Dirección</label>
                    <input id="origin_address" type="text"
                           wire:model.defer="form.origin_address"
                           class="form-control @error('form.origin_address') is-invalid @enderror">
                    @error('form.origin_address') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <h3 class="text-sm font-semibold text-token">Punto de llegada</h3>

                    <label class="form-label" for="destination_ubigeo">Ubigeo</label>
                    <input id="destination_ubigeo" type="text"
                           wire:model.defer="form.destination_ubigeo"
                           class="form-control @error('form.destination_ubigeo') is-invalid @enderror"
                           maxlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="Ej: 150102">
                    @error('form.destination_ubigeo') <p class="form-error">{{ $message }}</p> @enderror

                    <label class="form-label" for="destination_address">Dirección</label>
                    <input id="destination_address" type="text"
                           wire:model.defer="form.destination_address"
                           class="form-control @error('form.destination_address') is-invalid @enderror">
                    @error('form.destination_address') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- 5) Vehículo y conductor --}}
        <div class="surface-card rounded-xl border border-token p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-semibold text-token">Vehículo y conductor</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="form-label" for="truck_id">Vehículo</label>
                     <select id="truck_id"
                             wire:model.live="form.truck_id"
                             class="form-control @error('form.truck_id') is-invalid @enderror"
                             @disabled(!empty($form['assignment_id']))>
                         <option value="">Seleccione</option>
                         @foreach($trucks as $truck)
                            @if($truck->status === \App\Enums\Fleet\TruckStatus::Available || (!empty($form['truck_id']) && (string) $form['truck_id'] === (string) $truck->id))
                                 <option value="{{ $truck->id }}" @selected((string) ($form['truck_id'] ?? '') === (string) $truck->id)>
                                     {{ $truck->plate_number }} - {{ $truck->brand }}
                                 </option>
                             @endif
                         @endforeach
                     </select>
                    @error('form.truck_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="driver_id">Chofer</label>
                     <select id="driver_id"
                             wire:model.live="form.driver_id"
                             class="form-control @error('form.driver_id') is-invalid @enderror"
                             @disabled(!empty($form['assignment_id']))>
                         <option value="">Seleccione</option>
                         @foreach($drivers as $driver)
                            @if($driver->status === \App\Enums\Fleet\DriverStatus::Active || (!empty($form['driver_id']) && (string) $form['driver_id'] === (string) $driver->id))
                                 <option value="{{ $driver->id }}" @selected((string) ($form['driver_id'] ?? '') === (string) $driver->id)>
                                     {{ $driver->name }} ({{ $driver->license_number }})
                                 </option>
                             @endif
                         @endforeach
                     </select>
                    @error('form.driver_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-3">
                    @if(!empty($form['assignment_id']))
                        <p class="form-help">
                            Vehículo y conductor se llenan automáticamente según la asignación, pero puedes corregir los campos si falta información.
                        </p>
                    @endif
                </div>

                {{-- Editable (sin readonly) --}}
                <div>
                    <label class="form-label" for="vehicle_plate">Placa</label>
                    <input id="vehicle_plate" type="text"
                           wire:model.defer="form.vehicle_plate"
                           class="form-control @error('form.vehicle_plate') is-invalid @enderror">
                    @error('form.vehicle_plate') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="vehicle_brand">Marca</label>
                    <input id="vehicle_brand" type="text"
                           wire:model.defer="form.vehicle_brand"
                           class="form-control @error('form.vehicle_brand') is-invalid @enderror">
                    @error('form.vehicle_brand') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="mtc_registration_number">Certificado MTC</label>
                    <input id="mtc_registration_number" type="text"
                           wire:model.defer="form.mtc_registration_number"
                           class="form-control @error('form.mtc_registration_number') is-invalid @enderror">
                    @error('form.mtc_registration_number') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="driver_document_type">Tipo doc. conductor</label>
                    <input id="driver_document_type" type="text"
                           wire:model.defer="form.driver_document_type"
                           class="form-control @error('form.driver_document_type') is-invalid @enderror"
                           placeholder="Ej: DNI / CE / PAS">
                    @error('form.driver_document_type') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="driver_document_number">Número doc. conductor</label>
                    <input id="driver_document_number" type="text"
                           wire:model.defer="form.driver_document_number"
                           class="form-control @error('form.driver_document_number') is-invalid @enderror">
                    @error('form.driver_document_number') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="driver_name">Nombre del conductor</label>
                    <input id="driver_name" type="text"
                           wire:model.defer="form.driver_name"
                           class="form-control @error('form.driver_name') is-invalid @enderror">
                    @error('form.driver_name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="driver_license_number">Licencia de conducir</label>
                    <input id="driver_license_number" type="text"
                           wire:model.defer="form.driver_license_number"
                           class="form-control @error('form.driver_license_number') is-invalid @enderror">
                    @error('form.driver_license_number') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- 6) Ítems --}}
        <div class="surface-card rounded-xl border border-token p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-token">Detalle de bienes transportados</h2>
                <button type="button" wire:click="addItem" class="btn btn-secondary btn-sm">Añadir línea</button>
            </div>

            @error('items') <p class="form-error">{{ $message }}</p> @enderror

            <div class="space-y-4">
                @foreach($items as $index => $item)
                    <div class="rounded-lg border border-token p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-token">Ítem #{{ $index + 1 }}</p>
                            <button type="button" class="btn btn-ghost btn-sm" wire:click="removeItem({{ $index }})">Eliminar</button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4 mt-3">
                            <div>
                                <label class="form-label" for="description_{{ $index }}">Descripción</label>
                                <input id="description_{{ $index }}" type="text"
                                       wire:model.defer="items.{{ $index }}.description"
                                       class="form-control @error('items.' . $index . '.description') is-invalid @enderror">
                                @error('items.' . $index . '.description') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="form-label" for="unit_{{ $index }}">Unidad</label>
                                <input id="unit_{{ $index }}" type="text"
                                       wire:model.defer="items.{{ $index }}.unit_of_measure"
                                       class="form-control @error('items.' . $index . '.unit_of_measure') is-invalid @enderror">
                                @error('items.' . $index . '.unit_of_measure') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="form-label" for="qty_{{ $index }}">Cantidad</label>
                                <input id="qty_{{ $index }}" type="number" step="0.001" min="0.001"
                                       wire:model.defer="items.{{ $index }}.quantity"
                                       class="form-control @error('items.' . $index . '.quantity') is-invalid @enderror">
                                @error('items.' . $index . '.quantity') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="form-label" for="weight_{{ $index }}">Peso</label>
                                <input id="weight_{{ $index }}" type="number" step="0.001" min="0"
                                       wire:model.defer="items.{{ $index }}.weight"
                                       class="form-control @error('items.' . $index . '.weight') is-invalid @enderror">
                                @error('items.' . $index . '.weight') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading wire:target="save" class="animate-spin">⏳</span>
                Guardar guía
            </button>
        </div>
    </form>
</div>
