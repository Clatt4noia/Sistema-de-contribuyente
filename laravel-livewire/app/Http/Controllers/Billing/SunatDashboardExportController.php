<?php

namespace App\Http\Controllers\Billing;

use App\Exports\SunatStatusExport;
use App\Exports\Pdf\SunatStatusPdfExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\SunatDashboardFilterRequest;
use App\Support\Billing\SunatStatusAggregator;

class SunatDashboardExportController extends Controller
{
    public function excel(SunatDashboardFilterRequest $request, SunatStatusAggregator $aggregator)
    {
        $rows = $aggregator->forFilters($request->validated());

        return new SunatStatusExport($rows);
    }

    public function pdf(SunatDashboardFilterRequest $request, SunatStatusAggregator $aggregator)
    {
        $rows = $aggregator->forFilters($request->validated());

        return (new SunatStatusPdfExport($rows))->download();
    }
}
