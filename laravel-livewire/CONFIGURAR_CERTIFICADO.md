# Configuración del Certificado Digital (SUNAT) - Guía rápida

## Reglas del proyecto (importante)

- No guardes certificados dentro de `app/` ni dentro del repositorio.
- Usa una ruta externa (recomendado) o `storage/app/secure/` (ignorado por git).

## Opción A (recomendado): ruta externa

1) Coloca tu certificado en una carpeta fuera del repo, por ejemplo: `C:/certs/sunat/certificado.pfx`
2) Configura el `.env`:

```env
BILLING_CERTIFICATE_PATH=C:/certs/sunat/certificado.pfx
BILLING_CERTIFICATE_PASSPHRASE="TU_CONTRASEÑA"
```

## Opción B: `storage/app/secure/` (local, sin commitear)

1) Coloca el archivo en `laravel-livewire/storage/app/secure/sunat/certificado.pfx`
2) Configura el `.env` con la ruta absoluta al archivo:

```env
BILLING_CERTIFICATE_PATH=C:/ruta/al/proyecto/laravel-livewire/storage/app/secure/sunat/certificado.pfx
BILLING_CERTIFICATE_PASSPHRASE="TU_CONTRASEÑA"
```

## Limpiar caché y verificar

```powershell
php artisan config:clear
php verify_certificate.php
```

## Guardar la ruta en DB (opcional)

Si usas el modelo `Company` para almacenar `cert_path`, puedes configurarlo con:

```powershell
php artisan sunat:configure
```

La ruta relativa se interpreta como `storage/app/secure/<ruta>`.

