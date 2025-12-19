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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

// Este formulario debe servir tanto para GRE-T como para GRE-R. Usar $type para decidir serie por defecto (V o T),
// tipo de documento (31 o 09), textos del encabezado y ruta de retorno. Al guardar, siempre persistir el type correcto.
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

    public TransportGuide $transportGuide;
    public string $type = TransportGuide::TYPE_TRANSPORTISTA;
    public bool $isEdit = false;

    public array $form = [];
    public array $items = [];
    public array $transferReasonOptions = [];
    public array $transportModeOptions = [];

    public $clients;
    public $trucks;
    public $drivers;
    public $assignments;
    public $invoices;
    protected ?int $previousInvoiceId = null;
    protected bool $syncingAssignment = false;
    protected bool $syncingInvoice = false;

    protected function messages(): array
    {
        return [
            'form.origin_ubigeo.required' => 'El ubigeo de partida es obligatorio.',
            'form.origin_ubigeo.size' => 'El ubigeo de partida debe tener 6 digitos.',
            'form.origin_ubigeo.regex' => 'El ubigeo de partida debe tener 6 digitos.',
            'form.destination_ubigeo.required' => 'El ubigeo de llegada es obligatorio.',
            'form.destination_ubigeo.size' => 'El ubigeo de llegada debe tener 6 digitos.',
            'form.destination_ubigeo.regex' => 'El ubigeo de llegada debe tener 6 digitos.',
            'form.transfer_reason_code.regex' => 'El motivo de traslado debe ser un codigo de 2 digitos (catalogo 20).',
            'form.transport_mode_code.in' => 'La modalidad de transporte debe ser 01 (publico) o 02 (privado).',
        ];
    }

    public function mount(?TransportGuide $transportGuide = null, string $type = TransportGuide::TYPE_TRANSPORTISTA): void
    {
        $this->type = $this->normalizeType($transportGuide?->type ?: $type);
        $this->transferReasonOptions = self::TRANSFER_REASONS;
        $this->transportModeOptions = self::TRANSPORT_MODES;

        if ($transportGuide && $transportGuide->exists) {
            $this->transportGuide = $transportGuide->load('items');
            $this->authorize('view', $this->transportGuide);
            $this->isEdit = true;
            $this->previousInvoiceId = $this->transportGuide->related_invoice_id;

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
                'transfer_reason_description' => self::TRANSFER_REASONS['01'],
                'transport_mode_code' => '01',
                'scheduled_transshipment' => false,
            ]);
            $this->authorize('create', TransportGuide::class);
            $this->transportGuide->correlative = $this->nextCorrelative($series);
        }

        // Obtener datos de la empresa para remitente y transportista
        $company = \App\Models\Company::first();
        $companyRuc = $company ? $company->ruc : Config::get('billing.sunat.ruc');
        $companyName = $company ? $company->razon_social : Config::get('app.name');

        // Inicializar campos según el tipo de guía
        if ($this->type === TransportGuide::TYPE_REMITENTE) {
            // GRE-R: La empresa es el REMITENTE (dueño de la mercancía)
            $remitenteRuc = $companyRuc;
            $remitenteName = $companyName;
            $remitenteDocType = '6';
            $remitenteDocNumber = $companyRuc;

            // Destinatario se llenará al seleccionar cliente
            $destinatarioDocType = $this->transportGuide->destinatario_document_type ?? '6';
            $destinatarioDocNumber = $this->transportGuide->destinatario_document_number ?? '';
            $destinatarioName = $this->transportGuide->destinatario_name ?? '';
        } else {
            // GRE-T: La empresa es el TRANSPORTISTA, el cliente es el REMITENTE
            // Remitente se llenará al seleccionar cliente
            $remitenteRuc = $this->transportGuide->remitente_ruc ?? '';
            $remitenteName = $this->transportGuide->remitente_name ?? '';
            $remitenteDocType = $this->transportGuide->remitente_document_type ?? '6';
            $remitenteDocNumber = $this->transportGuide->remitente_document_number ?? '';

            // Destinatario también se llenará (puede ser el mismo que remitente)
            $destinatarioDocType = $this->transportGuide->destinatario_document_type ?? '6';
            $destinatarioDocNumber = $this->transportGuide->destinatario_document_number ?? '';
            $destinatarioName = $this->transportGuide->destinatario_name ?? '';
        }

        $this->form = [
            'series' => $this->transportGuide->series,
            'correlative' => $this->transportGuide->correlative,
            'document_type_code' => $this->transportGuide->document_type_code ?? $this->documentTypeForType(),

            'observations' => $this->transportGuide->observations,
            'client_id' => $this->transportGuide->client_id,

            // REMITENTE (varía según tipo)
            'remitente_document_type' => $remitenteDocType,
            'remitente_document_number' => $remitenteDocNumber,
            'remitente_ruc' => $remitenteRuc,
            'remitente_name' => $remitenteName,

            // DESTINATARIO (se llena al seleccionar cliente)
            'destinatario_document_type' => $destinatarioDocType,
            'destinatario_document_number' => $destinatarioDocNumber,
            'destinatario_name' => $destinatarioName,

            // TRANSPORTISTA (siempre es la empresa)
            'transportista_ruc' => $companyRuc,
            'transportista_name' => $companyName,

            'order_id' => $this->transportGuide->order_id,

            // >>> CAMBIO: assignment_id ES LA FUENTE DE VERDAD <<<
            'assignment_id' => $this->transportGuide->assignment_id,

            // Estos se seguirán guardando como snapshot, pero NO se deben elegir manualmente
            'truck_id' => $this->transportGuide->truck_id,
            'driver_id' => $this->transportGuide->driver_id,

            'vehicle_plate' => $this->transportGuide->vehicle_plate,
            'vehicle_brand' => $this->transportGuide->vehicle_brand,
            'mtc_registration_number' => $this->transportGuide->mtc_registration_number,
            'driver_document_number' => $this->transportGuide->driver_document_number,
            'driver_document_type' => $this->transportGuide->driver_document_type,
            'driver_name' => $this->transportGuide->driver_name,
            'driver_license_number' => $this->transportGuide->driver_license_number,

            'transfer_reason_code' => $this->transportGuide->transfer_reason_code,
            'transfer_reason_description' => $this->transportGuide->transfer_reason_description
                ?: ($this->transferReasonOptions[$this->transportGuide->transfer_reason_code ?? ''] ?? null),
            'transport_mode_code' => $this->transportGuide->transport_mode_code,
            'scheduled_transshipment' => (bool) $this->transportGuide->scheduled_transshipment,
            'start_transport_date' => optional($this->transportGuide->start_transport_date)->format('Y-m-d') ?: now()->toDateString(),
            'delivery_date' => optional($this->transportGuide->delivery_date)->format('Y-m-d'),
            'gross_weight' => $this->transportGuide->gross_weight,
            'gross_weight_unit' => $this->transportGuide->gross_weight_unit ?? 'KGM',
            'total_packages' => $this->transportGuide->total_packages,
            'origin_ubigeo' => $this->transportGuide->origin_ubigeo,
            'origin_address' => $this->transportGuide->origin_address,
            'destination_ubigeo' => $this->transportGuide->destination_ubigeo,
            'destination_address' => $this->transportGuide->destination_address,
            'related_invoice_id' => $this->transportGuide->related_invoice_id,
            'related_invoice_number' => $this->transportGuide->related_invoice_number,
            'related_sender_guide_number' => $this->transportGuide->related_sender_guide_number,
            'additional_document_reference' => $this->transportGuide->additional_document_reference,
        ];

        $this->items = $this->transportGuide->items->map(function (TransportGuideItem $item) {
            return [
                'id' => $item->id,
                'description' => $item->description,
                'unit_of_measure' => $item->unit_of_measure,
                'quantity' => (float) $item->quantity,
                'weight' => $item->weight,
                'order_item_id' => $item->order_item_id,
            ];
        })->values()->toArray();

        if (empty($this->items)) {
            $this->items = [[
                'description' => '',
                'unit_of_measure' => 'NIU',
                'quantity' => 1,
                'weight' => null,
                'order_item_id' => null,
            ]];
        }

        $this->clients = Client::orderBy('business_name')->get();
        $this->trucks = Truck::orderBy('plate_number')->get();
        $this->drivers = Driver::orderBy('name')->get();

        // Carga asignaciones sin filtrar por truck/driver (porque ya no se seleccionan)
        $this->loadAssignments();

        // Si hay asignación (edit o draft), aplicar SIEMPRE la asignación como fuente de verdad
        $selectedAssignment = null;
        if (!empty($this->form['assignment_id'])) {
            $selectedAssignment = Assignment::with(['truck', 'driver', 'order'])->find((int) $this->form['assignment_id']);
            if ($selectedAssignment) {
                $this->applyAssignment($selectedAssignment);
            }
        }

        $this->invoices = Invoice::with('client')->orderByDesc('issue_date')->limit(50)->get();

        if ($selectedAssignment) {
            $orderId = isset($this->form['order_id']) ? (int) $this->form['order_id'] : null;

            if (empty($this->form['client_id']) && $selectedAssignment->order?->client_id) {
                $this->form['client_id'] = (int) $selectedAssignment->order->client_id;
            }

            $invoice = $this->applyInvoiceFromOrder($orderId, false);
            $this->applyClientFromAssignment($selectedAssignment, $invoice);
            $this->applySenderGuideReferenceFromOrder($orderId);
            if ($selectedAssignment->order) {
                $this->applyGuideFieldsFromOrder($selectedAssignment->order, $invoice);
            }
        }
    }

    protected function rules(): array
    {
        return [
            'form.series' => ['required', 'string', 'max:4', 'regex:' . $this->seriesPattern()],
            'form.correlative' => 'required|integer|min:1',
            'form.document_type_code' => 'required|in:' . $this->documentTypeForType(),

            'form.observations' => 'nullable|string',
            'form.client_id' => 'required|exists:clients,id',

            // Remitente (siempre es la empresa para GRE-R)
            'form.remitente_document_type' => 'required|string|max:2',
            'form.remitente_document_number' => 'required|string|max:20',
            'form.remitente_ruc' => 'required|string|max:11',
            'form.remitente_name' => 'required|string|max:100',

            // Transportista (siempre es la empresa para GRE-R)
            'form.transportista_ruc' => 'required|string|max:11',
            'form.transportista_name' => 'required|string|max:100',

            'form.destinatario_document_type' => 'required|string|max:2',
            'form.destinatario_document_number' => 'required|string|max:20',
            'form.destinatario_name' => 'required|string|max:100',
            'form.order_id' => 'nullable|exists:orders,id',

            // >>> CAMBIO: asignación requerida (única selección) <<<
'form.assignment_id' => 'nullable|exists:assignments,id',

            // Ya no se eligen manualmente; igual validamos si están presentes
            'form.truck_id' => 'nullable|exists:trucks,id',
            'form.driver_id' => 'nullable|exists:drivers,id',

            'form.vehicle_plate' => 'required|string|max:20',
            'form.vehicle_brand' => 'nullable|string|max:50',
            'form.mtc_registration_number' => 'nullable|string|max:50',
            'form.driver_document_number' => 'required|string|max:20',
            'form.driver_document_type' => 'required|string|max:4',
            'form.driver_name' => 'required|string|max:100',
            'form.driver_license_number' => 'required|string|max:20',
            'form.transfer_reason_code' => ['required', 'string', 'size:2', 'regex:/^\\d{2}$/'],
            'form.transfer_reason_description' => 'nullable|string|max:100',
            'form.transport_mode_code' => ['required', 'string', 'size:2', Rule::in(array_keys($this->transportModeOptions))],
            'form.scheduled_transshipment' => 'boolean',
            'form.start_transport_date' => 'required|date',
            'form.delivery_date' => 'nullable|date|after_or_equal:form.start_transport_date',
            'form.gross_weight' => 'required|numeric|min:0.001',
            'form.gross_weight_unit' => 'required|string|max:4',
            'form.total_packages' => 'nullable|integer|min:1',
            'form.origin_ubigeo' => ['required', 'string', 'size:6', 'regex:/^\\d{6}$/'],
            'form.origin_address' => 'required|string|max:100',
            'form.destination_ubigeo' => ['required', 'string', 'size:6', 'regex:/^\\d{6}$/'],
            'form.destination_address' => 'required|string|max:100',
            'form.related_invoice_id' => 'nullable|exists:invoices,id',
            'form.related_invoice_number' => 'nullable|string|max:20',
            'form.related_sender_guide_number' => 'nullable|string|max:20',
            'form.additional_document_reference' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:250',
            'items.*.unit_of_measure' => 'required|string|max:4',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.weight' => 'nullable|numeric|min:0',
            'items.*.order_item_id' => 'nullable|integer',
        ];
    }

    /**
     * Fuente de verdad: Assignment
     * Aplica truck/driver y snapshots de vehículo/conductor.
     */
