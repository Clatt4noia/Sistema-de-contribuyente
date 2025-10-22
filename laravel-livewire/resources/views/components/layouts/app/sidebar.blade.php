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

        return \Illuminate\Support\Facades\View::exists($viewName)
            ? $component
            : $fallbackIcon;
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => $title])
</head>

<body class="min-h-screen bg-surface text-token antialiased">
    <div class="min-h-screen bg-elevated">

        <div data-app-shell data-sidebar-state="auto"
            class="relative min-h-screen transition-all duration-300 lg:pl-[19rem]
                   data-[sidebar-state=closed]:lg:pl-0">

            <!-- SIDEBAR -->
            <aside id="app-sidebar" data-app-sidebar data-state="auto"
                class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col
                       overflow-y-auto border-r border-token bg-elevated px-5 py-6 text-token
                       shadow-[4px_0_12px_-4px_rgba(0,0,0,0.08)] transition-all duration-300
                       data-[state=open]:translate-x-0 lg:translate-x-0
                       lg:data-[state=closed]:-translate-x-full">

                <!-- LOGO Y BOTÓN DE CIERRE -->
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('dashboard') }}"
                        class="flex flex-1 items-center gap-3 rounded-2xl px-4 py-4 transition hover:[background-color:var(--color-primary-muted)]">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-accent-soft text-accent">
                            <x-app-logo-icon class="h-7 w-7" />
                        </span>
                        <div class="hidden text-sm font-medium leading-tight text-token xl:block">
                            <span class="text-base font-semibold text-token">{{ config('app.name') }}</span>
                            <span class="block text-xs text-accent-soft">{{ __('Panel de control') }}</span>
                        </div>
                    </a>

                    <button type="button" data-sidebar-toggle aria-controls="app-sidebar" aria-expanded="false"
                        class="btn btn-secondary btn-icon rounded-xl lg:hidden">
                        <span class="sr-only">{{ __('Cerrar menú') }}</span>
                        <x-dynamic-component :component="$resolveIcon('heroicon-o-x-mark')" class="size-5" />
                    </button>
                </div>

                <!-- MENÚ PRINCIPAL -->
                @php
                    $groupedNavItems = collect($navItems)->groupBy(fn ($item) => $item['group'] ?? __('General'));
                @endphp

                <nav class="mt-10 flex-1 space-y-3 px-2">
                    @foreach ($groupedNavItems as $group => $items)
                        @php
                            $isOpen = collect($items)->contains(fn ($entry) => $entry['current']);
                            $groupIcon = $items->first()['icon'] ?? null;
                        @endphp

                        <details @class([
                            'group/nav overflow-hidden rounded-xl border border-transparent transition-all duration-200',
                            'bg-elevated shadow-sm ring-1 ring-primary-100' => $isOpen,
                            'hover:border-token' => ! $isOpen,
                        ]) {{ $isOpen ? 'open' : '' }}>
                            <summary
                                class="flex cursor-pointer items-center justify-between gap-3 px-4 py-3 text-sm font-semibold">
                                <span class="flex items-center gap-3">
                                    <span
                                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-accent-soft text-accent/90 transition duration-150 group-hover/nav:text-accent">
                                        @php($summaryIcon = $resolveIcon($groupIcon))
                                        <x-dynamic-component :component="$summaryIcon" class="size-5" />
                                    </span>
                                    <span>{{ $group }}</span>
                                </span>
                                <x-dynamic-component :component="$resolveIcon('heroicon-o-chevron-down')"
                                    class="size-4 text-token-muted transition-transform duration-200 group-open/nav:rotate-180" />
                            </summary>

                            <div class="space-y-1 border-t border-token bg-elevated px-2 py-2">
                                @foreach ($items as $item)
                                    <a href="{{ $item['href'] }}"
                                        @class([
                                            'group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition',
                                            '[background-color:var(--color-primary-600)] [color:var(--color-primary-foreground)] shadow-lg ring-1 [--tw-ring-color:var(--color-primary-border)]' =>
                                                $item['current'],
                                            'text-token hover:[background-color:var(--color-primary-muted)] hover:text-token' =>
                                                ! $item['current'],
                                        ])
                                        @if($item['current']) aria-current="page" @endif>

                                        <span
                                            @class([
                                                'flex h-9 w-9 items-center justify-center rounded-xl transition',
                                                '[background-color:var(--color-primary-500)] [color:var(--color-primary-foreground)]' =>
                                                    $item['current'],
                                                'bg-accent-soft text-accent group-hover:[background-color:var(--color-primary-muted)] group-hover:text-accent' =>
                                                    ! $item['current'],
                                            ])>
                                            @php($iconComponent = $resolveIcon($item['icon'] ?? null))
                                            <x-dynamic-component :component="$iconComponent" class="size-5" />
                                        </span>

                                        <span class="flex flex-1 items-center justify-between truncate">
                                            <span class="truncate">{{ $item['label'] }}</span>

                                            @if (isset($item['badge']))
                                                <span
                                                    @class([
                                                        'ml-3 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold shadow-sm',
                                                        $item['badge_style'] ?? 'bg-accent-soft text-accent',
                                                    ])>
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

                <!-- PERFIL / LOGOUT -->
                <div class="mt-10 space-y-6 px-4">
                    <div
                        class="flex items-center gap-3 rounded-2xl border border-token bg-elevated px-4 py-4 text-sm text-token shadow-inner">
                        <span
                            class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-accent-soft text-accent">
                            {{ auth()->user()->initials() }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-token">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-token-muted">{{ auth()->user()->email }}</p>
                        </div>

                        <flux:dropdown position="bottom" align="end">
                            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                            <flux:menu
                                class="w-48 rounded-2xl border border-token bg-elevated text-token shadow-lg">
                                <flux:menu.item :href="route('profile.edit')" icon="cog">{{ __('Configuración') }}</flux:menu.item>
                                <flux:menu.separator class="border-token" />

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                                        class="w-full">{{ __('Cerrar sesión') }}</flux:menu.item>
                                </form>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>
            </aside>

            <!-- BACKDROP -->
            <div data-app-sidebar-backdrop data-state="closed"
                class="fixed inset-0 z-40 bg-surface-strong opacity-0 transition-opacity duration-300 pointer-events-none data-[state=open]:opacity-100 data-[state=open]:pointer-events-auto lg:hidden"></div>

            <!-- CONTENIDO -->
            <div class="flex min-h-screen flex-col bg-surface">
                <header
                    class="flex items-center gap-3 border-b border-token bg-elevated px-4 py-3 text-token shadow-sm">

                    <button type="button" data-sidebar-toggle aria-controls="app-sidebar" aria-expanded="false"
                        class="btn btn-secondary btn-icon rounded-xl">
                        <span class="sr-only">{{ __('Abrir menú') }}</span>
                        <x-dynamic-component :component="$resolveIcon('heroicon-o-bars-2')" class="size-5" />
                    </button>

                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-lg font-semibold">
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-soft text-accent">
                            <x-app-logo-icon class="h-6 w-6" />
                        </span>
                        <span class="text-sm font-semibold text-token">{{ config('app.name') }}</span>
                    </a>

                    <flux:spacer />

                    <flux:dropdown position="bottom" align="end">
                        <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
                        <flux:menu
                            class="w-48 rounded-2xl border border-token bg-elevated text-token shadow-lg">
                            <flux:menu.item :href="route('profile.edit')" icon="cog">{{ __('Configuración') }}</flux:menu.item>
                            <flux:menu.separator class="border-token" />

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                                    class="w-full">{{ __('Cerrar sesión') }}</flux:menu.item>
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
            if (typeof window === 'undefined' || typeof document === 'undefined') return;

            var shell = document.querySelector('[data-app-shell]');
            var sidebar = document.querySelector('[data-app-sidebar]');
            if (!shell || !sidebar) return;

            var toggles = [...document.querySelectorAll('[data-sidebar-toggle]')];
            if (toggles.length === 0) return;

            var backdrop = document.querySelector('[data-app-sidebar-backdrop]');
            var breakpoint = window.matchMedia('(min-width: 1024px)');
            var previousOverflow = '';
            var lastFocused = null;

            function applyState(state) {
                if (state !== 'open' && state !== 'closed') {
                    state = breakpoint.matches ? 'open' : 'closed';
                }
                sidebar.setAttribute('data-state', state);
                shell.setAttribute('data-sidebar-state', state);
                if (backdrop) backdrop.setAttribute('data-state', state);

                toggles.forEach(btn => btn.setAttribute('aria-expanded', state === 'open'));

                document.documentElement.style.scrollBehavior = 'auto';
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
            }

            function focusFirstItem() {
                if (breakpoint.matches) return;
                const focusable = sidebar.querySelector('a[href], button:not([disabled])');
                if (focusable) focusable.focus({ preventScroll: true });
            }

            function openSidebar() {
                if (sidebar.getAttribute('data-state') === 'open') return;
                lastFocused = !breakpoint.matches && document.activeElement instanceof HTMLElement
                    ? document.activeElement
                    : null;
                applyState('open');
                focusFirstItem();
            }

            function closeSidebar() {
                if (sidebar.getAttribute('data-state') === 'closed') return;
                applyState('closed');
                if (!breakpoint.matches && lastFocused) {
                    try { lastFocused.focus({ preventScroll: true }); } catch {}
                }
                if (!breakpoint.matches) lastFocused = null;
            }

            toggles.forEach(btn => btn.addEventListener('click', e => {
                e.preventDefault();
                sidebar.getAttribute('data-state') === 'open' ? closeSidebar() : openSidebar();
            }));

            backdrop?.addEventListener('click', closeSidebar);
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && sidebar.getAttribute('data-state') === 'open') {
                    e.preventDefault();
                    closeSidebar();
                }
            });

            function syncWithViewport() {
                applyState(breakpoint.matches ? 'open' : 'closed');
                if (breakpoint.matches) lastFocused = null;
            }

            syncWithViewport();
            breakpoint.addEventListener('change', syncWithViewport);

            window.addEventListener('resize', () => {
                document.documentElement.style.scrollBehavior = '';
            });
        })();
    </script>
</body>
</html>
