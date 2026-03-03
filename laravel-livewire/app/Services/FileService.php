<?php

namespace App\Services;

use ZipArchive;
use RuntimeException;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * Create a ZIP file containing the XML content.
     */
    public function createZip(string $name, string $xmlContent): string
    {
        $zipPath = sys_get_temp_dir() . '/' . $name . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Error creating ZIP');
        }
        $zip->addFromString($name . '.xml', $xmlContent);
        $zip->close();
        return $zipPath;
    }

    /**
     * Resolve the absolute path to the certificate file.
     */
    public function getCertificatePath(): string
    {
        $certPath = config('greenter.company.certificate_path');
        
        // 1. Try as absolute or relative to CWD
        if (file_exists($certPath)) return $certPath;

        // 2. Try relative to base_path (project root)
        $baseCertPath = base_path($certPath);
        if (file_exists($baseCertPath)) return $baseCertPath;
        
        // 3. Try relative to public_path
        $publicCertPath = public_path($certPath);
        if (file_exists($publicCertPath)) return $publicCertPath;
        
        // 4. Fallback default
        $defaultPath = storage_path('public/certificate.pem');
        if (file_exists($defaultPath)) return $defaultPath;

        throw new RuntimeException("Certificado no encontrado en: $certPath (ni en base/public path)");
    }

    /**
     * Save the signed XML to the configured storage disk.
     */
    public function saveXml(string $name, string $content): void
    {
        $disk = config('greenter.storage.disk_xml_cdr', 'public');
        $directory = config('greenter.storage.xml_directory', 'xml') . '/guides/';
        Storage::disk($disk)->put($directory . $name . '.xml', $content);
    }

    /**
     * Save the CDR ZIP to the configured storage disk.
     */
    public function saveCdr(string $name, string $content): void
    {
        $disk = config('greenter.storage.disk_xml_cdr', 'public');
        $directory = config('greenter.storage.cdr_directory', 'cdr') . '/guides/';
        Storage::disk($disk)->put($directory . 'R-' . $name . '.zip', $content);
    }
}
