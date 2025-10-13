<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#f5f8ff] antialiased">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-gradient-to-br from-white via-sky-50 to-indigo-50/70 p-6 md:p-10">
            <div class="flex flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium">
                    <span class="flex h-9 w-9 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-9 fill-current text-black" />
                    </span>

                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>

                <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white/95 text-slate-700 shadow-xl shadow-slate-200/60">
                    <div class="px-8 py-6">{{ $slot }}</div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
