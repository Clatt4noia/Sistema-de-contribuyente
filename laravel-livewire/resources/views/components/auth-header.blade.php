<div class="text-center space-y-2 mb-6">
    <div class="flex justify-center">
        <img src="{{ asset('logoo1.png') }}" alt="Logo" class="h-24 w-auto object-contain shadow-md" />
    </div>
    <h1 class="text-2xl font-bold text-token">
        {{ $title }}
    </h1>

    @isset($description)
        <p class="text-sm text-token-muted">
            {{ $description }}
        </p>
    @endisset
</div>
