@echo off
chcp 65001 >nul
echo ========================================
echo  CONFIGURACIÓN IIS PRODUCCIÓN - MÉTODO ALTERNATIVO
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
echo  MÉTODO 1: USANDO POWERSHELL SCRIPT
echo ========================================

if exist "configure_iis_production.ps1" (
    echo Ejecutando configuración automática de IIS...
    powershell -ExecutionPolicy Bypass -File "configure_iis_production.ps1"
    if errorlevel 1 (
        echo ❌ Error en configuración automática
    ) else (
        echo ✅ Configuración automática completada
    )
) else (
    echo ❌ Archivo configure_iis_production.ps1 no encontrado
)

echo.
echo ========================================
echo  MÉTODO 2: COMANDOS MANUALES SIMPLIFICADOS
echo ========================================
echo.
echo Si el método automático falló, ejecuta estos comandos manualmente:
echo.
echo 1. Abrir Administrador de IIS como Administrador
echo.
echo 2. Crear/Verificar Grupo de Aplicaciones:
echo    - Grupos de aplicaciones → cdd_app
echo    - Identidad: NetworkService
echo.
echo 3. Crear/Verificar Sitio Web:
echo    - Sitios → cdd_app (si existe, verificar configuración)
echo    - Ruta física: C:\inetpub\wwwroot\cdd_app
echo    - Puerto: 80
echo    - Grupo de aplicaciones: cdd_app
echo.
echo 4. Configurar Controlador PHP:
echo    - Sitio → Controladores → Agregar controlador
echo    - Nombre: PHP_via_FastCGI
echo    - Extensión: *.php
echo    - Ejecutable: C:\php84\php-cgi.exe
echo    - Tipo: FastCgiModule
echo.
echo 5. Configurar Documento Predeterminado:
echo    - Sitio → Documentos predeterminados
echo    - Agregar: index.php
echo.
echo ========================================
echo  MÉTODO 3: USANDO APPCMD (LÍNEA DE COMANDOS)
echo ========================================
echo.
echo Si tienes problemas con la interfaz gráfica, usa estos comandos:
echo.

REM Crear sitio usando appcmd si existe
where appcmd >nul 2>&1
if errorlevel 1 (
    echo ❌ appcmd no disponible
) else (
    echo ✅ appcmd disponible

    REM Verificar si sitio existe
    appcmd list site "cdd_app" >nul 2>&1
    if errorlevel 1 (
        echo Creando sitio web...
        appcmd add site /name:"cdd_app" /physicalPath:"C:\inetpub\wwwroot\cdd_app" /bindings:http/*:80:
    ) else (
        echo ✅ Sitio ya existe
    )

    REM Verificar grupo de aplicaciones
    appcmd list apppool "cdd_app" >nul 2>&1
    if errorlevel 1 (
        echo Creando grupo de aplicaciones...
        appcmd add apppool /name:"cdd_app" /processModel.identityType:2
    ) else (
        echo ✅ Grupo de aplicaciones ya existe
    )

    REM Configurar aplicación para sitio
    appcmd set site /site.name:"cdd_app" /applicationDefaults.applicationPool:"cdd_app"
)

echo.
echo ========================================
echo  PERMISOS NTFS REQUERIDOS
echo ========================================
echo.

echo Aplicando permisos NTFS críticos...

REM Permisos para NetworkService
icacls "%~dp0" /grant "NETWORKSERVICE:(OI)(CI)RX" /T /Q
if errorlevel 1 (
    echo ❌ Error permisos NetworkService
) else (
    echo ✅ Permisos NetworkService aplicados
)

REM Permisos para IIS_IUSRS
icacls "%~dp0" /grant "IIS_IUSRS:(OI)(CI)M" /T /Q
if errorlevel 1 (
    echo ❌ Error permisos IIS_IUSRS
) else (
    echo ✅ Permisos IIS_IUSRS aplicados
)

REM Permisos específicos para directorios críticos
if exist "%~dp0storage" (
    icacls "%~dp0storage" /grant "NETWORKSERVICE:(OI)(CI)M" /T /Q
    icacls "%~dp0storage" /grant "IIS_IUSRS:(OI)(CI)M" /T /Q
    echo ✅ Permisos storage aplicados
) else (
    echo ⚠️ Directorio storage no existe
)

if exist "%~dp0bootstrap\cache" (
    icacls "%~dp0bootstrap\cache" /grant "NETWORKSERVICE:(OI)(CI)M" /T /Q
    icacls "%~dp0bootstrap\cache" /grant "IIS_IUSRS:(OI)(CI)M" /T /Q
    echo ✅ Permisos bootstrap\cache aplicados
) else (
    echo ⚠️ Directorio bootstrap\cache no existe
)

echo.
echo ========================================
echo  VERIFICACIÓN FINAL
echo ========================================
echo.

echo Verificando configuración actual de IIS...
powershell -Command "
try {
    Import-Module WebAdministration;
    \$site = Get-IISSite -Name 'cdd_app';
    if (\$site) {
        Write-Host '✅ Sitio encontrado:';
        \$site | Select-Object Name, State, PhysicalPath | Format-List;
    } else {
        Write-Host '❌ Sitio no encontrado';
    }

    \$pool = Get-IISAppPool -Name 'cdd_app';
    if (\$pool) {
        Write-Host '✅ Grupo de aplicaciones encontrado:';
        \$pool | Select-Object Name, State, @{Name='Identity';Expression={\$_.processModel.identityType}} | Format-List;
    } else {
        Write-Host '❌ Grupo de aplicaciones no encontrado';
    }
} catch {
    Write-Host 'Error verificando IIS:' \$_.Exception.Message;
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
echo    http://localhost/index.php
echo    http://localhost/test.php
echo.
echo 3. Si funciona, ejecutar optimizaciones:
echo    php artisan config:cache
echo    php artisan route:cache
echo    php artisan view:cache
echo.
echo ========================================
echo  ARCHIVOS DE AYUDA
echo ========================================
echo.
echo - configure_iis_production.ps1 (configuración automática)
echo - setup_iis_production.bat (este archivo)
echo - start_production.bat (inicio en producción)
echo.

pause