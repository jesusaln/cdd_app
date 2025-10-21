# 🚀 CDD Sistema - Configuración de Producción

## 📋 Guía de Instalación en Windows Server 2019

### ✅ Prerrequisitos

1. **Windows Server 2019** con IIS instalado
2. **PHP 8.1+** instalado y configurado con IIS
3. **PostgreSQL** o **SQL Server** instalado y configurado
4. **Composer** instalado globalmente
5. **Node.js** (opcional, para compilación de assets)

### 🔧 Configuración Inicial

1. **Ejecutar el script de configuración:**
   ```batch
   start_production.bat
   ```

2. **Configurar IIS automáticamente:**
   ```batch
   # Ejecutar como Administrador
   setup_iis.bat
   ```

3. **Si aparece error 500.19, ejecutar la solución automática:**
   ```batch
   # Ejecutar como Administrador
   fix_50019_error.bat
   ```

4. **O configurar manualmente:**
   - Abrir **Administrador de IIS** como Administrador
   - Crear nuevo sitio web apuntando a: `C:\inetpub\wwwroot\cdd_app`
   - Puerto: `80` (o el deseado)
   - Pool de aplicaciones: Crear nuevo o usar existente
   - Configurar PHP según la instalación

### 🚨 Configuraciones Alternativas para Error 500.19

Si el error persiste, prueba estas opciones en orden:

**Opción 1 - Configuración Mínima:**
```batch
copy web.config.minimal web.config
```

**Opción 2 - Configuración Básica:**
```batch
copy web.config.simple web.config
```

**Opción 3 - Sin web.config (apuntar a carpeta public):**
- Crear sitio en IIS apuntando a: `C:\inetpub\wwwroot\cdd_app\public`

### 📊 Configuración de Base de Datos

#### Para PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=cdd_app_prod
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

#### Para SQL Server:
```env
DB_CONNECTION=sqlsrv
DB_HOST=localhost
DB_PORT=1433
DB_DATABASE=cdd_app_prod
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 🔒 Seguridad en Producción

1. **Configurar SSL/HTTPS** (recomendado)
2. **Configurar firewall** para permitir solo puertos necesarios
3. **Crear usuario específico** para la aplicación en la base de datos
4. **Configurar permisos mínimos** en el sistema de archivos

### 🚀 Inicio de la Aplicación

#### Opción 1: IIS (Recomendado)
1. Configurar sitio en IIS apuntando a `C:\inetpub\wwwroot\cdd_app`
2. Configurar PHP en el sitio
3. Navegar a `http://tuservidor/cdd_app` o configurar dominio

#### Opción 2: PHP Integrado
```batch
php artisan serve --host=0.0.0.0 --port=80
```

### 📁 Estructura de Archivos

```
C:\inetpub\wwwroot\cdd_app\
├── app/                    # Código de la aplicación
├── bootstrap/             # Archivos de inicio
├── config/                # Configuraciones
├── database/              # Migraciones y seeds
├── public/                # Archivos públicos
├── resources/             # Vistas y assets
├── routes/                # Definición de rutas
├── storage/               # Archivos generados
├── vendor/                # Dependencias de Composer
├── web.config             # Configuración de IIS
├── .env                   # Variables de entorno
└── artisan                # Comando de Laravel
```

### 🔧 Comandos Útiles de Mantenimiento

```batch
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar migraciones
php artisan migrate

# Crear respaldo de BD
php artisan backup:run
```

### 📊 Monitoreo y Logs

- **Logs de aplicación:** `storage/logs/laravel.log`
- **Logs de IIS:** Configurar en el sitio de IIS
- **Logs de Windows:** Event Viewer > Windows Logs > Application

### 🚨 Solución de Problemas

#### Error 500.19 (Archivo de configuración no válido)
Este error ocurre cuando hay problemas con el archivo `web.config`:

**Solución Automática (Recomendada):**
```batch
# Ejecutar como Administrador
fix_50019_error.bat
```

**Soluciones Manuales:**

1. **Configuración mínima:**
   ```batch
   copy web.config.minimal web.config
   ```

2. **Configuración básica:**
   ```batch
   copy web.config.simple web.config
   ```

3. **Configuración completa:**
   ```batch
   setup_iis.bat
   ```

4. **Sin archivo web.config (alternativa):**
   - Crear sitio en IIS apuntando a la carpeta `public/`
   - Configurar ruta física como: `C:\inetpub\wwwroot\cdd_app\public`

**Verificar permisos:**
```powershell
# Ejecutar en PowerShell como Administrador
icacls "C:\inetpub\wwwroot\cdd_app" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "C:\inetpub\wwwroot\cdd_app\storage" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "C:\inetpub\wwwroot\cdd_app\bootstrap\cache" /grant "IIS_IUSRS:(OI)(CI)F" /T
```

#### Error 500 (Error Interno del Servidor)
1. Verificar permisos de directorio `storage/`
2. Revisar logs en `storage/logs/laravel.log`
3. Verificar configuración de PHP en IIS

#### Error de Conexión a Base de Datos
1. Verificar credenciales en `.env`
2. Confirmar que el servicio de BD esté corriendo
3. Verificar permisos del usuario de BD

#### Problemas de Permisos
```powershell
# Reparar permisos
icacls "C:\inetpub\wwwroot\cdd_app\storage" /reset /T /C /Q
icacls "C:\inetpub\wwwroot\cdd_app\bootstrap\cache" /reset /T /C /Q
```

### 📞 Soporte

Para soporte técnico o problemas:
1. Revisar logs en `storage/logs/`
2. Verificar configuración en `.env`
3. Confirmar permisos del sistema de archivos

---

**🎉 ¡Tu aplicación CDD Sistema está lista para producción!**

**URL de acceso:** `http://tuservidor/cdd_app` o `http://localhost/cdd_app`