<?php

namespace App\Livewire\Billing;

use App\Models\Invoice;
use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $client_id = '';
    public string $order_id = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'client_id' => ['except' => ''],
        'order_id' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingClientId(): void
    {
        $this->resetPage();
    }

    public function updatingOrderId(): void
    {
        $this->resetPage();
    }

    public function markAsPaid(int $invoiceId): void
    {
        $invoice = Invoice::with('payments')->find($invoiceId);
        if (!$invoice) {
            return;
        }

        if ($invoice->balance > 0) {
            session()->flash('error', 'La factura aun tiene saldo pendiente.');
            return;
        }

        $invoice->status = 'paid';
        $invoice->save();

        session()->flash('message', 'Factura marcada como pagada.');
    }

    public function render()
    {
        $invoices = Invoice::query()
            ->with(['client', 'order'])
            ->when($this->search, function ($query) {
                $query->where(function ($searchQuery) {
                    $searchQuery->where('invoice_number', 'like', '%' . $this->search . '%')
                        ->orWhere('notes', 'like', '%' . $this->search . '%')
                        ->orWhereHas('client', function ($clientQuery) {
                            $clientQuery->where('business_name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->client_id, fn ($query) => $query->where('client_id', $this->client_id))
            ->when($this->order_id, fn ($query) => $query->where('order_id', $this->order_id))
            ->orderByDesc('issue_date')
            ->paginate(10);

        $totals = [
            'issued' => Invoice::where('status', 'issued')->sum('total'),
            'paid' => Invoice::where('status', 'paid')->sum('total'),
            'overdue' => Invoice::where('status', 'overdue')->sum('total'),
            'balance' => Invoice::all()->sum->balance,
        ];

        $clients = \App\Models\Client::orderBy('business_name')->get();
        $orders = Order::orderBy('reference')->get();

        return view('livewire.billing.invoice-list', [
            'invoices' => $invoices,
            'totals' => $totals,
            'clients' => $clients,
            'orders' => $orders,
        ]);
    }
}
