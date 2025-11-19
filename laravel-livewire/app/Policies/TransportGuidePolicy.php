<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\TransportGuide;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransportGuidePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->hasAnyRole([
            UserRole::ADMIN,
            UserRole::LOGISTICS_MANAGER,
            UserRole::FLEET_MANAGER,
            UserRole::FINANCE_MANAGER,
            UserRole::FINANCE_ANALYST,
        ])) {
            return true;
        }

        if ($user->hasRole(UserRole::CLIENT)) {
            return $user->client_id !== null;
        }

        return false;
    }

    public function view(User $user, TransportGuide $transportGuide): bool
    {
        if ($user->hasAnyRole([
            UserRole::ADMIN,
            UserRole::LOGISTICS_MANAGER,
            UserRole::FLEET_MANAGER,
            UserRole::FINANCE_MANAGER,
            UserRole::FINANCE_ANALYST,
        ])) {
            return true;
        }

        if ($user->hasRole(UserRole::CLIENT) && $user->client_id) {
            return (int) $transportGuide->client_id === (int) $user->client_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN,
            UserRole::LOGISTICS_MANAGER,
        ]);
    }

    public function update(User $user, TransportGuide $transportGuide): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, TransportGuide $transportGuide): bool
    {
        return $this->create($user);
    }

    public function sendToSunat(User $user, TransportGuide $transportGuide): bool
    {
        return $this->create($user);
    }

    public function issue(User $user, TransportGuide $transportGuide): bool
    {
        return $this->sendToSunat($user, $transportGuide);
    }

}
