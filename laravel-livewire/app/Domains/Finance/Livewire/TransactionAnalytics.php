<?php

namespace App\Domains\Finance\Livewire;

use App\Models\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TransactionAnalytics extends Component
{
    use AuthorizesRequests;

    public string $range = '90';

    public function mount(): void
    {
        $this->authorize('viewAny', Transaction::class);

        if (! array_key_exists($this->range, $this->rangeOptions())) {
            $this->range = array_key_first($this->rangeOptions());
        }
    }

    public function updatedRange(string $value): void
    {
        if (! array_key_exists($value, $this->rangeOptions())) {
            $this->range = array_key_first($this->rangeOptions());
        }
    }

    public function render()
    {
        return view('livewire.finance.transaction-analytics', [
            'summary' => $this->summary,
            'monthly' => $this->monthlyBreakdown,
            'weekly' => $this->weeklyBreakdown,
            'daily' => $this->dailyBreakdown,
            'categorySplit' => $this->categorySplit,
            'rangeOptions' => $this->rangeOptions(),
        ])->layout('components.layouts.dashboard', [
            'title' => __('Analíticas de transacciones'),
        ]);
    }

    #[Computed]
    public function summary(): array
    {
        [$start, $end] = $this->rangeBounds();

        $baseQuery = $this->baseQuery()
            ->whereBetween('occurred_on', [$start, $end]);

        $income = (clone $baseQuery)->where('type', 'income')->sum('amount');
        $expense = (clone $baseQuery)->where('type', 'expense')->sum('amount');

        return [
            'range_label' => $this->rangeLabel($start, $end),
            'income' => (float) $income,
            'expense' => (float) $expense,
            'balance' => (float) $income - (float) $expense,
        ];
    }

    #[Computed]
    public function monthlyBreakdown(): array
    {
        $end = Carbon::today()->endOfDay();
        $start = $end->copy()->startOfMonth()->subMonths(11);

        $transactions = $this->baseQuery()
            ->whereBetween('occurred_on', [$start, $end])
            ->get(['occurred_on', 'type', 'amount']);

        $periods = [];

        $cursor = $start->copy();
        for ($i = 0; $i < 12; $i++) {
            $key = $cursor->format('Y-m');
            $periods[$key] = [
                'label' => Str::title($cursor->format('M Y')),
                'income' => 0.0,
                'expense' => 0.0,
                'balance' => 0.0,
            ];

            $cursor->addMonth();
        }

        foreach ($transactions as $transaction) {
            $key = $transaction->occurred_on->format('Y-m');

            if (! isset($periods[$key])) {
                continue;
            }

            $amount = (float) $transaction->amount;

            if ($transaction->type === 'income') {
                $periods[$key]['income'] += $amount;
            } else {
                $periods[$key]['expense'] += $amount;
            }
        }

        foreach ($periods as &$period) {
            $period['balance'] = $period['income'] - $period['expense'];
        }

        return array_values($periods);
    }

    #[Computed]
    public function weeklyBreakdown(): array
    {
        $end = Carbon::today()->endOfWeek();
        $start = Carbon::today()->startOfWeek()->subWeeks(7);

        $transactions = $this->baseQuery()
            ->whereBetween('occurred_on', [$start, $end])
            ->get(['occurred_on', 'type', 'amount']);

        $periods = [];

        $cursor = $start->copy()->startOfWeek();
        for ($i = 0; $i < 8; $i++) {
            $weekStart = $cursor->copy();
            $weekEnd = $cursor->copy()->endOfWeek();
            $key = $weekStart->format('o-W');

            $periods[$key] = [
                'label' => __('Semana :number', ['number' => $weekStart->isoWeek()]),
                'range' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M'),
                'income' => 0.0,
                'expense' => 0.0,
                'balance' => 0.0,
            ];

            $cursor->addWeek();
        }

        foreach ($transactions as $transaction) {
            $weekStart = $transaction->occurred_on->copy()->startOfWeek();
            $key = $weekStart->format('o-W');

            if (! isset($periods[$key])) {
                continue;
            }

            $amount = (float) $transaction->amount;

            if ($transaction->type === 'income') {
                $periods[$key]['income'] += $amount;
            } else {
                $periods[$key]['expense'] += $amount;
            }
        }

        foreach ($periods as &$period) {
            $period['balance'] = $period['income'] - $period['expense'];
        }

        return array_values($periods);
    }

    #[Computed]
    public function dailyBreakdown(): array
    {
        $end = Carbon::today()->endOfDay();
        $start = Carbon::today()->subDays(13)->startOfDay();

        $transactions = $this->baseQuery()
            ->whereBetween('occurred_on', [$start, $end])
            ->get(['occurred_on', 'type', 'amount']);

        $periods = [];

        $cursor = $start->copy();
        while ($cursor->lessThanOrEqualTo($end)) {
            $key = $cursor->format('Y-m-d');

            $periods[$key] = [
                'label' => $cursor->format('d M'),
                'income' => 0.0,
                'expense' => 0.0,
                'balance' => 0.0,
            ];

            $cursor->addDay();
        }

        foreach ($transactions as $transaction) {
            $key = $transaction->occurred_on->format('Y-m-d');

            if (! isset($periods[$key])) {
                continue;
            }

            $amount = (float) $transaction->amount;

            if ($transaction->type === 'income') {
                $periods[$key]['income'] += $amount;
            } else {
                $periods[$key]['expense'] += $amount;
            }
        }

        foreach ($periods as &$period) {
            $period['balance'] = $period['income'] - $period['expense'];
        }

        return array_values($periods);
    }

    #[Computed]
    public function categorySplit(): array
    {
        [$start, $end] = $this->rangeBounds();

        $transactions = $this->baseQuery()
            ->whereBetween('occurred_on', [$start, $end])
            ->get(['category', 'type', 'amount']);

        $grouped = $transactions->groupBy('category')->map(function ($items) {
            $income = $items->where('type', 'income')->sum('amount');
            $expense = $items->where('type', 'expense')->sum('amount');

            return [
                'income' => (float) $income,
                'expense' => (float) $expense,
                'balance' => (float) $income - (float) $expense,
            ];
        });

        $sorted = $grouped->sortByDesc(function ($totals) {
            return max($totals['income'], $totals['expense']);
        })->take(5);

        $result = [];
        foreach ($sorted as $category => $totals) {
            $result[] = array_merge(['category' => $category], $totals);
        }

        return $result;
    }

    protected function baseQuery()
    {
        return Transaction::query()->forUser(auth()->id());
    }

    protected function rangeBounds(): array
        {
            // La clave (30, 90, 180, 365) ya representa los días
            $days = (int) $this->range;

            // fallback por si algo raro llega
            if ($days <= 0) {
                $days = 90;
            }

            $end = Carbon::today()->endOfDay();
            $start = $end->copy()->subDays($days - 1)->startOfDay();

            return [$start, $end];
        }

    protected function rangeLabel(Carbon $start, Carbon $end): string
    {
        return $start->format('d M Y') . ' - ' . $end->format('d M Y');
    }

    protected function rangeOptions(): array
    {
        return [
            '30' => __('Últimos 30 días'),
            '90' => __('Últimos 90 días'),
            '180' => __('Últimos 180 días'),
            '365' => __('Últimos 12 meses'),
        ];
    }
}
