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
        return $user->hasAnyRole(['admin', 'fleet_manager', 'viewer']);
    }

    public function view(User $user, Maintenance $maintenance): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'fleet_manager']);
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
