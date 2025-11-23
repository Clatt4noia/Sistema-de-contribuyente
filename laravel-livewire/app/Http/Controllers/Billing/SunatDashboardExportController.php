<?php

namespace App\Http\Controllers\Billing;

use App\Exports\SunatStatusExport;
use App\Exports\Pdf\SunatStatusPdfExport;
use App\Http\Controllers\Controller;
use App\Support\Billing\SunatStatusAggregator;
use Illuminate\Http\Request;

class SunatDashboardExportController extends Controller
{
    public function excel(Request $request, SunatStatusAggregator $aggregator)
    {
        $rows = $aggregator->forFilters($request->all());

        return new SunatStatusExport($rows);
    }

    public function pdf(Request $request, SunatStatusAggregator $aggregator)
    {
        $rows = $aggregator->forFilters($request->all());

        return (new SunatStatusPdfExport($rows))->download();
    }
}
