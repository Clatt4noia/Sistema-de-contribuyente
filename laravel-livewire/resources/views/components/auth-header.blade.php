<div class="text-center space-y-1 mb-6">
    <h1 class="text-2xl font-bold text-token">
        {{ $title }}
    </h1>

    @isset($description)
        <p class="text-sm text-token-muted">
            {{ $description }}
        </p>
    @endisset
</div>
