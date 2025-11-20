<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CollectionsExpensesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Periodo', 'Rango', 'Facturado', 'Cobrado', 'Gastos operativos'];
    }

    public function map($row): array
    {
        return [
            $row['label'],
            $row['range'],
            $row['invoiced'],
            $row['collected'],
            $row['expenses'],
        ];
    }
}
