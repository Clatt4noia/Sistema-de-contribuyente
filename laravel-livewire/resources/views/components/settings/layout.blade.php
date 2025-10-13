<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('profile.edit')">{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('password.edit')">{{ __('Password') }}</flux:navlist.item>
            <flux:navlist.item :href="route('appearance.edit')">{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    @php
        $headingText = $heading ?? null;
        $subheadingText = $subheading ?? null;
    @endphp

    <div class="flex-1 self-stretch max-md:pt-6">
        @if (! empty($headingText))
            <h2 class="text-xl font-semibold text-slate-800">{{ $headingText }}</h2>
        @endif

        @if (! empty($subheadingText))
            <p class="mt-1 text-sm text-slate-500">{{ $subheadingText }}</p>
        @endif

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
