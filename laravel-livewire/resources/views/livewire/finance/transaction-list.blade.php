<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-token">{{ __('Control de caja') }}</h1>
            <p class="mt-1 text-sm text-token-muted">
                {{ __('Registra manualmente tus ingresos y egresos para llevar un seguimiento del flujo de caja.') }}
            </p>
        </div>

        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
            <a href="{{ route('finance.transactions.analytics') }}" class="btn btn-ghost w-full sm:w-auto">
                {{ __('Ver analíticas') }}
            </a>
            <button type="button" wire:click="openCreateModal" class="btn btn-primary w-full sm:w-auto">
                {{ __('Nuevo movimiento') }}
            </button>
        </div>

    </div>

    @if (session()->has('message'))
        <div class="alert alert-success" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="surface-card rounded-2xl border border-token bg-elevated p-4 shadow-sm">
            <p class="text-sm text-token-muted">{{ __('Ingresos') }}</p>
            <p class="mt-1 text-2xl font-semibold text-success-strong">
                {{ \App\Support\Formatters\MoneyFormatter::pen($summary['income'] ?? 0) }}
            </p>
        </div>

        <div class="surface-card rounded-2xl border border-token bg-elevated p-4 shadow-sm">
            <p class="text-sm text-token-muted">{{ __('Egresos') }}</p>
            <p class="mt-1 text-2xl font-semibold text-danger-strong">
                {{ \App\Support\Formatters\MoneyFormatter::pen($summary['expense'] ?? 0) }}
            </p>
        </div>

        <div class="surface-card rounded-2xl border border-token bg-elevated p-4 shadow-sm">
            <p class="text-sm text-token-muted">{{ __('Balance') }}</p>
            <p class="mt-1 text-2xl font-semibold text-token">
                {{ \App\Support\Formatters\MoneyFormatter::pen($summary['balance'] ?? 0) }}
            </p>
        </div>
    </div>

    <div class="surface-card overflow-hidden rounded-2xl border border-token bg-elevated shadow-lg">
        <div class="space-y-4 border-b border-token px-4 py-4">
            <div class="grid gap-4 md:grid-cols-4">
                <input
                    type="text"
                    wire:model.debounce.400ms="search"
                    placeholder="{{ __('Buscar por categoría o detalle...') }}"
                    class="form-control"
                />

                <select wire:model="typeFilter" class="form-control">
                    <option value="all">{{ __('Todos los tipos') }}</option>
                    <option value="income">{{ __('Ingresos') }}</option>
                    <option value="expense">{{ __('Egresos') }}</option>
                </select>

                @php
                    $monthOptions = [
                        '01' => __('Enero'),
                        '02' => __('Febrero'),
                        '03' => __('Marzo'),
                        '04' => __('Abril'),
                        '05' => __('Mayo'),
                        '06' => __('Junio'),
                        '07' => __('Julio'),
                        '08' => __('Agosto'),
                        '09' => __('Septiembre'),
                        '10' => __('Octubre'),
                        '11' => __('Noviembre'),
                        '12' => __('Diciembre'),
                    ];
                @endphp
                <select wire:model="month" class="form-control">
                    <option value="">{{ __('Todos los meses') }}</option>
                    @foreach ($monthOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select wire:model="year" class="form-control">
                    <option value="">{{ __('Todos los años') }}</option>
                    @foreach ($availableYears as $availableYear)
                        <option value="{{ $availableYear }}">{{ $availableYear }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-md">
                <thead>
                    <tr class="table-row">
                        <th class="table-header">{{ __('Fecha') }}</th>
                        <th class="table-header">{{ __('Tipo') }}</th>
                        <th class="table-header">{{ __('Categoría') }}</th>
                        <th class="table-header">{{ __('Detalle') }}</th>
                        <th class="table-header">{{ __('Monto') }}</th>
                        <th class="table-header">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        @php
                            $typeStyles = [
                                'income' => 'bg-success-soft text-success-strong',
                                'expense' => 'bg-danger-soft text-danger-strong',
                            ];
                            $typeLabels = [
                                'income' => __('Ingreso'),
                                'expense' => __('Egreso'),
                            ];
                        @endphp
                        <tr class="table-row table-row-hover">
                            <td class="table-cell whitespace-nowrap text-sm text-token">
                                {{ optional($transaction->occurred_on)->format('d/m/Y') }}
                            </td>
                            <td class="table-cell whitespace-nowrap text-sm">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $typeStyles[$transaction->type] ?? 'bg-accent-soft text-accent' }}">
                                    {{ $typeLabels[$transaction->type] ?? $transaction->type }}
                                </span>
                            </td>
                            <td class="table-cell whitespace-nowrap text-sm text-token">
                                {{ $transaction->category }}
                            </td>
                            <td class="table-cell text-sm text-token">
                                {{ $transaction->description ? \Illuminate\Support\Str::limit($transaction->description, 80) : '—' }}
                            </td>
                            <td class="table-cell whitespace-nowrap text-sm font-semibold text-token">
                                {{ \App\Support\Formatters\MoneyFormatter::pen($transaction->amount) }}
                            </td>
                            <td class="table-cell whitespace-nowrap text-sm">
                                <div class="flex flex-wrap items-center gap-2">
                                    <button
                                        type="button"
                                        wire:click="openEditModal({{ $transaction->id }})"
                                        class="btn btn-secondary btn-sm"
                                    >
                                        {{ __('Editar') }}
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="deleteTransaction({{ $transaction->id }})"
                                        onclick="confirm('{{ __('¿Deseas eliminar este movimiento?') }}') || event.stopImmediatePropagation()"
                                        class="btn btn-danger btn-sm"
                                    >
                                        {{ __('Eliminar') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="6" class="table-empty">
                                {{ __('Aún no has registrado movimientos para los filtros seleccionados.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            {{ $transactions->links() }}
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-surface-strong/70 px-4 py-6">
            <div class="w-full max-w-xl rounded-2xl border border-token bg-elevated p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-token">
                            {{ $transactionId ? __('Editar movimiento') : __('Nuevo movimiento') }}
                        </h2>
                        <p class="mt-1 text-sm text-token-muted">
                            {{ __('Registra la información del movimiento a controlar.') }}
                        </p>
                    </div>
                    <button type="button" class="btn btn-ghost btn-icon" wire:click="closeModal">
                        <span class="sr-only">{{ __('Cerrar') }}</span>
                        <x-dynamic-component :component="'heroicon-o-x-mark'" class="size-5" />
                    </button>
                </div>

                <form wire:submit.prevent="saveTransaction" class="mt-6 space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label" for="transaction-type">{{ __('Tipo') }}</label>
                            <select id="transaction-type" wire:model="formType" class="form-control">
                                <option value="income">{{ __('Ingreso') }}</option>
                                <option value="expense">{{ __('Egreso') }}</option>
                            </select>
                            @error('formType')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label" for="transaction-category">{{ __('Categoría') }}</label>
                            <input
                                id="transaction-category"
                                type="text"
                                wire:model.defer="category"
                                class="form-control"
                                placeholder="{{ __('Ej. Sueldo, Alquiler, Servicios') }}"
                            />
                            @error('category')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label" for="transaction-amount">{{ __('Monto') }}</label>
                            <input
                                id="transaction-amount"
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model.defer="amount"
                                class="form-control"
                                placeholder="0.00"
                            />
                            @error('amount')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label" for="transaction-date">{{ __('Fecha') }}</label>
                            <input
                                id="transaction-date"
                                type="date"
                                wire:model="occurred_on"
                                class="form-control"
                            />
                            @error('occurred_on')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="transaction-description">{{ __('Descripción') }}</label>
                        <textarea
                            id="transaction-description"
                            rows="3"
                            wire:model.defer="description"
                            class="form-control"
                            placeholder="{{ __('Notas adicionales del movimiento') }}"
                        ></textarea>
                        @error('description')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3 border-t border-token pt-4 md:flex-row md:justify-end">
                        <button type="button" class="btn btn-ghost md:w-auto" wire:click="closeModal">
                            {{ __('Cancelar') }}
                        </button>
                        <button type="submit" class="btn btn-primary md:w-auto">
                            {{ $transactionId ? __('Guardar cambios') : __('Registrar movimiento') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
