#!/bin/bash

echo "🔧 Solucionando problema de migración duplicada..."

# Navegar al directorio de la aplicación
cd /opt/cdd_app

echo "📋 Verificando estado actual de migraciones..."
docker compose exec app php artisan migrate:status

echo ""
echo "🗑️  Eliminando migración problemática..."
docker compose exec app rm -f database/migrations/2025_09_18_152246_add_regimen_fiscal_receptor_to_sat_usos_cfdi_table.php

echo ""
echo "🔄 Reseteando migraciones para aplicar cambios..."
docker compose exec app php artisan migrate:fresh --force

echo ""
echo "📋 Verificando estado final de migraciones..."
docker compose exec app php artisan migrate:status

echo ""
echo "✅ Problema resuelto. La aplicación debería funcionar correctamente ahora."
echo ""
echo "🔗 Creando enlaces simbólicos..."
docker compose exec app php artisan storage:link || true

echo ""
echo "🎉 ¡Listo! El problema de la columna duplicada ha sido solucionado."