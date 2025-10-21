@echo off
chcp 65001 >nul
echo ========================================
echo  VERIFICACIÓN DE ESTADO PARA PRODUCCIÓN
echo ========================================
echo.

echo Verificando archivos críticos para producción...
echo.

REM Verificar archivos críticos
set "critical_files=web.config .env composer.json artisan public\index.php"

for %%f in (%critical_files%) do (
    if exist "%%f" (
        echo ✅ %%f existe
    ) else (
        echo ❌ %%f NO existe
    )
)

echo.
echo ========================================
echo  VERIFICACIÓN DE DIRECTORIOS
echo ========================================

set "critical_dirs=storage bootstrap\cache public vendor"

for %%d in (%critical_dirs%) do (
    if exist "%%d" (
        echo ✅ %%d existe
    ) else (
        echo ❌ %%d NO existe
    )
)

echo.
echo ========================================
echo  VERIFICACIÓN DE ARCHIVO WEB.CONFIG
echo ========================================

if exist "web.config" (
    echo ✅ web.config existe
    echo.
    echo Contenido actual:
    echo ----------------------------------------
    type web.config
    echo ----------------------------------------
    echo.

    REM Verificar configuración PHP
    findstr "C:\php84\php-cgi.exe" web.config >nul 2>&1
    if errorlevel 1 (
        echo ❌ web.config no apunta a PHP correcto
        echo    Debería apuntar a: C:\php84\php-cgi.exe
    ) else (
        echo ✅ web.config apunta a PHP correcto
    )

) else (
    echo ❌ No existe web.config
)

echo.
echo ========================================
echo  VERIFICACIÓN DE ARCHIVO .ENV
echo ========================================

if exist ".env" (
    echo ✅ .env existe

    REM Verificar configuración crítica
    findstr "APP_KEY" .env >nul 2>&1
    if errorlevel 1 (
        echo ❌ .env no tiene APP_KEY
    ) else (
        echo ✅ .env tiene APP_KEY
    )

    findstr "APP_ENV" .env >nul 2>&1
    if errorlevel 1 (
        echo ❌ .env no tiene APP_ENV configurado
    ) else (
        findstr "APP_ENV=production" .env >nul 2>&1
        if errorlevel 1 (
            echo ⚠️ APP_ENV no está configurado como production
        ) else (
            echo ✅ APP_ENV configurado como production
        )
    )

) else (
    echo ❌ No existe .env
    if exist ".env.example" (
        echo    ✅ .env.example existe - copiar a .env
    ) else (
        echo    ❌ No existe .env ni .env.example
    )
)

echo.
echo ========================================
echo  VERIFICACIÓN DE DEPENDENCIAS
echo ========================================

if exist "vendor\autoload.php" (
    echo ✅ Composer dependencies instaladas
) else (
    echo ❌ Composer dependencies NO instaladas
    echo    Ejecutar: composer install
)

if exist "node_modules" (
    echo ✅ NPM dependencies instaladas
) else (
    echo ❌ NPM dependencies NO instaladas
    echo    Ejecutar: npm install
)

echo.
echo ========================================
echo  VERIFICACIÓN DE PHP
echo ========================================

php --version >nul 2>&1
if errorlevel 1 (
    echo ❌ PHP no está disponible en PATH
    echo    Verificar instalación de PHP
) else (
    echo ✅ PHP disponible
    php --version
)

echo.
echo ========================================
echo  RESUMEN DE CONFIGURACIÓN
echo ========================================
echo.

echo Estado actual para producción:
echo.

REM Crear recomendaciones específicas
if not exist "web.config" (
    echo 🔴 CRÍTICO: Crear web.config
    echo    Solución: Usar configuración mínima garantizada
)

if not exist ".env" (
    echo 🔴 CRÍTICO: Crear archivo .env
    echo    Solución: Copiar .env.example a .env
)

if not exist "vendor\autoload.php" (
    echo 🔴 CRÍTICO: Instalar dependencias Composer
    echo    Solución: composer install
)

if not exist "node_modules" (
    echo ⚠️ ADVERTENCIA: Instalar dependencias NPM
    echo    Solución: npm install (para assets)
)

echo.
echo ========================================
echo  PASOS PARA COMPLETAR PRODUCCIÓN
echo ========================================
echo.
echo 1. Crear archivo .env desde .env.example
echo 2. Configurar .env para producción
echo 3. Ejecutar: php artisan key:generate
echo 4. Ejecutar: composer install --optimize-autoloader
echo 5. Ejecutar: php artisan config:cache
echo 6. Ejecutar: php artisan route:cache
echo 7. Ejecutar: php artisan view:cache
echo 8. Ejecutar: npm run production (si usas assets)
echo 9. Configurar sitio en IIS apuntando a: %~dp0
echo 10. Configurar permisos NTFS correctamente
echo 11. Reiniciar IIS: iisreset
echo.

echo ========================================
echo  ARCHIVOS DE AYUDA
echo ========================================
echo.
echo - setup_iis.bat (configuración IIS inicial)
echo - start_production.bat (inicio en producción)
echo - diagnose_production.bat (diagnóstico completo)
echo.

pause