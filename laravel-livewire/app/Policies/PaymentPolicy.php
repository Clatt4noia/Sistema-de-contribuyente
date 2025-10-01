<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'billing_manager', 'viewer']);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'billing_manager']);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->create($user);
    }
}
