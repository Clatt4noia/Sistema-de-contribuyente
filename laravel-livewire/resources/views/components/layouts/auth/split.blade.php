<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[color:var(--color-surface)] text-[color:var(--color-text)] antialiased">
        <div class="flex min-h-svh flex-col bg-[color:var(--color-elevated)] lg:grid lg:grid-cols-2">
            <div class="relative hidden h-dvh lg:flex rounded-br-[3rem] overflow-hidden">
                <div class="flex h-full w-full items-center justify-center p-6">
                    <img src="{{ asset('logoo1.png') }}" alt="Logo" class="max-h-[90%] max-w-[90%] object-contain" />
                </div>
            </div>

            <div class="flex items-center justify-center px-6 py-12">
                <div class="surface-card w-full max-w-md">
                    <div class="px-10 py-8">{{ $slot }}</div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
