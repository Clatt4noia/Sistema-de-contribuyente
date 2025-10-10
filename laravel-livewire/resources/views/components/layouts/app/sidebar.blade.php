@props([
    'title' => null,
    'menu' => null,
])

@php
    use Illuminate\Support\Facades\View;

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

        // normalizamos el nombre
        $component = str_starts_with($icon, 'heroicon-')
            ? $icon
            : 'heroicon-o-' . ltrim($icon, '-');

        // comprobamos si existe la vista del componente
        $viewName = 'components.' . str_replace('-', '.', $component);

        if (View::exists($viewName)) {
            return $component;
        }

        return $fallbackIcon;
    };
    $storedTheme = request()->cookie('app_theme');
    $initialTheme = in_array($storedTheme, ['light', 'dark'], true) ? $storedTheme : null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $initialTheme === 'dark' ? 'dark' : '' }}" @if($initialTheme) data-theme="{{ $initialTheme }}" @endif>
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-800 antialiased transition-colors duration-300 dark:bg-slate-950 dark:text-slate-100">
        <div class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-slate-200/80 transition-colors duration-500 dark:from-slate-950 dark:via-slate-950/90 dark:to-slate-950/80">
            <div data-app-shell data-sidebar-state="auto" class="relative min-h-screen transition-[padding-left] duration-300 lg:pl-[18rem] data-[sidebar-state=closed]:lg:pl-0">
                <aside id="app-sidebar" data-app-sidebar data-state="auto" class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col overflow-y-auto border-r border-slate-200/70 bg-gradient-to-b from-white via-slate-50 to-slate-100 px-4 py-6 text-slate-700 shadow-2xl shadow-indigo-900/30 transition-transform duration-300 data-[state=open]:translate-x-0 dark:border-slate-800/80 dark:from-slate-950 dark:via-slate-950/90 dark:to-slate-950/85 dark:text-slate-100 lg:translate-x-0 lg:data-[state=closed]:-translate-x-full">
                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ route('dashboard') }}" class="flex flex-1 items-center gap-3 rounded-2xl px-4 py-4 transition hover:bg-slate-900/5 dark:hover:bg-white/10">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-500 dark:bg-indigo-500/20 dark:text-indigo-200">
                                <x-app-logo-icon class="h-7 w-7" />
                            </span>

                            <div class="hidden text-sm font-medium leading-tight text-slate-700 dark:text-slate-200 xl:block">
                                <span class="text-base font-semibold text-slate-900 dark:text-white">{{ config('app.name') }}</span>
                                <span class="block text-xs text-indigo-500 dark:text-indigo-200/80">{{ __('Panel de control') }}</span>
                            </div>
                        </a>

                        <button
                            type="button"
                            data-sidebar-toggle
                            aria-controls="app-sidebar"
                            aria-expanded="false"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900/5 text-slate-600 transition hover:bg-slate-900/10 dark:bg-white/10 dark:text-slate-200 dark:hover:bg-white/20 lg:hidden"
                        >
                            <span class="sr-only">{{ __('Cerrar menú') }}</span>
                            <x-dynamic-component :component="$resolveIcon('heroicon-o-x-mark')" class="size-5" />
                        </button>
                    </div>

                    <div class="mt-8 px-4">
                        <label class="relative flex h-12 w-full items-center">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400/80 dark:text-slate-500">
                                <x-dynamic-component :component="$resolveIcon('heroicon-o-magnifying-glass')" class="size-5" />
                            </span>

                            <input
                                type="search"
                                placeholder="{{ __('Buscar...') }}"
                                class="h-12 w-full rounded-2xl border border-slate-200/70 bg-white/80 pl-11 pr-4 text-sm font-medium text-slate-700 placeholder:text-slate-400/80 outline-none transition focus:border-indigo-400/70 focus:ring-2 focus:ring-indigo-400/70 dark:border-slate-700/70 dark:bg-slate-900/70 dark:text-slate-100"

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

                            <details @class(['group/nav overflow-hidden rounded-2xl border border-slate-200/70 bg-white/70 text-slate-700 shadow-sm transition dark:border-slate-800/60 dark:bg-slate-900/60 dark:text-slate-200']) {{ $isOpen ? 'open' : '' }}>
                                <summary class="flex cursor-pointer items-center justify-between gap-3 px-4 py-3 text-sm font-semibold">
                                    <span class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900/5 text-indigo-500 transition group-open/nav:bg-indigo-500/15 dark:bg-white/10 dark:text-indigo-200">
                                            @php($summaryIcon = $resolveIcon($groupIcon))
                                            <x-dynamic-component :component="$summaryIcon" class="size-5" />
                                        </span>
                                        <span>{{ $group }}</span>
                                    </span>
                                    <x-dynamic-component :component="$resolveIcon('heroicon-o-chevron-down')" class="size-4 transition group-open/nav:rotate-180" />
                                </summary>

                                <div class="space-y-1 border-t border-slate-200/70 bg-white/50 px-2 py-2 dark:border-slate-800/60 dark:bg-slate-900/50">
                                    @foreach ($items as $item)
                                        <a
                                            href="{{ $item['href'] }}"
                                            @class([
                                                'group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition',
                                                'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30 ring-1 ring-indigo-300/40 dark:bg-indigo-500/90 dark:text-white' => $item['current'],
                                                'text-slate-700/90 hover:bg-slate-900/5 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white' => ! $item['current'],
                                            ])
                                            aria-current="{{ $item['current'] ? 'page' : 'false' }}"
                                        >
                                            <span
                                                @class([
                                                    'flex h-9 w-9 items-center justify-center rounded-xl transition',
                                                    'bg-indigo-500/20 text-white dark:bg-indigo-500/20' => $item['current'],
                                                    'bg-slate-900/5 text-indigo-500 dark:bg-white/5 dark:text-indigo-200' => ! $item['current'],
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
                                </div>
                            </details>
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
                                    <x-dynamic-component :component="$resolveIcon('heroicon-o-sun')" class="size-5 dark:hidden" />
                                    <x-dynamic-component :component="$resolveIcon('heroicon-o-moon')" class="hidden size-5 dark:block" />
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

                                <flux:menu class="w-48 rounded-2xl border border-slate-200/70 bg-white/95 text-slate-700 shadow-lg backdrop-blur transition-colors duration-300 dark:border-slate-800/70 dark:bg-slate-950/80 dark:text-slate-100">
                                    <flux:menu.item :href="route('profile.edit')" icon="cog">{{ __('Configuración') }}</flux:menu.item>
                                    <flux:menu.separator class="border-slate-200/70 dark:border-slate-700/70" />
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">{{ __('Cerrar sesión') }}</flux:menu.item>
                                    </form>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                </aside>

                <div data-app-sidebar-backdrop data-state="closed" class="fixed inset-0 z-40 bg-slate-900/50 opacity-0 transition-opacity duration-300 pointer-events-none data-[state=open]:opacity-100 data-[state=open]:pointer-events-auto lg:hidden"></div>

                <div class="flex min-h-screen flex-col bg-white/70 backdrop-blur transition-colors duration-500 dark:bg-slate-950/40 dark:backdrop-blur">
                    <header class="flex items-center gap-3 border-b border-slate-200/70 bg-white/80 px-4 py-3 text-slate-700 shadow-sm backdrop-blur-sm supports-[backdrop-filter]:bg-white/60 dark:border-slate-800/70 dark:bg-slate-950/70 dark:text-slate-100">

                        <button
                            type="button"
                            data-sidebar-toggle
                            aria-controls="app-sidebar"
                            aria-expanded="false"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900/5 text-slate-600 transition hover:bg-slate-900/10 dark:bg-white/10 dark:text-slate-200 dark:hover:bg-white/20"
                        >
                            <span class="sr-only">{{ __('Abrir menú') }}</span>
                            <x-dynamic-component :component="$resolveIcon('heroicon-o-bars-2')" class="size-5" />
                        </button>

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
                            <x-dynamic-component :component="$resolveIcon('heroicon-o-sun')" class="size-5 dark:hidden" />
                            <x-dynamic-component :component="$resolveIcon('heroicon-o-moon')" class="hidden size-5 dark:block" />
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
