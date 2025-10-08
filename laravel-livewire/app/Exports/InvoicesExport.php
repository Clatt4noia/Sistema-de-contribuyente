<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoicesExport implements FromCollection, WithMapping, WithHeadings, Responsable
{
    public string $fileName = 'facturas.xlsx';

    public function collection(): Collection
    {
        return Invoice::with(['client', 'order'])
            ->latest('issue_date')
            ->get();
    }

    public function map($invoice): array
    {
        return [
            $invoice->numero_completo,
            $invoice->client->business_name ?? '',
            $invoice->issue_date?->format('d/m/Y'),
            $invoice->due_date?->format('d/m/Y'),
            $invoice->sunat_status,
            $invoice->sunat_response_message,
            $invoice->total,
            $invoice->tax,
            $invoice->balance,
        ];
    }

    public function headings(): array
    {
        return [
            'Comprobante',
            'Cliente',
            'Fecha emisión',
            'Fecha vencimiento',
            'Estado SUNAT',
            'Mensaje SUNAT',
            'Total',
            'IGV',
            'Saldo',
        ];
    }
}
