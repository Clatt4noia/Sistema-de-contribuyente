<?php

namespace App\Policies;

use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaintenancePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(User::LOGISTICS_ROLES);
    }

    public function view(User $user, Maintenance $maintenance): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(User::FLEET_MANAGEMENT_ROLES);
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
