<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ConfigureSunat extends Command
{
    protected $signature = 'sunat:configure {--company-ruc= : RUC de la empresa a configurar}';

    protected $description = 'Configura el entorno (BETA/PRODUCCIÓN) y las credenciales SOL de la empresa.';

    public function handle(): int
    {
        $this->info('Configuración de Entorno SUNAT');
        $this->info('=============================');

        $company = $this->resolveCompany();
        if (! $company) {
            $this->error('No hay empresas registradas. Ejecute el seeder o cree una empresa primero.');
            return 1;
        }

        $this->comment("Empresa: {$company->razon_social} (RUC: {$company->ruc})");
        $this->comment('Entorno actual: ' . ($company->production ? 'PRODUCCIÓN (REAL)' : 'BETA (PRUEBAS)'));
        $this->newLine();

        $mode = $this->choice('¿Qué entorno desea activar?', ['BETA (Pruebas)', 'PRODUCCIÓN (Real)'], $company->production ? 1 : 0);
        $isProduction = $mode === 'PRODUCCIÓN (Real)';

        if ($isProduction) {
            $this->alert('¡ATENCIÓN! Está por activar el entorno de PRODUCCIÓN REAL.');
            $this->warn('Los documentos emitidos tendrán validez legal ante SUNAT.');
            $this->warn('Use un USUARIO SOL secundario con permisos de emisión XML.');

            if (! $this->confirm('¿Está seguro de continuar?')) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        $solUser = $this->ask('Usuario SOL (solo usuario, sin RUC)', $company->sol_user);
        $solPass = $this->secret('Clave SOL (dejar vacío para mantener actual)');

        $certPath = $company->cert_path;
        if ($this->confirm('¿Desea cambiar la ruta del certificado digital?')) {
            $certPath = $this->promptCertificatePath($company->cert_path);
        }

        $company->production = $isProduction;
        $company->sol_user = $solUser;
        if ($solPass) {
            $company->sol_pass = $solPass;
        }
        $company->cert_path = $certPath;
        $company->save();

        $this->info('Configuración actualizada correctamente.');
        $this->table(
            ['Campo', 'Valor'],
            [
                ['Entorno', $company->production ? 'PRODUCCIÓN' : 'BETA'],
                ['RUC', $company->ruc],
                ['Usuario SOL', $company->sol_user],
                ['Certificado', $company->cert_path],
            ]
        );

        return 0;
    }

    private function resolveCompany(): ?Company
    {
        $ruc = $this->option('company-ruc');

        if ($ruc) {
            return Company::where('ruc', $ruc)->first();
        }

        return Company::first();
    }

    private function promptCertificatePath(?string $current): ?string
    {
        $input = trim((string) $this->ask(
            'Ruta del certificado (.pem o .pfx). Relativa a storage/app/secure o absoluta',
            $current
        ));

        if ($input === '') {
            return null;
        }

        $normalized = str_replace('\\', '/', $input);
        $isAbsolute = Str::startsWith($normalized, ['/'])
            || preg_match('/^[A-Za-z]:\\//', $normalized) === 1;

        if ($isAbsolute) {
            $certPath = $normalized;
            $checkPath = $normalized;
        } else {
            $relative = ltrim($normalized, '/');
            if (! Str::startsWith($relative, 'secure/')) {
                $relative = 'secure/' . $relative;
            }

            $certPath = $relative;
            $checkPath = storage_path('app/' . $relative);
        }

        if (! file_exists($checkPath)) {
            $this->error("Advertencia: El archivo '{$checkPath}' no parece existir.");
            if (! $this->confirm('¿Desea guardar esta ruta de todas formas?')) {
                return $current;
            }
        }

        return $certPath;
    }
}

