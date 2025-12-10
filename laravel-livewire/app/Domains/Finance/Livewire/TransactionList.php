<?php

namespace App\Domains\Finance\Livewire;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionList extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';
    public string $typeFilter = 'all';
    public ?string $month = null;
    public ?string $year = null;

    public bool $showModal = false;
    public ?int $transactionId = null;
    public string $formType = 'income';
    public string $category = '';
    public string $amount = '';
    public string $occurred_on = '';
    public ?string $description = null;

    protected $paginationTheme = 'tailwind';

    protected array $rules = [
        'formType' => 'required|in:income,expense',
        'category' => 'required|string|max:100',
        'amount' => 'required|numeric|min:0.01',
        'occurred_on' => 'required|date',
        'description' => 'nullable|string',
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Transaction::class);

        $today = Carbon::now();
        $this->occurred_on = $today->format('Y-m-d');
        $this->year = (string) $today->year;
        $this->month = str_pad((string) $today->month, 2, '0', STR_PAD_LEFT);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'typeFilter', 'month', 'year'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        return view('livewire.finance.transaction-list', [
            'transactions' => $this->transactions,
            'summary' => $this->summary,
            'availableYears' => $this->availableYears,
        ])->layout('components.layouts.dashboard', [
            'title' => __('Control de transacciones'),
        ]);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEditModal(int $transactionId): void
    {
        $transaction = $this->transactionsQuery()
            ->where('transactions.id', $transactionId)
            ->firstOrFail();

        $this->authorize('view', $transaction);

        $this->transactionId = $transaction->id;
        $this->formType = $transaction->type;
        $this->category = $transaction->category;
        $this->amount = number_format((float) $transaction->amount, 2, '.', '');
        $this->occurred_on = $transaction->occurred_on->format('Y-m-d');
        $this->description = $transaction->description;

        $this->resetValidation();
        $this->showModal = true;
    }

    public function saveTransaction(): void
    {
        $data = $this->validate();

        $attributes = [
            'type' => $data['formType'],
            'category' => $data['category'],
            'amount' => $data['amount'],
            'occurred_on' => Carbon::parse($data['occurred_on'])->format('Y-m-d'),
            'description' => $data['description'],
        ];

        if ($this->transactionId) {
            $transaction = $this->transactionsQuery()
                ->where('transactions.id', $this->transactionId)
                ->firstOrFail();

            $this->authorize('update', $transaction);

            $transaction->fill($attributes);
            $transaction->save();

            $message = __('Movimiento actualizado correctamente.');
        } else {
            $this->authorize('create', Transaction::class);

            $transaction = new Transaction($attributes);
            $transaction->user_id = auth()->id();
            $transaction->save();

            $message = __('Movimiento registrado correctamente.');
        }

        session()->flash('message', $message);

        $this->closeModal();
        $this->resetForm();
        $this->resetPage();
    }

    public function deleteTransaction(int $transactionId): void
    {
        $transaction = $this->transactionsQuery()
            ->where('transactions.id', $transactionId)
            ->firstOrFail();

        $this->authorize('delete', $transaction);

        $transaction->delete();

        session()->flash('message', __('Movimiento eliminado correctamente.'));

        $this->resetPage();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    protected function resetForm(): void
    {
        $today = Carbon::now();

        $this->transactionId = null;
        $this->formType = 'income';
        $this->category = '';
        $this->amount = '';
        $this->occurred_on = $today->format('Y-m-d');
        $this->description = null;
    }

    #[Computed]
    public function transactions(): LengthAwarePaginator
    {
        return $this->transactionsQuery()
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $baseQuery = $this->transactionsQuery();

        $income = (clone $baseQuery)->where('type', 'income')->sum('amount');
        $expense = (clone $baseQuery)->where('type', 'expense')->sum('amount');

        return [
            'income' => (float) $income,
            'expense' => (float) $expense,
            'balance' => (float) $income - (float) $expense,
        ];
    }

    #[Computed]
    public function availableYears(): array
    {
        $years = Transaction::query()
            ->forUser(auth()->id())
            ->selectRaw("DISTINCT EXTRACT(YEAR FROM occurred_on)::int AS year")
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (string) $year)
            ->all();

        if (empty($years)) {
            $years[] = (string) Carbon::now()->year;
        }

        return $years;
    }


    protected function transactionsQuery(): Builder
    {
        return Transaction::query()
            ->with('user')
            ->forUser(auth()->id())
            ->searchTerm($this->search)
            ->ofType($this->typeFilter)
            ->forYear($this->year)
            ->forMonth($this->month);
    }
}
