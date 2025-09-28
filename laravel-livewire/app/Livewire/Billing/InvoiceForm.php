<?php

namespace App\Livewire\Billing;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class InvoiceForm extends Component
{
    public Invoice $invoice;
    public bool $isEdit = false;
    public $clients;
    public $orders;

    protected function rules(): array
    {
        $invoiceId = $this->invoice->id ?? 'NULL';

        return [
            'invoice.invoice_number' => 'required|string|max:30|unique:invoices,invoice_number,' . $invoiceId,
            'invoice.client_id' => 'required|exists:clients,id',
            'invoice.order_id' => 'nullable|exists:orders,id',
            'invoice.issue_date' => 'required|date',
            'invoice.due_date' => 'nullable|date|after_or_equal:invoice.issue_date',
            'invoice.subtotal' => 'required|numeric|min:0',
            'invoice.tax' => 'nullable|numeric|min:0',
            'invoice.total' => 'nullable|numeric|min:0',
            'invoice.status' => 'required|in:draft,issued,paid,overdue',
            'invoice.notes' => 'nullable|string',
        ];
    }

    public function mount($invoice = null): void
    {
        if ($invoice) {
            $this->invoice = $invoice;
            $this->isEdit = true;
        } else {
            $this->invoice = new Invoice([
                'status' => 'draft',
                'issue_date' => now()->format('Y-m-d'),
                'tax' => 0,
            ]);
        }

        if ($this->invoice->issue_date instanceof Carbon) {
            $this->invoice->issue_date = $this->invoice->issue_date->format('Y-m-d');
        }

        if ($this->invoice->due_date instanceof Carbon) {
            $this->invoice->due_date = $this->invoice->due_date->format('Y-m-d');
        }

        $this->clients = \App\Models\Client::orderBy('business_name')->get();
        $this->orders = Order::orderBy('reference')->get();
    }

    public function updatedInvoiceOrderId($orderId): void
    {
        if ($orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $this->invoice->client_id = $order->client_id;
            }
        }
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $this->invoice->issue_date = Carbon::parse($this->invoice->issue_date);
            $this->invoice->due_date = $this->invoice->due_date ? Carbon::parse($this->invoice->due_date) : null;
            $this->invoice->tax = $this->invoice->tax ?? 0;
            $this->invoice->total = $this->invoice->total ?? ($this->invoice->subtotal + $this->invoice->tax);

            if ($this->invoice->status === 'issued' && $this->invoice->due_date && $this->invoice->due_date->isPast()) {
                $this->invoice->status = 'overdue';
            }

            $this->invoice->save();
        });

        session()->flash('message', $this->isEdit ? 'Factura actualizada correctamente.' : 'Factura generada correctamente.');
        return redirect()->route('billing.invoices.index');
    }

    public function render()
    {
        return view('livewire.billing.invoice-form');
    }
}
