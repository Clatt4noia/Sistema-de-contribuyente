<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900 antialiased dark:bg-zinc-950 dark:text-slate-100">
        <flux:sidebar sticky stashable
            class="border-e border-indigo-500/20
                bg-gradient-to-b from-indigo-600 via-indigo-700 to-slate-900
                dark:border-indigo-400/30
                dark:from-indigo-700 dark:via-indigo-900 dark:to-slate-950
                text-white shadow-2xl">

            <!-- Botón para cerrar en mobile -->
            <flux:sidebar.toggle class="lg:hidden text-white hover:text-indigo-200" icon="x-mark" />

            <!-- Logo -->
            <a href="{{ route('dashboard') }}"
            class="me-5 flex items-center space-x-2 rtl:space-x-reverse text-lg font-semibold text-white transition hover:text-indigo-200"
            wire:navigate>
                <x-app-logo />
            </a>

            <!-- Menú principal -->
            <flux:navlist variant="outline" class="text-white">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item
                        icon="home"
                        :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')"
                        class="rounded-md transition hover:bg-white/10"
                        wire:navigate>
                        {{ __('Inicio') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <!-- Links externos -->
            <flux:navlist variant="outline" class="text-white">
                <flux:navlist.item icon="folder-git-2"
                    href="https://github.com/bryansoberon"
                    target="_blank"
                    class="rounded-md transition hover:bg-white/10">
                    {{ __('Github bryan') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text"
                    href="https://www.instagram.com/bryansoberon"
                    target="_blank"
                    class="rounded-md transition hover:bg-white/10">
                    {{ __('Instagram') }}
                </flux:navlist.item>
            </flux:navlist>

            <!-- Menú de usuario en desktop -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                    class="text-white"
                />

                <flux:menu class="w-[240px] rounded-2xl bg-slate-900/90 text-white shadow-xl ring-1 ring-indigo-500/30">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-white/15 text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-indigo-200">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator class="border-indigo-500/40" />

                    <flux:menu.radio.group>
                        <flux:menu.item
                            :href="route('profile.edit')"
                            icon="cog"
                            wire:navigate
                            class="hover:bg-white/10">
                            {{ __('Configuración') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator class="border-indigo-500/40" />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full hover:bg-white/10"
                            data-test="logout-button">
                            {{ __('Cerrar sesión') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>


        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

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
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 dark:bg-neutral-700 dark:text-white"
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

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Configuración') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Cerrar sesión') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <main class="min-h-screen w-full px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-6xl space-y-6">
                {{ $slot }}
            </div>
        </main>

        @fluxScripts
    </body>
</html>
