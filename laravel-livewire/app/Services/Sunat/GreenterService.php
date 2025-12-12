<?php

namespace App\Services\Sunat;

use App\Models\Company;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Illuminate\Support\Facades\Storage;

class GreenterService
{
    public function getSee(Company $company): See
    {
        $see = new See();
        $see->setCertificate(Storage::get($company->cert_path)); // Leer certificado del storage
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
