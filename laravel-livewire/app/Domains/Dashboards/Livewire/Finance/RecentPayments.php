<?php

namespace App\Domains\Dashboards\Livewire\Finance;

use App\Models\Payment;
use App\Support\Formatters\MoneyFormatter;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecentPayments extends Component
{
    #[Computed]
    public function payments(): Collection
    {
        return Payment::query()
            ->select(['id', 'invoice_id', 'reference', 'amount', 'paid_at'])
            ->with(['invoice:id,invoice_number,client_id', 'invoice.client:id,business_name,contact_name'])
            ->latest('paid_at')
            ->limit(6)
            ->get()
            ->map(function (Payment $payment) {
                $payment->formatted_amount = MoneyFormatter::pen($payment->amount);

                return $payment;
            });
    }

    public function render(): View
    {
        return view('livewire.dashboards.finance.recent-payments');
    }
}
