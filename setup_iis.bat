@echo off
echo ========================================
echo  Configuración de IIS para CDD Sistema
echo ========================================
echo.

REM Verificar si se está ejecutando como Administrador
net session >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ Ejecutando como Administrador
) else (
    echo ❌ Este script debe ejecutarse como Administrador
    echo    Haz clic derecho en el archivo y selecciona "Ejecutar como administrador"
    pause
    exit /b 1
)

echo.

REM Crear sitio web en IIS
echo Creando sitio web en IIS...
powershell -Command "
try {
    Import-Module WebAdministration

    # Crear pool de aplicaciones
    if (!(Test-Path IIS:\AppPools\cdd_app)) {
        New-Item IIS:\AppPools\cdd_app
        Set-ItemProperty IIS:\AppPools\cdd_app -name processModel.identityType -value 2
        Set-ItemProperty IIS:\AppPools\cdd_app -name recycling.periodicRestart.time -value '00:00:00'
        Set-ItemProperty IIS:\AppPools\cdd_app -name recycling.periodicRestart.requests -value 10000
        Write-Host 'Pool de aplicaciones creado exitosamente'
    }

    # Crear sitio web
    if (!(Test-Path IIS:\Sites\cdd_app)) {
        New-Website -Name 'cdd_app' -PhysicalPath '%~dp0' -Port 80 -ApplicationPool 'cdd_app' -Force
        Write-Host 'Sitio web creado exitosamente'
    }

    # Configurar permisos NTFS
    $acl = Get-Acl '%~dp0'
    $rule = New-Object System.Security.AccessControl.FileSystemAccessRule('IIS_IUSRS', 'FullControl', 'ContainerInherit,ObjectInherit', 'None', 'Allow')
    $acl.SetAccessRule($rule)
    Set-Acl '%~dp0' $acl

    # Configurar permisos específicos para directorios críticos
    $storagePath = '%~dp0storage'
    $cachePath = '%~dp0bootstrap\cache'

    if (Test-Path $storagePath) {
        $acl = Get-Acl $storagePath
        $rule = New-Object System.Security.AccessControl.FileSystemAccessRule('IIS_IUSRS', 'FullControl', 'ContainerInherit,ObjectInherit', 'None', 'Allow')
        $acl.SetAccessRule($rule)
        Set-Acl $storagePath $acl
    }

    if (Test-Path $cachePath) {
        $acl = Get-Acl $cachePath
        $rule = New-Object System.Security.AccessControl.FileSystemAccessRule('IIS_IUSRS', 'FullControl', 'ContainerInherit,ObjectInherit', 'None', 'Allow')
        $acl.SetAccessRule($rule)
        Set-Acl $cachePath $acl
    }

    Write-Host 'Permisos NTFS configurados exitosamente'
    Write-Host 'Configuración de IIS completada'
} catch {
    Write-Host 'Error durante la configuración: ' $_.Exception.Message
    exit 1
}
"

if %errorLevel% == 0 (
    echo.
    echo ✅ Configuración de IIS completada exitosamente
    echo.
    echo El sitio web está disponible en:
    echo - Local: http://localhost/cdd_app
    echo - Red: http://%computername%/cdd_app
    echo.
    echo Para acceder desde otras máquinas, asegúrate de que:
    echo 1. El firewall de Windows permita conexiones al puerto 80
    echo 2. La configuración de red permita el acceso
    echo.
) else (
    echo.
    echo ❌ Error durante la configuración de IIS
    echo.
    echo Soluciones posibles:
    echo 1. Verificar que IIS esté instalado
    echo 2. Verificar que PHP esté configurado en IIS
    echo 3. Ejecutar como Administrador
    echo.
)

echo ========================================
echo  Configuración completada
echo ========================================
echo.
pause