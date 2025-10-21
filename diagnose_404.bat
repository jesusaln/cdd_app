@echo off
chcp 65001 >nul
echo ========================================
echo  DIAGNÓSTICO RÁPIDO: ERROR 404
echo ========================================
echo.

echo Analizando posible causa del error 404...
echo.

echo ========================================
echo  VERIFICACIÓN DE ARCHIVOS
echo ========================================

echo Verificando archivos críticos...

if exist "web.config" (
    echo ✅ web.config existe
    findstr "C:\php84\php-cgi.exe" web.config >nul 2>&1
    if errorlevel 1 (
        echo ❌ web.config no apunta a PHP correcto
    ) else (
        echo ✅ web.config apunta a PHP correcto
    )
) else (
    echo ❌ No existe web.config
)

if exist "public\test.php" (
    echo ✅ public\test.php existe
    echo Tamaño del archivo: & dir public\test.php | findstr "test.php"
) else (
    echo ❌ No existe public\test.php
)

if exist "public\index.php" (
    echo ✅ public\index.php existe
) else (
    echo ❌ No existe public\index.php
)

echo.
echo ========================================
echo  VERIFICACIÓN DE PERMISOS
echo ========================================

echo Verificando permisos de archivos...

echo Permisos actuales de public\test.php:
icacls "public\test.php" 2>nul | findstr /C:"IIS_IUSRS" /C:"NETWORKSERVICE" /C:"Everyone"

if errorlevel 1 (
    echo ⚠️ Permisos podrían ser insuficientes
) else (
    echo ✅ Permisos verificados
)

echo.
echo ========================================
echo  VERIFICACIÓN DE PHP
echo ========================================

echo Verificando instalación de PHP...
php --version >nul 2>&1
if errorlevel 1 (
    echo ❌ PHP no está disponible en PATH
) else (
    echo ✅ PHP disponible
    php --version
)

echo.
echo ========================================
echo  POSIBLES CAUSAS DEL ERROR 404
echo ========================================
echo.
echo Basado en el análisis, el error 404 puede deberse a:
echo.
echo 1. 🔴 CONFIGURACIÓN IIS:
echo    - El sitio no apunta al directorio correcto
echo    - El grupo de aplicaciones no está configurado
echo    - El controlador PHP no está registrado
echo.
echo 2. 🔴 PERMISOS NTFS:
echo    - IIS no tiene permisos para leer archivos PHP
echo    - Cuenta NetworkService no tiene acceso
echo.
echo 3. 🔴 ARCHIVO WEB.CONFIG:
echo    - Puede estar corrupto o mal configurado
echo    - PHP no está registrado correctamente
echo.
echo ========================================
echo  SOLUCIONES RÁPIDAS
echo ========================================
echo.
echo SOLUCIÓN 1: Verificar configuración IIS
echo ----------------------------------------
echo 1. Abrir Administrador de IIS como Administrador
echo 2. Verificar sitio "cdd_app":
echo    - Ruta física: C:\inetpub\wwwroot\cdd_app
echo    - Puerto: 80
echo    - Grupo de aplicaciones: cdd_app
echo.
echo SOLUCIÓN 2: Verificar permisos
echo -----------------------------
echo Ejecutar como Administrador:
echo icacls "C:\inetpub\wwwroot\cdd_app" /grant "IIS_IUSRS:(OI)(CI)M" /T /Q
echo icacls "C:\inetpub\wwwroot\cdd_app\public" /grant "IIS_IUSRS:(OI)(CI)RX" /T /Q
echo.
echo SOLUCIÓN 3: Recrear web.config
echo -------------------------------
echo Si web.config está corrupto, eliminarlo y crear nuevo:
echo del web.config
echo Copiar configuración mínima garantizada
echo.
echo ========================================
echo  COMANDOS PARA EJECUTAR MANUALMENTE
echo ========================================
echo.
echo Copia y pega estos comandos en PowerShell (como Administrador):
echo.
echo # 1. Verificar sitio actual
echo Get-IISSite -Name 'cdd_app' | Select-Object Name, State, PhysicalPath
echo.
echo # 2. Verificar grupo de aplicaciones
echo Get-IISAppPool -Name 'cdd_app' | Select-Object Name, State, @{Name='Identity';Expression={$_.processModel.identityType}}
echo.
echo # 3. Crear sitio si no existe
echo if (!(Test-Path IIS:\Sites\cdd_app)) {
echo     New-Website -Name 'cdd_app' -PhysicalPath 'C:\inetpub\wwwroot\cdd_app' -Port 80 -ApplicationPool 'cdd_app' -Force
echo     Write-Host 'Sitio creado'
echo }
echo.
echo # 4. Configurar controlador PHP
echo Set-WebConfiguration -Filter '/system.webServer/handlers' -PSPath 'MACHINE/WEBROOT/APPHOST/cdd_app' -Value @{
echo     add = @{
echo         name = 'PHP_via_FastCGI'
echo         path = '*.php'
echo         verb = '*'
echo         modules = 'FastCgiModule'
echo         scriptProcessor = 'C:\php84\php-cgi.exe'
echo         resourceType = 'Either'
echo     }
echo }
echo.
echo ========================================
echo  PRUEBA DESPUÉS DE APLICAR SOLUCIONES
echo ========================================
echo.
echo Después de aplicar las soluciones, prueba:
echo.
echo 1. Reiniciar IIS: iisreset
echo.
echo 2. Probar URLs:
echo    http://localhost/test.php
echo    http://localhost/index.php
echo    http://localhost/public/test.php
echo.
echo 3. Si funciona, entonces:
echo    ✅ Configuración IIS correcta
echo    ✅ PHP procesando archivos correctamente
echo    ✅ Permisos NTFS adecuados
echo.

pause