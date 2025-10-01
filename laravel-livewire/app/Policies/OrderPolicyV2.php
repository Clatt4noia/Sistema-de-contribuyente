<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * OrderPolicyV2 expone reglas basadas en UserRole para órdenes y convive con
 * la OrderPolicy heredada hasta que sea retirada.
 */
class OrderPolicyV2
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            ...UserRole::forLogistics(),
            ...UserRole::forFinance(),
        ]);
    }

    public function view(User $user, Order $order): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN,
            UserRole::LOGISTICS_MANAGER,
        ]);
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
