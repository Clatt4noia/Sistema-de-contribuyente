@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[300px_minmax(0,1fr)]">
            <aside class="hidden min-h-screen flex-col border-e border-cyan-100/80 bg-gradient-to-b from-white via-cyan-50 to-sky-100 p-6 text-slate-700 shadow-xl lg:flex">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-lg font-semibold text-cyan-700 transition hover:text-slate-900">
                    <x-app-logo />
                </a>

                <div class="mt-10 flex-1 space-y-8">
                    <div>
                        <flux:navlist variant="outline" class="text-slate-700">
                            <flux:navlist.group :heading="__('Platform')" class="grid">
                                <flux:navlist.item
                                    icon="home"
                                    :href="route('dashboard')"
                                    :current="request()->routeIs('dashboard')"
                                    class="rounded-md transition hover:bg-white/70">
                                    {{ __('Inicio') }}
                                </flux:navlist.item>
                            </flux:navlist.group>
                        </flux:navlist>
                    </div>

                    <div>
                        <flux:navlist variant="outline" class="text-slate-700">
                            <flux:navlist.item
                                icon="folder-git-2"
                                href="https://github.com/bryansoberon"
                                target="_blank"
                                class="rounded-md transition hover:bg-white/70">
                                {{ __('Github bryan') }}
                            </flux:navlist.item>

                            <flux:navlist.item
                                icon="book-open-text"
                                href="https://www.instagram.com/bryansoberon"
                                target="_blank"
                                class="rounded-md transition hover:bg-white/70">
                                {{ __('Instagram') }}
                            </flux:navlist.item>
                        </flux:navlist>
                    </div>
                </div>

                <div class="mt-6">
                    <flux:dropdown position="bottom" align="start" class="w-full">
                        <flux:profile
                            :name="auth()->user()->name"
                            :initials="auth()->user()->initials()"
                            icon:trailing="chevrons-up-down"
                            class="w-full justify-between text-cyan-700"
                        />

                        <flux:menu class="w-[240px] rounded-2xl bg-white/95 text-slate-700 shadow-xl ring-1 ring-cyan-100">
                            <flux:menu.radio.group>
                                <div class="p-0 text-sm font-normal">
                                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                        <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                            <span class="flex h-full w-full items-center justify-center rounded-lg bg-cyan-100 text-cyan-700">
                                                {{ auth()->user()->initials() }}
                                            </span>
                                        </span>

                                        <div class="grid flex-1 text-start text-sm leading-tight">
                                            <span class="truncate font-semibold text-slate-900">{{ auth()->user()->name }}</span>
                                            <span class="truncate text-xs text-cyan-600">{{ auth()->user()->email }}</span>
                                        </div>
                                    </div>
                                </div>
                            </flux:menu.radio.group>

                            <flux:menu.separator class="border-cyan-200" />

                            <flux:menu.radio.group>
                                <flux:menu.item
                                    :href="route('profile.edit')"
                                    icon="cog"
                                    class="hover:bg-cyan-50">
                                    {{ __('Configuracion') }}
                                </flux:menu.item>
                            </flux:menu.radio.group>

                            <flux:menu.separator class="border-cyan-200" />

                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item
                                    as="button"
                                    type="submit"
                                    icon="arrow-right-start-on-rectangle"
                                    class="w-full hover:bg-cyan-50"
                                    data-test="logout-button">
                                    {{ __('Cerrar sesion') }}
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </aside>

            <div class="flex min-h-screen flex-col">
                <header class="flex items-center gap-3 border-b border-cyan-100 bg-white px-4 py-3 shadow-sm lg:hidden">
                    <flux:sidebar.toggle class="text-cyan-700" icon="bars-2" inset="left" />

                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-lg font-semibold text-cyan-700">
                        <x-app-logo />
                    </a>

                    <flux:spacer />

                    <flux:dropdown position="top" align="end">
                        <flux:profile
                            :initials="auth()->user()->initials()"
                            icon-trailing="chevron-down"
                        />

                        <flux:menu>
                            <flux:menu.radio.group>
                                <div class="p-0 text-sm font-normal">
                                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                        <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                            <span class="flex h-full w-full items-center justify-center rounded-lg bg-cyan-100 text-cyan-700">
                                                {{ auth()->user()->initials() }}
                                            </span>
                                        </span>

                                        <div class="grid flex-1 text-start text-sm leading-tight">
                                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                        </div>
                                    </div>
                                </div>
                            </flux:menu.radio.group>

                            <flux:menu.separator class="border-cyan-100" />

                            <flux:menu.radio.group>
                                <flux:menu.item :href="route('profile.edit')" icon="cog">{{ __('Configuracion') }}</flux:menu.item>
                            </flux:menu.radio.group>

                            <flux:menu.separator class="border-cyan-100" />

                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                                    {{ __('Cerrar sesion') }}
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </header>

                <div class="flex-1 overflow-y-auto">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <flux:sidebar stashable sticky
            class="lg:hidden border-e border-cyan-100/80 bg-gradient-to-b from-white via-cyan-50 to-sky-100 p-6 text-slate-700 shadow-xl">
            <flux:sidebar.toggle class="text-cyan-700" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="mb-6 flex items-center gap-2 text-lg font-semibold text-cyan-700">
                <x-app-logo />
            </a>

            <flux:navlist variant="outline" class="text-slate-700">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item
                        icon="home"
                        :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')"
                        class="rounded-md transition hover:bg-white/70">
                        {{ __('Inicio') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline" class="text-slate-700">
                <flux:navlist.item
                    icon="folder-git-2"
                    href="https://github.com/bryansoberon"
                    target="_blank"
                    class="rounded-md transition hover:bg-white/70">
                    {{ __('Github bryan') }}
                </flux:navlist.item>

                <flux:navlist.item
                    icon="book-open-text"
                    href="https://www.instagram.com/bryansoberon"
                    target="_blank"
                    class="rounded-md transition hover:bg-white/70">
                    {{ __('Instagram') }}
                </flux:navlist.item>
            </flux:navlist>

            <div class="mt-6 space-y-3 text-sm">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-9 w-9 shrink-0 overflow-hidden rounded-lg">
                        <span class="flex h-full w-full items-center justify-center rounded-lg bg-cyan-100 text-cyan-700">
                            {{ auth()->user()->initials() }}
                        </span>
                    </span>

                    <div class="flex-1">
                        <p class="font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-cyan-600">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <div class="grid gap-2">
                    <flux:link :href="route('profile.edit')" icon="cog" class="justify-start text-sm font-medium text-slate-700 hover:text-cyan-700">
                        {{ __('Configuracion') }}
                    </flux:link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:button type="submit" icon="arrow-right-start-on-rectangle" variant="ghost" class="w-full justify-start text-sm font-medium text-slate-700 hover:text-cyan-700">
                            {{ __('Cerrar sesion') }}
                        </flux:button>
                    </form>
                </div>
            </div>
        </flux:sidebar>

        @livewireScripts
        @fluxScripts
    </body>
</html>
