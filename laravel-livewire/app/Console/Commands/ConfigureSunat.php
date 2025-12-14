<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ConfigureSunat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:configure {--company-ruc= : RUC de la empresa a configurar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configura el entorno (BETA/PRODUCCIÓN) y las credenciales SOL de la empresa.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Configuración de Entorno SUNAT');
        $this->info('==============================');

        $ruc = $this->option('company-ruc');
        if (!$ruc) {
            $company = Company::first();
            if (!$company) {
                $this->error('No hay empresas registradas. Ejecute el seeder o cree una empresa primero.');
                return 1;
            }
            $ruc = $company->ruc;
        } else {
            $company = Company::where('ruc', $ruc)->first();
            if (!$company) {
                $this->error("Empresa con RUC {$ruc} no encontrada.");
                return 1;
            }
        }

        $this->comment("Empresa seleccionada: {$company->razon_social} (RUC: {$ruc})");
        $this->comment("Estado actual: " . ($company->production ? 'PRODUCCIÓN (REAL)' : 'BETA (PRUEBAS)'));
        $this->newLine();

        $mode = $this->choice('¿Qué entorno desea activar?', ['BETA (Pruebas)', 'PRODUCCIÓN (Real)'], $company->production ? 1 : 0);
        $isProduction = $mode === 'PRODUCCIÓN (Real)';

        if ($isProduction) {
            $this->alert('¡ATENCIÓN! ESTÁ POR ACTIVAR EL ENTORNO DE PRODUCCIÓN REAL.');
            $this->warn('Los documentos emitidos tendrán validez legal ante SUNAT.');
            $this->warn('Asegúrese de usar un USUARIO SOL SECUNDARIO con permisos de emisión XML.');
            if (!$this->confirm('¿Está seguro de continuar?')) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        $solUser = $this->ask('Usuario SOL (Solo Usuario, sin RUC)', $company->sol_user);
        $solPass = $this->secret('Clave SOL (Dejar vacío para mantener actual)');

        $certPath = $company->cert_path;
        if ($this->confirm('¿Desea cambiar la ruta del certificado digital?')) {
             $certPath = $this->ask('Ruta relativa del certificado (.pem o .pfx)', $company->cert_path);
             if (!file_exists(base_path($certPath)) && !Storage::exists($certPath)) {
                 $this->error("Advertencia: El archivo '{$certPath}' no parece existir.");
                 if (!$this->confirm('¿Desea guardar esta ruta de todas formas?')) {
                     return 1;
                 }
             }
        }

        // Guardar
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
                ['Entorno', $isProduction ? 'PRODUCCIÓN' : 'BETA'],
                ['RUC', $company->ruc],
                ['Usuario SOL', $company->sol_user],
                ['Certificado', $company->cert_path],
            ]
        );

        return 0;
    }
}
