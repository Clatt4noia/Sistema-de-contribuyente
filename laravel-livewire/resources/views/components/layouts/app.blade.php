<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main class="min-h-screen w-full px-4 py-6 sm:px-6 lg:px-8 mx-auto max-w-6xl space-y-6">
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
