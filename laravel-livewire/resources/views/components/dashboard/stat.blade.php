@props([
    'label' => '',
    'value' => null,
    'icon' => 'circle-dot',
    'decimals' => 0,
])

@php
    use Illuminate\Support\Facades\View;

    $requestedComponent = 'flux.icon.' . $icon;
    $componentView = 'components.' . str_replace('.', '/', $requestedComponent);
    $iconComponent = View::exists($componentView) ? $requestedComponent : 'flux.icon.bar-chart-3';
@endphp

<div class="surface-muted flex flex-col gap-3 rounded-2xl p-4 shadow-sm">
    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
        <span>{{ $label }}</span>
        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-500 dark:bg-indigo-500/20 dark:text-indigo-200">
            <x-dynamic-component :component="$iconComponent" class="size-4" />
        </span>
    </div>
    <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format((float) $value, $decimals) }}</p>
</div>
