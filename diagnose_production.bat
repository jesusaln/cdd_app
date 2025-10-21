@echo off
chcp 65001 >nul
echo ========================================
echo  DIAGNÓSTICO COMPLETO PARA PRODUCCIÓN
echo ========================================
echo.
echo Analizando configuración para Windows Server 2019 + IIS + PHP
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
echo  PASO 1: VERIFICAR INSTALACIÓN IIS
echo ========================================

echo Verificando características de IIS instaladas...
powershell -Command "
Import-Module ServerManager;

Write-Host '=== CARACTERÍSTICAS IIS INSTALADAS ===';
Get-WindowsFeature -Name *IIS* | Where-Object {$_.InstallState -eq 'Installed'} | Format-Table -Property Name, InstallState;

Write-Host '=== CARACTERÍSTICAS DE DESARROLLO ===';
Get-WindowsFeature -Name *CGI*, *ISAPI*, *ASP*, *NET* | Where-Object {$_.InstallState -eq 'Installed'} | Format-Table -Property Name, InstallState;

Write-Host '=== VERIFICANDO SERVICIOS IIS ===';
Get-Service -Name W3SVC, WAS, WMSVC | Format-Table -Property Name, Status, StartType;
"

echo.
echo ========================================
echo  PASO 2: VERIFICAR INSTALACIÓN PHP
echo ========================================

echo Verificando instalación de PHP...
if exist "C:\php84\php.exe" (
    echo ✅ PHP encontrado en C:\php84\php.exe
    "C:\php84\php.exe" --version
) else (
    echo ❌ PHP no encontrado en C:\php84\
    echo    Descargar de: https://windows.php.net/download/
    echo    Archivo: php-8.4.x-nts-Win32-vs17-x64.zip
)

if exist "C:\php84\php-cgi.exe" (
    echo ✅ PHP-CGI encontrado
) else (
    echo ❌ PHP-CGI no encontrado
)

echo.
echo ========================================
echo  PASO 3: VERIFICAR CONFIGURACIÓN IIS
echo ========================================

echo Verificando configuración de IIS...
powershell -Command "
Import-Module WebAdministration;

Write-Host '=== SITIOS WEB ===';
try {
    Get-IISSite | Format-Table -Property Name, State, PhysicalPath, Bindings
} catch {
    Write-Host 'Error obteniendo sitios:' $_.Exception.Message
}

Write-Host '=== GRUPOS DE APLICACIONES ===';
try {
    Get-IISAppPool | Format-Table -Property Name, State, ProcessModel
} catch {
    Write-Host 'Error obteniendo pools:' $_.Exception.Message
}

Write-Host '=== CONTROLADORES REGISTRADOS ===';
try {
    Get-WebHandler -PSPath 'MACHINE/WEBROOT/APPHOST' | Format-Table -Property Name, Path, Modules, ScriptProcessor
} catch {
    Write-Host 'Error obteniendo handlers:' $_.Exception.Message
}
"

echo.
echo ========================================
echo  PASO 4: VERIFICAR PERMISOS NTFS
echo ========================================

echo Verificando permisos del directorio raíz...
icacls "%~dp0" | findstr /C:"NETWORKSERVICE" /C:"IIS_IUSRS" /C:"SYSTEM" /C:"Administradores"

if errorlevel 1 (
    echo ⚠️ Permisos podrían no estar configurados correctamente
) else (
    echo ✅ Permisos verificados
)

echo.
echo Verificando permisos de directorios críticos...
if exist "%~dp0storage" (
    echo Permisos de storage:
    icacls "%~dp0storage" | findstr /C:"NETWORKSERVICE" /C:"IIS_IUSRS"
) else (
    echo ⚠️ Directorio storage no existe
)

if exist "%~dp0bootstrap\cache" (
    echo Permisos de bootstrap\cache:
    icacls "%~dp0bootstrap\cache" | findstr /C:"NETWORKSERVICE" /C:"IIS_IUSRS"
) else (
    echo ⚠️ Directorio bootstrap\cache no existe
)

echo.
echo ========================================
echo  PASO 5: VERIFICAR ARCHIVO WEB.CONFIG
echo ========================================

if exist "web.config" (
    echo ✅ web.config existe
    echo.
    echo Contenido actual de web.config:
    echo ----------------------------------------
    type web.config
    echo ----------------------------------------
    echo.

    REM Verificar elementos críticos
    findstr "C:\php84\php-cgi.exe" web.config >nul 2>&1
    if errorlevel 1 (
        echo ❌ web.config no apunta a PHP correcto
    ) else (
        echo ✅ web.config apunta a PHP correcto
    )

) else (
    echo ❌ No existe web.config
)

