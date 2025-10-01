<?php

namespace App\Policies;

use App\Models\Truck;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TruckPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(User::LOGISTICS_ROLES);
    }

    public function view(User $user, Truck $truck): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(User::FLEET_MANAGEMENT_ROLES);
    }

    public function update(User $user, Truck $truck): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Truck $truck): bool
    {
        return $this->create($user);
    }
}
