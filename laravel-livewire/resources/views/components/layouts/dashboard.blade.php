@props([
    'title' => null,
    'menu' => null,
])

<x-layouts.app.sidebar :title="$title" :menu="$menu">
    <main class="relative mx-auto w-full max-w-6xl space-y-6 px-4 py-8 transition-colors duration-300 sm:px-6 lg:px-12">
        {{ $slot }}
    </main>
</x-layouts.app.sidebar>
