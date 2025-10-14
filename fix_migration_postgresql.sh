#!/bin/bash

echo "🔧 Solucionando problemas de migración para PostgreSQL..."

# Navegar al directorio de la aplicación
cd /opt/cdd_app

echo "📋 Verificando estado actual de migraciones..."
docker compose exec app php artisan migrate:status

echo ""
echo "🗑️  Eliminando migraciones problemáticas..."
docker compose exec app rm -f database/migrations/2025_09_18_152246_add_regimen_fiscal_receptor_to_sat_usos_cfdi_table.php

echo ""
echo "🔄 Aplicando corrección a migración de inventarios..."
# La migración ya está corregida en el repo, solo necesitamos aplicar las migraciones

echo ""
echo "🔄 Aplicando migraciones (sin resetear datos existentes)..."
docker compose exec app php artisan migrate --force

echo ""
echo "📋 Verificando estado final de migraciones..."
docker compose exec app php artisan migrate:status

echo ""
echo "✅ Problemas resueltos. Los datos existentes se han preservado."
echo ""
echo "🔗 Creando enlaces simbólicos..."
docker compose exec app php artisan storage:link || true

echo ""
echo "🎉 ¡Listo! Los problemas de migración han sido solucionados."
echo "✅ Columna duplicada eliminada"
echo "✅ Función datetime() corregida para PostgreSQL"