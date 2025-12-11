<?php

namespace App\Domains\Billing\Support;

use App\Models\Invoice;
use App\Models\TransportGuide;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SunatStatusAggregator
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function forFilters(array $filters): Collection
    {
        $from = $this->parseDate($filters['date_from'] ?? null, now()->subDays(30)->toDateString())->startOfDay();
        $to = $this->parseDate($filters['date_to'] ?? null, now()->toDateString())->endOfDay();
        $series = trim((string) ($filters['series'] ?? ''));
        $documentType = $filters['document_type'] ?? 'all';
        $sunatStatus = $filters['sunat_status'] ?? '';

        $rows = collect();

        if ($documentType === 'all' || $documentType === 'invoice') {
            $rows = $rows->merge($this->invoiceRows($from, $to, $series, $sunatStatus));
        }

        if ($documentType === 'all' || $documentType === 'gre') {
            $rows = $rows->merge($this->guideRows($from, $to, $series, $sunatStatus));
        }

        return $rows->sortByDesc('issued_at')->values();
    }

    protected function invoiceRows(Carbon $from, Carbon $to, string $series, string $sunatStatus): Collection
    {
        return Invoice::query()
            ->with('client')
            ->whereBetween('issue_date', [$from, $to])
            ->when($series, fn ($query) => $query->where('series', 'like', "%{$series}%"))
            ->when($sunatStatus, fn ($query) => $query->where('sunat_status', $sunatStatus))
            ->get()
            ->map(function (Invoice $invoice) {
                return [
                    'id' => $invoice->getKey(),
                    'type' => 'invoice',
                    'document_label' => 'Comprobante',
                    'code' => $invoice->numero_completo ?: $invoice->invoice_number,
                    'series' => $invoice->series,
                    'sunat_status' => $invoice->sunat_status ?? 'pendiente',
                    'sunat_message' => $invoice->sunat_response_message,
                    'client' => $invoice->client?->business_name,
                    'issued_at' => $invoice->issue_date,
                    'last_synced_at' => $invoice->sunat_sent_at,
                    'retryable' => in_array($invoice->sunat_status, ['pendiente', 'rechazado', 'observado'], true),
                ];
            });
    }

    protected function guideRows(Carbon $from, Carbon $to, string $series, string $sunatStatus): Collection
    {
        $guideStatuses = $this->guideStatusesFromHuman($sunatStatus);

        return TransportGuide::query()
            ->with('client')
            ->whereBetween('issue_date', [$from, $to])
            ->when($series, fn ($query) => $query->where('series', 'like', "%{$series}%"))
            ->when($guideStatuses, fn ($query) => $query->whereIn('sunat_status', $guideStatuses))
            ->get()
            ->map(function (TransportGuide $guide) {
                return [
                    'id' => $guide->getKey(),
                    'type' => 'gre',
                    'document_label' => $guide->type === TransportGuide::TYPE_REMITENTE ? 'GRE-R' : 'GRE-T',
                    'code' => $guide->display_code,
                    'series' => $guide->series,
                    'sunat_status' => $this->humanizeGuideStatus($guide->sunat_status),
                    'sunat_message' => $guide->sunat_notes,
                    'client' => $guide->client?->business_name,
                    'issued_at' => $guide->issue_date,
                    'last_synced_at' => $guide->sent_at ?? $guide->accepted_at,
                    'retryable' => in_array($guide->sunat_status, [
                        TransportGuide::STATUS_PENDING,
                        TransportGuide::STATUS_SENT,
                        TransportGuide::STATUS_ERROR,
                        TransportGuide::STATUS_REJECTED,
                    ], true),
                ];
            });
    }

    protected function humanizeGuideStatus(?string $status): string
    {
        return match ($status) {
            TransportGuide::STATUS_ACCEPTED => 'aceptado',
            TransportGuide::STATUS_REJECTED, TransportGuide::STATUS_ERROR, TransportGuide::STATUS_CANCELLED => 'rechazado',
            TransportGuide::STATUS_PENDING, TransportGuide::STATUS_SENT => 'pendiente',
            default => 'observado',
        };
    }

    protected function guideStatusesFromHuman(?string $status): array
    {
        return match ($status) {
            'aceptado' => [TransportGuide::STATUS_ACCEPTED],
            'rechazado' => [TransportGuide::STATUS_REJECTED, TransportGuide::STATUS_ERROR, TransportGuide::STATUS_CANCELLED],
            'pendiente' => [TransportGuide::STATUS_PENDING, TransportGuide::STATUS_SENT],
            'observado' => [TransportGuide::STATUS_ERROR],
            default => [],
        };
    }

    protected function parseDate(?string $value, string $default): Carbon
    {
        return $value ? Carbon::parse($value) : Carbon::parse($default);
    }
}
