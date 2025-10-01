<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * InvoicePolicyV2 aplica la misma regla funcional que InvoicePolicy pero con
 * UserRole para documentar explícitamente los actores financieros.
 */
class InvoicePolicyV2
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(UserRole::forFinance());
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN,
            UserRole::FINANCE_MANAGER,
        ]);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->create($user);
    }
}
