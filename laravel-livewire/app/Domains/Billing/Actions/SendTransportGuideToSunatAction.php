<?php

namespace App\Domains\Billing\Actions;

use App\Domains\Billing\Jobs\SendTransportGuideToSunat;
use App\Models\TransportGuide;
use Illuminate\Support\Facades\Config;

class SendTransportGuideToSunatAction
{
    public function execute(TransportGuide $transportGuide): void
    {
        SendTransportGuideToSunat::dispatch($transportGuide)
            ->onQueue(Config::get('greenter.queues.sunat', 'sunat'));
    }
}
