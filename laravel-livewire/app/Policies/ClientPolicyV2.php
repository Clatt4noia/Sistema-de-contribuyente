<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ClientPolicyV2 mantiene la compatibilidad con ClientPolicy pero actualiza
 * las reglas usando UserRole para evitar listas sueltas de strings.
 */
class ClientPolicyV2
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            ...UserRole::forLogistics(),
            ...UserRole::forFinance(),
        ]);
    }

    public function view(User $user, Client $client): bool
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

    public function update(User $user, Client $client): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN,
            UserRole::LOGISTICS_MANAGER,
            UserRole::FINANCE_MANAGER,
        ]);
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->create($user);
    }
}
