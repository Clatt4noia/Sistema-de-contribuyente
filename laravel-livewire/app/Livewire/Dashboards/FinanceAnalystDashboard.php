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

        $outstandingInvoices = Invoice::with('client')
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date')
            ->take(5)
            ->get();

        $latestPayments = Payment::with('invoice')
            ->latest('paid_at')
            ->take(5)
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
