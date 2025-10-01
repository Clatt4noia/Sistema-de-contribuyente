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
        return $user->hasAnyRole(User::LOGISTICS_ROLES);

    }

    public function view(User $user, Assignment $assignment): bool
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

    public function update(User $user, Assignment $assignment): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->create($user);
    }
}
