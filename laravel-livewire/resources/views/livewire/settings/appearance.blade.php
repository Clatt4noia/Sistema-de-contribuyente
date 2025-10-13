<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <div class="space-y-4">
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            </flux:radio.group>
            <p class="text-sm text-slate-500">
                {{ __('The interface now uses a single light theme across all módulos to keep the experience consistent.') }}
            </p>
        </div>
    </x-settings.layout>
</section>
