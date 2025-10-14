#!/bin/bash

echo "🚀 SOLUCIÓN COMPLETA PARA PDO POSTGRESQL - CDD APP"
echo "=================================================="
echo "Fecha: $(date)"
echo ""

# Función para verificar errores
check_error() {
    if [ $? -ne 0 ]; then
        echo "❌ ERROR en el paso: $1"
        echo "El proceso se detuvo. Revisa los errores arriba."
        exit 1
    fi
}

echo "🔍 PASO 1: Ejecutar debugging completo..."
chmod +x debug_postgresql.sh
./debug_postgresql.sh
check_error "debugging inicial"

echo ""
echo "📦 PASO 2: Instalar dependencias faltantes si es necesario..."
echo "Verificando si necesitamos instalar postgresql-server-dev-all..."
if ! which pg_config >/dev/null 2>&1; then
    echo "Instalando postgresql-server-dev-all..."
    apt-get update
    apt-get install -y postgresql-server-dev-all
    check_error "instalación de postgresql-server-dev-all"
else
    echo "✅ pg_config ya está disponible"
fi

echo ""
echo "🔧 PASO 3: Construir imagen con Dockerfile corregido..."
echo "Comando: docker build --no-cache -t cdd_app_fixed ."
docker build --no-cache -t cdd_app_fixed .
check_error "construcción de imagen"

echo ""
echo "✅ PASO 4: Verificar que PDO PostgreSQL esté disponible..."
echo "Ejecutar contenedor temporal para verificar extensiones:"
docker run --rm cdd_app_fixed php -m | grep -E "(pdo_mysql|pdo_pgsql|mbstring|gd|zip|bcmath)"
check_error "verificación de extensiones PHP"

echo ""
echo "🔍 PASO 5: Verificación adicional con php -i..."
echo "Verificando configuración detallada de PDO PostgreSQL:"
docker run --rm cdd_app_fixed php -i | grep -A 5 -B 5 "pdo_pgsql"
check_error "verificación detallada de PDO PostgreSQL"

echo ""
echo "🎉 ¡PROBLEMA DE PDO POSTGRESQL RESUELTO!"
echo ""
echo "📋 RESUMEN DE CAMBIOS APLICADOS:"
echo "✅ Agregado postgresql-server-dev-all al Dockerfile"
echo "✅ Mejorado orden de instalación de dependencias"
echo "✅ Agregada limpieza de caché de apt"
echo "✅ Incluidas herramientas de desarrollo necesarias"
echo ""
echo "🚀 PRÓXIMOS PASOS:"
echo "1. Levantar servicios completos:"
echo "   docker-compose up --build -d"
echo ""
echo "2. Verificar que la aplicación funciona:"
echo "   curl http://localhost:80"
echo ""
echo "3. Si todo está bien, limpiar imágenes de prueba:"
echo "   docker rmi cdd_app_fixed"
echo ""
echo "✅ El problema de 'Cannot find libpq-fe.h' debería estar completamente resuelto ahora."
echo ""
echo "🔧 SCRIPTS CREADOS PARA FUTURO:"
echo "- build_step_by_step.sh (construcción paso a paso)"
echo "- debug_postgresql.sh (debugging específico)"
echo "- fix_postgresql_complete.sh (solución completa)"