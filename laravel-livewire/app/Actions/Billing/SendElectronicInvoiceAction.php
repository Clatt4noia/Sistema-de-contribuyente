<?php

namespace App\Actions\Billing;

use App\Jobs\SendElectronicInvoice;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SendElectronicInvoiceAction
{
    public function execute(Invoice $invoice, array $items): void
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Debe registrar al menos un ítem para emitir el comprobante.',
            ]);
        }

        $totals = $this->calculateTotals($items);

        DB::transaction(function () use ($invoice, $items, $totals) {
            $invoice->forceFill([
                'subtotal' => $totals['subtotal'],
                'taxable_amount' => $totals['taxable'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'metadata' => ['items' => $items],
            ])->save();
        });

        $companyData = [
            'ruc' => $invoice->ruc_emisor,
            'legal_name' => Config::get('app.name', 'Carlos Gabriel Transporte S.A.C.'),
            'commercial_name' => Config::get('app.name', 'Carlos Gabriel Transporte S.A.C.'),
        ];

        $client = Client::find($invoice->client_id);
        $customerData = [
            'ruc' => $invoice->ruc_receptor,
            'scheme_id' => '6',
            'name' => $client?->business_name ?? 'Cliente sin razón social',
        ];

        SendElectronicInvoice::dispatch($invoice, $items, $companyData, $customerData)
            ->onQueue(config('billing.queues.sunat', 'sunat'));
    }

    protected function calculateTotals(array $items): array
    {
        $subtotal = collect($items)->sum('total');
        $tax = collect($items)->sum('tax_amount');

        return [
            'subtotal' => round($subtotal, 2),
            'taxable' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'total' => round($subtotal + $tax, 2),
        ];
    }
}
