@echo off
chcp 65001 >nul
echo ========================================
echo  SOLUCIÓN: ERROR 404 NOT FOUND
echo ========================================
echo.
echo Diagnosticando y solucionando error 404 para archivos PHP...
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
echo  PASO 1: DIAGNÓSTICO DE LA SITUACIÓN ACTUAL
echo ========================================

echo Verificando configuración actual de IIS...
powershell -Command "
Import-Module WebAdministration;

Write-Host '=== SITIOS WEB ===';
Get-IISSite | Format-Table -Property Name, State, PhysicalPath, Bindings;

Write-Host '=== GRUPOS DE APLICACIONES ===';
Get-IISAppPool | Format-Table -Property Name, State, ProcessModel;

Write-Host '=== CONTROLADORES REGISTRADOS ===';
try {
    Get-WebHandler -PSPath 'MACHINE/WEBROOT/APPHOST' | Format-Table -Property Name, Path, Modules, ScriptProcessor
} catch {
    Write-Host 'Error obteniendo handlers:' $_.Exception.Message
}
"

echo.
echo ========================================
echo  PASO 2: VERIFICAR ARCHIVOS CRÍTICOS
echo ========================================

echo Verificando archivos críticos...

if exist "web.config" (
    echo ✅ web.config existe en raíz
    findstr "C:\php84\php-cgi.exe" web.config >nul 2>&1
    if errorlevel 1 (
        echo ❌ web.config no apunta a PHP correcto
    ) else (
        echo ✅ web.config apunta a PHP correcto
    )
) else (
    echo ❌ No existe web.config en raíz
)

if exist "public\test.php" (
    echo ✅ public\test.php existe
    echo Contenido del archivo test.php:
    echo ----------------------------------------
    type public\test.php
    echo ----------------------------------------
) else (
    echo ❌ No existe public\test.php
    echo Creando archivo de prueba...
    (
    echo ^<?php
    echo echo "¡PHP funcionando correctamente!\n";
    echo echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
    echo echo "Archivo ejecutándose desde: " . __FILE__ . "\n";
    echo phpinfo();
    echo ?^>
    ) > public\test.php
    echo ✅ Archivo de prueba creado
)

if exist "public\index.php" (
    echo ✅ public\index.php existe
) else (
    echo ❌ No existe public\index.php
)

echo.
echo ========================================
echo  PASO 3: VERIFICAR PERMISOS DE ARCHIVOS
echo ========================================

echo Verificando permisos de archivos críticos...

echo Permisos de public\test.php:
icacls "public\test.php" | findstr /C:"NETWORKSERVICE" /C:"IIS_IUSRS" /C:"Everyone"

if errorlevel 1 (
    echo Aplicando permisos a archivos PHP...
    icacls "public\test.php" /grant "NETWORKSERVICE:(RX)" /Q
    icacls "public\test.php" /grant "IIS_IUSRS:(RX)" /Q
    icacls "public\index.php" /grant "NETWORKSERVICE:(RX)" /Q 2>nul
    icacls "public\index.php" /grant "IIS_IUSRS:(RX)" /Q 2>nul
    echo ✅ Permisos aplicados
) else (
    echo ✅ Permisos ya están configurados
)

echo.
echo ========================================
echo  PASO 4: VERIFICAR Y CORREGIR CONFIGURACIÓN IIS
echo ========================================

echo Verificando configuración del sitio 'cdd_app'...
powershell -Command "
Import-Module WebAdministration;

try {
    \$site = Get-IISSite -Name 'cdd_app'
    if (\$site) {
        Write-Host '✅ Sitio encontrado:'
        \$site | Select-Object Name, State, PhysicalPath | Format-List

        # Verificar si el sitio apunta al directorio correcto
        \$expectedPath = 'C:\inetpub\wwwroot\cdd_app'
        \$actualPath = \$site.Applications[0].VirtualDirectories[0].PhysicalPath

        if (\$actualPath -eq \$expectedPath) {
            Write-Host '✅ Sitio apunta al directorio correcto'
        } else {
            Write-Host '❌ Sitio apunta a:' \$actualPath
            Write-Host '✅ Debería apuntar a:' \$expectedPath
            Write-Host 'Corrigiendo configuración del sitio...'
            Set-ItemProperty IIS:\Sites\cdd_app -name physicalPath -value \$expectedPath
            Write-Host '✅ Ruta física corregida'
        }
    } else {
        Write-Host '❌ Sitio cdd_app no encontrado'
        Write-Host 'Creando sitio web...'
        New-Website -Name 'cdd_app' -PhysicalPath 'C:\inetpub\wwwroot\cdd_app' -Port 80 -ApplicationPool 'cdd_app' -Force
        Write-Host '✅ Sitio creado'
    }
} catch {
    Write-Host 'Error verificando sitio:' \$_.Exception.Message
}
"

echo.
echo ========================================
echo  PASO 5: VERIFICAR Y CORREGIR GRUPO DE APLICACIONES
echo ========================================

echo Verificando grupo de aplicaciones...
powershell -Command "
Import-Module WebAdministration;

