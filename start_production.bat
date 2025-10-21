@echo off
echo ========================================
echo  Iniciando CDD Sistema en Producción
echo ========================================
echo.

REM Verificar si PHP está disponible
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP no está instalado o no está en el PATH
    echo.
    echo Por favor instala PHP o agrega PHP al PATH del sistema
    pause
    exit /b 1
)

REM Verificar si Composer está disponible
composer --version >nul 2>&1
if errorlevel 1 (
    echo ADVERTENCIA: Composer no está disponible
    echo Algunas funciones pueden no trabajar correctamente
    echo.
    pause
)

echo Configurando permisos de storage...
icacls storage /grant "IIS_IUSRS:(OI)(CI)F" /T /Q
icacls bootstrap\cache /grant "IIS_IUSRS:(OI)(CI)F" /T /Q

echo.
echo Generando clave de aplicación...
php artisan key:generate

echo.
echo Optimizando aplicación para producción...
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo.
echo Ejecutando migraciones pendientes...
php artisan migrate

echo.
echo ========================================
echo  Configuración completada exitosamente
echo ========================================
echo.
echo La aplicación está lista para producción
echo.
echo Para iniciar el servidor web:
echo 1. Abre el Administrador de IIS
echo 2. Crea o verifica el sitio web apuntando a:
echo    Directorio físico: %~dp0
echo    Puerto: 80
echo.
echo O usa el comando: php artisan serve --host=0.0.0.0
echo.
pause