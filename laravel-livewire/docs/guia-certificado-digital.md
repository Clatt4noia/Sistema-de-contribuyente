# Guía de Configuración del Certificado Digital para SUNAT

## Introducción

Para emitir documentos electrónicos (facturas, boletas, guías de remisión) a SUNAT, es **obligatorio** contar con un **certificado digital** válido. Este certificado se utiliza para firmar digitalmente los archivos XML antes de enviarlos a SUNAT.

## ¿Qué es un Certificado Digital?

Un certificado digital es un archivo electrónico que funciona como una "firma digital" que garantiza:
- La autenticidad del emisor del documento
- La integridad del documento (que no ha sido modificado)
- El no repudio (el emisor no puede negar haber emitido el documento)

## Requisitos del Certificado

### Formato Aceptado
- **Formato:** `.pfx` (PKCS#12) o `.pem`
- **Algoritmo:** RSA con SHA-256 (mínimo)
- **Emisor:** Entidad certificadora acreditada en Perú

### Entidades Certificadoras Acreditadas en Perú

Las siguientes entidades están autorizadas por INDECOPI para emitir certificados digitales válidos para SUNAT:

1. **eCert Perú** - https://www.ecert.pe
2. **Certicámara** - https://www.certicamara.com.pe
3. **Digicert** - https://www.digicert.com
4. **GlobalSign** - https://www.globalsign.com

## Proceso de Configuración

### Paso 1: Obtener el Certificado Digital

1. Contacta a una de las entidades certificadoras acreditadas
2. Solicita un certificado digital para facturación electrónica
3. Proporciona la documentación requerida (RUC, DNI del representante legal, etc.)
4. Descarga el certificado en formato `.pfx` una vez aprobado
5. Guarda la **contraseña** del certificado en un lugar seguro

### Paso 2: Almacenar el Certificado en el Servidor

1. Crea un directorio seguro para almacenar el certificado:
   ```powershell
   # En Windows PowerShell
   New-Item -ItemType Directory -Path "C:\certificados" -Force
   ```

2. Copia el archivo del certificado al directorio:
   ```powershell
   Copy-Item "C:\ruta\de\descarga\certificado.pfx" -Destination "C:\certificados\certificado.pfx"
   ```

3. **Importante:** Asegúrate de que el directorio tenga permisos adecuados:
   - El usuario que ejecuta la aplicación web debe tener permisos de lectura
   - Evita dar permisos de escritura innecesarios
   - No almacenes el certificado en directorios públicos

### Paso 3: Configurar las Variables de Entorno

1. Abre el archivo `.env` en la raíz del proyecto Laravel:
   ```powershell
   notepad .env
   ```

2. Busca o agrega las siguientes líneas:
   ```env
   BILLING_CERTIFICATE_PATH=C:/certificados/certificado.pfx
   BILLING_CERTIFICATE_PASSPHRASE="tu_contraseña_aqui"
   ```

3. **Notas importantes:**
   - Usa barras diagonales `/` en lugar de barras invertidas `\` en la ruta
   - Si la contraseña contiene espacios o caracteres especiales, enciérrala entre comillas dobles
   - Si el certificado **no tiene contraseña**, deja el valor vacío: `BILLING_CERTIFICATE_PASSPHRASE=""`

4. Guarda el archivo `.env`

5. Limpia la caché de configuración de Laravel:
   ```powershell
   php artisan config:clear
   php artisan config:cache
   ```

### Paso 4: Verificar la Configuración

#### Opción 1: Usando Laravel Tinker

```powershell
php artisan tinker
```

Luego ejecuta:
```php
config('billing.certificate.path')
// Debe mostrar: "C:/certificados/certificado.pfx"

config('billing.certificate.passphrase')
// Debe mostrar: "tu_contraseña_aqui" (o vacío si no tiene contraseña)

file_exists(config('billing.certificate.path'))
// Debe mostrar: true
```

Presiona `Ctrl+C` para salir de Tinker.

#### Opción 2: Verificar Manualmente

```powershell
# Verificar que el archivo existe
Test-Path "C:/certificados/certificado.pfx"
# Debe retornar: True
```

## Troubleshooting (Solución de Problemas)

### Error: "No se encontró el certificado digital configurado"

**Causas posibles:**
1. La variable `BILLING_CERTIFICATE_PATH` no está configurada en `.env`
2. La ruta del certificado es incorrecta
3. El archivo del certificado no existe en la ubicación especificada
4. La aplicación no tiene permisos para leer el archivo

**Soluciones:**
1. Verifica que las variables estén configuradas en `.env`
2. Verifica que la ruta sea absoluta y use barras diagonales `/`
3. Verifica que el archivo exista usando `Test-Path` en PowerShell
4. Verifica los permisos del archivo y directorio
5. Limpia la caché de configuración: `php artisan config:clear`

### Error: "No fue posible interpretar el certificado PFX. Verifique la contraseña"

**Causas posibles:**
1. La contraseña del certificado es incorrecta
2. El certificado está corrupto
3. El formato del certificado no es compatible

**Soluciones:**
1. Verifica que la contraseña sea correcta (distingue mayúsculas/minúsculas)
2. Intenta abrir el certificado con otra herramienta para verificar que no esté corrupto
3. Contacta a la entidad certificadora si el problema persiste

### Error: "El XML no contiene el nodo de extensión requerido para la firma"

**Causas posibles:**
1. El XML generado no cumple con el estándar UBL 2.1
2. Hay un problema en la generación del XML

**Soluciones:**
1. Revisa los logs de la aplicación en `storage/logs/laravel.log`
2. Contacta al soporte técnico del sistema

## Seguridad del Certificado

### Mejores Prácticas

1. **Nunca compartas el certificado ni su contraseña** con personas no autorizadas
2. **No subas el certificado a repositorios de código** (Git, GitHub, etc.)
3. **Realiza copias de seguridad** del certificado en un lugar seguro
4. **Renueva el certificado antes de que expire** (generalmente tienen validez de 1-2 años)
5. **Usa contraseñas fuertes** para proteger el certificado
6. **Restringe los permisos de acceso** al archivo del certificado en el servidor

### Archivo .gitignore

Asegúrate de que tu archivo `.gitignore` incluya:
```
/certificados/
*.pfx
*.pem
.env
```

## Renovación del Certificado

Cuando tu certificado esté por vencer:

1. Solicita un nuevo certificado a tu entidad certificadora
2. Descarga el nuevo certificado
3. Reemplaza el archivo antiguo con el nuevo (mantén el mismo nombre o actualiza la ruta en `.env`)
4. Actualiza la contraseña en `.env` si cambió
5. Limpia la caché: `php artisan config:clear`
6. Verifica que todo funcione correctamente emitiendo un documento de prueba

## Soporte

Si tienes problemas con la configuración del certificado:

1. Revisa los logs de la aplicación: `storage/logs/laravel.log`
2. Verifica la configuración usando los comandos de verificación mencionados arriba
3. Consulta la documentación de SUNAT: https://www.sunat.gob.pe
4. Contacta a tu entidad certificadora para problemas relacionados con el certificado mismo
