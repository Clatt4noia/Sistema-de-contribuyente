# Sistema de Gestión Logística e Integración SUNAT

Este repositorio contiene el sistema de facturación y emisión de Guías de Remisión (Remitente y Transportista) con integración electrónica hacia la SUNAT, desarrollado sobre Laravel y Livewire.

---

## 🛠️ Manual de Configuración del Proyecto

### 1. Requisitos Previos
Para levantar y ejecutar correctamente el sistema, tu entorno lógico debe contar con:
- **PHP** >= 8.2
- **Composer** (v2+)
- **Node.js** y **NPM**
- Servidor de base de datos (MySQL/MariaDB recomendado)

### 2. Instalación y Puesta en Marcha
Una vez clonado el repositorio, sigue estos pasos para el despliegue inicial:

```bash
# 1. Instalar dependencias de PHP
composer install

# 2. Instalar dependencias de Frontend (Vite)
npm install

# 3. Preparar tu archivo de entorno global
cp .env.example .env
php artisan key:generate

# 4. Configurar la Base de Datos en el `.env`
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel12-auth (u otro nombre)
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Ejecutar Migraciones e Inyectar Datos Prueba (Seeders)
php artisan migrate:fresh --seed

# 6. Levantar los servidores locales
php artisan serve
npm run dev
```

### 3. Credenciales de Prueba y Acceso
El sistema se pre-configura con un seeder de pruebas (`DatabaseSeeder`) que aloja la siguiente cuenta:
- **Correo**: `admin@admin.com`
- **Contraseña**: `password`

---

## 🚚 Requisitos Estrictos: Guías de Remisión Transportista (GRE-T)

Para evitar rechazos y errores estructurales de formato al emitir **Guías de Remisión (Transportista / Remitente)** mediante OSE o SUNAT (como el clásico Error 3355), el sistema exige registrar la información maestra con los siguientes formatos obligatorios:

### 1. Vehículos (Tractos y Remolques)
* **Placa del Vehículo:** 6 caracteres alfanuméricos en mayúsculas, **sin guiones** (ej. `C9F813`).
* **TUCE (Tarjeta Única de Circulación):** Código alfanumérico emitido al vehículo (ej. `15M23039620E`). Indispensable para validar la unidad principal y al menos un remolque.
* **Autorización Especial (Emisor):** Usualmente `MTC` (Mapeado internamente en el XML al Código `06` del Catálogo D-37 SUNAT).
* **N° de Autorización:** Número oficial vinculado al vehículo (ej. `151908863`).
* **Vehículos Secundarios:** El sistema distingue entre "Principal" y "Secundario" mediante la bandera `is_secondary`. Si se anexa un remolque a la guía, se inyecta la etiqueta UBL `<cac:AttachedTransportEquipment>` respetando la secuencia estandarizada requerida por la SUNAT.

### 2. Conductores
* **Tipo de Documento:** DNI (`1`) obligatorio para nacionales.
* **Número de Documento:** Exactamente `8` dígitos numéricos.
* **Licencia de Conducir:** Alfanumérico exacto de `9` caracteres. Normalmente, es una letra alfabética seguida de 8 números (casi siempre el DNI). Ejemplo: `Q09794946` o `V16837629`.

### 3. Clientes (Remitentes / Destinatarios)
* **Tipo de Documento:** RUC (`6`) para el registro formal de la carga.
* **Número de RUC:** Exactamente `11` dígitos numéricos (prefijos obligatorios `10` o `20`).

### 4. Detalles de la Carga (Bienes a Transportar)
* **Unidad de Medida (UND):** Deben utilizarse catálogos internacionales de la ONU - rec 20 requeridos por SUNAT (ej. `KGM` para kilogramos, `NIU` para unidades, `TNE` para toneladas).

---
*Nota: Si utilizas el entorno NubeFact de "Pruebas/Beta", deberás inyectar un RUC de prueba (`20000000001` - `GREEN SAC`) y acatar temporalmente las TUCEs aprobadas en la base de datos estática de sus servidores de desarrollo.*