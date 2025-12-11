<?php

namespace Tests\Feature\Authorization;

use App\Domains\Billing\Livewire\InvoiceForm;
use App\Domains\Billing\Livewire\PaymentList;
use App\Domains\Fleet\Livewire\TruckList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Comprueba que las políticas v2 basadas en enums respetan los accesos
 * definidos para logística y finanzas.
 */
class PolicyV2AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_logistics_manager_can_list_trucks_with_policy_v2(): void
    {
        $user = User::factory()->logisticsManager()->create();

        Livewire::actingAs($user)
            ->test(TruckList::class)
            ->assertOk();
    }

    public function test_client_cannot_list_trucks_with_policy_v2(): void
    {
        $user = User::factory()->client()->create();

        Livewire::actingAs($user)
            ->test(TruckList::class)
            ->assertForbidden();
    }

    public function test_finance_manager_can_list_payments_with_policy_v2(): void
    {
        $user = User::factory()->financeManager()->create();

        Livewire::actingAs($user)
            ->test(PaymentList::class)
            ->assertOk();
    }

    public function test_finance_analyst_cannot_create_invoice_with_policy_v2(): void
    {
        $user = User::factory()->financeAnalyst()->create();

        Livewire::actingAs($user)
            ->test(InvoiceForm::class)
            ->assertForbidden();
    }
}
