@props([
    'label' => '',
    'value' => null,
    'icon' => 'circle-dot',
    'decimals' => 0,
])

@php
    $requestedComponent = 'flux.icon.' . $icon;
    $componentView = 'components.' . str_replace('.', '/', $requestedComponent);
    $iconComponent = \Illuminate\Support\Facades\View::exists($componentView) ? $requestedComponent : 'flux.icon.bar-chart-3';
    $displayValue = is_numeric($value) ? number_format((float) $value, $decimals) : $value;
@endphp

<div class="stat-card flex flex-col gap-4">
    <div class="flex items-center justify-between text-[11px] font-semibold uppercase tracking-wide text-slate-400">
        <span>{{ $label }}</span>
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-accent-soft text-accent">

            <x-dynamic-component :component="$iconComponent" class="size-4" />
        </span>
    </div>
    <p class="text-2xl font-semibold text-slate-800">{{ $displayValue }}</p>
</div>
