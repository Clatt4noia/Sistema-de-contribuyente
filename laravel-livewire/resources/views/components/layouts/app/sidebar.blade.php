@props([
    'title' => null,
    'menu' => null,
])

@php

    $menuBuilder = \App\Support\Navigation\MainMenuV2::class;
    $navItems = $menu
        ?? (class_exists($menuBuilder)
            ? $menuBuilder::for(auth()->user())
            : \App\Support\Navigation\MainMenu::for(auth()->user()));
    $fallbackIcon = 'heroicon-o-squares-2x2';

    $resolveIcon = static function (?string $icon) use ($fallbackIcon) {
        if (! $icon) {
            return $fallbackIcon;
        }

        $component = str_starts_with($icon, 'heroicon-')
            ? $icon
            : 'heroicon-o-' . ltrim($icon, '-');

        $viewName = 'components.' . str_replace('-', '.', $component);

        if (\Illuminate\Support\Facades\View::exists($viewName)) {
            return $component;
        }

        return $fallbackIcon;
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-700 antialiased">
        <div class="min-h-screen bg-white">

            <div data-app-shell data-sidebar-state="auto" class="relative min-h-screen transition-[padding-left] duration-300 lg:pl-[19rem] data-[sidebar-state=closed]:lg:pl-0">
                <aside
                    id="app-sidebar"
                    data-app-sidebar
                    data-state="auto"
                    class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col overflow-y-auto border-r border-slate-200 bg-white px-5 py-6 text-slate-600 shadow-xl shadow-slate-200 transition-transform duration-300 data-[state=open]:translate-x-0 lg:translate-x-0 lg:data-[state=closed]:-translate-x-full"
                >
                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ route('dashboard') }}" class="flex flex-1 items-center gap-3 rounded-2xl px-4 py-4 transition hover:bg-sky-50">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                                <x-app-logo-icon class="h-7 w-7" />
                            </span>

                            <div class="hidden text-sm font-medium leading-tight text-slate-600 xl:block">
                                <span class="text-base font-semibold text-slate-900">{{ config('app.name') }}</span>
                                <span class="block text-xs text-sky-500">{{ __('Panel de control') }}</span>
                            </div>
                        </a>


                        <button
                            type="button"
                            data-sidebar-toggle
                            aria-controls="app-sidebar"
                            aria-expanded="false"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-sky-200 hover:text-slate-700 lg:hidden"

                        >
                            <span class="sr-only">{{ __('Cerrar menú') }}</span>
                            <x-dynamic-component :component="$resolveIcon('heroicon-o-x-mark')" class="size-5" />
                        </button>
                    </div>

                    <div class="mt-8 px-4">
                        <label class="relative flex h-12 w-full items-center">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-300">
                                <x-dynamic-component :component="$resolveIcon('heroicon-o-magnifying-glass')" class="size-5" />
                            </span>

                            <input
                                type="search"
                                placeholder="{{ __('Buscar...') }}"
                                class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-4 text-sm font-medium text-slate-600 placeholder:text-slate-400 outline-none transition focus:border-sky-300 focus:ring-2 focus:ring-sky-200"

                            />
                        </label>
                    </div>

                    @php
                        $groupedNavItems = collect($navItems)->groupBy(fn ($item) => $item['group'] ?? __('General'));
                    @endphp

                    <nav class="mt-10 flex-1 space-y-3 px-2">
                        @foreach ($groupedNavItems as $group => $items)
                            @php
                                $isOpen = collect($items)->contains(fn ($entry) => $entry['current']);
                                $groupIcon = $items->first()['icon'] ?? null;
                            @endphp

                            <details @class(['group/nav overflow-hidden rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition']) {{ $isOpen ? 'open' : '' }}>
                                <summary class="flex cursor-pointer items-center justify-between gap-3 px-4 py-3 text-sm font-semibold">
                                    <span class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600 transition group-open/nav:bg-sky-100">

                                            @php($summaryIcon = $resolveIcon($groupIcon))
                                            <x-dynamic-component :component="$summaryIcon" class="size-5" />
                                        </span>
                                        <span>{{ $group }}</span>
                                    </span>
                                    <x-dynamic-component :component="$resolveIcon('heroicon-o-chevron-down')" class="size-4 text-slate-400 transition group-open/nav:rotate-180" />
                                </summary>

                                <div class="space-y-1 border-t border-slate-200 bg-white px-2 py-2">

                                    @foreach ($items as $item)
                                        <a
                                            href="{{ $item['href'] }}"
                                            @class([
                                                'group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition',
                                                'bg-sky-500 text-white shadow-lg shadow-sky-200 ring-1 ring-sky-200' => $item['current'],
                                                'text-slate-600 hover:bg-sky-50 hover:text-slate-900' => ! $item['current'],

                                            ])
                                            aria-current="{{ $item['current'] ? 'page' : 'false' }}"
                                        >
                                            <span
                                                @class([
                                                    'flex h-9 w-9 items-center justify-center rounded-xl transition',
                                                    'bg-sky-500/20 text-white' => $item['current'],
                                                    'bg-sky-50 text-sky-600 group-hover:bg-sky-100 group-hover:text-sky-700' => ! $item['current'],

                                                ])
                                            >
                                                @php($iconComponent = $resolveIcon($item['icon'] ?? null))
                                                <x-dynamic-component :component="$iconComponent" class="size-5" />
                                            </span>

                                            <span class="flex flex-1 items-center justify-between truncate">
                                                <span class="truncate">{{ $item['label'] }}</span>

                                                @if (isset($item['badge']))
                                                    <span
                                                        @class([
                                                            'ml-3 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold shadow-sm',
                                                            $item['badge_style'] ?? 'bg-sky-50 text-sky-600',
                                                            'shadow-sky-200' => $item['current'],

                                                        ])
                                                    >
                                                        {{ $item['badge'] }}
                                                    </span>
                                                @endif
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </nav>

                    <div class="mt-10 space-y-6 px-4">
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-600 shadow-inner shadow-slate-200">
                            <span class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-sky-100 text-sky-600">

                                {{ auth()->user()->initials() }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-700">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-slate-400">{{ auth()->user()->email }}</p>

                            </div>

                            <flux:dropdown position="bottom" align="end">
                                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                                <flux:menu class="w-48 rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-lg">
                                    <flux:menu.item :href="route('profile.edit')" icon="cog">{{ __('Configuración') }}</flux:menu.item>
                                    <flux:menu.separator class="border-slate-200" />

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">{{ __('Cerrar sesión') }}</flux:menu.item>
                                    </form>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                </aside>

                <div data-app-sidebar-backdrop data-state="closed" class="fixed inset-0 z-40 bg-slate-200 opacity-0 transition-opacity duration-300 pointer-events-none data-[state=open]:opacity-100 data-[state=open]:pointer-events-auto lg:hidden"></div>

                <div class="flex min-h-screen flex-col bg-slate-50">
                    <header class="flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 text-slate-600 shadow-sm">


                        <button
                            type="button"
                            data-sidebar-toggle
                            aria-controls="app-sidebar"
                            aria-expanded="false"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-sky-200 hover:text-slate-700"

                        >
                            <span class="sr-only">{{ __('Abrir menú') }}</span>
                            <x-dynamic-component :component="$resolveIcon('heroicon-o-bars-2')" class="size-5" />
                        </button>

                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-lg font-semibold">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 text-sky-600">

                                <x-app-logo-icon class="h-6 w-6" />
                            </span>
                            <span class="text-sm font-semibold text-slate-600">{{ config('app.name') }}</span>
                        </a>

                        <flux:spacer />

                        <flux:dropdown position="bottom" align="end">
                            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                            <flux:menu class="w-48 rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-lg">

                                <flux:menu.item :href="route('profile.edit')" icon="cog">{{ __('Configuración') }}</flux:menu.item>
                                <flux:menu.separator class="border-slate-200" />

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

        @livewireScripts
        @fluxScripts

        <script>
            (function () {
                if (typeof window === 'undefined' || typeof document === 'undefined') {
                    return;
                }

                var shell = document.querySelector('[data-app-shell]');
                var sidebar = document.querySelector('[data-app-sidebar]');
                if (!shell || !sidebar) {
                    return;
                }

                var toggles = Array.prototype.slice.call(document.querySelectorAll('[data-sidebar-toggle]'));
                if (toggles.length === 0) {
                    return;
                }

                var backdrop = document.querySelector('[data-app-sidebar-backdrop]');
                var breakpoint = window.matchMedia('(min-width: 1024px)');
                var previousOverflow = '';
                var lastFocused = null;

                var applyState = function (state) {
                    if (state !== 'open' && state !== 'closed') {
                        state = breakpoint.matches ? 'open' : 'closed';
                    }

                    sidebar.setAttribute('data-state', state);
                    shell.setAttribute('data-sidebar-state', state);


                    if (backdrop) {
                        backdrop.setAttribute('data-state', state);
                    }


                    toggles.forEach(function (button) {
                        button.setAttribute('aria-expanded', state === 'open' ? 'true' : 'false');
                    });

                    if (!breakpoint.matches) {
                        if (state === 'open') {
                            previousOverflow = document.body.style.overflow || '';
                            document.body.style.overflow = 'hidden';
                        } else {
                            document.body.style.overflow = previousOverflow;
                        }
                    } else {
                        document.body.style.overflow = previousOverflow;
                    }
                };

                var focusFirstItem = function () {
                    if (breakpoint.matches) {
                        return;
                    }

                    var focusable = sidebar.querySelector('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])');

                    if (focusable && typeof focusable.focus === 'function') {
                        focusable.focus({ preventScroll: true });
                    }
                };

                var openSidebar = function () {
                    if (sidebar.getAttribute('data-state') === 'open') {
                        return;
                    }

                    if (!breakpoint.matches && document.activeElement instanceof HTMLElement) {
                        lastFocused = document.activeElement;
                    } else {
                        lastFocused = null;
                    }


                    applyState('open');
                    focusFirstItem();
                };

                var closeSidebar = function () {
                    if (sidebar.getAttribute('data-state') === 'closed') {
                        return;
                    }

                    applyState('closed');

                    if (!breakpoint.matches && lastFocused && typeof lastFocused.focus === 'function') {
                        try {
                            lastFocused.focus({ preventScroll: true });
                        } catch (error) {
                            // Ignore focus restoration errors
                        }
                    }

                    if (!breakpoint.matches) {
                        lastFocused = null;
                    }
                };

                var toggleSidebar = function () {
                    if (sidebar.getAttribute('data-state') === 'open') {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                };

                toggles.forEach(function (button) {
                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        toggleSidebar();
                    });
                });

                if (backdrop) {
                    backdrop.addEventListener('click', closeSidebar);
                }

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && sidebar.getAttribute('data-state') === 'open') {
                        event.preventDefault();
                        closeSidebar();
                    }
                });

                var syncWithViewport = function () {
                    var desiredState = breakpoint.matches ? 'open' : 'closed';
                    applyState(desiredState);

                    if (breakpoint.matches) {
                        lastFocused = null;
                    }
                };

                syncWithViewport();
                breakpoint.addEventListener('change', syncWithViewport);
            })();
        </script>

    </body>

</html>

