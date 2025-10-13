<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gradient-to-br from-[#f7faff] via-white to-[#eaf1ff] antialiased">
        <div class="flex min-h-svh flex-col bg-gradient-to-br from-white via-slate-50 to-sky-50/70 lg:grid lg:grid-cols-2">
            <div class="relative hidden h-dvh flex-col p-10 text-white lg:flex">
                <div class="absolute inset-0 rounded-br-[3rem] bg-gradient-to-br from-indigo-500 via-sky-500 to-cyan-400"></div>
                <div class="relative z-10 flex flex-1 flex-col">
                    <div class="flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-white/10">
                            <x-app-logo-icon class="size-9 fill-current text-white" />
                        </span>
                        <span class="text-lg font-semibold">{{ config('app.name', 'Laravel') }}</span>
                    </div>
                    <div class="mt-auto">
                        {{ $aside ?? '' }}
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center p-6">
                <div class="w-full max-w-md rounded-2xl border border-slate-100/80 bg-white/95 text-slate-700 shadow-xl shadow-slate-200/60 backdrop-blur">
                    <div class="px-10 py-8">{{ $slot }}</div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
