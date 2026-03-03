<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class GreApiService
{
    /**
     * Get OAuth Token for SUNAT API authentication.
     * Cached for 3000 seconds.
     */
    public function getToken(): string
    {
        return Cache::remember('sunat_oauth_token', 3000, function () {
            $mode = config('greenter.mode');
            $baseUrl = config("greenter.endpoints.api.{$mode}.auth");
            $clientId = config('greenter.company.credentials.client_id');
            $clientSecret = config('greenter.company.credentials.client_secret');
            $authUrl = $baseUrl . '/clientessol/' . $clientId . '/oauth2/token';
            $ruc = config('greenter.company.ruc');
            $solUser = config('greenter.company.clave_sol.user');
            $solPass = config('greenter.company.clave_sol.password');

            if (empty($baseUrl)) throw new RuntimeException("Config URL auth missing");

            $response = Http::withoutVerifying()->asForm()->post($authUrl, [
                'grant_type' => 'password',
                'scope' => 'https://api-cpe.sunat.gob.pe',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'username' => $ruc . $solUser,
                'password' => $solPass,
            ]);

            if (!$response->successful()) {
                Cache::forget('sunat_oauth_token');
                throw new RuntimeException('Auth Error: ' . $response->body());
            }
            return $response->json()['access_token'];
        });
    }

    /**
     * Send ZIP file to SUNAT GRE Endpoint.
     * Returns the Ticket Number.
     */
    public function send(string $zipPath, string $zipFilename): string
    {
        $mode = config('greenter.mode');
        $baseUrl = config("greenter.endpoints.api.$mode.cpe");
        $url = $baseUrl . '/contribuyente/gem/comprobantes/' . str_replace('.zip', '', $zipFilename);
        $zipContent = file_get_contents($zipPath);
        
        $payload = [
            'archivo' => [
                'nomArchivo' => $zipFilename,
                'arcGreZip' => base64_encode($zipContent),
                'hashZip' => hash('sha256', $zipContent),
            ]
        ];

        $token = $this->getToken();

        $response = Http::withoutVerifying()
            ->withHeaders(['Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json'])
            ->post($url, $payload);

        if (!$response->successful()) {
             $body = $response->json();
             $msg = $body['message'] ?? $body['mensaje'] ?? $response->body();
             throw new RuntimeException("Error enviando GRE: $msg");
        }
        $data = $response->json();
        return $data['numTicket'] ?? $data['ticket'] ?? null;
    }

    /**
     * Poll Ticket Status to retrieve CDR.
     * Returns the decoded CDR ZIP content or null if pending.
     */
    public function getStatus(string $ticket, int $maxRetries = 10): ?string
    {
        $mode = config('greenter.mode');
        $baseUrl = config("greenter.endpoints.api.$mode.cpe");
        $url = $baseUrl . '/contribuyente/gem/comprobantes/envios/' . $ticket;
        $token = $this->getToken();

        for ($i = 0; $i < $maxRetries; $i++) {
            sleep(2);
            $response = Http::withoutVerifying()
                ->withHeaders(['Authorization' => 'Bearer ' . $token])->get($url);

            if (!$response->successful()) continue;

            $data = $response->json();
            $status = $data['codRespuesta'] ?? null;

            if ($status === '0' || $status === 0 || $status === '99') {
                $arcCdr = $data['arcCdr'] ?? $data['cdr'] ?? null;
                if ($arcCdr) return base64_decode($arcCdr);
                if ($status === '99') throw new RuntimeException('Rechazo sin CDR: ' . json_encode($data));
            }
        }
        throw new RuntimeException('Timeout polling ticket');
    }
}
