<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $mode = (string) config('billing.sunat.mode', 'homologation');
        $solUser = (string) config('billing.sunat.user', 'MODDATOS');
        $solPass = (string) config('billing.sunat.password', 'MODDATOS');

        $certPath = (string) (config('billing.certificate.path') ?: 'secure/sunat/certificate.pfx');
        $certPath = str_replace('\\', '/', $certPath);

        if (
            Str::startsWith($certPath, ['app/', '/app/', 'laravel-livewire/app/'])
            || Str::contains($certPath, ['/app/billing/', '/laravel-livewire/app/'])
        ) {
            $certPath = 'secure/sunat/certificate.pfx';
        }

        Company::updateOrCreate(['ruc' => '20000000001'], [
            'ruc' => '20000000001',
            'razon_social' => 'EMPRESA DE PRUEBA S.A.C.',
            'nombre_comercial' => 'DEMO STORE',
            'address' => 'AV. DEMO 123 LIMA - LIMA - LIMA',
            'ubigeo' => '150101',
            'sol_user' => $solUser,
            'sol_pass' => $solPass,
            'cert_path' => $certPath,
            'production' => $mode === 'production',
            'client_id' => env('SUNAT_CLIENT_ID'),
            'client_secret' => env('SUNAT_CLIENT_SECRET'),
        ]);
    }
}
