<?php

namespace App\Domains\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Exports\Pdf\InvoicePdfExport;
use App\Models\Invoice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceFileController extends Controller
{
    use AuthorizesRequests;

    public function xml(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        abort_unless($request->hasValidSignature(), 403);

        $disk = config('greenter.storage.disk_xml_cdr');
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

        return (new InvoicePdfExport($invoice))->download();
    }
}
