<div>
    <div class="max-w-5xl mx-auto py-10 sm:px-6 lg:px-8 font-sans text-gray-900">
        
        <!-- Step Indicators (Progress Bar) -->
        <div class="mb-10">
            <div class="relative">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-between">
                    @php
                        $steps = [
                            1 => 'Datos Generales',
                            2 => 'Actores',
                            3 => 'Traslado',
                            4 => 'Recursos',
                            5 => 'Items',
                            6 => 'Confirmar'
                        ];
                    @endphp

                    @foreach($steps as $step => $label)
                        <div class="flex flex-col items-center">
                            @if($currentStep > $step)
                                <!-- Completed Step -->
                                <div class="bg-indigo-600 text-white rounded-full h-8 w-8 flex items-center justify-center shadow">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            @elseif($currentStep === $step)
                                <!-- Current Step -->
                                <div class="bg-indigo-600 text-white rounded-full h-8 w-8 flex items-center justify-center ring-4 ring-indigo-100 shadow font-bold">
                                    {{ $step }}
                                </div>
                            @else
                                <!-- Pending Step -->
                                <div class="bg-white border-2 border-gray-300 text-gray-400 rounded-full h-8 w-8 flex items-center justify-center font-bold">
                                    {{ $step }}
                                </div>
                            @endif
                            <span class="mt-2 text-xs font-medium {{ $currentStep >= $step ? 'text-indigo-700' : 'text-gray-500' }}">
                                {{ $label }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <form wire:submit.prevent="save" class="bg-white shadow-lg sm:rounded-xl overflow-hidden border border-gray-200">
            <div class="p-8">
                
                <!-- Step 1: General (Header) -->
                @if($currentStep === 1)
                    <div class="space-y-8 animate-fade-in-up">
                        <div class="border-b border-gray-200 pb-4">
                            <h3 class="text-xl font-semibold text-gray-900">Datos Generales</h3>
                            <p class="mt-1 text-sm text-gray-500">Información base del documento electrónico.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                            <!-- Readonly Series/Correlative -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Serie</label>
                                <input type="text" wire:model="form.series" disabled class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-lg shadow-sm sm:text-sm text-gray-500 cursor-not-allowed">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Correlativo</label>
                                <input type="text" wire:model="form.correlative" disabled class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-lg shadow-sm sm:text-sm text-gray-500 cursor-not-allowed">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Fecha Emisión</label>
                                <input type="text" value="{{ now()->format('d/m/Y') }}" disabled class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-lg shadow-sm sm:text-sm text-gray-500 cursor-not-allowed">
                            </div>

                            <!-- Readonly Company Info -->
                            <div class="sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">RUC Empresa</label>
                                <input type="text" wire:model="form.company_ruc" disabled class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-lg shadow-sm sm:text-sm text-gray-500 cursor-not-allowed">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Razón Social Empresa</label>
                                <input type="text" wire:model="form.company_name" disabled class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-lg shadow-sm sm:text-sm text-gray-500 cursor-not-allowed">
                            </div>
                            

                        </div>
                    </div>
                @endif

                <!-- Step 2: Stakeholders -->
                @if($currentStep === 2)
                    <div class="space-y-8 animate-fade-in-up">
                        <div class="border-b border-gray-200 pb-4">
                            <h3 class="text-xl font-semibold text-gray-900">Actores del Traslado</h3>
                            <p class="mt-1 text-sm text-gray-500">¿Quién envía y quién recibe la carga?</p>
                        </div>
                        
                        <!-- Remitente Card -->
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h4 class="text-md font-semibold text-indigo-700 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                                Remitente (Cliente)
                            </h4>
                            <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Tipo Doc.</label>
                                    <select wire:model="form.remitente_document_type" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @foreach($documentTypeOptions as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Nro Documento</label>
                                    <input type="text" wire:model="form.remitente_document_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div class="sm:col-span-6 md:col-span-2"> <!-- Adjusted Logic: Name usually takes full width if needed -->
                                    <label class="block text-sm font-medium text-gray-700">Razón Social</label>
                                    <input type="text" wire:model="form.remitente_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Destinatario Card -->
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h4 class="text-md font-semibold text-indigo-700 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Destinatario (Llegada)
                            </h4>
                            <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Tipo Doc.</label>
                                    <select wire:model="form.destinatario_document_type" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @foreach($documentTypeOptions as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Nro Documento</label>
                                    <input type="text" wire:model="form.destinatario_document_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div class="sm:col-span-6 md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Razón Social</label>
                                    <input type="text" wire:model="form.destinatario_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Pagador del Flete Card -->
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h4 class="text-md font-semibold text-indigo-700 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Pagador del Flete (Opcional)
                            </h4>
                            <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">RUC</label>
                                    <input type="text" wire:model="form.payer_ruc" placeholder="Opcional - Si es distinto al Remitente" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div class="sm:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700">Razón Social</label>
                                    <input type="text" wire:model="form.payer_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div class="sm:col-span-6 text-xs text-gray-500">
                                    * Si se deja vacío, se asumirá que el <strong>Remitente</strong> es quien paga el flete.
                                </div>
                            </div>
                        </div>

                        <!-- Documentos Relacionados -->
                        @if($type === \App\Models\TransportGuide::TYPE_TRANSPORTISTA)
                        <div class="pt-4 border-t border-gray-200">
                             <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700">Guía Remitente Asociada (Serie-Num)</label>
                                    <input type="text" wire:model="form.related_sender_guide_number" placeholder="Ej: T001-450" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>

                            </div>
                        </div>
                        @endif
                    </div>
                @endif
                
                <!-- Step 3: Shipment -->
                @if($currentStep === 3)
                    <div class="space-y-8 animate-fade-in-up">
                        <div class="border-b border-gray-200 pb-4">
                            <h3 class="text-xl font-semibold text-gray-900">Datos del Traslado</h3>
                            <p class="mt-1 text-sm text-gray-500">Logística, fechas y direcciones.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                            <!-- Motivo y Modalidad -->
                            <div class="sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Motivo</label>
                                <select wire:model="form.transfer_reason_code" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @foreach($transferReasonOptions as $code => $label)
                                        <option value="{{ $code }}">{{ $code }} - {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                             <div class="sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Modalidad</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <select wire:model="form.transport_mode_code" disabled class="block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md sm:text-sm text-gray-500 cursor-not-allowed">
                                        @foreach($transportModeOptions as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                         <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Fechas -->
                            <div class="sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Inicio Traslado</label>
                                <input type="date" wire:model="form.start_transport_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>


                            <!-- Pesos -->
                            <div class="sm:col-span-3">
                                <div class="flex justify-between">
                                    <label class="block text-sm font-medium text-gray-700">Peso Bruto Total (KGM)</label>
                                </div>
                                <input type="number" step="0.001" wire:model="form.gross_weight" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-mono">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Total Bultos (Paquetes)</label>
                                <input type="number" step="1" wire:model="form.total_packages" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-mono">
                            </div>

                             <!-- Direcciones con Selector Ubigeo -->
                             <div class="sm:col-span-6 mt-4">
                                <h4 class="text-sm font-semibold text-indigo-700 mb-3 border-b border-indigo-100 pb-1">Direcciones</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Punto de Partida -->
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <h5 class="font-bold text-xs uppercase text-gray-500 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            Punto de Partida
                                        </h5>
                                        
                                        <div class="space-y-3">
                                            <!-- Selectores Ubigeo -->
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                <select wire:model.live="originDepartment" class="block w-full py-1.5 px-2 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs">
                                                    <option value="">Dep.</option>
                                                    @foreach($departments as $dep)
                                                        <option value="{{ $dep }}">{{ $dep }}</option>
                                                    @endforeach
                                                </select>
                                                <select wire:model.live="originProvince" class="block w-full py-1.5 px-2 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs" {{ empty($originProvinces) ? 'disabled' : '' }}>
                                                    <option value="">Prov.</option>
                                                    @foreach($originProvinces as $prov)
                                                        <option value="{{ $prov }}">{{ $prov }}</option>
                                                    @endforeach
                                                </select>
                                                <select wire:model.live="originDistrict" class="block w-full py-1.5 px-2 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs" {{ empty($originDistricts) ? 'disabled' : '' }}>
                                                    <option value="">Dist.</option>
                                                    @foreach($originDistricts as $dist)
                                                        <option value="{{ $dist['code'] }}">{{ $dist['district'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <!-- Direccion Texto -->
                                            <div>
                                                <input type="text" wire:model="form.origin_address" placeholder="Dirección exacta (Calle, Nro, Urb)..." class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                @if($form['origin_ubigeo'])
                                                    <p class="mt-1 text-xs text-green-600">Ubigeo: {{ $form['origin_ubigeo'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Punto de Llegada -->
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <h5 class="font-bold text-xs uppercase text-gray-500 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-8a2 2 0 012-2h14a2 2 0 012 2v8"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7"></path></svg>
                                            Punto de Llegada
                                        </h5>
                                        
                                        <div class="space-y-3">
                                            <!-- Selectores Ubigeo -->
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                <select wire:model.live="destinationDepartment" class="block w-full py-1.5 px-2 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs">
                                                    <option value="">Dep.</option>
                                                    @foreach($departments as $dep)
                                                        <option value="{{ $dep }}">{{ $dep }}</option>
                                                    @endforeach
                                                </select>
                                                <select wire:model.live="destinationProvince" class="block w-full py-1.5 px-2 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs" {{ empty($destinationProvinces) ? 'disabled' : '' }}>
                                                    <option value="">Prov.</option>
                                                    @foreach($destinationProvinces as $prov)
                                                        <option value="{{ $prov }}">{{ $prov }}</option>
                                                    @endforeach
                                                </select>
                                                <select wire:model.live="destinationDistrict" class="block w-full py-1.5 px-2 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs" {{ empty($destinationDistricts) ? 'disabled' : '' }}>
                                                    <option value="">Dist.</option>
                                                    @foreach($destinationDistricts as $dist)
                                                        <option value="{{ $dist['code'] }}">{{ $dist['district'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <!-- Direccion Texto -->
                                            <div>
                                                <input type="text" wire:model="form.destination_address" placeholder="Dirección exacta (Calle, Nro, Urb)..." class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                 @if($form['destination_ubigeo'])
                                                    <p class="mt-1 text-xs text-green-600">Ubigeo: {{ $form['destination_ubigeo'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Step 4: Resources -->
                @if($currentStep === 4)
                    <div class="space-y-8 animate-fade-in-up">
                        <div class="border-b border-gray-200 pb-4">
                            <h3 class="text-xl font-semibold text-gray-900">Recursos de Transporte</h3>
                            <p class="mt-1 text-sm text-gray-500">Unidad de transporte y conductor asignado.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-y-8 gap-x-6 sm:grid-cols-6">
                            <!-- Vehiculo Card -->
                            <div class="sm:col-span-6 md:col-span-3 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <label class="block text-sm font-medium text-gray-900 mb-2">Vehículo / Tracto (Principal) <span class="text-red-500">*</span></label>
                                <select wire:model.live="form.truck_id" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($primaryTrucks as $truck)
                                        <option value="{{ $truck->id }}">{{ $truck->plate_number }} {{ $truck->brand ? '- '.$truck->brand : '' }}</option>
                                    @endforeach
                                </select>
                                
                                @if($form['vehicle_plate'])
                                    <div class="mt-4 p-3 bg-white rounded border border-gray-200 shadow-sm">
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Placa Principal</p>
                                        <p class="text-lg font-mono font-bold text-indigo-600">{{ $form['vehicle_plate'] }}</p>
                                        @if($form['vehicle_brand'])
                                            <p class="text-sm text-gray-600">{{ $form['vehicle_brand'] }}</p>
                                        @endif
                                        <div class="mt-2 pt-2 border-t border-gray-100">
                                             <label class="block text-xs font-medium text-gray-500">MTC (TUC)</label>
                                             <input type="text" wire:model="form.mtc_registration_number" readonly class="mt-1 block w-full bg-gray-50 border-gray-200 rounded text-xs text-gray-600">
                                        </div>
                                    </div>

                                    @if(!empty($form['special_auth_issuer']) || !empty($form['special_auth_number']))
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Autorización Especial (Principal)</h4>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Entidad Emisora</label>
                                                <input type="text" wire:model="form.special_auth_issuer" readonly class="mt-1 block w-full bg-gray-50 border-gray-200 rounded-md shadow-sm sm:text-sm text-gray-600 cursor-not-allowed">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">N° Autorización</label>
                                                <input type="text" wire:model="form.special_auth_number" readonly class="mt-1 block w-full bg-gray-50 border-gray-200 rounded-md shadow-sm sm:text-sm text-gray-600 cursor-not-allowed">
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endif

                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <label class="block text-sm font-medium text-gray-900 mb-2">Remolque / Secundario (Opcional)</label>
                                    <select wire:model.live="form.secondary_truck_id" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">-- Sin Remolque --</option>
                                        @foreach($secondaryTrucks as $truck)
                                            <option value="{{ $truck->id }}">{{ $truck->plate_number }} {{ $truck->brand ? '- '.$truck->brand : '' }}</option>
                                        @endforeach
                                    </select>
                                    
                                    @if($form['secondary_vehicle_plate'])
                                        <div class="mt-4 p-3 bg-white rounded border border-gray-200 shadow-sm">
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">Placa Secundaria</p>
                                            <p class="text-lg font-mono font-bold text-indigo-600">{{ $form['secondary_vehicle_plate'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Conductor Card -->
                            <div class="sm:col-span-6 md:col-span-3 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <label class="block text-sm font-medium text-gray-900 mb-2">Conductor</label>
                                <select wire:model.live="form.driver_id" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                    @endforeach
                                </select>
                                
                                 @if($form['driver_name'])
                                    <div class="mt-4 p-3 bg-white rounded border border-gray-200 shadow-sm">
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Conductor Asignado</p>
                                        <p class="text-md font-bold text-gray-900">{{ $form['driver_name'] }}</p>
                                        <p class="text-sm text-gray-600">Licencia: <span class="font-mono text-indigo-600">{{ $form['driver_license_number'] }}</span></p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Step 5: Items -->
                @if($currentStep === 5)
                     <div class="space-y-6 animate-fade-in-up">
                        <div class="border-b border-gray-200 pb-4 flex justify-between items-center">
                             <div>
                                <h3 class="text-xl font-semibold text-gray-900">Bienes a Transportar</h3>
                                <p class="mt-1 text-sm text-gray-500">Detalle de la carga.</p>
                            </div>
                            <button type="button" wire:click="addItem" class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Agregar Item
                            </button>
                        </div>
                        
                        <div class="space-y-3">
                            @if(count($items) === 0)
                                <div class="text-center py-12 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg">
                                    <p class="text-gray-500">No hay bienes agregados.</p>
                                </div>
                            @endif

                            @foreach($items as $index => $item)
                                <div class="flex items-start p-4 bg-gray-50 rounded-lg border border-gray-200 gap-4 transition-all hover:shadow-md">
                                    <div class="flex-grow grid grid-cols-12 gap-4">
                                        <div class="col-span-12 md:col-span-8">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Descripción</label>
                                            <input type="text" wire:model="items.{{ $index }}.description" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                        <div class="col-span-6 md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Cant.</label>
                                            <input type="number" step="0.01" wire:model="items.{{ $index }}.quantity" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center">
                                        </div>
                                        <div class="col-span-6 md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">UND</label>
                                            <select wire:model="items.{{ $index }}.unit_of_measure" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                @foreach($unitOptions as $code => $label)
                                                    <option value="{{ $code }}">{{ $code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 pt-6">
                                        <button type="button" wire:click="removeItem({{ $index }})" class="text-gray-400 hover:text-red-600 transition-colors p-1" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Observaciones -->
                        <div class="mt-6">
                             <label class="block text-sm font-medium text-gray-700">Observaciones (Opcional)</label>
                             <textarea wire:model="form.observations" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Ingrese información adicional para la guía..."></textarea>
                        </div>
                    </div>
                @endif
                
                <!-- Step 6: Preview -->
                @if($currentStep === 6)
                    @include('livewire.billing.transport-guides.partials.preview')
                @endif
            </div>

             <!-- Navigation Footer -->
             <div class="bg-gray-50 px-8 py-5 border-t border-gray-200 flex justify-between items-center">
                <button type="button" wire:click="previousStep" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all" 
                    {{ $currentStep === 1 ? 'disabled' : '' }}>
                    <svg class="mr-2 -ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Anterior
                </button>
                
                @if($currentStep < $totalSteps)
                    <button type="button" wire:click="nextStep" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                        Siguiente
                        <svg class="ml-2 -mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                @else
                    <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all transform hover:scale-105">
                        <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Emitir Guía Electrónica
                    </button>
                @endif
            </div>
            
             <!-- Error Summary (Toast-like inline) -->
             @if ($errors->any())
                <div class="bg-red-50 p-4 border-t border-red-100">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Atención:</h3>
                            <div class="mt-1 text-sm text-red-700">
                                <ul class="list-disc pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>
