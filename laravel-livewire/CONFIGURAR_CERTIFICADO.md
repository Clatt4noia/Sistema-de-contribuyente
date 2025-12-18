# Configuración del Certificado Digital - Guía Rápida

## Tu certificado está en: `app/billing/certificado.pfx`

### Paso 1: Configurar el archivo .env

Abre tu archivo `.env` y agrega o actualiza estas líneas:

```env
# Certificado Digital para SUNAT
BILLING_CERTIFICATE_PATH=C:/Users/User/Desktop/practicas_preprofesionales/laravel12-auth/laravel-livewire/app/billing/certificado.pfx
BILLING_CERTIFICATE_PASSPHRASE="TU_CONTRASEÑA_AQUI"
```

**Notas importantes:**
- Usa la ruta **completa y absoluta** al archivo
- Usa barras diagonales `/` (no barras invertidas `\`)
- Si el certificado **no tiene contraseña**, deja vacío: `BILLING_CERTIFICATE_PASSPHRASE=""`
- Si no conoces la contraseña, contacta a quien te proporcionó el certificado

### Paso 2: Limpiar la caché de Laravel

```powershell
php artisan config:clear
php artisan config:cache
```

### Paso 3: Verificar la configuración

Ejecuta el script de verificación:

```powershell
php verify_certificate.php
```

Este script te mostrará:
- ✅ Si el certificado está correctamente configurado
- 📄 Información del certificado (emisor, validez, fecha de vencimiento)
- ❌ Errores específicos si algo está mal configurado
- 💡 Sugerencias para resolver problemas

### Paso 4: Probar la emisión de GRE-R

1. Ve a **Facturación → Guías de Remitente (GRE-R)**
2. Crea una nueva guía
3. Completa todos los campos requeridos
4. Guarda la guía
5. Haz clic en **"Emitir"**
6. ✅ Verifica que **NO** aparezca el error de certificado
7. ✅ Verifica que el estado cambie a "Pendiente" o "Enviado"

## Solución de Problemas

### Error: "No se encontró el certificado digital configurado"

**Causa:** La variable `BILLING_CERTIFICATE_PATH` no está configurada o la ruta es incorrecta.

**Solución:**
1. Verifica que agregaste la línea en `.env`
2. Verifica que la ruta sea absoluta y correcta
3. Ejecuta: `php artisan config:clear`
4. Ejecuta: `php verify_certificate.php`

### Error: "No fue posible interpretar el certificado PFX"

**Causa:** La contraseña es incorrecta o el certificado está corrupto.

**Solución:**
1. Verifica la contraseña en `BILLING_CERTIFICATE_PASSPHRASE`
2. Intenta con contraseña vacía si no estás seguro: `BILLING_CERTIFICATE_PASSPHRASE=""`
3. Contacta a quien te proporcionó el certificado para confirmar la contraseña

### El script de verificación muestra "Certificado VENCIDO"

**Solución:**
1. Necesitas renovar el certificado con tu entidad certificadora
2. Una vez renovado, reemplaza el archivo `certificado.pfx`
3. Actualiza la contraseña en `.env` si cambió

## Archivos Importantes

- **Certificado:** `app/billing/certificado.pfx`
- **Configuración:** `.env` (en la raíz del proyecto)
- **Script de verificación:** `verify_certificate.php`
- **Documentación completa:** `docs/guia-certificado-digital.md`

## Soporte

Si después de seguir estos pasos aún tienes problemas:

1. Ejecuta: `php verify_certificate.php` y revisa los errores
2. Revisa los logs: `storage/logs/laravel.log`
3. Consulta la documentación completa: `docs/guia-certificado-digital.md`
