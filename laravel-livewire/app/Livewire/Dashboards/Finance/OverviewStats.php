<?php

namespace App\Livewire\Dashboards\Finance;

use App\Models\Invoice;
use App\Models\Payment;
use App\Support\Formatters\MoneyFormatter;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Illuminate\View\View;

class OverviewStats extends Component
{
    #[Computed]
    public function metrics(): array
    {
        $now = Carbon::now();

        $currentMonthTotal = Invoice::query()
            ->whereBetween('issue_date', [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ])
            ->sum('total');

        $pendingPayments = Invoice::query()
            ->whereIn('status', ['pending', 'overdue'])
            ->sum('total');

        $lastThirtyDays = Payment::query()
            ->whereDate('paid_at', '>=', $now->copy()->subDays(30))
            ->sum('amount');

        return [
            'current_month' => MoneyFormatter::pen($currentMonthTotal),
            'pending_payments' => MoneyFormatter::pen($pendingPayments),
            'last_thirty_days' => MoneyFormatter::pen($lastThirtyDays),
        ];
    }

    public function render(): View
    {
        return view('livewire.dashboards.finance.overview-stats');
    }
}
