<?php

namespace Tests\Feature\Authorization;

use App\Domains\Billing\Livewire\InvoiceForm;
use App\Domains\Billing\Livewire\InvoiceList;
use App\Domains\Billing\Livewire\PaymentForm;
use App\Domains\Billing\Livewire\PaymentList;
use App\Domains\Clients\Livewire\ClientForm;
use App\Domains\Clients\Livewire\ClientList;
use App\Domains\Fleet\Livewire\AssignmentForm;
use App\Domains\Fleet\Livewire\AssignmentList;
use App\Domains\Fleet\Livewire\DriverForm;
use App\Domains\Fleet\Livewire\DriverList;
use App\Domains\Fleet\Livewire\MaintenanceForm;
use App\Domains\Fleet\Livewire\MaintenanceList;
use App\Domains\Fleet\Livewire\TruckForm;
use App\Domains\Fleet\Livewire\TruckList;
use App\Domains\Orders\Livewire\OrderForm;
use App\Domains\Orders\Livewire\OrderList;
use App\Models\Assignment;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Maintenance;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permissions_cannot_view_management_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $routes = [
            route('fleet.trucks.index'),
            route('fleet.drivers.index'),
            route('fleet.maintenance.index'),
            route('fleet.assignments.index'),
            route('fleet.report'),
            route('orders.index'),
            route('clients.index'),
            route('billing.invoices.index'),
            route('billing.payments.index'),
        ];

        foreach ($routes as $route) {
            $this->get($route)->assertForbidden();
        }
    }

    public function test_finance_analyst_cannot_manage_fleet_resources(): void
    {
        $financeAnalyst = User::factory()->financeAnalyst()->create();


        $client = Client::create([
            'business_name' => 'Client Co',
            'tax_id' => '123456789',
            'contact_name' => 'John Doe',
        ]);

        $truck = Truck::create([
            'plate_number' => 'ABC123',
            'brand' => 'Volvo',
            'model' => 'FH',
            'year' => 2020,
            'type' => 'Trailer',
            'capacity' => 10000,
            'mileage' => 1000,
            'status' => 'available',
        ]);

        $driver = Driver::factory()->create([
            'name' => 'Jane',
            'last_name' => 'Doe',
            'license_expiration' => now()->addYear(),
            'status' => 'active',
        ]);

        $order = Order::create([
            'client_id' => $client->id,
            'reference' => 'ORD-1',
            'origin' => 'Origin',
            'destination' => 'Destination',
            'status' => 'pending',
        ]);

        $maintenance = Maintenance::create([
            'truck_id' => $truck->id,
            'maintenance_date' => now(),
            'maintenance_type' => 'Oil change',
            'cost' => 100,
            'status' => 'scheduled',
            'description' => 'Mantenimiento preventivo de prueba.',
        ]);

        $assignment = Assignment::create([
            'order_id' => $order->id,
            'truck_id' => $truck->id,
            'driver_id' => $driver->id,
            'start_date' => now(),
            'status' => 'scheduled',
            'description' => 'Test assignment',
        ]);

        $this->actingAs($financeAnalyst)->get(route('fleet.trucks.index'))->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(TruckList::class)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(TruckForm::class)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(TruckForm::class, ['truck' => $truck])
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(DriverList::class)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(DriverForm::class)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(DriverForm::class, ['id' => $driver->id])
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(MaintenanceList::class)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(MaintenanceForm::class)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(MaintenanceForm::class, ['id' => $maintenance->id])
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(AssignmentList::class)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(AssignmentForm::class)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)

            ->test(AssignmentForm::class, ['id' => $assignment->id])
            ->assertForbidden();
    }

    public function test_finance_analyst_cannot_mutate_order_records(): void
    {
        $financeAnalyst = User::factory()->financeAnalyst()->create();


        $client = Client::create([
            'business_name' => 'Client Co',
            'tax_id' => '123456780',
            'contact_name' => 'John Doe',
        ]);

        $order = Order::create([
            'client_id' => $client->id,
            'reference' => 'ORD-2',
            'origin' => 'Origin',
            'destination' => 'Destination',
            'status' => 'pending',
        ]);

        Livewire::actingAs($financeAnalyst)

            ->test(OrderList::class)
            ->call('deleteOrder', $order->id)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)

            ->test(OrderList::class)
            ->call('updateOrderStatus', $order->id, 'delivered')
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(OrderForm::class)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)

            ->test(OrderForm::class, ['order' => $order])
            ->assertForbidden();
    }

    public function test_finance_analyst_cannot_mutate_clients(): void
    {
        $financeAnalyst = User::factory()->financeAnalyst()->create();


        $client = Client::create([
            'business_name' => 'Client Test',
            'tax_id' => '123456781',
            'contact_name' => 'Jane Doe',
        ]);

        Livewire::actingAs($financeAnalyst)

            ->test(ClientList::class)
            ->call('deleteClient', $client->id)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(ClientForm::class)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)

            ->test(ClientForm::class, ['client' => $client])
            ->assertForbidden();
    }

    public function test_finance_analyst_cannot_mutate_billing_records(): void
    {
        $financeAnalyst = User::factory()->financeAnalyst()->create();


        $client = Client::create([
            'business_name' => 'Billing Client',
            'tax_id' => '123456782',
            'contact_name' => 'Bill Payer',
        ]);

        $order = Order::create([
            'client_id' => $client->id,
            'reference' => 'ORD-3',
            'origin' => 'Origin',
            'destination' => 'Destination',
            'status' => 'pending',
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'order_id' => $order->id,
            'invoice_number' => 'INV-1',
            'issue_date' => now(),
            'subtotal' => 100,
            'tax' => 0,
            'total' => 100,
            'status' => 'issued',
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => 50,
            'paid_at' => now(),
            'method' => 'cash',
        ]);

        Livewire::actingAs($financeAnalyst)

            ->test(InvoiceList::class)
            ->call('markAsPaid', $invoice->id)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(InvoiceForm::class)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(InvoiceForm::class, ['invoice' => $invoice])
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)

            ->test(PaymentList::class)
            ->call('deletePayment', $payment->id)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)
            ->test(PaymentForm::class)
            ->assertForbidden();

        Livewire::actingAs($financeAnalyst)

            ->test(PaymentForm::class, ['payment' => $payment])
            ->assertForbidden();
    }
}
