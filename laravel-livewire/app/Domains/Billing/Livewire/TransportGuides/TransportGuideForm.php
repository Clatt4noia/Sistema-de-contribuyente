<?php

namespace App\Domains\Billing\Livewire\TransportGuides;

use App\Models\Assignment;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\TransportGuide;
use App\Models\TransportGuideItem;
use App\Models\Truck;
use App\Models\Ubigeo;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TransportGuideForm extends Component
{
    use AuthorizesRequests;

    protected const TRANSFER_REASONS = [
        '01' => 'Venta',
        '02' => 'Compra',
        '04' => 'Traslado entre establecimientos de la misma empresa',
        '08' => 'Importacion',
        '09' => 'Exportacion',
        '13' => 'Otros',
    ];

    protected const TRANSPORT_MODES = [
        '01' => 'Publico',
        '02' => 'Privado',
    ];
    
    protected const UNIT_TYPES = [
        'KGM' => 'Kilogramos',
        'NIU' => 'Unidades',
        'ZZ' => 'Servicios',
    ];

    protected const DOCUMENT_TYPES = [
        '1' => 'DNI',
        '6' => 'RUC',
        '4' => 'Carnet de Extranjeria',
        '7' => 'Pasaporte',
        '0' => 'Sin Documento',
    ];

    public TransportGuide $transportGuide;
    public string $type = TransportGuide::TYPE_TRANSPORTISTA;
    public bool $isEdit = false;

    // Wizard Steps
    public int $currentStep = 1;
    public int $totalSteps = 6; // 1:Header, 2:Stakeholders, 3:Shipment, 4:Resources, 5:Items, 6:Preview

    public array $form = [];
    public array $items = [];
    
    // Catalogs
    public array $transferReasonOptions = [];
    public array $transportModeOptions = [];
    public array $unitOptions = [];
    public array $documentTypeOptions = [];

    // Resources from DB
    public $clients;
    public $trucks;
    public $drivers;

    // Ubigeo Selectors
    public $departments = [];
    
    public $originProvinces = [];
    public $originDistricts = [];
    public $originDepartment;
    public $originProvince;
    public $originDistrict;

    public $destinationProvinces = [];
    public $destinationDistricts = [];
    public $destinationDepartment;
    public $destinationProvince;
    public $destinationDistrict;

    protected function messages(): array
    {
        return [
            'form.origin_ubigeo.required' => 'El ubigeo de partida es obligatorio.',
            'form.origin_ubigeo.size' => 'El ubigeo de partida debe tener 6 digitos.',
            'form.destination_ubigeo.required' => 'El ubigeo de llegada es obligatorio.',
            'form.destination_ubigeo.size' => 'El ubigeo de llegada debe tener 6 digitos.',
            'form.transfer_reason_code.required' => 'Seleccione un motivo de traslado.',
            'form.transport_mode_code.required' => 'Seleccione una modalidad de transporte.',
            'form.truck_id.required_if' => 'Debe seleccionar un vehiculo.',
            'form.driver_id.required_if' => 'Debe seleccionar un conductor.',
        ];
    }

    public function mount(?TransportGuide $transportGuide = null, string $type = TransportGuide::TYPE_TRANSPORTISTA): void
    {
        $this->type = $this->normalizeType($transportGuide?->type ?: $type);
        $this->transferReasonOptions = self::TRANSFER_REASONS;
        $this->transportModeOptions = self::TRANSPORT_MODES;
        $this->unitOptions = self::UNIT_TYPES;
        $this->documentTypeOptions = self::DOCUMENT_TYPES;

        if ($transportGuide && $transportGuide->exists) {
            $this->transportGuide = $transportGuide->load('items');
            $this->authorize('view', $this->transportGuide);
            $this->isEdit = true;
            
            if ($this->transportGuide->sunat_status !== TransportGuide::STATUS_DRAFT) {
                abort(403, 'Solo se pueden editar guías en borrador.');
            }

            if ($this->transportGuide->document_type_code !== $this->documentTypeForType()) {
                $this->transportGuide->document_type_code = $this->documentTypeForType();
            }

            if (!preg_match($this->seriesPattern(), (string) $this->transportGuide->series)) {
                $this->transportGuide->series = $this->defaultSeriesForType();
                $this->transportGuide->correlative = $this->nextCorrelative($this->transportGuide->series);
            }
        } else {
            $series = $this->defaultSeriesForType();
            $this->transportGuide = new TransportGuide([
                'type' => $this->type,
                'sunat_status' => TransportGuide::STATUS_DRAFT,
                'document_type_code' => $this->documentTypeForType(),
                'series' => $series,
                'issue_date' => now()->toDateString(),
                'issue_time' => now()->format('H:i:s'),
                'start_transport_date' => now()->toDateString(),
                'gross_weight_unit' => 'KGM',
                'transfer_reason_code' => '01',
                'transport_mode_code' => '01', // Default Publico (cambiar segun logica)
            ]);
            $this->authorize('create', TransportGuide::class);
            $this->transportGuide->correlative = $this->nextCorrelative($series);
        }

        // Config Data
        $company = \App\Models\Company::first();
        $companyRuc = $company ? $company->ruc : Config::get('greenter.company.ruc');
        $companyName = $company ? $company->razon_social : Config::get('app.name');

        // Initial Data Logic
        if ($this->type === TransportGuide::TYPE_REMITENTE) {
            $remitenteRuc = $companyRuc;
            $remitenteName = $companyName;
            $remitenteDocType = '6';
            $remitenteDocNumber = $companyRuc;
            $destinatarioDocType = '6'; 
        } else {
            // GRE-T: Empresa es Transportista.
            $remitenteRuc = '';
            $remitenteName = '';
            $remitenteDocType = '6';
            $remitenteDocNumber = '';
        }

        $this->form = [
            'series' => $this->transportGuide->series,
            'correlative' => $this->transportGuide->correlative,
            'issue_date' => $this->transportGuide->issue_date instanceof \DateTimeInterface 
                ? $this->transportGuide->issue_date->format('Y-m-d') 
                : $this->transportGuide->issue_date,
            'issue_time' => $this->transportGuide->issue_time,
            'document_type_code' => $this->transportGuide->document_type_code ?? $this->documentTypeForType(),
            'observations' => $this->transportGuide->observations,
            
            // Step 1: Company Info (Visual)
            'company_ruc' => $companyRuc,
            'company_name' => $companyName,

            // Step 2: Stakeholders
            'remitente_document_type' => $this->transportGuide->remitente_document_type ?? $remitenteDocType,
            'remitente_document_number' => $this->transportGuide->remitente_document_number ?? $remitenteDocNumber,
            'remitente_name' => $this->transportGuide->remitente_name ?? $remitenteName,
            
            'destinatario_document_type' => $this->transportGuide->destinatario_document_type ?? '6',
            'destinatario_document_number' => $this->transportGuide->destinatario_document_number,
            'destinatario_name' => $this->transportGuide->destinatario_name,

            // Payer (Optional)
            'payer_ruc' => $this->transportGuide->payer_ruc,
            'payer_name' => $this->transportGuide->payer_name,

            // Docs relacionados
            'related_sender_guide_number' => $this->transportGuide->related_sender_guide_number,
            'additional_document_reference' => $this->transportGuide->additional_document_reference,

            // Step 3: Shipment
            'transfer_reason_code' => $this->transportGuide->transfer_reason_code ?? '01',
            'transfer_reason_description' => $this->transportGuide->transfer_reason_description,
            'transport_mode_code' => $this->transportGuide->transport_mode_code ?? '02', // Default Privado para GRE-T
            'start_transport_date' => optional($this->transportGuide->start_transport_date)->format('Y-m-d') ?: now()->toDateString(),
            'delivery_date' => optional($this->transportGuide->delivery_date)->format('Y-m-d'),
            'gross_weight' => $this->transportGuide->gross_weight ?? 0,
            'gross_weight_unit' => $this->transportGuide->gross_weight_unit ?? 'KGM',
            'total_packages' => $this->transportGuide->total_packages ?? 1,
            'origin_ubigeo' => $this->transportGuide->origin_ubigeo,
            'origin_address' => $this->transportGuide->origin_address,
            'destination_ubigeo' => $this->transportGuide->destination_ubigeo,
            'destination_address' => $this->transportGuide->destination_address,
            
            // Step 4: Resources
            'truck_id' => $this->transportGuide->truck_id,
            'vehicle_plate' => $this->transportGuide->vehicle_plate,
            'vehicle_brand' => $this->transportGuide->vehicle_brand,
            'mtc_registration_number' => $this->transportGuide->mtc_registration_number,
            'special_auth_issuer' => $this->transportGuide->special_auth_issuer,
            'special_auth_number' => $this->transportGuide->special_auth_number,
            'driver_id' => $this->transportGuide->driver_id,
            'driver_document_type' => $this->transportGuide->driver_document_type ?? '1',
            'driver_document_number' => $this->transportGuide->driver_document_number,
            'driver_name' => $this->transportGuide->driver_name,
            'driver_last_name' => $this->transportGuide->driver_last_name,
            'driver_license_number' => $this->transportGuide->driver_license_number,
        ];

        $this->items = $this->transportGuide->items->map(function ($item) {
            return [
                'description' => $item->description,
                'unit_of_measure' => $item->unit_of_measure,
                'quantity' => (float) $item->quantity,
            ];
        })->values()->toArray();

        if (empty($this->items)) {
            $this->items = [[
                'description' => '',
                'unit_of_measure' => 'NIU',
                'quantity' => 1,
            ]];
        }

        $this->clients = Client::orderBy('business_name')->get(); 
        $this->trucks = Truck::orderBy('plate_number')->get();
        $this->drivers = Driver::orderBy('name')->get();

        // Load Departments (distinc)
        $this->departments = Ubigeo::select('department')->distinct()->orderBy('department')->pluck('department')->toArray();

        // If editing, try to reverse-engineer selections (Optional enhancement for later, generally complex due to data loss if only code is saved)
        // For now start empty or implement if needed.
    }

    public function nextStep(): void
    {
        $this->validateStep($this->currentStep);
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    protected function validateStep(int $step): void
    {
        $rules = [];
        
        switch ($step) {
            case 1: // Header (Readonly mostly)
                $rules = [
                    'form.series' => 'required',
                    'form.correlative' => 'required',
                ];
                break;
            case 2: // Stakeholders
                $rules = [
                    'form.remitente_document_type' => 'required',
                    'form.remitente_document_number' => 'required|min:8|max:20',
                    'form.remitente_name' => 'required',
                    'form.destinatario_document_type' => 'required',
                    'form.destinatario_document_number' => 'required|min:8|max:20',
                    'form.destinatario_name' => 'required',
                    'form.payer_ruc' => 'nullable|min:11|max:11',
                    'form.payer_name' => 'nullable|string|max:100',
                ];
                break;
            case 3: // Shipment
                $rules = [
                    'form.transfer_reason_code' => 'required',
                    'form.transport_mode_code' => 'required',
                    'form.start_transport_date' => 'required|date',
                    'form.gross_weight' => 'required|numeric|min:0.01',
                    'form.origin_ubigeo' => 'required|size:6',
                    'form.origin_address' => 'required',
                    'form.destination_ubigeo' => 'required|size:6',
                    'form.destination_address' => 'required',
                ];
                break;
            case 4: // Resources (Si es GRE-R Privada o GRE-T)
                // Para GRE-T Recursos son obligatorios
                $ifPrivate = Rule::requiredIf($this->form['transport_mode_code'] === '02' || $this->type === TransportGuide::TYPE_TRANSPORTISTA);
                $rules = [
                    'form.truck_id' => $ifPrivate,
                    'form.driver_id' => $ifPrivate,
                    'form.vehicle_plate' => $ifPrivate,
                    'form.driver_document_number' => $ifPrivate,
                    'form.driver_name' => $ifPrivate,
                    'form.special_auth_issuer' => 'nullable|string|max:100',
                    'form.special_auth_number' => 'nullable|string|max:50',
                ];
                break;
            case 5: // Items
                $rules = [
                    'items' => 'required|array|min:1',
                    'items.*.description' => 'required|string',
                    'items.*.quantity' => 'required|numeric|min:0.01',
                    'form.observations' => 'nullable|string|max:500',
                ];
                break;
        }

        $this->validate($rules);
    }
    
    // Updated Logic for Resources Selection
    public function updatedFormTruckId($value): void
    {
        $truck = $this->trucks->find($value);
        if ($truck) {
            $this->form['vehicle_plate'] = $truck->plate_number;
            $this->form['vehicle_brand'] = $truck->brand;
            $this->form['mtc_registration_number'] = $truck->mtc_registration_number;
            $this->form['special_auth_issuer'] = $truck->special_auth_issuer;
            $this->form['special_auth_number'] = $truck->special_auth_number;
        }
    }

    public function updatedFormDriverId($value): void
    {
        $driver = $this->drivers->find($value);
        if ($driver) {
            $this->form['driver_document_type'] = $driver->document_type ?? '1';
            $this->form['driver_document_number'] = $driver->document_number;
            $this->form['driver_name'] = $driver->name;
            $this->form['driver_last_name'] = $driver->last_name;
            $this->form['driver_license_number'] = $driver->license_number;
        }
    }

    // --- Ubigeo Logic Origin ---
    public function updatedOriginDepartment($value)
    {
        $this->originProvinces = Ubigeo::where('department', $value)->select('province')->distinct()->orderBy('province')->pluck('province')->toArray();
        $this->originDistricts = [];
        $this->originProvince = null;
        $this->originDistrict = null;
        $this->form['origin_ubigeo'] = null;
    }

    public function updatedOriginProvince($value)
    {
        $this->originDistricts = Ubigeo::where('department', $this->originDepartment)
            ->where('province', $value)
            ->select('district', 'code')
            ->orderBy('district')
            ->get()
            ->toArray();
        $this->originDistrict = null; 
        $this->form['origin_ubigeo'] = null;
    }

    public function updatedOriginDistrict($value) // Value here will be the CODE if we bind to value=code in Select
    {
        // Actually, if we bind the Select to the CODE, we are good.
        // Assuming the view will use value="{{ $d['code'] }}"
        $this->form['origin_ubigeo'] = $value;
    }

    // --- Ubigeo Logic Destination ---
    public function updatedDestinationDepartment($value)
    {
        $this->destinationProvinces = Ubigeo::where('department', $value)->select('province')->distinct()->orderBy('province')->pluck('province')->toArray();
        $this->destinationDistricts = [];
        $this->destinationProvince = null;
        $this->destinationDistrict = null;
        $this->form['destination_ubigeo'] = null;
    }

    public function updatedDestinationProvince($value)
    {
        $this->destinationDistricts = Ubigeo::where('department', $this->destinationDepartment)
            ->where('province', $value)
            ->select('district', 'code')
            ->orderBy('district')
            ->get()
            ->toArray();
        $this->destinationDistrict = null;
        $this->form['destination_ubigeo'] = null;
    }

    public function updatedDestinationDistrict($value)
    {
        $this->form['destination_ubigeo'] = $value;
    }
    
    // Autocomplete Clients helper - kept simple for now
    // In a real scenario, updatedFormRemitenteDocumentNumber could query API or DB.

    public function addItem(): void
    {
        $this->items[] = [
            'description' => '',
            'unit_of_measure' => 'NIU',
            'quantity' => 1,
        ];
    }

    public function removeItem($index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        $this->validateStep(1);
        $this->validateStep(2);
        $this->validateStep(3);
        $this->validateStep(4);
        $this->validateStep(5);
        
        $this->transportGuide->forceFill([
            'type' => $this->type,
            'series' => $this->form['series'],
            'correlative' => $this->form['correlative'],
            'full_code' => $this->form['series'] . '-' . str_pad((string)$this->form['correlative'], 8, '0', STR_PAD_LEFT),
            'issue_date' => $this->form['issue_date'],
            'issue_time' => $this->form['issue_time'],
            'document_type_code' => $this->form['document_type_code'],
            'observations' => $this->form['observations'] ?? null,
            'client_id' => $this->form['client_id'] ?? null, 
            
            'remitente_document_type' => $this->form['remitente_document_type'],
            'remitente_document_number' => $this->form['remitente_document_number'],
            'remitente_name' => $this->form['remitente_name'],
            'remitente_ruc' => $this->form['remitente_document_number'], 
            
            'destinatario_document_type' => $this->form['destinatario_document_type'],
            'destinatario_document_number' => $this->form['destinatario_document_number'],
            'destinatario_name' => $this->form['destinatario_name'],

            'payer_ruc' => $this->form['payer_ruc'] ?? null,
            'payer_name' => $this->form['payer_name'] ?? null,

            'transportista_ruc' => $this->form['company_ruc'] ?? null,
            'transportista_name' => $this->form['company_name'] ?? null,
            
            'related_sender_guide_number' => $this->form['related_sender_guide_number'] ?? null,
            
            'transfer_reason_code' => $this->form['transfer_reason_code'],
            'transfer_reason_description' => self::TRANSFER_REASONS[$this->form['transfer_reason_code']] ?? '',
            'transport_mode_code' => $this->form['transport_mode_code'],
            'scheduled_transshipment' => $this->form['scheduled_transshipment'] ?? false, 
            'start_transport_date' => $this->form['start_transport_date'],
            'delivery_date' => $this->form['delivery_date'] ?? null,
            'gross_weight' => $this->form['gross_weight'],
            'gross_weight_unit' => $this->form['gross_weight_unit'],
            'total_packages' => $this->form['total_packages'],
            'origin_ubigeo' => $this->form['origin_ubigeo'],
            'origin_address' => $this->form['origin_address'],
            'destination_ubigeo' => $this->form['destination_ubigeo'],
            'destination_address' => $this->form['destination_address'],
            
            'truck_id' => $this->form['truck_id'] ?? null,
            'vehicle_plate' => $this->form['vehicle_plate'] ?? null,
            'vehicle_brand' => $this->form['vehicle_brand'] ?? null,
            'mtc_registration_number' => $this->form['mtc_registration_number'] ?? null,
            'special_auth_issuer' => $this->form['special_auth_issuer'] ?? null,
            'special_auth_number' => $this->form['special_auth_number'] ?? null,
            'driver_id' => $this->form['driver_id'] ?? null,
            'driver_document_type' => $this->form['driver_document_type'] ?? null,
            'driver_document_number' => $this->form['driver_document_number'] ?? null,
            'driver_name' => $this->form['driver_name'] ?? null,
            'driver_last_name' => $this->form['driver_last_name'] ?? null,
            'driver_license_number' => $this->form['driver_license_number'] ?? null,
        ]);
        
        $clientDoc = $this->type === TransportGuide::TYPE_TRANSPORTISTA 
            ? $this->form['remitente_document_number'] 
            : $this->form['destinatario_document_number'];
            
        $client = Client::where('tax_id', $clientDoc)->first(); 
        if ($client) {
            $this->transportGuide->client_id = $client->id;
        }
        // If not found, client_id remains null (Manual Entry), which is now allowed by DB migration.

        $this->transportGuide->save();
        
        $this->transportGuide->items()->delete();
        foreach ($this->items as $item) {
            $this->transportGuide->items()->create($item);
        }
        
        // Issue
        $issuer = app(\App\Domains\Billing\Services\TransportGuideIssuer::class);
        $issuer->issue($this->transportGuide);
        
        $route = $this->type === TransportGuide::TYPE_REMITENTE ? 'billing.remitter-guides.index' : 'billing.transport-guides.index';
        return redirect()->route($route);
    }

    // Helpers
    protected function nextCorrelative(string $series): int
    {
        return (int) (TransportGuide::where('series', $series)->max('correlative') + 1);
    }

    protected function normalizeType(?string $type): string
    {
        return in_array($type, [TransportGuide::TYPE_TRANSPORTISTA, TransportGuide::TYPE_REMITENTE], true)
            ? $type
            : TransportGuide::TYPE_TRANSPORTISTA;
    }

    protected function seriesPattern(): string
    {
        return $this->type === TransportGuide::TYPE_REMITENTE ? '/^T\d{3}$/' : '/^V\d{3}$/';
    }

    protected function defaultSeriesForType(): string
    {
        return $this->type === TransportGuide::TYPE_REMITENTE
            ? TransportGuide::DEFAULT_SERIES_GRE_REMITENTE
            : TransportGuide::DEFAULT_SERIES_GRE_TRANSPORTISTA;
    }

    protected function documentTypeForType(): string
    {
        return $this->type === TransportGuide::TYPE_REMITENTE
            ? TransportGuide::DOCUMENT_TYPE_GRE_REMITENTE
            : TransportGuide::DOCUMENT_TYPE_GRE_TRANSPORTISTA;
    }

    public function render()
    {
        return view('livewire.billing.transport-guides.form');
    }
}
