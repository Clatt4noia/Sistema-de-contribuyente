<?php

namespace App\Livewire\Billing;

use App\Jobs\SendElectronicInvoice;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Product;
use App\Models\SunatDocumentType;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateInvoice extends Component
{
    public string $documentType = '';

    public string $series = 'F001';

    public string $correlative = '';

    public string $currency = 'PEN';

    public string $issueDate;

    public ?string $dueDate = null;

    public string $clientSearch = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $clientResults = [];

    /**
     * @var array<string, mixed>|null
     */
    public ?array $selectedClient = null;

    public string $productSearch = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $productResults = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $invoiceItems = [];

    public float $subtotal = 0.0;

    public float $igv = 0.0;

    public float $total = 0.0;

    public function mount(): void
    {
        $this->issueDate = now()->format('Y-m-d');
        $this->dueDate = now()->format('Y-m-d');

        $this->documentType = SunatDocumentType::query()
            ->orderBy('code')
            ->value('code') ?? '01';

        $this->series = $this->defaultSeriesForDocument($this->documentType);
        $this->correlative = $this->suggestNextCorrelative();
    }

    public function updatedDocumentType(string $value): void
    {
        $this->documentType = $value;

        if ($value === '03' && Str::startsWith($this->series, 'F')) {
            $this->series = 'B001';
        }

        if ($value !== '03' && Str::startsWith($this->series, 'B')) {
            $this->series = 'F001';
        }

        $this->correlative = $this->suggestNextCorrelative();
    }

    public function updatedSeries(string $value): void
    {
        $this->series = Str::upper(Str::substr($value, 0, 4));
        $this->correlative = $this->suggestNextCorrelative();
    }

    public function updatedClientSearch(): void
    {
        $term = trim($this->clientSearch);

        if (strlen($term) < 2) {
            $this->clientResults = [];

            return;
        }

        $this->clientResults = Client::query()
            ->when(Schema::hasColumn('clients', 'business_name'), fn ($query) => $query->orderBy('business_name'))
            ->where(function ($query) use ($term) {
                $likeTerm = '%'.$term.'%';

                $query->when(Schema::hasColumn('clients', 'business_name'), fn ($q) => $q->orWhere('business_name', 'like', $likeTerm))
                    ->when(Schema::hasColumn('clients', 'social_reason'), fn ($q) => $q->orWhere('social_reason', 'like', $likeTerm))
                    ->when(Schema::hasColumn('clients', 'tax_id'), fn ($q) => $q->orWhere('tax_id', 'like', $likeTerm))
                    ->when(Schema::hasColumn('clients', 'document_number'), fn ($q) => $q->orWhere('document_number', 'like', $likeTerm));
            })
            ->limit(5)
            ->get()
            ->map(fn (Client $client) => [
                'id' => $client->getKey(),
                'name' => $client->business_name ?? $client->social_reason ?? $client->contact_name ?? 'Cliente',
                'document' => $client->tax_id ?? $client->document_number ?? '',
                'email' => $client->email,
                'phone' => $client->phone,
                'billing_address' => $client->billing_address ?? null,
            ])
            ->all();
    }

    public function selectClient(int $clientId): void
    {
        $client = Client::find($clientId);

        if (! $client) {
            return;
        }

        $this->selectedClient = [
            'id' => $client->getKey(),
            'name' => $client->business_name ?? $client->social_reason ?? $client->contact_name ?? 'Cliente',
            'document' => $client->tax_id ?? $client->document_number ?? '',
            'email' => $client->email,
            'phone' => $client->phone,
            'billing_address' => $client->billing_address ?? null,
        ];

        $this->clientSearch = $this->selectedClient['name'];
        $this->clientResults = [];
    }

    public function updatedProductSearch(): void
    {
        $term = trim($this->productSearch);

        if (strlen($term) < 2) {
            $this->productResults = [];

            return;
        }

        $this->productResults = Product::query()
            ->where(function ($query) use ($term) {
                $likeTerm = '%'.$term.'%';

                $query->when(Schema::hasColumn('products', 'code'), fn ($q) => $q->orWhere('code', 'like', $likeTerm))
                    ->when(Schema::hasColumn('products', 'sku'), fn ($q) => $q->orWhere('sku', 'like', $likeTerm))
                    ->when(Schema::hasColumn('products', 'name'), fn ($q) => $q->orWhere('name', 'like', $likeTerm))
                    ->when(Schema::hasColumn('products', 'description'), fn ($q) => $q->orWhere('description', 'like', $likeTerm));
            })
            ->limit(5)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->getKey(),
                'code' => $product->code ?? $product->sku ?? null,
                'description' => $product->description ?? $product->name ?? 'Producto',
                'unit_price' => (float) ($product->unit_price ?? $product->sale_price ?? 0),
            ])
            ->all();
    }

    public function addProduct(int $productId): void
    {
        $product = Product::find($productId);

        if (! $product) {
            return;
        }

        $existingIndex = collect($this->invoiceItems)
            ->search(fn (array $item) => (int) ($item['product_id'] ?? 0) === $product->getKey());

        if ($existingIndex !== false) {
            $this->invoiceItems[$existingIndex]['quantity'] = (float) ($this->invoiceItems[$existingIndex]['quantity'] ?? 0) + 1;
            $this->recalculateItem($existingIndex);
        } else {
            $this->invoiceItems[] = [
                'product_id' => $product->getKey(),
                'sku' => $product->sku ?? $product->code ?? 'ITEM-'.$product->getKey(),
                'description' => $product->description ?? $product->name ?? 'Producto',
                'quantity' => 1,
                'unit_price' => (float) ($product->unit_price ?? $product->sale_price ?? 0),
                'unit_code' => 'NIU',
                'price_type_code' => '01',
                'tax_percentage' => (float) ($product->tax_percentage ?? 18),
                'tax_exemption_reason' => '10',
            ];

            $this->recalculateItem(array_key_last($this->invoiceItems));
        }

        $this->productSearch = '';
        $this->productResults = [];
        $this->calculateTotals();
    }

    public function updateQuantity(int $index, $quantity): void
    {
        if (! isset($this->invoiceItems[$index])) {
            return;
        }

        $qty = max((float) $quantity, 0);
        $this->invoiceItems[$index]['quantity'] = $qty > 0 ? $qty : 1;

        $this->recalculateItem($index);
        $this->calculateTotals();
    }

    public function removeItem(int $index): void
    {
        if (! isset($this->invoiceItems[$index])) {
            return;
        }

        unset($this->invoiceItems[$index]);
        $this->invoiceItems = array_values($this->invoiceItems);

        $this->calculateTotals();
    }

    public function saveInvoice(): void
    {
        $this->resetValidation();

        $this->validate($this->rules());

        if (! $this->selectedClient || empty($this->selectedClient['id'])) {
            $this->addError('clientSearch', 'Debe seleccionar un cliente de la lista.');

            return;
        }

        if (empty($this->invoiceItems)) {
            $this->addError('invoiceItems', 'Debe agregar al menos un producto.');

            return;
        }

        $client = Client::find($this->selectedClient['id']);

        if (! $client) {
            $this->addError('clientSearch', 'El cliente seleccionado ya no existe.');

            return;
        }

        $invoice = null;

        DB::transaction(function () use (&$invoice, $client): void {
            $issueDate = Carbon::parse($this->issueDate);
            $dueDate = $this->dueDate ? Carbon::parse($this->dueDate) : null;

            $invoice = Invoice::create([
                'client_id' => $client->getKey(),
                'document_type' => $this->documentType,
                'series' => $this->series,
                'correlative' => $this->correlative,
                'invoice_number' => $this->series.'-'.$this->correlative,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'ruc_emisor' => Config::get('billing.sunat.ruc'),
                'ruc_receptor' => $client->tax_id ?? $client->document_number ?? null,
                'currency' => $this->currency,
                'subtotal' => $this->subtotal,
                'taxable_amount' => $this->subtotal,
                'tax' => $this->igv,
                'total' => $this->total,
                'status' => 'issued',
                'metadata' => [
                    'items' => $this->invoiceItems,
                ],
            ]);

            foreach ($this->invoiceItems as $item) {
                InvoiceDetail::create([
                    'invoice_id' => $invoice->getKey(),
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'] ?? 'Producto',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'tax_percentage' => $item['tax_percentage'] ?? 18,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'taxable_amount' => $item['taxable_amount'] ?? ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0),
                    'total' => $item['total'] ?? 0,
                    'metadata' => Arr::only($item, ['sku', 'unit_code', 'price_type_code', 'tax_exemption_reason']),
                ]);
            }
        });

        if (! $invoice) {
            $this->addError('save', 'No fue posible guardar la factura.');

            return;
        }

        $companyData = [
            'ruc' => Config::get('billing.sunat.ruc'),
            'legal_name' => Config::get('app.name', 'Carlos Gabriel Transporte S.A.C.'),
            'commercial_name' => Config::get('app.name', 'Carlos Gabriel Transporte S.A.C.'),
        ];

        $customerData = [
            'ruc' => $client->tax_id ?? $client->document_number ?? '',
            'scheme_id' => strlen((string) ($client->tax_id ?? $client->document_number ?? '')) === 11 ? '6' : '1',
            'name' => $this->selectedClient['name'],
        ];

        SendElectronicInvoice::dispatch($invoice->fresh(), $this->formattedItemsForDispatch(), $companyData, $customerData)
            ->onQueue(Config::get('billing.queues.sunat', 'sunat'));

        session()->flash('message', 'Factura registrada y enviada para procesamiento en SUNAT.');
        $this->redirectRoute('billing.invoices.index');
    }

    public function render()
    {
        return view('livewire.billing.create-invoice', [
            'documentTypes' => SunatDocumentType::query()->orderBy('code')->get(),
        ]);
    }

    protected function recalculateItem(int $index): void
    {
        if (! isset($this->invoiceItems[$index])) {
            return;
        }

        $quantity = (float) ($this->invoiceItems[$index]['quantity'] ?? 1);
        $unitPrice = (float) ($this->invoiceItems[$index]['unit_price'] ?? 0);
        $taxPercentage = (float) ($this->invoiceItems[$index]['tax_percentage'] ?? 18);

        $taxable = round($quantity * $unitPrice, 2);
        $taxAmount = round($taxable * ($taxPercentage / 100), 2);

        $this->invoiceItems[$index]['taxable_amount'] = $taxable;
        $this->invoiceItems[$index]['tax_amount'] = $taxAmount;
        $this->invoiceItems[$index]['total'] = round($taxable + $taxAmount, 2);
    }

    protected function calculateTotals(): void
    {
        $collection = EloquentCollection::make($this->invoiceItems);
        $this->subtotal = round($collection->sum('taxable_amount'), 2);
        $this->igv = round($collection->sum('tax_amount'), 2);
        $this->total = round($collection->sum('total'), 2);
    }

    protected function formattedItemsForDispatch(): array
    {
        return collect($this->invoiceItems)
            ->map(fn (array $item) => [
                'description' => $item['description'] ?? 'Producto',
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'] ?? 0,
                'tax_percentage' => $item['tax_percentage'] ?? 18,
                'tax_amount' => $item['tax_amount'] ?? 0,
                'taxable_amount' => $item['taxable_amount'] ?? 0,
                'total' => $item['total'] ?? 0,
                'unit_code' => $item['unit_code'] ?? 'NIU',
                'price_type_code' => $item['price_type_code'] ?? '01',
                'tax_exemption_reason' => $item['tax_exemption_reason'] ?? '10',
                'tax_code' => $item['tax_code'] ?? 'S',
                'sku' => $item['sku'] ?? null,
            ])
            ->all();
    }

    protected function rules(): array
    {
        return [
            'documentType' => 'required|string|exists:sunat_document_types,code',
            'series' => 'required|string|max:4',
            'correlative' => 'required|string|max:8',
            'currency' => 'required|string|in:PEN,USD',
            'issueDate' => 'required|date',
            'dueDate' => 'nullable|date|after_or_equal:issueDate',
        ];
    }

    protected function defaultSeriesForDocument(string $documentType): string
    {
        return $documentType === '03' ? 'B001' : 'F001';
    }

    protected function suggestNextCorrelative(): string
    {
        $last = Invoice::query()
            ->where('document_type', $this->documentType)
            ->where('series', $this->series)
            ->max('correlative');

        $numeric = (int) ltrim((string) $last, '0');
        $numeric++;

        return str_pad((string) $numeric, 8, '0', STR_PAD_LEFT);
    }
}
