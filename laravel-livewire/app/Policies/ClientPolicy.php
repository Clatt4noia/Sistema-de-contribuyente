<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            ...User::LOGISTICS_ROLES,
            ...User::FINANCE_ROLES,
        ]);

    }

    public function view(User $user, Client $client): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            User::ROLE_ADMIN,
            User::ROLE_LOGISTICS_MANAGER,
        ]);

    }

    public function update(User $user, Client $client): bool
    {
        return $user->hasAnyRole([
            User::ROLE_ADMIN,
            User::ROLE_LOGISTICS_MANAGER,
            User::ROLE_FINANCE_MANAGER,
        ]);

    }

    public function delete(User $user, Client $client): bool
    {
        return $this->create($user);
    }
}
