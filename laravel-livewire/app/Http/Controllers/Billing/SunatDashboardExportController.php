<?php

namespace App\Http\Controllers\Billing;

use App\Exports\SunatStatusExport;
use App\Http\Controllers\Controller;
use App\Support\Billing\SunatStatusAggregator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SunatDashboardExportController extends Controller
{
    public function excel(Request $request, SunatStatusAggregator $aggregator)
    {
        $rows = $aggregator->forFilters($request->all());

        return Excel::download(new SunatStatusExport($rows), 'sunat-dashboard.xlsx');
    }

    public function pdf(Request $request, SunatStatusAggregator $aggregator)
    {
        $rows = $aggregator->forFilters($request->all());

        $pdf = Pdf::loadView('pdf.sunat-status-report', [
            'rows' => $rows,
        ]);

        return $pdf->download('sunat-dashboard.pdf');
    }
}
