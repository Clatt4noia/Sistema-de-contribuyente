<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * PaymentPolicyV2 migra la autorización a UserRole para pagos sin borrar la
 * versión previa usada por otros equipos.
 */
class PaymentPolicyV2
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(UserRole::forFinance());
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN,
            UserRole::FINANCE_MANAGER,
        ]);
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
