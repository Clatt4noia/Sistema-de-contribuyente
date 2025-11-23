<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(UserRole::forFinance());
    }

    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->hasAnyRole([UserRole::ADMIN, UserRole::FINANCE_MANAGER])) {
            return true;
        }

        return $user->hasRole(UserRole::FINANCE_ANALYST)
            && $transaction->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN,
            UserRole::FINANCE_MANAGER,
        ]);
    }

    public function update(User $user, Transaction $transaction): bool
    {
        if (! $this->view($user, $transaction)) {
            return false;
        }

        return $this->create($user);
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $this->update($user, $transaction);
    }
}
