<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\TransportGuide;
use Illuminate\View\View;

class TransportGuideCreateController extends Controller
{
    public function __invoke(string $type = TransportGuide::TYPE_TRANSPORTISTA): View
    {
        return view('pages.billing.transport-guides.create', ['type' => $type]);
    }
}
