@echo off
chcp 65001 >nul
echo ========================================
echo  COMANDOS CORREGIDOS PARA IIS
echo ========================================
echo.
echo Basado en los errores encontrados, aquí están los comandos corregidos:
echo.

echo ========================================
echo  COMANDOS POWERSHELL CORREGIDOS
echo ========================================
echo.
echo Copia y pega estos comandos en PowerShell (como Administrador):
echo.

echo # 1. Crear grupo de aplicaciones (si no existe)
echo try {
echo     Import-Module WebAdministration
echo     if (!(Test-Path IIS:\AppPools\cdd_app)) {
echo         New-Item IIS:\AppPools\cdd_app
echo         Set-ItemProperty IIS:\AppPools\cdd_app -name processModel.identityType -value 2
echo         Write-Host "Grupo de aplicaciones creado"
echo     } else {
echo         Write-Host "Grupo de aplicaciones ya existe"
echo     }
echo } catch {
echo     Write-Host "Error:" $_.Exception.Message
echo }
echo.

echo # 2. Crear sitio web (forzar si existe)
echo try {
echo     Import-Module WebAdministration
echo     if (!(Test-Path IIS:\Sites\cdd_app)) {
echo         New-Website -Name 'cdd_app' -PhysicalPath 'C:\inetpub\wwwroot\cdd_app' -Port 80 -ApplicationPool 'cdd_app' -Force
echo         Write-Host "Sitio web creado"
echo     } else {
echo         Write-Host "Sitio web ya existe"
echo     }
echo } catch {
echo     Write-Host "Error:" $_.Exception.Message
echo }
echo.

echo # 3. Configurar controlador PHP
echo try {
echo     Import-Module WebAdministration
echo     Set-WebConfiguration -Filter '/system.webServer/handlers' -PSPath 'MACHINE/WEBROOT/APPHOST/cdd_app' -Value @{
echo         add = @{
echo             name = 'PHP_via_FastCGI'
echo             path = '*.php'
echo             verb = '*'
echo             modules = 'FastCgiModule'
echo             scriptProcessor = 'C:\php84\php-cgi.exe'
echo             resourceType = 'Either'
echo         }
echo     }
echo     Write-Host "Controlador PHP configurado"
echo } catch {
echo     Write-Host "Error:" $_.Exception.Message
echo }
echo.

echo ========================================
echo  COMANDOS APPCMD ALTERNATIVOS
echo ========================================
echo.
echo Si PowerShell falla, usa estos comandos appcmd:
echo.

echo # 1. Crear grupo de aplicaciones
echo appcmd add apppool /name:"cdd_app" /processModel.identityType:2
echo.

echo # 2. Crear sitio web (si no existe)
echo appcmd add site /name:"cdd_app" /physicalPath:"C:\inetpub\wwwroot\cdd_app" /bindings:http/*:80:
echo.

echo # 3. Configurar aplicación para sitio
echo appcmd set site /site.name:"cdd_app" /applicationDefaults.applicationPool:"cdd_app"
echo.

echo ========================================
echo  COMANDOS CMD SIMPLIFICADOS
echo ========================================
echo.
echo Si todo falla, usa estos comandos CMD básicos:
echo.

echo # 1. Reiniciar IIS completamente
echo net stop w3svc /y ^& net stop was /y ^& net start w3svc ^& net start was
echo.

echo # 2. Verificar servicios IIS
echo sc query w3svc
echo sc query was
echo.

echo ========================================
echo  EJECUCIÓN AUTOMÁTICA
echo ========================================
echo.

echo Ejecutando configuración automática...
powershell -Command "
try {
    Import-Module WebAdministration;

    # Crear grupo de aplicaciones
    if (!(Test-Path IIS:\AppPools\cdd_app)) {
        New-Item IIS:\AppPools\cdd_app;
        Set-ItemProperty IIS:\AppPools\cdd_app -name processModel.identityType -value 2;
        Write-Host 'Grupo de aplicaciones creado';
    }

    # Crear sitio web
    if (!(Test-Path IIS:\Sites\cdd_app)) {
        New-Website -Name 'cdd_app' -PhysicalPath 'C:\inetpub\wwwroot\cdd_app' -Port 80 -ApplicationPool 'cdd_app' -Force;
        Write-Host 'Sitio web creado';
    }

    # Configurar controlador PHP
    Set-WebConfiguration -Filter '/system.webServer/handlers' -PSPath 'MACHINE/WEBROOT/APPHOST/cdd_app' -Value @{
        add = @{
            name = 'PHP_via_FastCGI';
            path = '*.php';
            verb = '*';
            modules = 'FastCgiModule';
            scriptProcessor = 'C:\php84\php-cgi.exe';
            resourceType = 'Either';
        }
    };
    Write-Host 'Controlador PHP configurado';

    Write-Host 'Configuración completada exitosamente';
} catch {
    Write-Host 'Error durante configuración automática:' $_.Exception.Message;
}
"

echo.
echo ========================================
echo  VERIFICACIÓN FINAL
echo ========================================
echo.

echo Verificando configuración...
powershell -Command "
Import-Module WebAdministration;
try {
    \$site = Get-IISSite -Name 'cdd_app';
    \$pool = Get-IISAppPool -Name 'cdd_app';
    Write-Host 'Sitio:' \$site.Name 'Estado:' \$site.State;
    Write-Host 'Pool:' \$pool.Name 'Estado:' \$pool.State 'Identidad:' \$pool.processModel.identityType;
} catch {
    Write-Host 'Error verificando:' $_.Exception.Message;
}
"

echo.
echo ========================================
echo  PRÓXIMOS PASOS
echo ========================================
echo.
echo 1. Reiniciar IIS: iisreset
echo.
echo 2. Probar sitio:
echo    http://localhost/
echo    http://localhost/test.php
echo.
echo 3. Si funciona, ejecutar:
echo    php artisan config:cache
echo    php artisan route:cache
echo    php artisan view:cache
echo.

pause