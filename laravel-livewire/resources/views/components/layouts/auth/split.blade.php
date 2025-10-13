<x-theme.html>

    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh lg:grid-cols-2">
            <div class="relative hidden lg:flex flex-col p-10 text-white h-dvh dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-purple-600 dark:bg-purple-900"></div>

                    <a href="{{ route('home') }}" class="relative z-20 flex items-center text-lg font-medium">

                        {{ config('app.name', 'bryanmenu') }}
                    </a>

                    @php
                        [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                    @endphp

                    <div class="relative z-20 mt-auto">
                        <blockquote class="space-y-2">
                            <flux:heading size="lg">&ldquo;{{ __('Entorno sofisticado') }}&rdquo;</flux:heading>
                            <footer><flux:heading>{{ __('Bryan Soberón') }}</flux:heading></footer>
                        </blockquote>
                    </div>
                </div>
                <div class="w-full lg:p-8 flex items-center justify-center">
                    <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                        <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden">
                            <span class="flex h-9 w-9 items-center justify-center rounded-md">
                                <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                            </span>

                            <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                        </a>
                        {{ $slot }}
                    </div>
                </div>

            </div>
        </div>

        @fluxScripts

    </body>
</x-theme.html>
