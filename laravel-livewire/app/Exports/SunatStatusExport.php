<?php

namespace App\Exports;

use App\Exports\Contracts\ExcelExport;
use App\Exports\Traits\HasFileName;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SunatStatusExport implements ExcelExport, FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    use HasFileName;

    public function __construct(private readonly Collection $rows)
    {
        $this->fileName = 'sunat-dashboard.xlsx';
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Tipo',
            'Código',
            'Serie',
            'Cliente',
            'Estado SUNAT',
            'Mensaje',
            'Fecha emisión',
            'Último envío',
        ];
    }

    public function map($row): array
    {
        return [
            $row['document_label'],
            $row['code'],
            $row['series'],
            $row['client'] ?? 'Sin cliente',
            strtoupper($row['sunat_status']),
            $row['sunat_message'] ?? 'Sin respuesta',
            optional($row['issued_at'])->format('Y-m-d'),
            optional($row['last_synced_at'])->format('Y-m-d H:i'),
        ];
    }
}
