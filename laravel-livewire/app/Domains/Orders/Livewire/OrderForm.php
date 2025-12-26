<?php

namespace App\Domains\Orders\Livewire;

use App\Enums\Orders\OrderStatus;
use App\Models\CargoType;
use App\Models\Order;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Actions\Orders\SaveOrderAction;
use App\Domains\Orders\Services\MtcReferentialService;
use Livewire\Component;

class OrderForm extends Component
{
    use AuthorizesRequests;

    public Order $order;
    protected SaveOrderAction $saveOrderAction;

    public bool $isEdit = false;
    public bool $estimatedCostManuallyEdited = false;

    public array $form = [];

    public array $routePlan = [
        'planner' => null,
        'route_summary' => null,
        'map_url' => null,
        'route_data' => null,
    ];

    public $clients;
    public $cargoTypes;

    // UI/estado de cálculo MTC
    public array $mtc = [
        'ok' => false,
        'message' => null,
        'source' => 'DS-026-2024-MTC',
        'year' => 2024,
        'route_key' => null,
        'destination' => null,
        'rate_sxtm' => null,
        'estimated_cost' => null,
    ];

    public function boot(SaveOrderAction $saveOrderAction): void
    {
        $this->saveOrderAction = $saveOrderAction;
    }
  public function updatedFormEstimatedCost($value): void
    {
        // Si el usuario escribió algo (o incluso lo cambió), ya es manual.
        // Si lo deja vacío, puedes permitir que vuelva a ser automático:
    $this->estimatedCostManuallyEdited = !blank($this->order->estimated_cost);
    }
    protected function rules(): array
    {
        $orderId = $this->order->id ?? 'NULL';
        $orderStatuses = array_map(static fn (OrderStatus $status) => $status->value, OrderStatus::cases());

        return [
            'form.client_id' => 'required|exists:clients,id',
            'form.reference' => 'required|string|max:50|unique:orders,reference,' . $orderId,
            'form.cargo_type_id' => 'nullable|exists:cargo_types,id',
            'form.origin' => 'required|string|max:150',
            'form.origin_ubigeo' => ['nullable', 'string', 'size:6', 'regex:/^\\d{6}$/'],
            'form.origin_address' => ['nullable', 'string', 'max:255'],
            'form.destination' => 'required|string|max:150',
            'form.destination_ubigeo' => ['nullable', 'string', 'size:6', 'regex:/^\\d{6}$/'],
            'form.destination_address' => ['nullable', 'string', 'max:255'],

            // Coordenadas (opcionales)
            'form.origin_latitude' => 'nullable|numeric|between:-90,90',
            'form.origin_longitude' => 'nullable|numeric|between:-180,180',
            'form.destination_latitude' => 'nullable|numeric|between:-90,90',
            'form.destination_longitude' => 'nullable|numeric|between:-180,180',

            // Fechas
            'form.pickup_date' => 'nullable|date',
            'form.delivery_date' => 'nullable|date|after_or_equal:form.pickup_date',

            // Estado
            'form.status' => ['required', 'string', 'in:' . implode(',', $orderStatuses)],

            // Carga (sintetizado)
            'form.cargo_weight_kg' => 'nullable|numeric|min:0',
            'form.cargo_volume_m3' => 'nullable|numeric|min:0',
            'form.total_packages' => 'nullable|integer|min:1',
            'form.cargo_details' => 'nullable|string',

            // Destinatario (opcional) para autocompletar GRE-T desde la orden
            'form.destinatario_document_type' => ['nullable', 'string', 'max:2'],
            'form.destinatario_document_number' => ['nullable', 'string', 'max:20'],
            'form.destinatario_name' => ['nullable', 'string', 'max:150'],

            // Si quieres permitir override manual:
            'form.estimated_cost' => 'nullable|numeric|min:0',

            'form.notes' => 'nullable|string',

            // Plan ruta (opcionales)
            'routePlan.planner' => 'nullable|string|max:100',
            'routePlan.route_summary' => 'nullable|string',
            'routePlan.map_url' => 'nullable|url',
            'routePlan.route_data' => 'nullable|string',
        ];
    }

