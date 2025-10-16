<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
 public string $name = '';
 public string $email = '';
 public string $password = '';
 public string $password_confirmation = '';
 public string $role = UserRole::LOGISTICS_MANAGER->value;


 /**
 * Handle an incoming registration request.
 */
 public function register(): void
 {
 $validated = $this->validate([
 'name' => ['required', 'string', 'max:255'],
 'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
 'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
 'role' => [
 'required',
 Rule::enum(UserRole::class),
 Rule::in(array_map(
 fn (UserRole $role) => $role->value,
 UserRole::forSelfRegistration()
 )),
 ],

 ]);

 $validated['password'] = Hash::make($validated['password']);

 event(new Registered(($user = User::create($validated))));

 Auth::login($user);

 $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
 }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Crea tu cuenta')" :description="__('Ingresa tus datos para crear tu cuenta')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="register" class="space-y-6">
        <!-- Name -->
        <div class="form-field">
            <label for="name" class="text-sm font-medium text-[color:var(--color-text)]">
                {{ __('Nombre') }}
            </label>
            <input
                id="name"
                type="text"
                wire:model="name"
                required
                autofocus
                autocomplete="name"
                placeholder="{{ __('Full name') }}"
                class="form-control"
            />
            @error('name')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="form-field">
            <label for="email" class="text-sm font-medium text-[color:var(--color-text)]">
                {{ __('Correo electrónico') }}
            </label>
            <input
                id="email"
                type="email"
                wire:model="email"
                required
                autocomplete="email"
                placeholder="correo@ejemplo.com"
                class="form-control"
            />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-field">
            <label for="password" class="text-sm font-medium text-[color:var(--color-text)]">
                {{ __('Contraseña') }}
            </label>
            <input
                id="password"
                type="password"
                wire:model="password"
                required
                autocomplete="new-password"
                placeholder="{{ __('Contraseña') }}"
                class="form-control"
            />
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="form-field">
            <label for="password_confirmation" class="text-sm font-medium text-[color:var(--color-text)]">
                {{ __('Confirmar Contraseña') }}
            </label>
            <input
                id="password_confirmation"
                type="password"
                wire:model="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="{{ __('Confirmar Contraseña') }}"
                class="form-control"
            />
            @error('password_confirmation')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label for="role" class="text-sm font-medium text-[color:var(--color-text)]">
                {{ __('Rol de acceso') }}
            </label>
            <select
                id="role"
                wire:model="role"
                required
                class="form-control"
            >
                @foreach (\App\Models\User::roleOptions(UserRole::forSelfRegistration()) as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('role')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <button type="submit" class="btn btn-primary w-full" data-test="register-user-button">
                {{ __('Crear Cuenta') }}
            </button>
        </div>
    </form>

    <div class="flex justify-center gap-2 text-sm text-[color:var(--color-text-muted)]">
        <span>{{ __('Ya tienes una cuenta?') }}</span>
        <a class="font-semibold text-accent hover:underline" href="{{ route('login') }}">{{ __('Iniciar Sesión') }}</a>
    </div>
</div>
