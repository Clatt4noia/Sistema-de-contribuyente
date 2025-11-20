<?php

namespace App\Livewire\Billing\TransportGuides;

use App\Models\Assignment;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\TransportGuide;
use App\Models\TransportGuideItem;
use App\Models\Truck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransportGuideForm extends Component
{
    use AuthorizesRequests;

    public TransportGuide $transportGuide;
    public string $type = TransportGuide::TYPE_TRANSPORTISTA;
    public bool $isEdit = false;

    public array $form = [];
    public array $items = [];

    public $clients;
    public $trucks;
    public $drivers;
    public $assignments;
    public $invoices;

    public function mount(?TransportGuide $transportGuide = null, string $type = TransportGuide::TYPE_TRANSPORTISTA): void
    {
        $this->type = $this->normalizeType($transportGuide?->type ?: $type);

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
                'transport_mode_code' => '01',
                'scheduled_transshipment' => false,
            ]);
            $this->authorize('create', TransportGuide::class);
            $this->transportGuide->correlative = $this->nextCorrelative($series);
        }

        $this->form = [
            'series' => $this->transportGuide->series,
            'correlative' => $this->transportGuide->correlative,
            'issue_date' => optional($this->transportGuide->issue_date)->format('Y-m-d') ?: now()->toDateString(),
            'issue_time' => $this->transportGuide->issue_time ?? now()->format('H:i:s'),
            'document_type_code' => $this->transportGuide->document_type_code ?? $this->documentTypeForType(),
            'observations' => $this->transportGuide->observations,
            'client_id' => $this->transportGuide->client_id,
            'remitente_document_type' => $this->transportGuide->remitente_document_type ?? '6',
            'remitente_document_number' => $this->transportGuide->remitente_document_number ?? Config::get('billing.sunat.ruc'),
            'remitente_ruc' => $this->transportGuide->remitente_ruc ?? Config::get('billing.sunat.ruc'),
            'remitente_name' => $this->transportGuide->remitente_name ?? Config::get('app.name'),
            'destinatario_document_type' => $this->transportGuide->destinatario_document_type ?? ($this->transportGuide->remitente_document_type ?? '6'),
            'destinatario_document_number' => $this->transportGuide->destinatario_document_number ?? $this->transportGuide->remitente_document_number ?? $this->transportGuide->remitente_ruc ?? Config::get('billing.sunat.ruc'),
            'destinatario_name' => $this->transportGuide->destinatario_name ?? $this->transportGuide->remitente_name ?? Config::get('app.name'),
            'transportista_ruc' => $this->transportGuide->transportista_ruc ?? Config::get('billing.sunat.ruc'),
            'transportista_name' => $this->transportGuide->transportista_name ?? Config::get('app.name'),
            'order_id' => $this->transportGuide->order_id,
            'assignment_id' => $this->transportGuide->assignment_id,
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
            'transfer_reason_description' => $this->transportGuide->transfer_reason_description,
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
        $this->trucks = Truck::orderBy('plate')->get();
        $this->drivers = Driver::orderBy('name')->get();
        $this->assignments = Assignment::orderByDesc('created_at')->limit(50)->get();
        $this->invoices = Invoice::orderByDesc('issue_date')->limit(50)->get();
    }

    protected function rules(): array
    {
        return [
            'form.series' => ['required', 'string', 'max:4', 'regex:' . $this->seriesPattern()],
            'form.correlative' => 'required|integer|min:1',
            'form.issue_date' => 'required|date',
            'form.issue_time' => 'required',
            'form.document_type_code' => 'required|in:' . $this->documentTypeForType(),
            'form.observations' => 'nullable|string',
            'form.client_id' => 'required|exists:clients,id',
            'form.remitente_document_type' => 'required|string|max:2',
            'form.remitente_document_number' => 'required|string|max:20',
            'form.remitente_ruc' => 'required|regex:/^\d{8,11}$/',
            'form.remitente_name' => 'required|string|max:100',
            'form.destinatario_document_type' => 'required|string|max:2',
            'form.destinatario_document_number' => 'required|string|max:20',
            'form.destinatario_name' => 'required|string|max:100',
            'form.transportista_ruc' => 'required|digits:11',
            'form.transportista_name' => 'required|string|max:100',
            'form.order_id' => 'nullable|exists:orders,id',
            'form.assignment_id' => 'nullable|exists:assignments,id',
            'form.truck_id' => 'required|exists:trucks,id',
            'form.driver_id' => 'required|exists:drivers,id',
            'form.vehicle_plate' => 'required|string|max:20',
            'form.vehicle_brand' => 'nullable|string|max:50',
            'form.mtc_registration_number' => 'nullable|string|max:50',
            'form.driver_document_number' => 'required|string|max:20',
            'form.driver_document_type' => 'required|string|max:4',
            'form.driver_name' => 'required|string|max:100',
            'form.driver_license_number' => 'required|string|max:20',
            'form.transfer_reason_code' => 'required|string|max:2',
            'form.transfer_reason_description' => 'nullable|string|max:100',
            'form.transport_mode_code' => 'required|string|max:2',
            'form.scheduled_transshipment' => 'boolean',
            'form.start_transport_date' => 'required|date',
            'form.delivery_date' => 'nullable|date|after_or_equal:form.start_transport_date',
            'form.gross_weight' => 'required|numeric|min:0.001',
            'form.gross_weight_unit' => 'required|string|max:4',
            'form.total_packages' => 'nullable|integer|min:1',
            'form.origin_ubigeo' => 'required|digits:8',
            'form.origin_address' => 'required|string|max:100',
            'form.destination_ubigeo' => 'required|digits:8',
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

    public function updatedFormTruckId($value): void
    {
        $truck = Truck::find($value);
        if ($truck) {
            $this->form['vehicle_plate'] = $truck->plate;
            $this->form['vehicle_brand'] = $truck->brand;
            $this->form['mtc_registration_number'] = $truck->mtc_registration_number;
        }
    }

    public function updatedFormDriverId($value): void
    {
        $driver = Driver::find($value);
        if ($driver) {
            $this->form['driver_document_number'] = $driver->document_number;
            $this->form['driver_document_type'] = $driver->document_type;
            $this->form['driver_name'] = $driver->name;
            $this->form['driver_license_number'] = $driver->license_number;
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

        DB::transaction(function () use ($validated) {
            $this->persistGuide($validated['form']);
            $this->syncItems($validated['items']);
        });

        session()->flash('message', $this->isEdit ? 'Guía actualizada correctamente.' : 'Guía registrada correctamente.');
        $this->redirectRoute($this->indexRouteName());
    }

    protected function persistGuide(array $data): void
    {
        $this->transportGuide->fill([
            'type' => $this->type,
            'series' => $data['series'],
            'correlative' => $data['correlative'],
            'full_code' => sprintf('%s-%08d', $data['series'], $data['correlative']),
            'issue_date' => $data['issue_date'],
            'issue_time' => $data['issue_time'],
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
            'truck_id' => $data['truck_id'],
            'driver_id' => $data['driver_id'],
            'vehicle_plate' => $data['vehicle_plate'],
            'vehicle_brand' => $data['vehicle_brand'] ?: null,
            'mtc_registration_number' => $data['mtc_registration_number'] ?: null,
            'driver_document_number' => $data['driver_document_number'],
            'driver_document_type' => $data['driver_document_type'],
            'driver_name' => $data['driver_name'],
            'driver_license_number' => $data['driver_license_number'],
            'transfer_reason_code' => $data['transfer_reason_code'],
            'transfer_reason_description' => $data['transfer_reason_description'] ?: null,
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

        $this->transportGuide->issue_date = Carbon::parse($data['issue_date']);
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
}
