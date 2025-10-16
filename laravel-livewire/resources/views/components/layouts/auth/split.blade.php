<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[color:var(--color-surface)] text-[color:var(--color-text)] antialiased">
        <div class="flex min-h-svh flex-col bg-[color:var(--color-elevated)] lg:grid lg:grid-cols-2">
            <div class="relative hidden h-dvh flex-col bg-[color:var(--color-primary)] p-10 text-[color:var(--color-primary-foreground)] lg:flex">
                <div class="absolute inset-0 rounded-br-[3rem] bg-gradient-to-br from-[color:var(--color-primary-300)] via-[color:var(--color-primary)] to-[color:var(--color-primary-emphasis)] opacity-90"></div>
                <div class="relative z-10 flex flex-1 flex-col">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-[color:var(--color-primary-foreground)/0.16]">
                            <x-app-logo-icon class="size-9 fill-current text-[color:var(--color-primary-foreground)]" />
                        </span>
                        <span class="text-lg font-semibold text-[color:var(--color-primary-foreground)]">{{ config('app.name', 'Laravel') }}</span>
                    </div>
                    <div class="mt-auto">
                        {{ $aside ?? '' }}
                    </div>
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
