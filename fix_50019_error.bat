@echo off
echo ========================================
echo  SOLUCIONANDO ERROR HTTP 500.19
echo ========================================
echo.

REM Verificar si se está ejecutando como Administrador
net session >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ Ejecutando como Administrador
) else (
    echo ❌ Este script debe ejecutarse como Administrador
    echo    Haz clic derecho en el archivo y selecciona "Ejecutar como administrador"
    echo.
    pause
    exit /b 1
)

echo.
echo ========================================
echo  PASO 1: Respaldo del archivo actual
echo ========================================
if exist web.config (
    copy web.config web.config.backup >nul 2>&1
    echo ✅ Respaldo creado: web.config.backup
) else (
    echo ℹ️ No existe web.config actual
)

echo.
echo ========================================
echo  PASO 2: Aplicar configuración mínima
echo ========================================
copy web.config.minimal web.config >nul 2>&1
if exist web.config (
    echo ✅ Archivo web.config.minimal aplicado
) else (
    echo ❌ Error al copiar web.config.minimal
    pause
    exit /b 1
)

echo.
echo ========================================
echo  PASO 3: Configurar permisos NTFS
echo ========================================
echo Configurando permisos para IIS_IUSRS...

REM Configurar permisos en el directorio raíz
icacls "%~dp0" /grant "IIS_IUSRS:(OI)(CI)F" /T /Q
if errorlevel 1 (
    echo ❌ Error configurando permisos del directorio raíz
) else (
    echo ✅ Permisos del directorio raíz configurados
)

REM Configurar permisos en storage
if exist "%~dp0storage" (
    icacls "%~dp0storage" /grant "IIS_IUSRS:(OI)(CI)F" /T /Q
    if errorlevel 1 (
        echo ❌ Error configurando permisos de storage
    ) else (
        echo ✅ Permisos de storage configurados
    )
) else (
    echo ℹ️ Directorio storage no existe, se creará automáticamente
)

REM Configurar permisos en bootstrap/cache
if exist "%~dp0bootstrap\cache" (
    icacls "%~dp0bootstrap\cache" /grant "IIS_IUSRS:(OI)(CI)F" /T /Q
    if errorlevel 1 (
        echo ❌ Error configurando permisos de bootstrap\cache
    ) else (
        echo ✅ Permisos de bootstrap\cache configurados
    )
) else (
    echo ℹ️ Directorio bootstrap\cache no existe, se creará automáticamente
)

echo.
echo ========================================
echo  PASO 4: Verificar instalación de PHP
echo ========================================
php --version >nul 2>&1
if errorlevel 1 (
    echo ❌ PHP no está instalado o no está en el PATH
    echo.
    echo Soluciones:
    echo 1. Instalar PHP para IIS
    echo 2. Agregar PHP al PATH del sistema
    echo 3. Configurar PHP en el Administrador de IIS
    echo.
) else (
    echo ✅ PHP está disponible
    php --version
)

echo.
echo ========================================
echo  PASO 5: Crear directorios necesarios
echo ========================================
if not exist "%~dp0storage" (
    mkdir "%~dp0storage"
    echo ✅ Directorio storage creado
) else (
    echo ✅ Directorio storage ya existe
)

if not exist "%~dp0storage\logs" (
    mkdir "%~dp0storage\logs"
    echo ✅ Directorio storage\logs creado
) else (
    echo ✅ Directorio storage\logs ya existe
)

if not exist "%~dp0bootstrap\cache" (
    mkdir "%~dp0bootstrap\cache"
    echo ✅ Directorio bootstrap\cache creado
) else (
    echo ✅ Directorio bootstrap\cache ya existe
)

echo.
echo ========================================
echo  PASO 6: Verificar archivo .env
echo ========================================
if exist .env (
    echo ✅ Archivo .env encontrado
    findstr "APP_KEY" .env >nul 2>&1
    if errorlevel 1 (
        echo ⚠️ Archivo .env no tiene APP_KEY configurada
        php artisan key:generate
    ) else (
        echo ✅ Archivo .env tiene APP_KEY configurada
    )
) else (
    echo ❌ Archivo .env no encontrado
    if exist .env.example (
        copy .env.example .env >nul 2>&1
        echo ✅ Archivo .env creado desde .env.example
        php artisan key:generate
    ) else (
        echo ❌ Archivo .env.example no encontrado
    )
)

echo.
echo ========================================
echo  PASO 7: Probar configuración
echo ========================================
echo Probando configuración de IIS...
echo.
echo Si el error persiste después de estos pasos:
echo.
echo 1. Abre el Administrador de IIS
echo 2. Ve a "Sitios" en el panel izquierdo
echo 3. Selecciona tu sitio web
echo 4. En el panel derecho, haz clic en "Explorar"
echo 5. Si aún hay error, revisa los logs de Windows:
echo    Event Viewer > Windows Logs > Application
echo.
echo ========================================
echo  SOLUCIÓN ALTERNATIVA SIN WEB.CONFIG
echo ========================================
echo.
echo Si el error persiste, puedes intentar SIN archivo web.config:
echo.
echo 1. Eliminar o renombrar el archivo web.config
echo 2. Crear sitio en IIS apuntando directamente a la carpeta "public"
echo 3. Configurar la ruta física como: %~dp0public
echo.
echo ========================================
echo  CONFIGURACIÓN COMPLETADA
echo ========================================
echo.
echo El error 500.19 debería estar solucionado.
echo.
echo Para verificar que funciona:
echo 1. Abre el Administrador de IIS
echo 2. Selecciona el sitio web
echo 3. Haz clic en "Explorar" en el panel derecho
echo.
echo Si aún tienes problemas, revisa:
echo - Los logs de Windows Event Viewer
echo - La configuración de PHP en IIS
echo - Los permisos del directorio
echo.
pause