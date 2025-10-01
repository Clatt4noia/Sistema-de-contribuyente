<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * MaintenancePolicyV2 usa UserRole para alinear los permisos con la matriz
 * documentada sin tocar la versión previa.
 */
class MaintenancePolicyV2
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(UserRole::forLogistics());
    }

    public function view(User $user, Maintenance $maintenance): bool
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

    public function update(User $user, Maintenance $maintenance): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Maintenance $maintenance): bool
    {
        return $this->create($user);
    }
}
