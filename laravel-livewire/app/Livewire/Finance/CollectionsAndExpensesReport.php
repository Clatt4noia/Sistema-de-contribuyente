<?php

namespace App\Livewire\Finance;

use App\Exports\CollectionsExpensesExport;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CollectionsAndExpensesReport extends Component
{
    use AuthorizesRequests;

    public string $period = 'month';
    public string $startDate;
    public string $endDate;
    public string $clientId = '';
    public string $vehicleId = '';
    public string $routeFilter = '';

    public function mount(): void
    {
        $this->authorize('view-dashboard.finance');

        $this->endDate = Carbon::now()->toDateString();
        $this->startDate = Carbon::now()->subDays(30)->toDateString();
    }

    public function render()
    {
        return view('livewire.finance.collections-and-expenses-report', [
            'summary' => $this->summary,
            'periodRows' => $this->periodRows,
            'clients' => $this->clients,
            'vehicles' => $this->vehicles,
        ])->layout('components.layouts.dashboard', [
            'title' => __('Cobranzas y gastos'),
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(new CollectionsExpensesExport($this->periodRows), 'cobranzas-gastos.xlsx');
    }

    #[Computed]
    public function clients()
    {
        return \App\Models\Client::orderBy('business_name')->get();
    }

    #[Computed]
    public function vehicles()
    {
        return \App\Models\Truck::orderBy('plate_number')->get();
    }

    #[Computed]
    public function summary(): array
    {
        $invoices = $this->invoiceQuery()->get();
        $invoiceIds = $invoices->pluck('id');
        $payments = Payment::whereIn('invoice_id', $invoiceIds)->get();
        $expenses = $this->expenseQuery()->get();

        $billed = $invoices->sum('total');
        $collected = $payments->sum('amount');
        $pending = max($billed - $collected, 0);
        $operationalExpenses = $expenses->sum('amount');

        return [
            'billed' => (float) $billed,
            'collected' => (float) $collected,
            'pending' => (float) $pending,
            'expenses' => (float) $operationalExpenses,
        ];
    }

    #[Computed]
    public function periodRows(): Collection
    {
        $period = $this->period;
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $range = $this->generatePeriods($start, $end, $period);
        $invoices = $this->invoiceQuery()->get();
        $payments = Payment::whereIn('invoice_id', $invoices->pluck('id'))->get();
        $expenses = $this->expenseQuery()->get();

        foreach ($range as &$row) {
            $row['invoiced'] = 0;
            $row['collected'] = 0;
            $row['expenses'] = 0;
        }

        foreach ($invoices as $invoice) {
            $key = $this->periodKey($invoice->issue_date, $period);
            if (isset($range[$key])) {
                $range[$key]['invoiced'] += (float) $invoice->total;
            }
        }

        foreach ($payments as $payment) {
            if (! $payment->paid_at) {
                continue;
            }

            $key = $this->periodKey($payment->paid_at, $period);
            if (isset($range[$key])) {
                $range[$key]['collected'] += (float) $payment->amount;
            }
        }

        foreach ($expenses as $expense) {
            if (! $expense->occurred_on) {
                continue;
            }

            $key = $this->periodKey($expense->occurred_on, $period);
            if (isset($range[$key])) {
                $range[$key]['expenses'] += (float) $expense->amount;
            }
        }

        return collect($range)->values();
    }

    protected function invoiceQuery()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        return Invoice::query()
            ->with(['transportGuide', 'order'])
            ->whereBetween('issue_date', [$start, $end])
            ->when($this->clientId, fn ($query) => $query->where('client_id', $this->clientId))
            ->when($this->vehicleId, function ($query) {
                $query->whereHas('transportGuide', fn ($guideQuery) => $guideQuery->where('truck_id', $this->vehicleId));
            })
            ->when($this->routeFilter, function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->where('origin', 'like', '%'.$this->routeFilter.'%')
                        ->orWhere('destination', 'like', '%'.$this->routeFilter.'%');
                });
            });
    }

    protected function expenseQuery()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $operationalCategories = ['combustible', 'peaje', 'viatico', 'operativo', 'taller'];

        return Transaction::query()
            ->where('type', 'expense')
            ->whereBetween('occurred_on', [$start, $end])
            ->when($operationalCategories, fn ($query) => $query->whereIn('category', $operationalCategories));
    }

    protected function generatePeriods(Carbon $start, Carbon $end, string $period): array
    {
        $cursor = $start->copy();
        $periods = [];

        while ($cursor->lessThanOrEqualTo($end)) {
            $key = $this->periodKey($cursor, $period);

            if (! isset($periods[$key])) {
                $periods[$key] = [
                    'label' => $this->periodLabel($cursor, $period),
                    'range' => $this->periodRange($cursor, $period),
                ];
            }

            $cursor = $this->nextCursor($cursor, $period);
        }

        return $periods;
    }

    protected function periodKey(Carbon $date, string $period): string
    {
        return match ($period) {
            'day' => $date->format('Y-m-d'),
            'week' => $date->format('o-W'),
            default => $date->format('Y-m'),
        };
    }

    protected function periodLabel(Carbon $date, string $period): string
    {
        return match ($period) {
            'day' => $date->format('d M'),
            'week' => __('Semana :number', ['number' => $date->isoWeek()]),
            default => $date->format('M Y'),
        };
    }

    protected function periodRange(Carbon $date, string $period): string
    {
        return match ($period) {
            'day' => $date->format('d/m/Y'),
            'week' => $date->startOfWeek()->format('d/m') . ' - ' . $date->endOfWeek()->format('d/m'),
            default => $date->copy()->startOfMonth()->format('d/m') . ' - ' . $date->copy()->endOfMonth()->format('d/m'),
        };
    }

    protected function nextCursor(Carbon $date, string $period): Carbon
    {
        return match ($period) {
            'day' => $date->copy()->addDay(),
            'week' => $date->copy()->addWeek(),
            default => $date->copy()->addMonth(),
        };
    }
}
