<?php

namespace App\Domains\Billing\Livewire;

use App\Domains\Billing\Actions\SendElectronicInvoiceAction;

use App\Models\Invoice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ElectronicInvoiceForm extends Component
{
    use AuthorizesRequests;

    public SendElectronicInvoiceAction $sendElectronicInvoiceAction;
    public Invoice $invoice;
    public array $items = [];
    public array $newItem = [
        'description' => '',
        'quantity' => 1,
        'unit_price' => 0,
        'tax_percentage' => 18,
        'tax_amount' => 0,
        'taxable_amount' => 0,
        'unit_code' => 'NIU',
        'price_type_code' => '01',
        'tax_exemption_reason' => '10',
    ];

    public bool $confirmationOpen = false;

    protected function rules(): array
    {
        return [
            'newItem.description' => 'required|string|max:255',
            'newItem.quantity' => 'required|numeric|min:0.01',
            'newItem.unit_price' => 'required|numeric|min:0',
            'newItem.tax_percentage' => 'required|numeric|min:0',
            'newItem.unit_code' => 'required|string|max:3',
            'newItem.price_type_code' => 'required|string|max:2',
            'newItem.tax_exemption_reason' => 'required|string|max:2',
        ];
    }

    public function boot(SendElectronicInvoiceAction $sendElectronicInvoiceAction): void
    {
        $this->sendElectronicInvoiceAction = $sendElectronicInvoiceAction;
    }

    public function mount(Invoice $invoice): void
    {
        $this->authorize('view', $invoice);
        $this->invoice = $invoice->load('client');

        $this->items = $invoice->metadata['items'] ?? [];
    }

    public function addItem(): void
    {
        $this->validate();

        $taxable = round($this->newItem['quantity'] * $this->newItem['unit_price'], 2);
        $taxAmount = round($taxable * ($this->newItem['tax_percentage'] / 100), 2);

        $this->items[] = [
            'description' => $this->newItem['description'],
            'quantity' => (float) $this->newItem['quantity'],
            'unit_price' => (float) $this->newItem['unit_price'],
            'tax_percentage' => (float) $this->newItem['tax_percentage'],
            'tax_amount' => $taxAmount,
            'taxable_amount' => $taxable,
            'total' => $taxable,
            'unit_code' => $this->newItem['unit_code'],
            'price_type_code' => $this->newItem['price_type_code'],
            'tax_exemption_reason' => $this->newItem['tax_exemption_reason'],
        ];

        $this->reset('newItem');
        $this->newItem = [
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'tax_percentage' => 18,
            'tax_amount' => 0,
            'taxable_amount' => 0,
            'unit_code' => 'NIU',
            'price_type_code' => '01',
            'tax_exemption_reason' => '10',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function confirmSend(): void
    {
        $this->confirmationOpen = true;
    }

    public function sendToSunat(): void
    {
        $this->authorize('update', $this->invoice);

        $this->sendElectronicInvoiceAction->execute($this->invoice, $this->items);

        session()->flash('message', 'Se envió la factura electrónica a SUNAT. Revisa el estado en unos minutos.');
        $this->redirectRoute('billing.invoices.index');

    }

    protected function calculateTotals(): array
    {
        $subtotal = collect($this->items)->sum('total');
        $tax = collect($this->items)->sum('tax_amount');

        return [
            'subtotal' => round($subtotal, 2),
            'taxable' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'total' => round($subtotal + $tax, 2),
        ];
    }

    public function render()
    {
        $this->authorize('view', $this->invoice);

        $totals = $this->calculateTotals();

        return view('livewire.billing.electronic-invoice-form', [
            'totals' => $totals,
        ]);
    }
}