protected function applyAssignment(Assignment $assignment): void
{
    $this->syncingAssignment = true;

    if ($assignment->order_id) {
        $this->form['order_id'] = $assignment->order_id;
    }

    // Fuente de verdad (IDs)
    $this->form['assignment_id'] = $assignment->id;
    $this->form['truck_id'] = $assignment->truck_id;
    $this->form['driver_id'] = $assignment->driver_id;

    // Snapshots: solo setear si vienen con valor (no null/empty)
    if ($assignment->truck) {
        if (!empty($assignment->truck->plate_number)) {
            $this->form['vehicle_plate'] = $assignment->truck->plate_number;
        }
        if (!empty($assignment->truck->brand)) {
            $this->form['vehicle_brand'] = $assignment->truck->brand;
        }
        if (!empty($assignment->truck->mtc_registration_number)) {
            $this->form['mtc_registration_number'] = $assignment->truck->mtc_registration_number;
        }
    }

    if ($assignment->driver) {
        $rawDocumentNumber = trim((string) ($assignment->driver->document_number ?? ''));
        $digitsDocumentNumber = preg_replace('/\\D+/', '', $rawDocumentNumber) ?: '';

        if ($rawDocumentNumber !== '') {
            // Si el tipo de doc es DNI/CE (códigos SUNAT), guardar solo dígitos para evitar valores tipo "DNI00000000".
            $driverType = trim((string) ($assignment->driver->document_type ?? ''));
            if (in_array($driverType, ['1', '4', '6'], true) && $digitsDocumentNumber !== '') {
                $this->form['driver_document_number'] = $digitsDocumentNumber;
            } else {
                $this->form['driver_document_number'] = $rawDocumentNumber;
            }
        }

        // CLAVE: si el driver no tiene document_type, NO lo pises (para que el usuario lo complete).
        // Pero si se puede inferir de forma segura (DNI/RUC), completar.
        $driverType = trim((string) ($assignment->driver->document_type ?? ''));
        if ($driverType !== '') {
            $this->form['driver_document_type'] = $driverType;
        } elseif ($digitsDocumentNumber !== '') {
            $guessed = match (strlen($digitsDocumentNumber)) {
                8 => '1',   // DNI
                11 => '6',  // RUC (no común para conductor, pero evita vacío si lo ingresaron así)
                default => null,
            };

            if ($guessed && blank($this->form['driver_document_type'] ?? null)) {
                $this->form['driver_document_type'] = $guessed;
            }
        }
        if (!empty($assignment->driver->name)) {
            $this->form['driver_name'] = $assignment->driver->name;
        }
        if (!empty($assignment->driver->license_number)) {
            $this->form['driver_license_number'] = $assignment->driver->license_number;
        }
    }

    $this->syncingAssignment = false;
}

    protected function applyClientFromAssignment(Assignment $assignment, ?Invoice $invoice = null, bool $forceClientFromOrder = false): void
    {
        $setIfEmpty = function (string $key, mixed $value): void {
            if ($value === null) {
                return;
            }

            $current = $this->form[$key] ?? null;
            if (trim((string) $current) !== '') {
                return;
            }

            $value = is_string($value) ? trim($value) : $value;
            if ($value === '' || $value === []) {
                return;
            }

            $this->form[$key] = $value;
        };

        $normalizeDocumentNumber = static function (?string $value): string {
            $digits = preg_replace('/\\D+/', '', (string) $value) ?: '';

            return $digits;
        };

        $guessDocumentType = static function (string $documentNumber): ?string {
            return match (strlen($documentNumber)) {
                11 => '6', // RUC
                8 => '1',  // DNI
                default => null,
            };
        };

        $orderClientId = $assignment->order?->client_id;

        if ($orderClientId && ($forceClientFromOrder || empty($this->form['client_id']))) {
            $this->form['client_id'] = (int) $orderClientId;
        } elseif (empty($this->form['client_id']) && $invoice?->client_id) {
            $this->form['client_id'] = (int) $invoice->client_id;
        }

        $clientId = is_numeric($this->form['client_id'] ?? null) ? (int) $this->form['client_id'] : null;
        $client = $clientId ? Client::find($clientId) : null;

        // GRE-T: el client_id representa al remitente (dueño de la mercancía).
        if ($this->type === TransportGuide::TYPE_TRANSPORTISTA && $client) {
            $clientDoc = $normalizeDocumentNumber($client->tax_id ?? null);
            if ($clientDoc !== '' && strlen($clientDoc) <= 11) {
                $setIfEmpty('remitente_document_type', $guessDocumentType($clientDoc) ?? '6');
                $setIfEmpty('remitente_document_number', $clientDoc);
                $setIfEmpty('remitente_ruc', $clientDoc);
            }
            $setIfEmpty('remitente_name', $client->business_name);
        }

        // Asegurar remitente siempre (GRE-T), incluso si el hook del select no se dispara.
        $this->syncRemitenteFromClient($client);

        // Destinatario: mejor esfuerzo. Preferir factura si existe, si no, caer al cliente de la orden.
        if ($invoice) {
            $destDoc = $normalizeDocumentNumber($invoice->ruc_receptor ?? null);
            if ($destDoc !== '' && strlen($destDoc) <= 11) {
                $setIfEmpty('destinatario_document_type', $guessDocumentType($destDoc) ?? '6');
                $setIfEmpty('destinatario_document_number', $destDoc);
            }
            $destName = $invoice->client?->business_name ?: null;
            $setIfEmpty('destinatario_name', $destName);
        }

        if ($client && $this->type === TransportGuide::TYPE_REMITENTE) {
            // Para GRE-R tiene sentido que el client_id sea el destinatario.
            $fallbackDoc = $normalizeDocumentNumber($client->tax_id ?? null);
            if ($fallbackDoc !== '' && strlen($fallbackDoc) <= 11) {
                $setIfEmpty('destinatario_document_type', $guessDocumentType($fallbackDoc) ?? '6');
                $setIfEmpty('destinatario_document_number', $fallbackDoc);
            }
            $setIfEmpty('destinatario_name', $client->business_name);
        }
    }

    protected function syncRemitenteFromClient(?Client $client): void
    {
        if ($this->type !== TransportGuide::TYPE_TRANSPORTISTA) {
            return;
        }

        if (! $client) {
            return;
        }

        $digits = preg_replace('/\\D+/', '', (string) ($client->tax_id ?? '')) ?: '';

        $documentType = match (strlen($digits)) {
            11 => '6', // RUC
            8 => '1',  // DNI
            default => '6',
        };

        $number = $digits !== '' ? $digits : (string) ($client->tax_id ?? '');

        $this->form['remitente_document_type'] = $documentType;
        $this->form['remitente_document_number'] = $number;
        $this->form['remitente_ruc'] = $number;
        $this->form['remitente_name'] = $client->business_name;
    }

    protected function applySenderGuideReferenceFromOrder(?int $orderId): void
    {
        if (! $orderId) {
            return;
        }

        if ($this->type !== TransportGuide::TYPE_TRANSPORTISTA) {
            return;
        }

        if (trim((string) ($this->form['related_sender_guide_number'] ?? '')) !== '') {
            return;
        }

        $remitterGuide = TransportGuide::query()
            ->where('type', TransportGuide::TYPE_REMITENTE)
            ->where('order_id', $orderId)
            ->orderByDesc('issue_date')
            ->orderByDesc('created_at')
            ->first();

        if (! $remitterGuide) {
            return;
        }

        $this->form['related_sender_guide_number'] = $remitterGuide->full_code ?: $remitterGuide->display_code;
    }

    protected function applyGuideFieldsFromOrder(Order $order, ?Invoice $invoice = null): void
    {
        $setIfEmpty = function (string $key, $value): void {
            if ($value === null) {
                return;
            }

            if (is_string($value) && trim($value) === '') {
                return;
            }

            if (blank($this->form[$key] ?? null)) {
                $this->form[$key] = $value;
            }
        };

        $normalizeUbigeo = static function ($value): ?string {
            $digits = preg_replace('/\\D+/', '', (string) $value) ?: '';

            return strlen($digits) === 6 ? $digits : null;
        };

        $limit = static function (?string $value, int $max): ?string {
            $value = $value !== null ? trim($value) : '';

            if ($value === '') {
                return null;
            }

            return mb_substr($value, 0, $max);
        };

        $setIfEmpty('origin_ubigeo', $normalizeUbigeo($order->origin_ubigeo));
        $setIfEmpty('origin_address', $limit($order->origin_address, 100));
        $setIfEmpty('destination_ubigeo', $normalizeUbigeo($order->destination_ubigeo));
        $setIfEmpty('destination_address', $limit($order->destination_address, 100));

        if (blank($this->form['total_packages'] ?? null) && filled($order->total_packages)) {
            $this->form['total_packages'] = (int) $order->total_packages;
        }

        if (blank($this->form['gross_weight'] ?? null) && filled($order->cargo_weight_kg) && (float) $order->cargo_weight_kg > 0) {
            $this->form['gross_weight'] = (float) $order->cargo_weight_kg;
        }

        // Si el formulario estรก en defaults, preferir fechas de la orden.
        if (! empty($order->pickup_date) && filled($this->form['start_transport_date'] ?? null)) {
            $currentStart = Carbon::parse($this->form['start_transport_date'])->toDateString();
            if ($currentStart === now()->toDateString()) {
                $this->form['start_transport_date'] = optional($order->pickup_date)->format('Y-m-d');
            }
        }

        if (empty($this->form['delivery_date']) && ! empty($order->delivery_date)) {
            $this->form['delivery_date'] = optional($order->delivery_date)->format('Y-m-d');
        }

        // Destinatario: si la factura no trae receptor (o no hay factura), caer a los datos guardados en la orden.
        $hasInvoiceReceiver = $invoice && ! blank($invoice->ruc_receptor ?? null);
        if (! $hasInvoiceReceiver) {
            $setIfEmpty('destinatario_document_type', $limit($order->destinatario_document_type, 2));
            $setIfEmpty('destinatario_document_number', $limit($order->destinatario_document_number, 20));
            $setIfEmpty('destinatario_name', $limit($order->destinatario_name, 100));
        }

        // Items: si no hay descripciรn y la orden tiene detalle de carga, prellenar el primer item.
        if (! empty($order->cargo_details) && is_array($this->items) && count($this->items) > 0) {
            $firstDescription = trim((string) ($this->items[0]['description'] ?? ''));
            if ($firstDescription === '') {
                $this->items[0]['description'] = mb_substr(trim((string) $order->cargo_details), 0, 250);
            }
        }
    }

    protected function applyInvoiceFromOrder(?int $orderId, bool $enforceOrderMatch = false): ?Invoice
    {
        if (! $orderId) {
            return null;
        }

        $currentRelatedInvoiceId = $this->form['related_invoice_id'] ?? null;
        $currentRelatedInvoiceNumber = trim((string) ($this->form['related_invoice_number'] ?? ''));

        $invoice = null;

        if ($currentRelatedInvoiceId) {
            $invoice = Invoice::with('client')->find((int) $currentRelatedInvoiceId);
            if (! $invoice) {
                return null;
            }

            if ($enforceOrderMatch && $invoice->order_id && (int) $invoice->order_id !== $orderId) {
                $this->form['related_invoice_id'] = null;
                $this->form['related_invoice_number'] = null;
                $invoice = null;
            }
        } elseif ($currentRelatedInvoiceNumber !== '') {
            return null;
        }

        if (! $invoice) {
            $candidates = Invoice::with('client')
                ->where('order_id', $orderId)
                ->orderByDesc('issue_date')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();

            if ($candidates->isEmpty()) {
                return null;
            }

            $isPaid = static function (Invoice $candidate): bool {
                $values = [
                    $candidate->status ?? null,
                    $candidate->estado ?? null,
                    $candidate->state ?? null,
                    $candidate->payment_status ?? null,
                    $candidate->sunat_status ?? null,
                ];

                $values = array_values(array_filter($values, static fn ($value) => $value !== null && $value !== ''));
                $normalized = array_map(static fn ($value) => strtolower(trim((string) $value)), $values);

                return in_array('paid', $normalized, true)
                    || in_array('pagado', $normalized, true)
                    || in_array('pagada', $normalized, true)
                    || in_array('aceptado', $normalized, true);
            };

            $latestTimestamp = (int) $candidates->max(function (Invoice $candidate): int {
                return $candidate->issue_date?->getTimestamp() ?? $candidate->created_at?->getTimestamp() ?? 0;
            });

            $latestCandidates = $candidates->filter(function (Invoice $candidate) use ($latestTimestamp): bool {
                $ts = $candidate->issue_date?->getTimestamp() ?? $candidate->created_at?->getTimestamp() ?? 0;

                return $ts === $latestTimestamp;
            })->values();

            $invoice = $latestCandidates->first(fn (Invoice $candidate) => $isPaid($candidate)) ?: $latestCandidates->first();
        }

        if (! $invoice) {
            return null;
        }

        $this->ensureInvoiceInList($invoice);

        $this->syncingInvoice = true;
        try {
            if (empty($this->form['related_invoice_id'])) {
                $this->form['related_invoice_id'] = $invoice->getKey();
            }

            if (trim((string) ($this->form['related_invoice_number'] ?? '')) === '') {
                $number = $invoice->numero_completo
                    ?: $invoice->invoice_number
                    ?: (($invoice->series && $invoice->correlative) ? ($invoice->series.'-'.$invoice->correlative) : null);

                if ($number) {
                    $this->form['related_invoice_number'] = $number;
                }
            }

            if (empty($this->form['order_id']) || (int) $this->form['order_id'] === $orderId) {
                $this->form['order_id'] = $invoice->order_id ?: $orderId;
            }

            if (empty($this->form['client_id']) && $invoice->client_id) {
                $this->form['client_id'] = $invoice->client_id;
            }

            if (empty($this->form['destinatario_document_number']) && ! empty($invoice->ruc_receptor)) {
                $this->form['destinatario_document_number'] = $invoice->ruc_receptor;
            }

            if (empty($this->form['destinatario_name'])) {
                $name = $invoice->client?->business_name ?: $invoice->client?->name;
                if ($name) {
                    $this->form['destinatario_name'] = $name;
                }
            }
        } finally {
            $this->syncingInvoice = false;
        }

        return $invoice;
    }

    protected function ensureInvoiceInList(Invoice $invoice): void
    {
        if (! $this->invoices) {
            return;
        }

        if (! $this->invoices instanceof \Illuminate\Support\Collection) {
            return;
        }

        if ($this->invoices->contains('id', $invoice->getKey())) {
            return;
        }

        $this->invoices = $this->invoices->prepend($invoice);
    }


    public function updatedFormTruckId($value): void
    {
        // >>> CAMBIO: ya NO se debe seleccionar manualmente si hay asignación <<<
        if (!empty($this->form['assignment_id'])) {
            return;
        }

        $truckId = is_numeric($value) ? (int) $value : null;
        if (! $truckId) {
            $this->form['truck_id'] = null;
            $this->form['vehicle_plate'] = null;
            $this->form['vehicle_brand'] = null;
            $this->form['mtc_registration_number'] = null;
            $this->loadAssignments();

            return;
        }

        $truck = Truck::find($truckId);
        if ($truck) {
            $this->form['vehicle_plate'] = $truck->plate_number;
            $this->form['vehicle_brand'] = $truck->brand;
            $this->form['mtc_registration_number'] = $truck->mtc_registration_number;
        }

        $this->loadAssignments();
    }

    public function updatedFormDriverId($value): void
    {
        // >>> CAMBIO: ya NO se debe seleccionar manualmente si hay asignación <<<
        if (!empty($this->form['assignment_id'])) {
            return;
        }

        $driverId = is_numeric($value) ? (int) $value : null;
        if (! $driverId) {
            $this->form['driver_id'] = null;
            $this->form['driver_document_number'] = null;
            $this->form['driver_document_type'] = null;
            $this->form['driver_name'] = null;
            $this->form['driver_license_number'] = null;
            $this->loadAssignments();

            return;
        }

        $driver = Driver::find($driverId);
        if ($driver) {
            $rawDocumentNumber = trim((string) ($driver->document_number ?? ''));
            $digitsDocumentNumber = preg_replace('/\\D+/', '', $rawDocumentNumber) ?: '';

            $driverType = trim((string) ($driver->document_type ?? ''));
            if (in_array($driverType, ['1', '4', '6'], true) && $digitsDocumentNumber !== '') {
                $this->form['driver_document_number'] = $digitsDocumentNumber;
            } else {
                $this->form['driver_document_number'] = $rawDocumentNumber;
            }

            if ($driverType !== '') {
                $this->form['driver_document_type'] = $driverType;
            } elseif ($digitsDocumentNumber !== '') {
                $this->form['driver_document_type'] = match (strlen($digitsDocumentNumber)) {
                    8 => '1',
                    11 => '6',
                    default => $this->form['driver_document_type'] ?? null,
                };
            }
            $this->form['driver_name'] = $driver->name;
            $this->form['driver_license_number'] = $driver->license_number;
        }

        $this->loadAssignments();
    }

    public function updatedFormAssignmentId($value): void
    {
        // >>> CAMBIO: assignment_id es la única selección; limpiar si se quita <<<
        if (! $value) {
            $this->form['order_id'] = null;
            $this->form['truck_id'] = null;
            $this->form['driver_id'] = null;

            $this->form['vehicle_plate'] = null;
            $this->form['vehicle_brand'] = null;
            $this->form['mtc_registration_number'] = null;

            $this->form['driver_document_number'] = null;
            $this->form['driver_document_type'] = null;
            $this->form['driver_name'] = null;
            $this->form['driver_license_number'] = null;
            return;
        }

        $assignmentId = is_numeric($value) ? (int) $value : null;
        if (! $assignmentId) {
            return;
        }

        $assignment = Assignment::with(['truck', 'driver', 'order'])->find($assignmentId);
        if (! $assignment) {
            return;
        }

        $previousOrderId = is_numeric($this->form['order_id'] ?? null) ? (int) $this->form['order_id'] : null;

        $this->applyAssignment($assignment);

        $orderId = isset($this->form['order_id'])
            ? (int) $this->form['order_id']
            : ($assignment->order_id ? (int) $assignment->order_id : null);

        if ($previousOrderId && $orderId && $previousOrderId !== $orderId) {
            $this->form['client_id'] = null;
            $this->form['related_invoice_id'] = null;
            $this->form['related_invoice_number'] = null;
            $this->form['related_sender_guide_number'] = null;

            $this->form['gross_weight'] = null;
            $this->form['total_packages'] = null;
            $this->form['origin_ubigeo'] = null;
            $this->form['origin_address'] = null;
            $this->form['destination_ubigeo'] = null;
            $this->form['destination_address'] = null;
            $this->form['start_transport_date'] = now()->toDateString();
            $this->form['delivery_date'] = null;

            $this->form['destinatario_document_type'] = null;
            $this->form['destinatario_document_number'] = null;
            $this->form['destinatario_name'] = null;

            if ($this->type === TransportGuide::TYPE_TRANSPORTISTA) {
                $this->form['remitente_document_type'] = null;
                $this->form['remitente_document_number'] = null;
                $this->form['remitente_ruc'] = null;
                $this->form['remitente_name'] = null;
            }
        }

        $invoice = $this->applyInvoiceFromOrder($orderId, true);
        $this->applyClientFromAssignment($assignment, $invoice, true);
        $this->applySenderGuideReferenceFromOrder($orderId);
        if ($assignment->order) {
            $this->applyGuideFieldsFromOrder($assignment->order, $invoice);
        }

        $this->loadAssignments();
    }

    public function updatedFormTransferReasonCode($value): void
    {
        $code = (string) $value;
        if (isset($this->transferReasonOptions[$code])) {
            $this->form['transfer_reason_description'] = $this->transferReasonOptions[$code];
        }
    }

    public function updatedFormClientId($value): void
    {
        $clientId = is_numeric($value) ? (int) $value : null;
        if (! $clientId) {
            $this->form['client_id'] = null;

            if ($this->type === TransportGuide::TYPE_REMITENTE) {
                $this->form['destinatario_document_type'] = null;
                $this->form['destinatario_document_number'] = null;
                $this->form['destinatario_name'] = null;
            } else {
                $this->form['remitente_document_type'] = null;
                $this->form['remitente_document_number'] = null;
                $this->form['remitente_ruc'] = null;
                $this->form['remitente_name'] = null;

                $this->form['destinatario_document_type'] = null;
                $this->form['destinatario_document_number'] = null;
                $this->form['destinatario_name'] = null;
            }

            return;
        }

        $client = Client::find($clientId);
        if (! $client) {
            return;
        }

        if ($this->type === TransportGuide::TYPE_REMITENTE) {
            // GRE-R: El cliente es el DESTINATARIO
            // El remitente ya está configurado como la empresa
            $this->form['destinatario_document_type'] = '6'; // RUC
            $this->form['destinatario_document_number'] = $client->tax_id;
            $this->form['destinatario_name'] = $client->business_name;
        } else {
            // GRE-T: El cliente es el REMITENTE (dueño de la mercancía)
            $this->syncRemitenteFromClient($client);
            // NO setear destinatario por defecto en GRE-T.
            // El destinatario debe venir de factura/orden (si existe) o lo ingresa el usuario.
        }
    }

    protected function loadAssignments(): void
    {
        // >>> CAMBIO: ya no filtramos por truck/driver, porque no se eligen manualmente <<<
        $currentAssignmentId = $this->form['assignment_id'] ?? null;

        $this->assignments = Assignment::query()
            ->with(['truck', 'driver', 'order'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderByDesc('start_date')
            ->limit(50)
            ->get();

        if (! $currentAssignmentId) {
            return;
        }

        $selectedAssignment = Assignment::with(['truck', 'driver', 'order'])->find((int) $currentAssignmentId);
        if ($selectedAssignment && ! $this->assignments->contains('id', (int) $currentAssignmentId)) {
            $this->assignments = $this->assignments->prepend($selectedAssignment);
        }

        // No auto-null aquí: si eligieron una asignación, se respeta
        // (antes se anulaba por mismatch de truck/driver)
        if (! $selectedAssignment && ! $this->syncingAssignment) {
            $this->form['assignment_id'] = null;
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'description' => '',
            'unit_of_measure' => 'NIU',
            'quantity' => 1,
            'weight' => null,
            'order_item_id' => null,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(): void
    {
        $this->authorize($this->isEdit ? 'update' : 'create', $this->isEdit ? $this->transportGuide : TransportGuide::class);

        $validated = $this->validate();

        try {
            DB::transaction(function () use ($validated) {
                $this->persistGuide($validated['form']);
                $this->syncItems($validated['items']);

                $invoiceId = $validated['form']['related_invoice_id'] ?? null;
                $this->syncInvoiceLink($invoiceId ? (int) $invoiceId : null);
            });
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('save', 'No se pudo guardar la guía. Revise los datos e intente nuevamente.');

            return;
        }

        session()->flash('message', $this->isEdit ? 'Guía actualizada correctamente.' : 'Guía registrada correctamente.');
        $this->redirectRoute($this->indexRouteName());
    }

    protected function persistGuide(array $data): void
    {
        // >>> BLINDAJE: SIEMPRE derivar vehículo/conductor desde asignación <<<
       if (!empty($data['assignment_id'])) {
        $assignment = Assignment::with(['truck', 'driver', 'order'])->find((int) $data['assignment_id']);
        if ($assignment) {
            $data['order_id'] = $assignment->order_id ?: ($data['order_id'] ?? null);

            // IDs siempre desde asignación
            $data['truck_id'] = $assignment->truck_id;
            $data['driver_id'] = $assignment->driver_id;

            // Snapshots: solo completar desde asignación si asignación trae dato
            if ($assignment->truck) {
                if (!empty($assignment->truck->plate_number)) {
                    $data['vehicle_plate'] = $assignment->truck->plate_number;
                }
                if (!empty($assignment->truck->brand)) {
                    $data['vehicle_brand'] = $assignment->truck->brand;
                }
                if (!empty($assignment->truck->mtc_registration_number)) {
                    $data['mtc_registration_number'] = $assignment->truck->mtc_registration_number;
                }
            }

            if ($assignment->driver) {
                $rawDocumentNumber = trim((string) ($assignment->driver->document_number ?? ''));
                $digitsDocumentNumber = preg_replace('/\\D+/', '', $rawDocumentNumber) ?: '';

                if ($rawDocumentNumber !== '') {
                    $driverType = trim((string) ($assignment->driver->document_type ?? ''));
                    if (in_array($driverType, ['1', '4', '6'], true) && $digitsDocumentNumber !== '') {
                        $data['driver_document_number'] = $digitsDocumentNumber;
                    } else {
                        $data['driver_document_number'] = $rawDocumentNumber;
                    }
                }

                if (!empty($assignment->driver->document_type)) {
                    $data['driver_document_type'] = $assignment->driver->document_type;
                } elseif ($digitsDocumentNumber !== '') {
                    $data['driver_document_type'] = match (strlen($digitsDocumentNumber)) {
                        8 => '1',
                        11 => '6',
                        default => $data['driver_document_type'] ?? null,
                    };
                }
                if (!empty($assignment->driver->name)) {
                    $data['driver_name'] = $assignment->driver->name;
                }
                if (!empty($assignment->driver->license_number)) {
                    $data['driver_license_number'] = $assignment->driver->license_number;
                }
            }
        }
    }


        $transferReasonDescription = trim((string) ($data['transfer_reason_description'] ?? ''));
        if ($transferReasonDescription === '' && isset($this->transferReasonOptions[$data['transfer_reason_code']])) {
            $transferReasonDescription = $this->transferReasonOptions[$data['transfer_reason_code']];
        }

        $this->transportGuide->fill([
            'type' => $this->type,
            'series' => $data['series'],
            'correlative' => $data['correlative'],
            'full_code' => sprintf('%s-%08d', $data['series'], $data['correlative']),
            'issue_date' => now()->toDateString(),
            'issue_time' => now()->format('H:i:s'),
            'document_type_code' => $data['document_type_code'],
            'observations' => $data['observations'] ?: null,
            'client_id' => $data['client_id'],
            'remitente_document_type' => $data['remitente_document_type'],
            'remitente_document_number' => $data['remitente_document_number'],
            'remitente_ruc' => $data['remitente_ruc'],
            'remitente_name' => $data['remitente_name'],
            'destinatario_document_type' => $data['destinatario_document_type'],
            'destinatario_document_number' => $data['destinatario_document_number'],
            'destinatario_name' => $data['destinatario_name'],
            'transportista_ruc' => $data['transportista_ruc'],
            'transportista_name' => $data['transportista_name'],
            'order_id' => $data['order_id'] ?: null,
            'assignment_id' => $data['assignment_id'] ?: null,
            'truck_id' => $data['truck_id'] ?: null,
            'driver_id' => $data['driver_id'] ?: null,
            'vehicle_plate' => $data['vehicle_plate'],
            'vehicle_brand' => $data['vehicle_brand'] ?: null,
            'mtc_registration_number' => $data['mtc_registration_number'] ?: null,
            'driver_document_number' => $data['driver_document_number'],
            'driver_document_type' => $data['driver_document_type'],
            'driver_name' => $data['driver_name'],
            'driver_license_number' => $data['driver_license_number'],
            'transfer_reason_code' => $data['transfer_reason_code'],
            'transfer_reason_description' => $transferReasonDescription !== '' ? $transferReasonDescription : null,
            'transport_mode_code' => $data['transport_mode_code'],
            'scheduled_transshipment' => (bool) $data['scheduled_transshipment'],
            'start_transport_date' => $data['start_transport_date'],
            'delivery_date' => $data['delivery_date'] ?: null,
            'gross_weight' => $data['gross_weight'],
            'gross_weight_unit' => $data['gross_weight_unit'] ?? 'KGM',
            'total_packages' => $data['total_packages'] ?: null,
            'origin_ubigeo' => $data['origin_ubigeo'],
            'origin_address' => $data['origin_address'],
            'destination_ubigeo' => $data['destination_ubigeo'],
            'destination_address' => $data['destination_address'],
            'related_invoice_id' => $data['related_invoice_id'] ?: null,
            'related_invoice_number' => $data['related_invoice_number'] ?: null,
            'related_sender_guide_number' => $data['related_sender_guide_number'] ?: null,
            'additional_document_reference' => $data['additional_document_reference'] ?: null,
            'sunat_status' => $this->transportGuide->sunat_status ?: TransportGuide::STATUS_DRAFT,
        ]);

        $this->transportGuide->issue_date = now();
        $this->transportGuide->start_transport_date = Carbon::parse($data['start_transport_date']);
        $this->transportGuide->delivery_date = $data['delivery_date'] ? Carbon::parse($data['delivery_date']) : null;

        $this->transportGuide->save();
    }

    protected function syncItems(array $items): void
    {
        $cleanItems = collect($items)->map(function ($item) {
            return [
                'description' => $item['description'],
                'unit_of_measure' => $item['unit_of_measure'],
                'quantity' => $item['quantity'],
                'weight' => $item['weight'] !== null && $item['weight'] !== '' ? (float) $item['weight'] : null,
                'order_item_id' => $item['order_item_id'] ?: null,
            ];
        });

        $this->transportGuide->items()->delete();
        $this->transportGuide->items()->createMany($cleanItems->toArray());
    }

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

    protected function indexRouteName(): string
    {
        return $this->type === TransportGuide::TYPE_REMITENTE
            ? 'billing.remitter-guides.index'
            : 'billing.transport-guides.index';
    }

    public function render()
    {
        $this->authorize($this->isEdit ? 'view' : 'create', $this->isEdit ? $this->transportGuide : TransportGuide::class);

        return view('livewire.billing.transport-guides.form');
    }

    public function updatedFormRelatedInvoiceId($invoiceId): void
    {
        if ($this->syncingInvoice) {
            return;
        }

        if (! $invoiceId) {
            return;
        }

        $this->syncingInvoice = true;

        try {
            $invoice = Invoice::with('client', 'order')->find($invoiceId);

            if (! $invoice) {
                return;
            }

            $this->form['related_invoice_id'] = $invoiceId;
            $this->ensureInvoiceInList($invoice);
            $this->form['related_invoice_number'] = $invoice->numero_completo ?: $invoice->invoice_number;
            $this->form['client_id'] = $invoice->client_id;
            $this->form['order_id'] = $invoice->order_id;

            if ($this->type === TransportGuide::TYPE_TRANSPORTISTA) {
                $this->syncRemitenteFromClient($invoice->client);
            }

            $this->form['destinatario_document_number'] = $invoice->ruc_receptor;
            $this->form['destinatario_name'] = $invoice->client?->business_name ?: $this->form['destinatario_name'];
        } finally {
            $this->syncingInvoice = false;
        }
    }

    protected function syncInvoiceLink(?int $invoiceId): void
    {
        if ($this->previousInvoiceId && $this->previousInvoiceId !== $invoiceId) {
            $previousInvoice = Invoice::find($this->previousInvoiceId);

            if ($previousInvoice && $previousInvoice->transport_guide_id === $this->transportGuide->getKey()) {
                $previousInvoice->forceFill(['transport_guide_id' => null])->save();
            }
        }

        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);

            if ($invoice) {
                $invoice->forceFill(['transport_guide_id' => $this->transportGuide->getKey()])->save();
            }
        }

        $this->previousInvoiceId = $invoiceId;
    }
}
