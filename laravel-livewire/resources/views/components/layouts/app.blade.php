<x-layouts.app.sidebar :title="$title ?? null">
    <main class="mx-auto w-full max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>
</x-layouts.app.sidebar>
