<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * TruckPolicyV2 extiende la TruckPolicy original para centralizar la lógica
 * de roles con el enum App\Enums\UserRole. Mantiene la antigua como
 * compatibilidad temporal mientras el equipo migra sus referencias.
 */
class TruckPolicyV2
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(UserRole::forLogistics());
    }

    public function view(User $user, Truck $truck): bool
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

    public function update(User $user, Truck $truck): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Truck $truck): bool
    {
        return $this->create($user);
    }
}
