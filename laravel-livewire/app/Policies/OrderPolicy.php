<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            ...User::LOGISTICS_ROLES,
            ...User::FINANCE_ROLES,
        ]);
    }

    public function view(User $user, Order $order): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'logistics_manager']);
    }

    public function update(User $user, Order $order): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Order $order): bool
    {
        return $this->create($user);
    }
}
