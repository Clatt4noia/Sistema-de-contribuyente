<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssignmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'fleet_manager', 'logistics_manager', 'viewer']);
    }

    public function view(User $user, Assignment $assignment): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'fleet_manager', 'logistics_manager']);
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->create($user);
    }
}
