<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
 public string $name = '';
 public string $email = '';

 /**
 * Mount the component.
 */
 public function mount(): void
 {
 $this->name = Auth::user()->name;
 $this->email = Auth::user()->email;
 }

 /**
 * Update the profile information for the currently authenticated user.
 */
 public function updateProfileInformation(): void
 {
 $user = Auth::user();

 $validated = $this->validate([
 'name' => ['required', 'string', 'max:255'],

 'email' => [
 'required',
 'string',
 'lowercase',
 'email',
 'max:255',
 Rule::unique(User::class)->ignore($user->id)
 ],
 ]);

 $user->fill($validated);

 if ($user->isDirty('email')) {
 $user->email_verified_at = null;
 }

 $user->save();

 $this->dispatch('profile-updated', name: $user->name);
 }

 /**
 * Send an email verification notification to the current user.
 */
 public function resendVerificationNotification(): void
 {
 $user = Auth::user();

 if ($user->hasVerifiedEmail()) {
 $this->redirectIntended(default: route('dashboard', absolute: false));

 return;
 }

 $user->sendEmailVerificationNotification();

 Session::flash('status', 'verification-link-sent');
 }
}; ?>

<section class="w-full text-slate-800 [&_*]:!text-slate-800">
    @include('partials.settings-heading')

    <x-settings.layout
        :heading="__('Profile')"
        :subheading="__('Update your name and email address')"
        class="text-slate-800"
    >
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">

            <flux:input
                wire:model="name"
                :label="__('Name')"
                type="text"
                class="text-slate-800 !placeholder-slate-500"
                required
                autofocus
                autocomplete="name"
            />

            <flux:input
                wire:model="email"
                :label="__('Email')"
                type="email"
                class="text-slate-800 !placeholder-slate-500"
                required
                autocomplete="email"
            />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-4 space-y-2 text-sm text-slate-600">
                    <p>Your email address is unverified.</p>

                    <button
                        type="button"
                        class="btn btn-ghost btn-sm"
                        wire:click.prevent="resendVerificationNotification"
                    >
                        Click here to re-send the verification email.
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </p>
                    @endif
                </div>
            @endif

            <div class="flex items-center gap-4">
              <flux:button
                variant="primary"
                type="submit"
                class="btn btn-primary"
            >
                Save
            </flux:button>


            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>

