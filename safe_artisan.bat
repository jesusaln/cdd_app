@echo off
chcp 65001 >nul

REM Verificar si APP_ENV es production
findstr "APP_ENV=production" .env >nul 2>&1
if errorlevel 1 (
    echo ✅ Entorno seguro: APP_ENV no es production. Ejecutando comando...
    php artisan %*
) else (
    echo ❌ ERROR: Estás en entorno de PRODUCCIÓN (APP_ENV=production)
    echo.
    echo No se pueden ejecutar comandos de desarrollo en producción.
    echo.
    echo Si necesitas ejecutar este comando, cambia APP_ENV a 'local' en .env
    echo y reinicia el servidor.
    echo.
    echo Comando intentado: php artisan %*
    echo.
    pause
    exit /b 1
)