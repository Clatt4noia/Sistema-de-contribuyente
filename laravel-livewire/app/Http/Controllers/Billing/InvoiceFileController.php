<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceFileController extends Controller
{
    use AuthorizesRequests;

    public function xml(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        abort_unless($request->hasValidSignature(), 403);

        $disk = config('billing.storage.disk_xml_cdr');
        abort_if(! $invoice->xml_path || ! Storage::disk($disk)->exists($invoice->xml_path), 404);

        return Storage::disk($disk)->download($invoice->xml_path, $invoice->numero_completo.'.xml');
    }

    public function cdr(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        abort_unless($request->hasValidSignature(), 403);

        $disk = config('billing.storage.disk_xml_cdr');
        abort_if(! $invoice->cdr_path || ! Storage::disk($disk)->exists($invoice->cdr_path), 404);

        return Storage::disk($disk)->download($invoice->cdr_path, $invoice->numero_completo.'.zip');
    }

    public function pdf(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        abort_unless($request->hasValidSignature(), 403);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice->load('client', 'order'),
            'items' => collect($invoice->metadata['items'] ?? [])->map(function ($item) {
                $subtotal = ($item['taxable_amount'] ?? 0) + ($item['tax_amount'] ?? 0);
                return array_merge($item, [
                    'subtotal' => $subtotal,
                ]);
            }),
        ])->setPaper('a4');

        return $pdf->download($invoice->numero_completo.'.pdf');
    }
}
