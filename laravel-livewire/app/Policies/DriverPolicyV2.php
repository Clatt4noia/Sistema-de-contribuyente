<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * DriverPolicyV2 replica la DriverPolicy existente pero orientada a enums
 * para simplificar el mantenimiento de roles.
 */
class DriverPolicyV2
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(UserRole::forLogistics());
    }

    public function view(User $user, Driver $driver): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN,
            UserRole::FLEET_MANAGER,
        ]);
    }

    public function update(User $user, Driver $driver): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Driver $driver): bool
    {
        return $this->create($user);
    }
}
