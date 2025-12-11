<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LivewireComponentsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $components = [
            'actions.logout' => \App\Domains\Auth\Actions\Logout::class,

            'billing.create-invoice' => \App\Domains\Billing\Livewire\CreateInvoice::class,
            'billing.electronic-invoice-form' => \App\Domains\Billing\Livewire\ElectronicInvoiceForm::class,
            'billing.invoice-file-downloader' => \App\Domains\Billing\Livewire\InvoiceFileDownloader::class,
            'billing.invoice-form' => \App\Domains\Billing\Livewire\InvoiceForm::class,
            'billing.invoice-list' => \App\Domains\Billing\Livewire\InvoiceList::class,
            'billing.payment-form' => \App\Domains\Billing\Livewire\PaymentForm::class,
            'billing.payment-list' => \App\Domains\Billing\Livewire\PaymentList::class,
            'billing.sunat-status-badge' => \App\Domains\Billing\Livewire\SunatStatusBadge::class,
            'billing.sunat-unified-dashboard' => \App\Domains\Billing\Livewire\SunatUnifiedDashboard::class,
            'billing.transport-guides.transport-guide-form' => \App\Domains\Billing\Livewire\TransportGuides\TransportGuideForm::class,
            'billing.transport-guides.transport-guide-index' => \App\Domains\Billing\Livewire\TransportGuides\TransportGuideIndex::class,
            'billing.transport-guides.transport-guide-show' => \App\Domains\Billing\Livewire\TransportGuides\TransportGuideShow::class,
            'client-portal.order-tracker' => \App\Domains\ClientPortal\Livewire\OrderTracker::class,
            'clients.client-form' => \App\Domains\Clients\Livewire\ClientForm::class,
            'clients.client-list' => \App\Domains\Clients\Livewire\ClientList::class,
            'dashboards.admin-dashboard' => \App\Domains\Dashboards\Livewire\AdminDashboard::class,
            'dashboards.client-dashboard' => \App\Domains\Dashboards\Livewire\ClientDashboard::class,
            'dashboards.finance-analyst-dashboard' => \App\Domains\Dashboards\Livewire\FinanceAnalystDashboard::class,
            'dashboards.finance-dashboard' => \App\Domains\Dashboards\Livewire\FinanceDashboard::class,
            'dashboards.finance.overview-stats' => \App\Domains\Dashboards\Livewire\Finance\OverviewStats::class,
            'dashboards.finance.recent-invoices' => \App\Domains\Dashboards\Livewire\Finance\RecentInvoices::class,
            'dashboards.finance.recent-payments' => \App\Domains\Dashboards\Livewire\Finance\RecentPayments::class,
            'dashboards.fleet-dashboard' => \App\Domains\Dashboards\Livewire\FleetDashboard::class,
            'dashboards.logistics-dashboard' => \App\Domains\Dashboards\Livewire\LogisticsDashboard::class,
            'finance.collections-and-expenses-report' => \App\Domains\Finance\Livewire\CollectionsAndExpensesReport::class,
            'finance.transaction-analytics' => \App\Domains\Finance\Livewire\TransactionAnalytics::class,
            'finance.transaction-list' => \App\Domains\Finance\Livewire\TransactionList::class,
            'fleet.assignment-form' => \App\Domains\Fleet\Livewire\AssignmentForm::class,
            'fleet.assignment-list' => \App\Domains\Fleet\Livewire\AssignmentList::class,
            'fleet.availability-board' => \App\Domains\Fleet\Livewire\AvailabilityBoard::class,
            'fleet.document-manager' => \App\Domains\Fleet\Livewire\DocumentManager::class,
            'fleet.driver-form' => \App\Domains\Fleet\Livewire\DriverForm::class,
            'fleet.driver-list' => \App\Domains\Fleet\Livewire\DriverList::class,
            'fleet.maintenance-form' => \App\Domains\Fleet\Livewire\MaintenanceForm::class,
            'fleet.maintenance-list' => \App\Domains\Fleet\Livewire\MaintenanceList::class,
            'fleet.report' => \App\Domains\Fleet\Livewire\Report::class,
            'fleet.truck-form' => \App\Domains\Fleet\Livewire\TruckForm::class,
            'fleet.truck-list' => \App\Domains\Fleet\Livewire\TruckList::class,
            'logistics.live-tracking-board' => \App\Domains\Logistics\Livewire\LiveTrackingBoard::class,
            'logistics.order-status-notifications' => \App\Domains\Logistics\Livewire\OrderStatusNotifications::class,
            'orders.order-form' => \App\Domains\Orders\Livewire\OrderForm::class,
            'orders.order-list' => \App\Domains\Orders\Livewire\OrderList::class,
            'orders.route-plan-manager' => \App\Domains\Orders\Livewire\RoutePlanManager::class,
        ];

        foreach ($components as $alias => $class) {
            Livewire::component($alias, $class);
        }
    }
}
