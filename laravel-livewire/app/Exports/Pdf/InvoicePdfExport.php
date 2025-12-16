<?php

namespace App\Exports\Pdf;

use App\Exports\Contracts\PdfExport;
use App\Exports\Traits\HasFileName;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoicePdfExport implements PdfExport
{
    use HasFileName;

    public function __construct(private readonly Invoice $invoice)
    {
        $this->fileName = $invoice->numero_completo.'.pdf';
    }

    public function download(): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $this->invoice->load('client', 'order'),
            'items' => $this->formattedItems(),
        ])->setPaper('a4');

        return $pdf->download($this->fileName);
    }

    private function formattedItems(): Collection
    {
        return collect($this->invoice->metadata['items'] ?? [])->map(function ($item) {
            $subtotal = ($item['taxable_amount'] ?? 0) + ($item['tax_amount'] ?? 0);

            return array_merge($item, [
                'subtotal' => $subtotal,
            ]);
        });
    }
}