    public function mount($order = null): void
    {
        if ($order) {
            $this->order = $order;
            $this->authorize('update', $this->order);
            $this->order->load('routePlans');
            $this->isEdit = true;

            $firstPlan = $this->order->routePlans->first();
            if ($firstPlan) {
                $this->routePlan = [
                    'planner' => $firstPlan->planner,
                    'route_summary' => $firstPlan->route_summary,
                    'map_url' => $firstPlan->map_url,
                    'route_data' => $firstPlan->route_data ? json_encode($firstPlan->route_data) : null,
                ];
            }
        } else {
            $this->authorize('create', Order::class);
            $this->order = new Order(['status' => OrderStatus::Pending]);
        }

        $statusValue = $this->order->status instanceof OrderStatus
            ? $this->order->status->value
            : ($this->order->status ?? OrderStatus::Pending->value);

        $this->form = [
            'client_id' => $this->order->client_id ?? '',
            'destinatario_document_type' => $this->order->destinatario_document_type ?? '',
            'destinatario_document_number' => $this->order->destinatario_document_number ?? '',
            'destinatario_name' => $this->order->destinatario_name ?? '',
            'reference' => $this->order->reference ?? '',
            'cargo_type_id' => $this->order->cargo_type_id ?? '',
            'origin' => $this->order->origin ?? '',
            'origin_ubigeo' => $this->order->origin_ubigeo ?? '',
            'origin_address' => $this->order->origin_address ?? '',
            'origin_latitude' => $this->order->origin_latitude,
            'origin_longitude' => $this->order->origin_longitude,
            'destination' => $this->order->destination ?? '',
            'destination_ubigeo' => $this->order->destination_ubigeo ?? '',
            'destination_address' => $this->order->destination_address ?? '',
            'destination_latitude' => $this->order->destination_latitude,
            'destination_longitude' => $this->order->destination_longitude,
            'pickup_date' => optional($this->order->pickup_date)->format('Y-m-d\TH:i'),
            'delivery_date' => optional($this->order->delivery_date)->format('Y-m-d\TH:i'),
            'status' => $statusValue,
            'cargo_details' => $this->order->cargo_details ?? '',
            'cargo_weight_kg' => $this->order->cargo_weight_kg,
            'cargo_volume_m3' => $this->order->cargo_volume_m3,
            'total_packages' => $this->order->total_packages,
            'estimated_distance_km' => $this->order->estimated_distance_km ?? null,
            'estimated_duration_hours' => $this->order->estimated_duration_hours ?? null,


            // costo
            'estimated_cost' => $this->order->estimated_cost,

            'notes' => $this->order->notes ?? '',
        ];

        $this->clients = \App\Models\Client::orderBy('business_name')->get();
        $this->cargoTypes = CargoType::orderBy('name')->get();

        $this->recalculateMtc();
    }

    // Recalcula cuando cambien campos clave
    public function updatedFormOrigin(): void { $this->recalculateMtc(); }
    public function updatedFormDestination(): void { $this->recalculateMtc(); }
    public function updatedFormCargoWeightKg(): void { $this->recalculateMtc(); }


  protected function recalculateMtc(): void
    {
        /** @var \App\Domains\Orders\Services\MtcReferentialService $svc */
        $svc = app(\App\Domains\Orders\Services\MtcReferentialService::class);

        $weightKg = $this->form['cargo_weight_kg'] ?? null;
        $weightKg = is_numeric($weightKg) ? (float) $weightKg : null;

        $res = $svc->estimateFromAnnexII(
            $this->form['origin'] ?? null,
            $this->form['destination'] ?? null,
            $weightKg
        );

        $this->mtc = $res;

        // ✅ Si el usuario NO lo editó manualmente, SIEMPRE pisa con el cálculo nuevo.
        if (! $this->estimatedCostManuallyEdited) {
            $this->form['estimated_cost'] = $res['ok'] ? $res['estimated_cost'] : null;
        }
    }


    public function save()
    {
        $this->authorize($this->isEdit ? 'update' : 'create', $this->isEdit ? $this->order : Order::class);

        $validated = $this->validate();

        // inyectamos metadatos MTC a la orden antes de guardar
        $validated['form']['referential_rate_sxtm'] = $this->mtc['rate_sxtm'];
        $validated['form']['referential_route_key'] = $this->mtc['route_key'];
        $validated['form']['referential_route_dest'] = $this->mtc['destination'];
        $validated['form']['referential_source'] = $this->mtc['source'];
        $validated['form']['referential_year'] = $this->mtc['year'];

        $this->saveOrderAction->execute($this->order, $validated['form'], $this->routePlan);

        session()->flash('message', $this->isEdit ? 'Orden actualizada correctamente.' : 'Orden registrada correctamente.');
        return redirect()->route('orders.index');
    }

    public function render()
    {
        $this->authorize('viewAny', Order::class);

        return view('livewire.orders.order-form');
    }
}
