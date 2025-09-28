<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
        <flux:sidebar sticky stashable
            class="border-e border-cyan-100/80
                bg-gradient-to-b from-white via-cyan-50 to-sky-100
                text-slate-700 shadow-xl">

            <!-- Botón para cerrar en mobile -->
            <flux:sidebar.toggle class="lg:hidden text-cyan-600 hover:text-slate-800" icon="x-mark" />

            <!-- Logo -->
            <a href="{{ route('dashboard') }}"
            class="me-5 flex items-center space-x-2 rtl:space-x-reverse text-lg font-semibold text-cyan-700 transition hover:text-slate-900"
            wire:navigate>
                <x-app-logo />
            </a>

            <!-- Menú principal -->
            <flux:navlist variant="outline" class="text-slate-700">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item
                        icon="home"
                        :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')"
                        class="rounded-md transition hover:bg-white/70"

                        wire:navigate>
                        {{ __('Inicio') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <!-- Links externos -->
            <flux:navlist variant="outline" class="text-slate-700">
                <flux:navlist.item icon="folder-git-2"
                    href="https://github.com/bryansoberon"
                    target="_blank"
                    class="rounded-md transition hover:bg-white/70">

                    {{ __('Github bryan') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text"
                    href="https://www.instagram.com/bryansoberon"
                    target="_blank"
                    class="rounded-md transition hover:bg-white/70">

                    {{ __('Instagram') }}
                </flux:navlist.item>
            </flux:navlist>

            <!-- Menú de usuario en desktop -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                    class="text-cyan-700"
                />

                <flux:menu class="w-[240px] rounded-2xl bg-white/95 text-slate-700 shadow-xl ring-1 ring-cyan-100">

                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-cyan-100 text-cyan-700"

                                    >
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
                            wire:navigate
                            class="hover:bg-cyan-50">

                            {{ __('Configuración') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator class="border-cyan-200" />


                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full hover:bg-cyan-50"

                            data-test="logout-button">
                            {{ __('Cerrar sesión') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>


        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden text-cyan-700" icon="bars-2" inset="left" />

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
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-cyan-100 text-cyan-700"

                                    >
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
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Configuración') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator class="border-cyan-100" />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Cerrar sesión') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @livewireScripts
        @fluxScripts

    </body>
</html>
