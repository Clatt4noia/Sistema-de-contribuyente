<?php

namespace App\Exports\Contracts;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface PdfExport extends Exportable
{
    public function download(): \Illuminate\Http\Response;
}
