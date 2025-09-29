@props(['title' => null])

@php
    $navItems = [
        [
            'label' => __('Inicio'),
            'href' => route('dashboard'),
            'icon' => 'layout-grid',
            'current' => request()->routeIs('dashboard'),
        ],
        [
            'label' => __('Analíticas'),
            'href' => route('fleet.report'),
            'icon' => 'bar-chart-3',
            'current' => request()->routeIs('fleet.report'),
        ],
        [
            'label' => __('Nuevo camión'),
            'href' => route('fleet.trucks.create'),
            'icon' => 'bell',
            'badge' => __('Nuevo'),
            'badge_style' => 'bg-indigo-500 text-white dark:bg-indigo-400 dark:text-slate-900',
            'current' => request()->routeIs('fleet.trucks.create'),
        ],
        [
            'label' => __('Vehículos'),
            'href' => route('fleet.trucks.index'),
            'icon' => 'truck',
            'current' => request()->routeIs('fleet.trucks.index'),
        ],
        [
            'label' => __('Pagos'),
            'href' => route('billing.payments.index'),
            'icon' => 'credit-card',
            'badge' => '4',
            'badge_style' => 'bg-rose-500 text-white dark:bg-rose-400 dark:text-rose-950',
            'current' => request()->routeIs('billing.payments.*'),
        ],
        [
            'label' => __('Configuración'),
            'href' => route('profile.edit'),
            'icon' => 'settings',
            'current' => request()->routeIs('profile.*'),
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-800 antialiased transition-colors duration-300 dark:bg-slate-950 dark:text-slate-100">
        <div class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-slate-200/80 transition-colors duration-500 dark:from-slate-950 dark:via-slate-950/90 dark:to-slate-950/80">
            <div class="min-h-screen lg:grid lg:grid-cols-[280px_minmax(0,1fr)]">
                <aside class="hidden min-h-screen flex-col border-r border-slate-200/70 bg-gradient-to-b from-white via-slate-50 to-slate-100 px-4 py-6 text-slate-700 shadow-2xl shadow-indigo-900/30 transition-colors duration-500 dark:border-slate-800/80 dark:from-slate-950 dark:via-slate-950/90 dark:to-slate-950/85 dark:text-slate-100 lg:flex">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-2xl px-4 py-4 transition hover:bg-slate-900/5 dark:hover:bg-white/10">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-500 dark:bg-indigo-500/20 dark:text-indigo-200">
                            <x-app-logo-icon class="h-7 w-7" />
                        </span>

                        <div class="hidden text-sm font-medium leading-tight text-slate-700 dark:text-slate-200 xl:block">
                            <span class="text-base font-semibold text-slate-900 dark:text-white">{{ config('app.name') }}</span>
                            <span class="block text-xs text-indigo-500 dark:text-indigo-200/80">{{ __('Panel de control') }}</span>
                        </div>
                    </a>

                    <div class="mt-8 px-4">
                        <label class="relative flex h-12 w-full items-center">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400/80 dark:text-slate-500">
                                <x-dynamic-component :component="'flux.icon.search'" class="size-5" />
                            </span>

                            <input
                                type="search"
                                placeholder="{{ __('Buscar...') }}"
                                class="h-12 w-full rounded-2xl border border-slate-200/70 bg-white/80 pl-11 pr-4 text-sm font-medium text-slate-700 placeholder:text-slate-400/80 outline-none transition focus:border-indigo-400/70 focus:ring-2 focus:ring-indigo-400/70 dark:border-slate-700/70 dark:bg-slate-900/70 dark:text-slate-100"
                            />
                        </label>
                    </div>

                    <nav class="mt-10 flex-1 space-y-1 px-2">
                        @foreach ($navItems as $item)
                            <a
                                href="{{ $item['href'] }}"
                                @class([
                                    'group relative flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition',
                                    'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30 ring-1 ring-indigo-300/40 dark:bg-indigo-500/90 dark:text-white' => $item['current'],
                                    'text-slate-700/90 hover:bg-slate-900/5 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white' => ! $item['current'],
                                ])
                                aria-current="{{ $item['current'] ? 'page' : 'false' }}"
                            >
                                <span
                                    @class([
                                        'flex h-10 w-10 items-center justify-center rounded-xl transition',
                                        'bg-indigo-500/20 text-white dark:bg-indigo-500/20' => $item['current'],
                                        'bg-slate-900/5 text-indigo-500 dark:bg-white/5 dark:text-indigo-200' => ! $item['current'],
                                    ])
                                >
                                    <x-dynamic-component :component="'flux.icon.' . $item['icon']" class="size-5" />
                                </span>

                                <span class="flex flex-1 items-center justify-between truncate">
                                    <span class="truncate">{{ $item['label'] }}</span>

                                    @if (isset($item['badge']))
                                        <span
                                            @class([
                                                'ml-3 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold shadow-sm',
                                                $item['badge_style'] ?? 'bg-slate-900/5 text-slate-700 dark:bg-white/20 dark:text-slate-900',
                                                'shadow-indigo-500/40' => $item['current'],
                                            ])
                                        >
                                            {{ $item['badge'] }}
                                        </span>
                                    @endif
                                </span>
                            </a>
                        @endforeach
                    </nav>

                    <div class="mt-10 space-y-6 px-4">
                        <button
                            type="button"
                            data-theme-toggle
                            aria-pressed="false"
                            class="group flex w-full items-center justify-between gap-3 rounded-2xl bg-slate-900/5 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-900/10 dark:bg-white/10 dark:text-slate-100 dark:hover:bg-white/20"
                        >
                            <span class="flex items-center gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-500 transition group-hover:bg-indigo-500/20 group-hover:text-indigo-100 dark:bg-indigo-500/20 dark:text-indigo-200">
                                    <x-dynamic-component :component="'flux.icon.sun'" class="size-5 dark:hidden" />
                                    <x-dynamic-component :component="'flux.icon.moon-star'" class="hidden size-5 dark:block" />
                                </span>

                                <span class="grid text-left leading-tight">
                                    <span>{{ __('Modo oscuro') }}</span>
                                    <span class="text-xs font-normal text-slate-400 dark:text-slate-400">{{ __('Personaliza tu experiencia') }}</span>
                                </span>
                            </span>

                            <span class="relative inline-flex h-6 w-12 items-center rounded-full bg-slate-900/10 transition dark:bg-white/30">
                                <span class="sr-only">{{ __('Cambiar tema') }}</span>
                                <span class="pointer-events-none inline-block h-5 w-5 translate-x-1 rounded-full bg-white shadow transition dark:translate-x-6 dark:bg-slate-900"></span>
                            </span>
                        </button>

                        <div class="flex items-center gap-3 rounded-2xl bg-slate-900/5 px-4 py-4 text-sm text-slate-700 shadow-inner shadow-slate-900/10 dark:bg-white/10 dark:text-slate-100">
                            <span class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-indigo-500/10 text-indigo-500 dark:bg-indigo-500/30 dark:text-indigo-100">
                                {{ auth()->user()->initials() }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}</p>
                            </div>

                            <flux:dropdown position="bottom" align="end">
                                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                                <flux:menu class="w-48 rounded-2xl border border-slate-200/70 bg-white/95 text-slate-700 shadow-lg backdrop-blur transition-colors duration-300 dark:border-white/10 dark:bg-slate-900/80 dark:text-slate-100">
                                    <flux:menu.item :href="route('profile.edit')" icon="cog">{{ __('Configuración') }}</flux:menu.item>
                                    <flux:menu.separator class="border-slate-200/70 dark:border-white/10" />
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">{{ __('Cerrar sesión') }}</flux:menu.item>
                                    </form>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                </aside>

                <div class="flex min-h-screen flex-col bg-white/70 backdrop-blur transition-colors duration-500 dark:bg-slate-950/40 dark:backdrop-blur">
                    <header class="flex items-center gap-3 border-b border-slate-200/70 bg-white/80 px-4 py-3 text-slate-700 shadow-sm backdrop-blur-sm supports-[backdrop-filter]:bg-white/60 dark:border-slate-800/70 dark:bg-slate-950/70 dark:text-slate-100">
                        <flux:sidebar.toggle
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900/5 text-slate-600 transition hover:bg-slate-900/10 dark:bg-white/10 dark:text-slate-200 dark:hover:bg-white/20"
                            icon="bars-2"
                            inset="left"
                        />

                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-lg font-semibold">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-500 dark:bg-indigo-500/20 dark:text-indigo-200">
                                <x-app-logo-icon class="h-6 w-6" />
                            </span>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-100">{{ config('app.name') }}</span>
                        </a>

                        <flux:spacer />

                        <button
                            type="button"
                            data-theme-toggle
                            aria-pressed="false"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900/5 text-slate-600 transition hover:bg-slate-900/10 dark:bg-white/10 dark:text-slate-200 dark:hover:bg-white/20"
                        >
                            <span class="sr-only">{{ __('Cambiar tema') }}</span>
                            <x-dynamic-component :component="'flux.icon.sun'" class="size-5 dark:hidden" />
                            <x-dynamic-component :component="'flux.icon.moon-star'" class="hidden size-5 dark:block" />
                        </button>

                        <flux:dropdown position="bottom" align="end">
                            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                            <flux:menu class="w-48 rounded-2xl border border-slate-200/70 bg-white/95 text-slate-700 shadow-lg backdrop-blur transition-colors duration-300 dark:border-slate-800/70 dark:bg-slate-950/80 dark:text-slate-100">
                                <flux:menu.item :href="route('profile.edit')" icon="cog">{{ __('Configuración') }}</flux:menu.item>
                                <flux:menu.separator class="border-slate-200/70 dark:border-slate-700/70" />
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">{{ __('Cerrar sesión') }}</flux:menu.item>
                                </form>
                            </flux:menu>
                        </flux:dropdown>
                    </header>

                    <main class="flex-1 overflow-y-auto">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>

        <flux:sidebar
            stashable
            sticky
            class="lg:hidden border-r border-slate-200/70 bg-gradient-to-b from-white via-slate-50 to-slate-100 p-6 text-slate-700 shadow-2xl shadow-indigo-900/30 transition-colors duration-500 dark:border-slate-800/80 dark:from-slate-950 dark:via-slate-950/90 dark:to-slate-950/85 dark:text-slate-100"
        >
            <div class="flex items-center gap-3">
                <flux:sidebar.toggle class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900/5 text-slate-600 transition hover:bg-slate-900/10 dark:bg-white/10 dark:text-slate-200 dark:hover:bg-white/20" icon="x-mark" />

                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-500 dark:bg-indigo-500/20 dark:text-indigo-200">
                    <x-app-logo-icon class="h-6 w-6" />
                </span>

                <span class="text-sm font-semibold">{{ config('app.name') }}</span>
            </div>

            <div class="mt-6">
                <label class="relative flex h-12 w-full items-center">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400/80 dark:text-slate-500">
                        <x-dynamic-component :component="'flux.icon.search'" class="size-5" />
                    </span>

                    <input
                        type="search"
                        placeholder="{{ __('Buscar...') }}"
                        class="h-12 w-full rounded-2xl border border-slate-200/70 bg-white/80 pl-11 pr-4 text-sm font-medium text-slate-700 placeholder:text-slate-400/80 outline-none transition focus:border-indigo-400/70 focus:ring-2 focus:ring-indigo-400/70 dark:border-slate-700/70 dark:bg-slate-900/70 dark:text-slate-100"
                    />
                </label>
            </div>

            <nav class="mt-8 space-y-1">
                @foreach ($navItems as $item)
                    <a
                        href="{{ $item['href'] }}"
                        @class([
                            'group relative flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition',
                            'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30 ring-1 ring-indigo-300/40' => $item['current'],
                            'text-slate-700/90 hover:bg-slate-900/5 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white' => ! $item['current'],
                        ])
                        aria-current="{{ $item['current'] ? 'page' : 'false' }}"
                    >
                        <span
                            @class([
                                'flex h-10 w-10 items-center justify-center rounded-xl transition',
                                'bg-indigo-500/20 text-white dark:bg-indigo-500/20' => $item['current'],
                                'bg-slate-900/5 text-indigo-500 dark:bg-white/5 dark:text-indigo-200' => ! $item['current'],
                            ])
                        >
                            <x-dynamic-component :component="'flux.icon.' . $item['icon']" class="size-5" />
                        </span>

                        <span class="flex flex-1 items-center justify-between truncate">
                            <span class="truncate">{{ $item['label'] }}</span>

                            @if (isset($item['badge']))
                                <span
                                    @class([
                                        'ml-3 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold shadow-sm',
                                        $item['badge_style'] ?? 'bg-slate-900/5 text-slate-700 dark:bg-white/20 dark:text-slate-900',
                                        'shadow-indigo-500/40' => $item['current'],
                                    ])
                                >
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-8 space-y-4">
                <button
                    type="button"
                    data-theme-toggle
                    aria-pressed="false"
                    class="group flex w-full items-center justify-between gap-3 rounded-2xl bg-slate-900/5 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-900/10 dark:bg-white/10 dark:text-slate-100 dark:hover:bg-white/20"
                >
                    <span class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-500 transition group-hover:bg-indigo-500/20 group-hover:text-indigo-100 dark:bg-indigo-500/20 dark:text-indigo-200">
                            <x-dynamic-component :component="'flux.icon.sun'" class="size-5 dark:hidden" />
                            <x-dynamic-component :component="'flux.icon.moon-star'" class="hidden size-5 dark:block" />
                        </span>
                        <span>{{ __('Modo oscuro') }}</span>
                    </span>

                    <span class="relative inline-flex h-6 w-12 items-center rounded-full bg-slate-900/10 transition dark:bg-white/30">
                        <span class="sr-only">{{ __('Cambiar tema') }}</span>
                        <span class="pointer-events-none inline-block h-5 w-5 translate-x-1 rounded-full bg-white shadow transition dark:translate-x-6 dark:bg-slate-900"></span>
                    </span>
                </button>

                <div class="flex items-center gap-3 rounded-2xl bg-slate-900/5 px-4 py-4 text-sm text-slate-700 dark:bg-white/10 dark:text-slate-100">
                    <span class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-indigo-500/10 text-indigo-500 dark:bg-indigo-500/30 dark:text-indigo-100">
                        {{ auth()->user()->initials() }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:button type="submit" icon="arrow-right-start-on-rectangle" variant="ghost" class="text-slate-700 hover:text-slate-900 dark:text-slate-200 dark:hover:text-white">
                            {{ __('Cerrar sesión') }}
                        </flux:button>
                    </form>
                </div>
            </div>
        </flux:sidebar>

        @livewireScripts
        @fluxScripts

        <script>
            (function () {
                if (typeof window === 'undefined' || typeof document === 'undefined') {
                    return;
                }

                var storageKey = 'app:theme';
                var root = document.documentElement;
                var mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

                var applyTheme = function (theme) {
                    var isDark = theme === 'dark';
                    root.classList.toggle('dark', isDark);
                    root.setAttribute('data-theme', theme);

                    document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
                        button.setAttribute('aria-pressed', String(isDark));
                    });
                };

                var getStoredTheme = function () {
                    try {
                        return window.localStorage.getItem(storageKey);
                    } catch (error) {
                        return null;
                    }
                };

                var setStoredTheme = function (theme) {
                    try {
                        window.localStorage.setItem(storageKey, theme);
                    } catch (error) {
                        // Ignore storage errors
                    }
                };

                var resolveTheme = function () {
                    var stored = getStoredTheme();
                    if (stored === 'light' || stored === 'dark') {
                        return stored;
                    }

                    return mediaQuery.matches ? 'dark' : 'light';
                };

                var syncTheme = function (theme) {
                    applyTheme(theme);
                    setStoredTheme(theme);
                };

                var init = function () {
                    syncTheme(resolveTheme());

                    document.addEventListener('click', function (event) {
                        var trigger = event.target.closest('[data-theme-toggle]');
                        if (!trigger) {
                            return;
                        }

                        event.preventDefault();
                        var isDark = root.classList.contains('dark');
                        syncTheme(isDark ? 'light' : 'dark');
                    });

                    mediaQuery.addEventListener('change', function (event) {
                        var stored = getStoredTheme();
                        if (stored !== 'light' && stored !== 'dark') {
                            applyTheme(event.matches ? 'dark' : 'light');
                        }
                    });

                    window.addEventListener('storage', function (event) {
                        if (event.key === storageKey && event.newValue) {
                            applyTheme(event.newValue);
                        }
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }
            })();
        </script>
    </body>
</html>
