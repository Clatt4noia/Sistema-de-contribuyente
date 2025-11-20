<div class="space-y-6">
    <article class="surface-card">
        <header class="flex items-center justify-between border-b border-token px-6 py-5">
            <div>
                <h1 class="text-2xl font-semibold text-token">{{ __('Notificaciones de estado del pedido') }}</h1>
                <p class="mt-1 text-sm text-token">{{ __('Seguimiento cronológico de cambios en pedidos y asignaciones logísticas.') }}</p>
            </div>
            <div class="rounded-full bg-accent-soft px-4 py-2 text-xs font-semibold uppercase tracking-wide text-accent">
                {{ trans_choice(':count evento|:count eventos', count($notifications), ['count' => count($notifications)]) }}
            </div>
        </header>

        <div class="p-6">
            @if (empty($notifications))
                <p class="text-sm text-token">{{ __('Sin actualizaciones recientes de estado.') }}</p>
            @else
                @php
                    $statusColors = [
                        'pending' => 'bg-warning-soft text-warning',
                        'en_route' => 'bg-primary-soft text-primary',
                        'delivered' => 'bg-success-soft text-success',
                        'cancelled' => 'bg-danger-soft text-danger',
                        'delayed' => 'bg-warning-soft text-warning',
                    ];
                @endphp

                <ol class="space-y-4">
                    @foreach ($notifications as $update)
                        <li class="relative rounded-xl border border-token bg-elevated p-4 shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-token">{{ __('Pedido') }}</p>
                                    <div class="flex items-center gap-2 text-sm font-semibold text-token">
                                        <span>{{ $update['order_reference'] ?? '—' }}</span>
                                        @if($update['order_id'])
                                            <a class="text-primary hover:underline" href="{{ route('orders.edit', $update['order_id']) }}">{{ __('Ver detalle') }}</a>
                                        @endif
                                    </div>
                                    <p class="text-xs text-token">{{ $update['client'] ?? __('Cliente no registrado') }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($update['previous_status'])
                                        <span class="rounded-full bg-surface-muted px-3 py-1 text-xs font-semibold text-token">{{ __($update['previous_status']) }}</span>
                                        <span class="text-xs text-token">→</span>
                                    @endif
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusColors[$update['new_status'] ?? ''] ?? 'bg-accent-soft text-accent' }}">{{ __($update['new_status'] ?? 'desconocido') }}</span>
                                </div>
                            </div>

                            <div class="mt-3 grid gap-2 text-sm text-token sm:grid-cols-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-token/70">{{ __('Asignación / Vehículo') }}</p>
                                    <p class="font-medium">{{ $update['assignment'] ? '#'.$update['assignment'] : __('Sin asignar') }}</p>
                                    <p>{{ $update['truck'] ?? __('Vehículo no definido') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-token/70">{{ __('Chofer') }}</p>
                                    <p>{{ $update['driver'] ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-token/70">{{ __('Fecha del cambio') }}</p>
                                    <p class="font-medium">{{ $update['changed_at'] ?? '—' }}</p>
                                    <p class="text-xs">{{ __('Registrado por') }}: {{ $update['changed_by'] ?? __('Sistema') }}</p>
                                </div>
                            </div>

                            @if($update['notes'])
                                <p class="mt-3 rounded-lg bg-surface-muted px-3 py-2 text-sm text-token">{{ $update['notes'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </article>
</div>
