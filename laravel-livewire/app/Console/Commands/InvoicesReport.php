<?php

namespace App\Console\Commands;

use App\Exports\InvoicesExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class InvoicesReport extends Command
{
    protected $signature = 'invoices:report {--frequency=mensual}';

    protected $description = 'Genera un reporte de facturación y notifica a la gerencia';

    public function handle(): int
    {
        $frequency = $this->option('frequency');

        $this->info("Generando reporte de facturación ({$frequency})...");

        $fileName = 'reportes/facturacion-'.now()->format('Ymd-His').'.xlsx';
        Excel::store(new InvoicesExport(), $fileName, 'local');

        $path = Storage::disk('local')->path($fileName);

        $this->info('Reporte generado en: '.$path);
        Log::info('Reporte de facturas generado', ['frequency' => $frequency, 'path' => $path]);

        return self::SUCCESS;
    }
}