try {
    \$pool = Get-IISAppPool -Name 'cdd_app'
    if (\$pool) {
        Write-Host '✅ Grupo de aplicaciones encontrado:'
        \$pool | Select-Object Name, State, @{Name='IdentityType';Expression={\$_.processModel.identityType}}, @{Name='UserName';Expression={\$_.processModel.userName}} | Format-List

        # Verificar identidad
        if (\$pool.processModel.identityType -ne 2) {
            Write-Host 'Corrigiendo identidad del grupo de aplicaciones...'
            Set-ItemProperty IIS:\AppPools\cdd_app -name processModel.identityType -value 2
            Write-Host '✅ Identidad configurada como NetworkService'
        } else {
            Write-Host '✅ Identidad ya está configurada correctamente'
        }
    } else {
        Write-Host '❌ Grupo de aplicaciones no encontrado'
        Write-Host 'Creando grupo de aplicaciones...'
        New-Item IIS:\AppPools\cdd_app
        Set-ItemProperty IIS:\AppPools\cdd_app -name processModel.identityType -value 2
        Write-Host '✅ Grupo de aplicaciones creado'
    }
} catch {
    Write-Host 'Error verificando grupo de aplicaciones:' \$_.Exception.Message
}
"

echo.
echo ========================================
echo  PASO 6: VERIFICAR Y CORREGIR CONTROLADOR PHP
echo ========================================

echo Verificando controlador PHP...
powershell -Command "
Import-Module WebAdministration;

try {
    # Verificar si existe controlador PHP
    \$handlers = Get-WebConfiguration -Filter '/system.webServer/handlers' -PSPath 'MACHINE/WEBROOT/APPHOST'
    \$phpHandler = \$handlers | Where-Object { \$_.name -eq 'PHP_via_FastCGI' }

    if (\$phpHandler) {
        Write-Host '✅ Controlador PHP_via_FastCGI encontrado'
        Write-Host 'Path:' \$phpHandler.path
        Write-Host 'ScriptProcessor:' \$phpHandler.scriptProcessor
    } else {
        Write-Host '❌ Controlador PHP_via_FastCGI no encontrado'
        Write-Host 'Creando controlador PHP...'

        # Crear controlador PHP básico
        Add-WebHandler -Name 'PHP_via_FastCGI' -Path '*.php' -Verb '*' -Modules 'FastCgiModule' -ScriptProcessor 'C:\php84\php-cgi.exe' -PSPath 'MACHINE/WEBROOT/APPHOST' -ErrorAction SilentlyContinue
        Write-Host '✅ Controlador PHP creado'
    }

    # Verificar configuración específica del sitio
    \$siteHandlers = Get-WebConfiguration -Filter '/system.webServer/handlers' -PSPath 'MACHINE/WEBROOT/APPHOST/cdd_app'
    \$sitePhpHandler = \$siteHandlers | Where-Object { \$_.name -eq 'PHP_via_FastCGI' }

    if (\$sitePhpHandler) {
        Write-Host '✅ Controlador PHP configurado para sitio específico'
    } else {
        Write-Host 'Configurando controlador PHP para sitio específico...'
        Set-WebConfiguration -Filter '/system.webServer/handlers' -PSPath 'MACHINE/WEBROOT/APPHOST/cdd_app' -Value @{
            add = @{
                name = 'PHP_via_FastCGI'
                path = '*.php'
                verb = '*'
                modules = 'FastCgiModule'
                scriptProcessor = 'C:\php84\php-cgi.exe'
                resourceType = 'Either'
            }
        }
        Write-Host '✅ Controlador PHP configurado para sitio'
    }

} catch {
    Write-Host 'Error verificando controlador PHP:' \$_.Exception.Message
}
"

echo.
echo ========================================
echo  PASO 7: REINICIAR IIS Y VERIFICAR
echo ========================================

echo Reiniciando servicios IIS...
iisreset >nul 2>&1
if errorlevel 1 (
    echo ⚠️ No se pudo reiniciar IIS automáticamente
    echo    Reinicie manualmente desde el Administrador de IIS
) else (
    echo ✅ Servicios IIS reiniciados
)

echo.
echo ========================================
echo  PASO 8: PRUEBA FINAL
echo ========================================

echo Probando configuración...

echo.
echo URLs para probar:
echo.
echo 1. Archivo de prueba PHP:
echo    http://localhost/test.php
echo.
echo 2. Archivo principal Laravel:
echo    http://localhost/index.php
echo.
echo 3. Archivo de prueba directo:
echo    http://localhost/public/test.php
echo.
echo ========================================
echo  DIAGNÓSTICO ADICIONAL
echo ========================================
echo.
echo Si aún tienes error 404, puede ser debido a:
echo.
echo 1. El sitio en IIS no apunta al directorio correcto
echo    Solución: Verificar en IIS Manager → Sitios → cdd_app → Configuración básica
echo.
echo 2. Permisos de archivo insuficientes
echo    Solución: icacls "C:\inetpub\wwwroot\cdd_app\public\test.php" /grant "IIS_IUSRS:(RX)"
echo.
echo 3. PHP no está procesando archivos
echo    Solución: Verificar instalación de PHP y configuración de FastCGI
echo.
echo 4. Puerto 80 ocupado por otro servicio
echo    Solución: Verificar puertos con: netstat -an | findstr :80
echo.

echo ========================================
echo  COMANDOS DE VERIFICACIÓN RÁPIDA
echo ========================================
echo.
echo Copia y pega estos comandos para verificar rápidamente:
echo.
echo # Verificar sitio IIS
echo Get-IISSite -Name 'cdd_app' | Select-Object Name, State, PhysicalPath
echo.
echo # Verificar grupo de aplicaciones
echo Get-IISAppPool -Name 'cdd_app' | Select-Object Name, State, @{Name='Identity';Expression={$_.processModel.identityType}}
echo.
echo # Verificar controlador PHP
echo Get-WebHandler -PSPath 'MACHINE/WEBROOT/APPHOST/cdd_app' | Where-Object {$_.name -eq 'PHP_via_FastCGI'}
echo.

pause