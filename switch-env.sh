#!/bin/bash

# Script para cambiar entre diferentes configuraciones de entorno
# Uso: ./switch-env.sh [lan|zt|local]

case "$1" in
    "lan")
        echo "Cambiando a configuración LAN (192.168.1.x)"
        cp .env.lan .env
        echo "✅ Configuración LAN activada"
        echo "📍 Laravel: http://192.168.1.106:8000"
        echo "🎯 Vite: http://192.168.1.106:5173"
        ;;
    "zt")
        echo "Cambiando a configuración ZeroTier (192.168.191.x)"
        cp .env.zt .env
        echo "✅ Configuración ZeroTier activada"
        echo "📍 Laravel: http://192.168.191.226:8000"
        echo "🎯 Vite: http://192.168.191.226:5173"
        ;;
    "local")
        echo "Cambiando a configuración local"
        cp .env.local .env 2>/dev/null || echo "Archivo .env.local no encontrado, usando configuración por defecto"
        echo "✅ Configuración local activada"
        echo "📍 Laravel: http://localhost:8000"
        echo "🎯 Vite: http://localhost:5173"
        ;;
    *)
        echo "Uso: $0 {lan|zt|local}"
        echo ""
        echo "Ejemplos:"
        echo "  $0 lan    # Para máquinas en red 192.168.1.x"
        echo "  $0 zt     # Para máquinas remotas/ZeroTier"
        echo "  $0 local  # Para desarrollo local"
        echo ""
        echo "Después de cambiar, ejecuta:"
        echo "  php artisan optimize:clear"
        echo "  npm run dev"
        exit 1
        ;;
esac

echo ""
echo "🚀 Próximos pasos:"
echo "1. php artisan optimize:clear"
echo "2. npm run dev"
echo "3. Accede desde tu navegador"