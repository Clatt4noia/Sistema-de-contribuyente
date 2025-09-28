<?php

namespace App\Livewire\Billing;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PaymentForm extends Component
{
    public Payment $payment;
    public bool $isEdit = false;
    public $invoices;

    protected function rules(): array
    {
        return [
            'payment.invoice_id' => 'required|exists:invoices,id',
            'payment.amount' => 'required|numeric|min:0.01',
            'payment.paid_at' => 'required|date',
            'payment.method' => 'nullable|string|max:50',
            'payment.reference' => 'nullable|string|max:100',
            'payment.notes' => 'nullable|string',
        ];
    }

    public function mount($payment = null): void
    {
        if ($payment) {
            $this->payment = $payment;
            $this->isEdit = true;
        } else {
            $this->payment = new Payment([
                'paid_at' => now()->format('Y-m-d'),
            ]);
        }

        if ($this->payment->paid_at instanceof Carbon) {
            $this->payment->paid_at = $this->payment->paid_at->format('Y-m-d');
        }

        $this->invoices = Invoice::orderByDesc('issue_date')->get();
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $this->payment->paid_at = Carbon::parse($this->payment->paid_at);
            $this->payment->save();

            $invoice = $this->payment->invoice;
            if ($invoice) {
                if ($invoice->balance <= 0) {
                    $invoice->status = 'paid';
                } elseif ($invoice->due_date && $invoice->due_date->isPast()) {
                    $invoice->status = 'overdue';
                } else {
                    $invoice->status = 'issued';
                }

                $invoice->save();
            }
        });

        session()->flash('message', $this->isEdit ? 'Pago actualizado correctamente.' : 'Pago registrado correctamente.');
        return redirect()->route('billing.payments.index');
    }

    public function render()
    {
        return view('livewire.billing.payment-form');
    }
}
