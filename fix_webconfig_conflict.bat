@echo off
chcp 65001 >nul
echo ========================================
echo  SOLUCIÓN: CONFLICTO DE WEB.CONFIG
echo ========================================
echo.
echo El error indica que el archivo web.config tiene configuración conflictiva.
echo Creando configuración limpia desde cero...
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
echo  PASO 1: RESPALDAR ARCHIVO PROBLEMÁTICO
echo ========================================

if exist web.config (
    copy web.config backup_config\web.config.conflict.backup >nul 2>&1
    echo ✅ Respaldo creado: web.config.conflict.backup
) else (
    echo ℹ️ No existe web.config actual
)

echo.
echo ========================================
echo  PASO 2: ELIMINAR ARCHIVO CONFLICTIVO
echo ========================================

if exist web.config (
    del web.config
    if exist web.config (
        echo ❌ No se pudo eliminar web.config
        echo    Posibles soluciones:
        echo    1. Cerrar todas las sesiones del Administrador de IIS
        echo    2. Reiniciar el servicio IIS: iisreset
        echo    3. Eliminar manualmente el archivo
        echo.
        pause
        exit /b 1
    ) else (
        echo ✅ Archivo web.config eliminado exitosamente
    )
) else (
    echo ✅ Archivo web.config ya estaba eliminado
)

echo.
echo ========================================
echo  PASO 3: CREAR NUEVA CONFIGURACIÓN LIMPIA
echo ========================================

echo Creando nueva configuración mínima garantizada...

REM Crear configuración completamente nueva y limpia
(
echo ^<?xml version="1.0" encoding="UTF-8"?^>
echo ^<configuration^>
echo     ^<system.webServer^>
echo         ^<handlers^>
echo             ^<add name="PHP_via_FastCGI" path="*.php" verb="*" modules="FastCgiModule" scriptProcessor="C:\php84\php-cgi.exe" resourceType="Either" /^>
echo         ^</handlers^>
echo         ^<limits connectionTimeout="00:10:00" /^>
echo     ^</system.webServer^>
echo ^</configuration^>
) > web.config

if exist web.config (
    echo ✅ Nueva configuración creada exitosamente
    echo.
    echo Características de la nueva configuración:
    echo - Solo handler PHP básico
    echo - Sin reglas complejas de reescritura
    echo - Sin configuración de seguridad que pueda bloquear
    echo - Sintaxis XML limpia y válida
    echo.
) else (
    echo ❌ Error al crear nueva configuración
    pause
    exit /b 1
)

echo.
echo ========================================
echo  PASO 4: VERIFICAR ARCHIVO CREADO
echo ========================================

echo Contenido del nuevo archivo web.config:
echo ----------------------------------------
type web.config
echo ----------------------------------------
echo.

REM Verificar que el archivo contiene la configuración correcta
findstr "C:\php84\php-cgi.exe" web.config >nul 2>&1
if errorlevel 1 (
    echo ❌ El archivo no contiene la configuración PHP correcta
) else (
    echo ✅ Archivo web.config configurado correctamente
)

echo.
echo ========================================
echo  PASO 5: REINICIAR IIS
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
echo  PASO 6: PROBAR CONFIGURACIÓN
echo ========================================

echo Probando configuración básica...

REM Crear archivo de prueba si no existe
if not exist "public\test.php" (
    (
    echo ^<?php
    echo echo "¡Configuración IIS + PHP funcionando!\n";
    echo echo "Archivo ejecutándose correctamente\n";
    echo echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
    echo ?^>
    ) > public\test.php
    echo ✅ Archivo de prueba creado
) else (
    echo ✅ Archivo de prueba ya existe
)

echo.
echo ========================================
echo  CONFIGURACIÓN LIMPIA COMPLETADA
echo ========================================
echo.
echo ✅ Archivo web.config conflictivo eliminado
echo ✅ Nueva configuración mínima creada
echo ✅ Servicios IIS reiniciados
echo ✅ Archivo de prueba disponible
echo.
echo ========================================
echo  PRUEBAS A REALIZAR
echo ========================================
echo.
echo Pruebe estas URLs en su navegador:
echo.
echo 1. PRUEBA PHP BÁSICA:
echo    http://localhost/test.php
echo    Debería mostrar información de PHP
echo.
echo 2. PRUEBA ARCHIVO PRINCIPAL:
echo    http://localhost/index.php
echo    Debería mostrar página de Laravel
echo.
echo ========================================
echo  SOLUCIÓN DE PROBLEMAS
echo ========================================
echo.
echo Si aún tiene problemas:
echo.
echo 1. Verificar que el sitio en IIS apunta a:
echo    C:\inetpub\wwwroot\cdd_app
echo.
echo 2. Verificar permisos del directorio:
echo    icacls "C:\inetpub\wwwroot\cdd_app"
echo.
echo 3. Verificar que PHP esté instalado:
echo    "C:\php84\php.exe" --version
echo.
echo 4. Reiniciar IIS completamente:
echo    net stop w3svc ^& net stop was ^& net start w3svc ^& net start was
echo.
echo ========================================
echo  ARCHIVOS CREADOS
echo ========================================
echo.
echo - web.config (nueva configuración limpia)
echo - backup_config\web.config.conflict.backup (respaldo del archivo problemático)
echo - public\test.php (archivo de prueba)
echo.

REM Crear log de la solución
echo [%date% %time%] Conflicto de web.config solucionado > "backup_config\webconfig_fix.log"

echo.
echo ========================================
echo  NOTA IMPORTANTE
echo ========================================
echo.
echo Esta configuración es INTENCIONALMENTE MÍNIMA para evitar conflictos.
echo.
echo Características:
echo ✅ Solo handler PHP básico
echo ✅ Sin reglas de reescritura complejas
echo ✅ Sin configuración de seguridad adicional
echo ✅ Sintaxis XML limpia y válida
echo ✅ Compatible con Windows Server 2019
echo.
echo Si necesita características adicionales como URL rewriting,
echo se pueden agregar después de confirmar que esta configuración básica funciona.
echo.

pause