<?php

namespace Tests\Feature\Authorization;

use App\Enums\UserRole;
use App\Domains\Finance\Livewire\TransactionList;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_finance_user_cannot_access_finance_pages(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::LOGISTICS_MANAGER,
        ]);

        $this->actingAs($user)
            ->get(route('finance.transactions.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('finance.transactions.analytics'))
            ->assertForbidden();
    }

    public function test_finance_analyst_cannot_mutate_transactions(): void
    {
        $analyst = User::factory()->financeAnalyst()->create();
        $transaction = Transaction::create([
            'user_id' => $analyst->id,
            'type' => 'income',
            'category' => 'Consulting',
            'amount' => 100,
            'occurred_on' => now()->format('Y-m-d'),
            'description' => 'Existing record',
        ]);

        Livewire::actingAs($analyst)
            ->test(TransactionList::class)
            ->set('formType', 'income')
            ->set('category', 'New entry')
            ->set('amount', '50.00')
            ->set('occurred_on', now()->format('Y-m-d'))
            ->call('saveTransaction')
            ->assertForbidden();

        Livewire::actingAs($analyst)
            ->test(TransactionList::class)
            ->call('openEditModal', $transaction->id)
            ->set('category', 'Updated category')
            ->call('saveTransaction')
            ->assertForbidden();

        Livewire::actingAs($analyst)
            ->test(TransactionList::class)
            ->call('deleteTransaction', $transaction->id)
            ->assertForbidden();
    }

    public function test_finance_manager_can_manage_own_transactions(): void
    {
        $manager = User::factory()->financeManager()->create();

        Livewire::actingAs($manager)
            ->test(TransactionList::class)
            ->set('formType', 'expense')
            ->set('category', 'Supplies')
            ->set('amount', '25.00')
            ->set('occurred_on', now()->format('Y-m-d'))
            ->call('saveTransaction')
            ->assertHasNoErrors();

        $transaction = Transaction::first();

        Livewire::actingAs($manager)
            ->test(TransactionList::class)
            ->call('openEditModal', $transaction->id)
            ->set('category', 'Updated supplies')
            ->call('saveTransaction')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'category' => 'Updated supplies',
            'user_id' => $manager->id,
        ]);

        Livewire::actingAs($manager)
            ->test(TransactionList::class)
            ->call('deleteTransaction', $transaction->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }
}
