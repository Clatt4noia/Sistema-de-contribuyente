<div class="animate-fade-in-up">
    <div class="bg-white p-8 border border-gray-300 shadow-xl max-w-4xl mx-auto font-sans text-xs text-gray-800">
            @php
            $company = \App\Models\Company::first() ?? new \App\Models\Company([
                'razon_social' => config('greenter.company.razon_social', 'MI EMPRESA S.A.C.'),
                'ruc' => config('greenter.company.ruc', '20000000001'),
                'address' => config('greenter.company.address.direccion', 'AV. DEMO 123 - LIMA'),
                'mtc' => config('greenter.company.mtc', 'MTC-000000')
            ]);
        @endphp

        <!-- Header -->
        <div class="grid grid-cols-12 gap-4 mb-6">
            <div class="col-span-2 flex items-start justify-center">
                <div class="bg-gray-100 border border-gray-300 w-full aspect-square flex items-center justify-center text-gray-400">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4h2v-4zM6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <div class="col-span-6 pr-4">
                    <h1 class="text-xl font-bold uppercase mb-2">{{ $company->razon_social }}</h1>
                    <p class="mb-1"><span class="font-bold">Dirección:</span> {{ $company->address }}</p>
                    <p class="mb-1"><span class="font-bold">Número de Registro MTC:</span> {{ $company->mtc }}</p>
                    <p><span class="font-bold">Fecha y hora de emisión:</span> {{ \Carbon\Carbon::parse($form['issue_date'] . ' ' . $form['issue_time'])->format('d/m/Y h:i A') }}</p>
            </div>
            <div class="col-span-4 border-2 border-gray-800 p-2 text-center flex flex-col justify-center">
                    <h2 class="font-bold text-lg mb-1">R.U.C. N° {{ $company->ruc }}</h2>
                    <h2 class="font-bold text-sm bg-gray-100 py-2 border-y border-gray-200 mb-1">GUÍA DE REMISIÓN ELECTRÓNICA TRANSPORTISTA</h2>
                    <h2 class="font-bold text-lg">N° {{ $form['series'] }} - {{ str_pad($form['correlative'], 8, '0', STR_PAD_LEFT) }}</h2>
            </div>
        </div>

        <!-- Transport Dates & Points -->
        <div class="grid grid-cols-2 gap-8 mb-6">
            <div>
                <p class="mb-2"><span class="font-bold">Fecha de inicio de Traslado:</span> {{ \Carbon\Carbon::parse($form['start_transport_date'])->format('d/m/Y') }}</p>
            </div>
            <div class="space-y-2">
                <div>
                    <span class="font-bold block">Punto de Partida:</span>
                    <p>{{ $form['origin_address'] }} - {{ $form['origin_ubigeo'] }}</p>
                </div>
                <div>
                    <span class="font-bold block">Punto de Llegada:</span>
                    <p>{{ $form['destination_address'] }} - {{ $form['destination_ubigeo'] }}</p>
                </div>
            </div>
        </div>

        <!-- Stakeholders -->
        <div class="space-y-2 mb-6 border-t border-gray-200 pt-4">
            <p><span class="font-bold">Datos del remitente:</span> {{ $form['remitente_name'] }} - {{ $form['remitente_document_type'] }}: {{ $form['remitente_document_number'] }}</p>
            <p><span class="font-bold">Datos del destinatario:</span> {{ $form['destinatario_name'] }} - {{ $form['destinatario_document_type'] }}: {{ $form['destinatario_document_number'] }}</p>
        </div>

        <!-- Related Documents -->
        @if(!empty($form['related_sender_guide_number']) || !empty($form['transfer_reason_description']))
            <div class="mb-6 space-y-1">
                <p class="font-bold mb-1">Datos del traslado:</p>
                @if(!empty($form['related_sender_guide_number']))
                    <p>Guía de Remisión Remitente N°: {{ $form['related_sender_guide_number'] }}</p>
                @endif
                <p>Motivo: {{ $transferReasonOptions[$form['transfer_reason_code']] ?? $form['transfer_reason_code'] }}</p>
            </div>
        @endif

        <!-- Items -->
        <div class="mb-6">
            <span class="font-bold block mb-2">Bienes por Transportar:</span>
            <table class="w-full border-collapse border border-gray-300 mb-2">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-2 py-1 text-left w-12">#</th>
                        <th class="border border-gray-300 px-2 py-1 text-left">Descripción</th>
                        <th class="border border-gray-300 px-2 py-1 text-right w-20">Cant.</th>
                        <th class="border border-gray-300 px-2 py-1 text-center w-20">UND</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $i => $item)
                        <tr>
                            <td class="border border-gray-300 px-2 py-1">{{ $i + 1 }}</td>
                            <td class="border border-gray-300 px-2 py-1">{{ $item['description'] }}</td>
                            <td class="border border-gray-300 px-2 py-1 text-right">{{ $item['quantity'] }}</td>
                            <td class="border border-gray-300 px-2 py-1 text-center">{{ $item['unit_of_measure'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="flex justify-end gap-8 font-bold">
                <p>Unidad de Medida del Peso Bruto: {{ $form['gross_weight_unit'] }}</p>
                <p>Peso Bruto total de la carga: {{ number_format($form['gross_weight'], 3) }}</p>
            </div>
        </div>

        <!-- Vehicle & Driver -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 pt-4 border-t border-gray-200">
            <div>
                <p class="font-bold mb-2">Datos de los vehículos:</p>
                <div class="pl-2 border-l-2 border-gray-300">
                    <p><span class="font-bold">Principal:</span> Placa: {{ $form['vehicle_plate'] }}</p>
                    @if(!empty($form['mtc_registration_number']))
                        <p>N° TUC: {{ $form['mtc_registration_number'] }}</p>
                    @endif
                    @if(!empty($form['special_auth_number']))
                        <p>Auth. Especial: {{ $form['special_auth_number'] }} ({{ $form['special_auth_issuer'] }})</p>
                    @endif
                </div>
            </div>
            <div>
                <p class="font-bold mb-2">Datos de los conductores:</p>
                <div class="pl-2 border-l-2 border-gray-300">
                    <p><span class="font-bold">Principal:</span> {{ $form['driver_name'] }} {{ $form['driver_last_name'] }}</p>
                    <p>Doc: {{ $form['driver_document_number'] }}</p>
                    <p>Licencia: {{ $form['driver_license_number'] }}</p>
                </div>
            </div>
        </div>

        <!-- Observaciones -->
        @if(!empty($form['observations']))
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p><span class="font-bold">Observaciones:</span> {{ $form['observations'] }}</p>
            </div>
        @endif
        
        <div class="mt-8 text-center text-gray-500 text-[10px]">
            Representación impresa de la Guía de Remisión Electrónica generada en entorno de pruebas.
        </div>
    </div>
</div>
