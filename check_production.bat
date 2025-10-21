@echo off
echo ========================================
echo  Verificando Configuración de Producción
echo ========================================
echo.

REM Verificar PHP
echo Verificando PHP...
php --version >nul 2>&1
if errorlevel 1 (
    echo ❌ PHP no está instalado o no está en el PATH
) else (
    echo ✅ PHP está disponible
    php --version
)

echo.

REM Verificar permisos de storage
echo Verificando permisos de storage...
icacls storage /grant "IIS_IUSRS:(OI)(CI)F" >nul 2>&1
if errorlevel 1 (
    echo ❌ No se pudieron configurar permisos de storage
) else (
    echo ✅ Permisos de storage configurados
)

echo.

REM Verificar permisos de bootstrap/cache
echo Verificando permisos de bootstrap/cache...
icacls bootstrap\cache /grant "IIS_IUSRS:(OI)(CI)F" >nul 2>&1
if errorlevel 1 (
    echo ❌ No se pudieron configurar permisos de bootstrap/cache
) else (
    echo ✅ Permisos de bootstrap/cache configurados
)

echo.

REM Verificar archivo .env
if exist .env (
    echo ✅ Archivo .env encontrado
) else (
    echo ❌ Archivo .env no encontrado
)

echo.

REM Verificar configuración de base de datos
echo Verificando configuración de base de datos...
findstr "DB_CONNECTION" .env >nul 2>&1
if errorlevel 1 (
    echo ❌ Configuración de base de datos no encontrada en .env
) else (
    echo ✅ Configuración de base de datos encontrada
)

echo.

REM Verificar clave de aplicación
echo Verificando clave de aplicación...
php -r "echo 'APP_KEY=' . config('app.key') . PHP_EOL;" 2>nul | findstr "base64:" >nul 2>&1
if errorlevel 1 (
    echo ❌ Clave de aplicación no válida o no configurada
) else (
    echo ✅ Clave de aplicación válida
)

echo.

REM Verificar archivos críticos
echo Verificando archivos críticos...
if exist public\index.php (
    echo ✅ public\index.php encontrado
) else (
    echo ❌ public\index.php no encontrado
)

if exist web.config (
    echo ✅ web.config encontrado
) else (
    echo ❌ web.config no encontrado
)

echo.

REM Verificar dependencias de Composer
echo Verificando dependencias de Composer...
if exist vendor\autoload.php (
    echo ✅ Dependencias de Composer instaladas
) else (
    echo ❌ Dependencias de Composer no instaladas
    echo    Ejecuta: composer install
)

echo.

echo ========================================
echo  Verificación completada
echo ========================================
echo.
echo Para iniciar la aplicación:
echo 1. Abre el Administrador de IIS
echo 2. Crea un sitio web apuntando a este directorio
echo 3. Navega a http://localhost/cdd_app
echo.
echo O usa: php artisan serve --host=0.0.0.0
echo.
pause