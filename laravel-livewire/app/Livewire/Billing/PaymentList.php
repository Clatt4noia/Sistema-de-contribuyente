<?php

namespace App\Livewire\Billing;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $invoice_id = '';
    public string $method = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'invoice_id' => ['except' => ''],
        'method' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingInvoiceId(): void
    {
        $this->resetPage();
    }

    public function updatingMethod(): void
    {
        $this->resetPage();
    }

    public function deletePayment(int $paymentId): void
    {
        $payment = Payment::find($paymentId);
        if (!$payment) {
            return;
        }

        DB::transaction(function () use ($payment) {
            $invoice = $payment->invoice;
            $payment->delete();

            if ($invoice) {
                if ($invoice->balance <= 0 && $invoice->status !== 'paid') {
                    $invoice->status = 'paid';
                }

                if ($invoice->balance > 0 && $invoice->status === 'paid') {
                    $invoice->status = $invoice->due_date && $invoice->due_date->isPast() ? 'overdue' : 'issued';
                }

                $invoice->save();
            }
        });

        session()->flash('message', 'Pago eliminado correctamente.');
        $this->resetPage();
    }

    public function render()
    {
        $payments = Payment::query()
            ->with('invoice.client')
            ->when($this->search, function ($query) {
                $query->where(function ($searchQuery) {
                    $searchQuery->where('method', 'like', '%' . $this->search . '%')
                        ->orWhere('reference', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->invoice_id, fn ($query) => $query->where('invoice_id', $this->invoice_id))
            ->when($this->method, fn ($query) => $query->where('method', $this->method))
            ->orderByDesc('paid_at')
            ->paginate(10);

        $invoices = Invoice::orderBy('invoice_number')->get();

        $totals = [
            'received' => Payment::sum('amount'),
            'count' => Payment::count(),
        ];

        return view('livewire.billing.payment-list', [
            'payments' => $payments,
            'invoices' => $invoices,
            'totals' => $totals,
        ]);
    }
}
