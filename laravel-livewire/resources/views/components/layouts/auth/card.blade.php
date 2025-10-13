<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gradient-to-br from-[#f7faff] via-white to-[#eaf1ff] antialiased">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-gradient-to-br from-white via-slate-50 to-sky-50/70 p-6 md:p-10">

            <div class="flex w-full max-w-md flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium">
                    <span class="flex h-9 w-9 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-9 fill-current text-black" />
                    </span>

                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>

                <div class="flex flex-col gap-6">
                    <div class="rounded-2xl border border-slate-100/80 bg-white/95 text-slate-700 shadow-xl shadow-slate-200/60 backdrop-blur">

                        <div class="px-10 py-8">{{ $slot }}</div>
                    </div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</x-theme.html>
