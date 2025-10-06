<?php

namespace App\Livewire\Dashboards;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class FinanceAnalystDashboard extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('view-dashboard.finance-analyst');
    }

    public function render()
    {
        $metrics = [
            'pendingCount' => Invoice::whereIn('status', ['pending', 'overdue'])->count(),
            'overdueCount' => Invoice::where('status', 'overdue')->count(),
            'recentPayments' => Payment::where('paid_at', '>=', now()->subDays(30))->sum('amount'),
        ];

        $outstandingInvoices = Invoice::query()
            ->select(['id', 'invoice_number', 'client_id', 'status', 'due_date', 'total'])
            ->with(['client:id,business_name,contact_name'])
            ->withSum('payments as payments_sum_amount', 'amount')
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $latestPayments = Payment::query()
            ->select(['id', 'invoice_id', 'reference', 'amount', 'paid_at'])
            ->with(['invoice:id,invoice_number,client_id', 'invoice.client:id,business_name,contact_name'])
            ->latest('paid_at')
            ->limit(5)
            ->get();

        return view('livewire.dashboards.finance-analyst-dashboard', [
            'metrics' => $metrics,
            'outstandingInvoices' => $outstandingInvoices,
            'latestPayments' => $latestPayments,
        ])->layout('components.layouts.dashboard', [
            'title' => __('Panel de analista financiero'),
        ]);
    }
}
