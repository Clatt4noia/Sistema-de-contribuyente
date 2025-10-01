<?php

namespace App\Livewire\Dashboards;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ClientDashboard extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('view-dashboard.client');
    }

    public function render()
    {
        $user = auth()->user();

        $orderScope = fn ($query) => $query->where('email', $user->email);

        $orders = Order::with('client')
            ->whereHas('client', $orderScope)
            ->latest('pickup_date')
            ->take(5)
            ->get();

        $invoices = Invoice::with('client')
            ->whereHas('client', $orderScope)
            ->latest('issue_date')
            ->take(5)
            ->get();

        $metrics = [
            'orders' => Order::whereHas('client', $orderScope)->count(),
            'invoices' => Invoice::whereHas('client', $orderScope)->count(),
            'openInvoices' => Invoice::whereHas('client', $orderScope)
                ->where('status', '!=', 'paid')
                ->count(),
        ];

        return view('livewire.dashboards.client-dashboard', [
            'metrics' => $metrics,
            'orders' => $orders,
            'invoices' => $invoices,
            'contactEmail' => $user->email,
        ])->layout('components.layouts.dashboard', [
            'title' => __('Portal del cliente'),
        ]);
    }
}
