<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-surface text-token antialiased">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-surface p-6 md:p-10">

            <div class="flex flex-col gap-6">

                <div class="w-full max-w-sm rounded-2xl border border-token bg-elevated text-token shadow-xl">

                    <div class="px-8 py-6">{{ $slot }}</div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
