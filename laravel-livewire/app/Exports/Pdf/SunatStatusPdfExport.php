<?php

namespace App\Exports\Pdf;

use App\Exports\Contracts\PdfExport;
use App\Exports\Traits\HasFileName;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SunatStatusPdfExport implements PdfExport
{
    use HasFileName;

    public function __construct(private readonly Collection $rows)
    {
        $this->fileName = 'sunat-dashboard.pdf';
    }

    public function download(): BinaryFileResponse
    {
        $pdf = Pdf::loadView('pdf.sunat-status-report', [
            'rows' => $this->rows,
        ]);

        return $pdf->download($this->fileName);
    }
}
