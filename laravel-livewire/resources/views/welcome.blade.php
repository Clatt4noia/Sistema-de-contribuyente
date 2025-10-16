<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-700 antialiased">
        <div class="relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 -z-10 flex justify-center">
                <div class="h-64 w-full max-w-4xl bg-gradient-to-r from-[color:var(--color-primary-200)] via-[color:var(--color-primary-muted)] to-[color:var(--color-success-100)] blur-3xl"></div>
            </div>

            <header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-10">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[color:var(--color-primary)] text-[color:var(--color-primary-foreground)] shadow-lg">
                        <x-app-logo-icon class="h-7 w-7" />
                    </span>
                    <span class="text-lg font-semibold text-slate-800">{{ config('app.name', 'Laravel') }}</span>
                </div>
                @if (Route::has('login'))
                    <nav class="flex items-center gap-3 text-sm font-semibold">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="rounded-xl bg-white px-4 py-2 text-slate-600 shadow-sm transition hover:text-slate-900 hover:shadow">{{ __('Dashboard') }}</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-xl bg-white px-4 py-2 text-slate-600 shadow-sm transition hover:text-slate-900 hover:shadow">{{ __('Log in') }}</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">{{ __('Register') }}</a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <main class="mx-auto max-w-5xl px-6 pb-24">
                <section class="rounded-3xl border border-slate-200 bg-white p-12 text-center shadow-xl shadow-slate-200">
                    <h1 class="text-4xl font-semibold tracking-tight text-slate-900">{{ __('Gestiona tu logística con claridad absoluta') }}</h1>
                    <p class="mt-4 text-lg text-slate-600">{{ __('Monitorea pedidos, flota y finanzas desde un panel moderno y coherente diseñado para equipos de transporte.') }}</p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                            {{ __('Comenzar ahora') }}
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-secondary btn-lg">
                            {{ __('Ver panel de demostración') }}
                        </a>
                    </div>
                </section>

                <section class="mt-16 grid gap-6 md:grid-cols-3">
                    <article class="surface-card p-6 text-left">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-accent-soft text-accent">📦</span>
                        <h2 class="mt-4 text-lg font-semibold text-slate-900">{{ __('Pedidos y rutas') }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ __('Organiza entregas, visualiza avances y mantén informado a tu equipo en tiempo real.') }}</p>
                    </article>
                    <article class="surface-card p-6 text-left">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-success-soft text-success">🚚</span>
                        <h2 class="mt-4 text-lg font-semibold text-slate-900">{{ __('Gestión de flota') }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ __('Controla disponibilidad, mantenimientos y documentos clave de cada vehículo.') }}</p>
                    </article>
                    <article class="surface-card p-6 text-left">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-accent-soft text-accent">📈</span>
                        <h2 class="mt-4 text-lg font-semibold text-slate-900">{{ __('Finanzas en orden') }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ __('Centraliza facturas, pagos y métricas con reportes claros y accionables.') }}</p>
                    </article>
                </section>
            </main>
        </div>
    </body>
</html>
