<?php

namespace App\Domains\Billing\Jobs;

use App\Domains\Billing\Services\TransportGuideIssuer;
use App\Models\TransportGuide;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendTransportGuideToSunat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public TransportGuide $transportGuide)
    {
        $this->onQueue(config('greenter.queues.sunat', 'sunat'));
    }

    public function handle(TransportGuideIssuer $issuer): void
    {
        try {
            $issuer->issue($this->transportGuide);
        } catch (Throwable $e) {
            Log::error('Job de envío de guía a SUNAT falló', [
                'guide_id' => $this->transportGuide->id,
                'full_code' => $this->transportGuide->full_code,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Job de envío de guía a SUNAT falló definitivamente', [
            'guide_id' => $this->transportGuide->id,
            'full_code' => $this->transportGuide->full_code,
            'error' => $exception->getMessage(),
        ]);

        $this->transportGuide->forceFill([
            'sunat_status' => TransportGuide::STATUS_ERROR,
            'sunat_notes' => 'Error al procesar guía en segundo plano: ' . $exception->getMessage(),
        ])->save();
    }
}
