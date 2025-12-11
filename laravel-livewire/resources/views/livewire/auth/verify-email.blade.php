<?php

use App\Domains\Auth\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
 /**
 * Send an email verification notification to the user.
 */
 public function sendVerification(): void
 {
 Auth::user()->sendEmailVerificationNotification();

 Session::flash('status', 'verification-link-sent');
 }

 /**
 * Log the current user out of the application.
 */
 public function logout(Logout $logout): void
 {
 $logout();

 $this->redirect('/', navigate: true);
 }

 /**
 * Handle the component's rendering hook.
 */
 public function rendering(View $view): void
 {
 if (Auth::user()->hasVerifiedEmail()) {
 $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

 return;
 }
 }
}; ?>

<div class="mt-4 flex flex-col gap-6">
    <p class="text-center text-[color:var(--color-text)]">
        {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <p class="text-center text-sm font-medium text-success">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </p>
    @endif

    <div class="flex flex-col gap-3">
        <button type="button" wire:click="sendVerification" class="btn btn-primary w-full">
            {{ __('Resend verification email') }}
        </button>

        <button type="button" wire:click="logout" class="btn btn-ghost w-full" data-test="logout-button">
            {{ __('Log out') }}
        </button>
    </div>
</div>
