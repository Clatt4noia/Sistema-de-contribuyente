<?php

namespace App\Domains\Billing\Jobs;

use App\Models\TransportGuide;
use App\Services\Sunat\TransportGuideService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTransportGuideToSunat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public TransportGuide $transportGuide)
    {
        $this->onQueue(config('billing.queues.sunat', 'sunat'));
    }

    public function handle(TransportGuideService $transportGuideService): void
    {
        try {
            $result = $transportGuideService->send($this->transportGuide);

            if ($result->isSuccess()) {
                Log::info('Guía de remitente enviada exitosamente a SUNAT', [
                    'guide_id' => $this->transportGuide->id,
                    'full_code' => $this->transportGuide->full_code,
                ]);
            } else {
                Log::warning('Guía de remitente rechazada por SUNAT', [
                    'guide_id' => $this->transportGuide->id,
                    'full_code' => $this->transportGuide->full_code,
                    'error' => $result->getError()->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar guía de remitente', [
                'guide_id' => $this->transportGuide->id,
                'full_code' => $this->transportGuide->full_code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->transportGuide->update([
                'sunat_status' => TransportGuide::STATUS_ERROR,
                'sunat_notes' => 'Error al procesar: ' . $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job de envío de guía a SUNAT falló definitivamente', [
            'guide_id' => $this->transportGuide->id,
            'full_code' => $this->transportGuide->full_code,
            'error' => $exception->getMessage(),
        ]);

        $this->transportGuide->update([
            'sunat_status' => TransportGuide::STATUS_ERROR,
            'sunat_notes' => 'Error al procesar guía: ' . $exception->getMessage(),
        ]);
    }
}