echo.
echo ========================================
echo  PASO 6: VERIFICAR DEPENDENCIAS PHP
echo ========================================

echo Verificando extensiones PHP críticas...
"C:\php84\php.exe" -m | findstr /C:"pdo_mysql" /C:"mbstring" /C:"tokenizer" /C:"xml" /C:"ctype" /C:"json" /C:"bcmath" >nul 2>&1

if errorlevel 1 (
    echo ⚠️ Algunas extensiones PHP críticas podrían faltar
    echo    Extensiones necesarias para Laravel:
    echo    - pdo_mysql, mbstring, tokenizer, xml, ctype, json, bcmath
) else (
    echo ✅ Extensiones PHP críticas verificadas
)

echo.
echo ========================================
echo  PASO 7: VERIFICAR ARCHIVO .ENV
echo ========================================

if exist ".env" (
    echo ✅ Archivo .env existe
    findstr "APP_KEY" .env >nul 2>&1
    if errorlevel 1 (
        echo ⚠️ Archivo .env no tiene APP_KEY configurada
        echo    Ejecutar: php artisan key:generate
    ) else (
        echo ✅ Archivo .env tiene APP_KEY configurada
    )

    findstr "APP_ENV=production" .env >nul 2>&1
    if errorlevel 1 (
        echo ⚠️ APP_ENV no está configurado como production
    ) else (
        echo ✅ APP_ENV configurado como production
    )

) else (
    echo ❌ Archivo .env no existe
    if exist ".env.example" (
        echo    Copiar .env.example a .env y configurar
    ) else (
        echo    Crear archivo .env con configuración de producción
    )
)

echo.
echo ========================================
echo  PASO 8: VERIFICAR DIRECTORIOS NECESARIOS
echo ========================================

echo Verificando estructura de directorios...

set "dirs=storage storage\logs storage\app storage\framework bootstrap\cache public"

for %%d in (%dirs%) do (
    if exist "%%d" (
        echo ✅ %%d existe
    ) else (
        echo ❌ %%d no existe
        mkdir "%%d" 2>nul
        if exist "%%d" (
            echo    ✅ %%d creado
        ) else (
            echo    ❌ No se pudo crear %%d
        )
    )
)

echo.
echo ========================================
echo  PASO 9: VERIFICAR ARCHIVOS CRÍTICOS
echo ========================================

echo Verificando archivos críticos de Laravel...

set "files=artisan composer.json public\index.php"

for %%f in (%files%) do (
    if exist "%%f" (
        echo ✅ %%f existe
    ) else (
        echo ❌ %%f no existe
    )
)

echo.
echo ========================================
echo  PASO 10: VERIFICAR CONFIGURACIÓN DE RED
echo ========================================

echo Información de red y puertos...
netstat -an | findstr /C:":80 " /C:":443" >nul 2>&1
if errorlevel 1 (
    echo ⚠️ Puerto 80 podría no estar disponible
) else (
    echo ✅ Puerto 80 disponible
)

echo.
echo ========================================
echo  DIAGNÓSTICO COMPLETADO
echo ========================================
echo.
echo Resumen del diagnóstico:
echo.

REM Crear recomendaciones basadas en los resultados
echo RECOMENDACIONES:
echo.

if not exist "C:\php84\php.exe" (
    echo 🔴 CRÍTICO: Instalar PHP 8.4 para Windows
    echo    Descargar: https://windows.php.net/download/
)

if not exist "web.config" (
    echo 🔴 CRÍTICO: Crear archivo web.config
    echo    Usar configuración mínima garantizada
)

if not exist ".env" (
    echo 🔴 CRÍTICO: Crear archivo .env
    echo    Copiar .env.example a .env y configurar
)

echo.
echo PASOS PARA COMPLETAR CONFIGURACIÓN:
echo.
echo 1. Instalar PHP si es necesario
echo 2. Configurar sitio en IIS apuntando a: %~dp0
echo 3. Configurar permisos NTFS correctamente
echo 4. Crear/editar archivo .env
echo 5. Ejecutar: php artisan key:generate
echo 6. Ejecutar: php artisan config:cache
echo 7. Reiniciar IIS: iisreset
echo 8. Probar: http://localhost/
echo.

echo ========================================
echo  ARCHIVOS DE AYUDA DISPONIBLES
echo ========================================
echo.
echo - setup_iis.bat (configuración inicial IIS)
echo - start_production.bat (inicio en producción)
echo - diagnose_production.bat (este archivo)
echo.

pause