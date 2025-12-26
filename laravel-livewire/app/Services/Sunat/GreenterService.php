<?php

namespace App\Services\Sunat;

use App\Models\Company;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Illuminate\Support\Str;

class GreenterService
{
    public function getSee(Company $company): See
    {
        $see = new See();
        $certificate = null;
        
        // Log para debugging
        \Log::info('GreenterService: Intentando cargar certificado', [
            'cert_path_configured' => $company->cert_path,
            'company_ruc' => $company->ruc,
        ]);
        
        $certPath = trim((string) $company->cert_path);
        $paths = [];

        if ($certPath !== '') {
            $normalized = str_replace('\\', '/', $certPath);

            // Absolute path: allow external location (recommended for production).
            $isAbsolute = Str::startsWith($normalized, ['/'])
                || preg_match('/^[A-Za-z]:\\//', $normalized) === 1;

            if ($isAbsolute) {
                $paths[] = $normalized;
            } else {
                // Relative path: force under storage/app/secure (never inside app/ nor repo).
                $relative = ltrim($normalized, '/');

                if (! Str::startsWith($relative, 'secure/')) {
                    $relative = 'secure/' . $relative;
                }

                $paths[] = storage_path('app/' . $relative);
            }
        }
        
        \Log::info('GreenterService: Rutas a verificar', ['paths' => $paths]);
        
        foreach ($paths as $index => $path) {
            $exists = file_exists($path);
            \Log::info("GreenterService: Verificando ruta #{$index}", [
                'path' => $path,
                'exists' => $exists,
            ]);
            
            if ($exists) {
                $certificate = file_get_contents($path);
                \Log::info('GreenterService: Certificado cargado exitosamente', ['path' => $path]);
                break;
            }
        }
        
        if (!$certificate) {
            $errorMsg = "No se encontró el certificado digital configurado.\n" .
                "Ruta configurada: {$company->cert_path}\n" .
                "Rutas verificadas:\n" .
                "- " . implode("\n- ", $paths) . "\n\n" .
                "Por favor, asegúrate de que el archivo existe en alguna de estas ubicaciones.";
            
            \Log::error('GreenterService: Certificado NO encontrado', [
                'cert_path' => $company->cert_path,
                'paths_checked' => $paths,
            ]);
            
            throw new \Exception($errorMsg);
        }

        $see->setCertificate($certificate);
        $see->setService($company->production ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);
        
        // Clave SOL
        $see->setClaveSOL($company->ruc, $company->sol_user, $company->sol_pass);
        
        // Cache (opcional pero recomendado)
        // $see->setCachePath(storage_path('app/greenter/cache'));

        return $see;
    }

    public function getSeeApi(Company $company)
    {
        // Para Guías (API REST) puede requerir configuración distinta (Api/ApiFactory)
        // GRE usa envío Oauth/API REST actualmente en muchos casos, pero también SOAP legacy.
        // Asumiremos uso de clases estándar Despatch que usa See.
        
        // Nota: A partir de normas recientes, GRE usa 'See' igual que Factura para firma, 
        // pero el envío puede variar. Greenter encapsula esto.
        
        return $this->getSee($company);
    }
}
