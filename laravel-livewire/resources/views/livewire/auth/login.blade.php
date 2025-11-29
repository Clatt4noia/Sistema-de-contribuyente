<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
 #[Validate('required|string|email')]
 public string $email = '';

 #[Validate('required|string')]
 public string $password = '';

 public bool $remember = false;

 /**
 * Handle an incoming authentication request.
 */
 public function login(): void
 {
 $this->validate();

 $this->ensureIsNotRateLimited();

 if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
 RateLimiter::hit($this->throttleKey());

 throw ValidationException::withMessages([
 'email' => __('auth.failed'),
 ]);
 }

 RateLimiter::clear($this->throttleKey());
 Session::regenerate();

 $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
 }

 /**
 * Ensure the authentication request is not rate limited.
 */
 protected function ensureIsNotRateLimited(): void
 {
 if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
 return;
 }

 event(new Lockout(request()));

 $seconds = RateLimiter::availableIn($this->throttleKey());

 throw ValidationException::withMessages([
 'email' => __('auth.throttle', [
 'seconds' => $seconds,
 'minutes' => ceil($seconds / 60),
 ]),
 ]);
 }

 /**
 * Get the authentication rate limiting throttle key.
 */
 protected function throttleKey(): string
 {
 return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
 }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex justify-center">
        <img src="{{ asset('logoo1.png') }}" alt="Logo" class="h-60 w-auto object-contain" />
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="login" class="space-y-6">
        <!-- Email Address -->
        <div class="form-field">
            <label for="email" class="text-sm font-medium text-[color:var(--color-text)]">
                {{ __('Correo electronico') }}
            </label>
            <input
                id="email"
                type="email"
                wire:model="email"
                required
                autofocus
                autocomplete="email"
                placeholder="ingresa tu correo"
                class="form-control"
            />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-field">
            <div class="flex items-center justify-between">
                <label for="password" class="text-sm font-medium text-[color:var(--color-text)]">
                    {{ __('Contraseña') }}
                </label>
                @if (Route::has('password.request'))
                    <a class="text-sm font-semibold text-accent hover:underline" href="{{ route('password.request') }}">
                        {{ __('Olvidaste tu contraseña?') }}
                    </a>
                @endif
            </div>
            <input
                id="password"
                type="password"
                wire:model="password"
                required
                autocomplete="current-password"
                placeholder="{{ __('Contraseña') }}"
                class="form-control"
            />
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <label class="inline-flex items-center gap-3 text-sm text-[color:var(--color-text)]">
            <input
                type="checkbox"
                wire:model="remember"
                class="size-4 rounded border-[color:var(--color-border)] text-[color:var(--color-primary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-200 focus-visible:ring-offset-2 focus-visible:ring-offset-[color:var(--color-elevated)]"
            />
            <span>{{ __('Recordarme') }}</span>
        </label>

        <div>
            <button type="submit" class="btn btn-primary w-full" data-test="login-button">
                {{ __('Ingresar') }}
            </button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="flex justify-center gap-2 text-sm text-[color:var(--color-text-muted)]">
            <span>{{ __('No tienes una cuenta?') }}</span>
            <a class="font-semibold text-accent hover:underline" href="{{ route('register') }}">{{ __('Registrate') }}</a>
        </div>
    @endif
</div>
