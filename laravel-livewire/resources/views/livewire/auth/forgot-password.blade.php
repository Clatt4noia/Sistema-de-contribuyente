<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    @if (config('mail.default') === 'log')
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <p class="font-semibold">{{ __('Emails are not delivered in this environment.') }}</p>
            <p class="mt-2 leading-relaxed">
                {{ __('Password reset links are written to :path. Open the latest entry and look for "Reset Password Notification" to copy the URL.', ['path' => storage_path('logs/laravel.log')]) }}
            </p>
        </div>
    @endif

    <form method="POST" wire:submit="sendPasswordResetLink" class="space-y-6">
        <!-- Email Address -->
        <div class="form-field">
            <label for="email" class="text-sm font-medium text-[color:var(--color-text)]">
                {{ __('Email Address') }}
            </label>
            <input
                id="email"
                type="email"
                wire:model="email"
                required
                autofocus
                placeholder="correo@ejemplo.com"
                class="form-control"
            />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-full" data-test="email-password-reset-link-button">
            {{ __('Email password reset link') }}
        </button>
    </form>

    <div class="flex justify-center gap-2 text-sm text-[color:var(--color-text-muted)]">
        <span>{{ __('Or, return to') }}</span>
        <a class="font-semibold text-accent hover:underline" href="{{ route('login') }}">{{ __('log in') }}</a>
    </div>
</div>
