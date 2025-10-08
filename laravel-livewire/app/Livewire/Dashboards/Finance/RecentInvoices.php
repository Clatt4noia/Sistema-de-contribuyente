<?php

namespace App\Livewire\Dashboards\Finance;

use App\Models\Invoice;
use App\Support\Formatters\MoneyFormatter;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecentInvoices extends Component
{
    #[Computed]
    public function invoices(): Collection
    {
        return Invoice::query()
            ->select(['id', 'invoice_number', 'client_id', 'status', 'issue_date', 'due_date', 'total'])
            ->with(['client:id,business_name,contact_name'])
            ->withSum('payments as payments_sum_amount', 'amount')
            ->latest('issue_date')
            ->limit(6)
            ->get()
            ->map(function (Invoice $invoice) {
                $invoice->formatted_total = MoneyFormatter::pen($invoice->total);

                return $invoice;
            });
    }

    public function render(): View
    {
        return view('livewire.dashboards.finance.recent-invoices');
    }
}
