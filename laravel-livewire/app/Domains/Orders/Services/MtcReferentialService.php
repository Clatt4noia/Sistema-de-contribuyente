<?php

namespace App\Domains\Orders\Services;

use App\Models\MtcReferentialRate;
use Illuminate\Support\Str;

class MtcReferentialService
{
    public const SOURCE = 'DS-026-2024-MTC';
    public const YEAR = 2024;

    /**
     * Intento simple y realista:
     * - Solo calcula si la ruta es Lima -> X o X -> Lima (Anexo II es "desde Lima").
     * - Matchea destino por "contains" + normalización.
     */
    public function estimateFromAnnexII(?string $origin, ?string $destination, ?float $weightKg): array
    {
        $origin = trim((string) $origin);
        $destination = trim((string) $destination);

        if ($origin === '' || $destination === '') {
            return $this->empty('Faltan origen/destino.');
        }

        $originNorm = $this->norm($origin);
        $destNorm = $this->norm($destination);

        $isOriginLima = Str::contains($originNorm, 'lima');
        $isDestLima = Str::contains($destNorm, 'lima');

        if (!$isOriginLima && !$isDestLima) {
            return $this->empty('Anexo II aplica a distancias virtuales desde Lima.');
        }

        $destToSearch = $isOriginLima ? $destination : $origin; // Lima -> Dest, o Dest -> Lima
        $destToSearchNorm = $this->norm($destToSearch);

        // Buscamos mejor match por "destination like"
        // Nota: es mejor tener un selector de destino (lista) para 100% precisión.
        $rate = MtcReferentialRate::query()
            ->where('year', self::YEAR)
            ->where('source', self::SOURCE)
            ->where(function ($q) use ($destToSearchNorm) {
                $q->whereRaw('LOWER(destination) LIKE ?', ['%'.$destToSearchNorm.'%'])
                  ->orWhereRaw('LOWER(destination) LIKE ?', ['%'.Str::of($destToSearchNorm)->replace('-', ' ')->value().'%']);
            })
            ->orderByDesc('dv_acum_km') // si hay varios, prioriza el más “largo”
            ->first();

        if (!$rate) {
            return $this->empty('No hay tarifa MTC para ese destino en tu tabla.');
        }

        $weightTm = ($weightKg && $weightKg > 0) ? ($weightKg / 1000.0) : null;

        $estimated = null;
        if ($weightTm !== null) {
            $estimated = round(((float)$rate->rate_soles_per_tm) * $weightTm, 2);
        }

        return [
            'ok' => true,
            'message' => $estimated === null
                ? 'Tarifa encontrada. Ingresa peso para calcular costo.'
                : 'Costo referencial calculado.',
            'source' => self::SOURCE,
            'year' => self::YEAR,
            'route_key' => $rate->route_key,
            'destination' => $rate->destination,
            'rate_sxtm' => (float) $rate->rate_soles_per_tm,
            'estimated_cost' => $estimated,
        ];
    }

    private function empty(string $msg): array
    {
        return [
            'ok' => false,
            'message' => $msg,
            'source' => self::SOURCE,
            'year' => self::YEAR,
            'route_key' => null,
            'destination' => null,
            'rate_sxtm' => null,
            'estimated_cost' => null,
        ];
    }

    private function norm(string $s): string
    {
        $s = Str::lower($s);
        $s = Str::ascii($s);
        $s = preg_replace('/[^a-z0-9\s\-]/', ' ', $s) ?? $s;
        $s = preg_replace('/\s+/', ' ', $s) ?? $s;
        return trim(str_replace(' ', '-', $s));
    }
}
