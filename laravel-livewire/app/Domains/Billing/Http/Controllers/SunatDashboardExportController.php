<?php

namespace App\Domains\Billing\Http\Controllers;

use App\Exports\SunatStatusExport;
use App\Exports\Pdf\SunatStatusPdfExport;
use App\Http\Controllers\Controller;
use App\Domains\Billing\Http\Requests\SunatDashboardFilterRequest;
use App\Domains\Billing\Support\SunatStatusAggregator;
use Maatwebsite\Excel\Facades\Excel;


class SunatDashboardExportController extends Controller
{
    public function excel(SunatDashboardFilterRequest $request, SunatStatusAggregator $aggregator)
    {
        $rows = $aggregator->forFilters($request->validated());

        $export = new SunatStatusExport($rows);

        return Excel::download($export, $export->fileName());
    }

    public function pdf(SunatDashboardFilterRequest $request, SunatStatusAggregator $aggregator)
    {
        $rows = $aggregator->forFilters($request->validated());

        return (new SunatStatusPdfExport($rows))->download();
    }
}
