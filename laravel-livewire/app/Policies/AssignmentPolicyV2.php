<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * AssignmentPolicyV2 introduce enums para facilitar auditorías de permisos
 * y convivirá con AssignmentPolicy hasta que se elimine.
 */
class AssignmentPolicyV2
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(UserRole::forLogistics());
    }

    public function view(User $user, Assignment $assignment): bool
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

    public function update(User $user, Assignment $assignment): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->create($user);
    }
}
