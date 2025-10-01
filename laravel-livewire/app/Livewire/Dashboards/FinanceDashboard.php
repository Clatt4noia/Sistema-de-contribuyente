<?php

namespace App\Livewire\Dashboards;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class FinanceDashboard extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('view-dashboard.finance');
    }

    public function render()
    {
        $totals = [
            'issued' => Invoice::sum('total'),
            'paid' => Invoice::where('status', 'paid')->sum('total'),
            'pending' => Invoice::whereIn('status', ['pending', 'overdue'])->sum('total'),
            'payments' => Payment::sum('amount'),
        ];

        $recentInvoices = Invoice::with('client')
            ->latest('issue_date')
            ->take(5)
            ->get();

        $recentPayments = Payment::with('invoice')
            ->latest('paid_at')
            ->take(5)
            ->get();

        return view('livewire.dashboards.finance-dashboard', [
            'totals' => $totals,
            'recentInvoices' => $recentInvoices,
            'recentPayments' => $recentPayments,
        ])->layout('components.layouts.dashboard', [
            'title' => __('Panel financiero'),
        ]);
    }
}
