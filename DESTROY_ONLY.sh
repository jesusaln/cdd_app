#!/bin/bash

echo "💀 DESTRUCCIÓN COMPLETA - SOLO ELIMINAR (VPS)"
echo "============================================"
echo "Fecha: $(date)"
echo ""
echo "⚠️  ATENCIÓN: Esto eliminará TODOS los contenedores, imágenes y volúmenes"
echo "   relacionados con Docker en tu VPS."
echo ""
read -p "¿Estás seguro de que quieres continuar? (yes/no): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    echo "Operación cancelada."
    exit 1
fi

echo ""
echo "🔥 EJECUTANDO DESTRUCCIÓN TOTAL EN VPS..."
echo "======================================="

echo ""
echo "1. Deteniendo servicios de CDD App..."
docker-compose down -v 2>/dev/null || true

echo "2. Deteniendo TODOS los contenedores..."
docker container stop $(docker container ls -aq) 2>/dev/null || true

echo "3. Eliminando todos los contenedores..."
docker container prune -f

echo "4. Eliminando imágenes de CDD App..."
docker images | grep cdd_app | awk '{print $3}' | xargs docker rmi -f 2>/dev/null || true

echo "5. Eliminando imágenes huérfanas..."
docker image prune -a -f

echo "6. Eliminando todos los volúmenes..."
docker volume prune -f

echo "7. Eliminando redes personalizadas..."
docker network prune -f

echo "8. Limpiando sistema completo..."
docker system prune -a -f --volumes

echo "9. Limpiando caché de construcción..."
docker builder prune -a -f

echo ""
echo "📊 VERIFICACIÓN DE DESTRUCCIÓN:"
echo "=============================="
echo "Contenedores activos:"
docker ps -a

echo ""
echo "Imágenes disponibles:"
docker images

echo ""
echo "Volúmenes:"
docker volume ls

echo ""
echo "Espacio liberado:"
df -h /

echo ""
echo "✅ DESTRUCCIÓN COMPLETA FINALIZADA"
echo ""
echo "💡 El VPS está completamente limpio."
echo "   Puedes empezar desde cero con un nuevo proyecto."
echo ""
echo "📅 Destrucción completada: $(date)"

echo ""
echo "🗑️  LO QUE SE ELIMINÓ:"
echo "   ✅ Todos los contenedores Docker"
echo "   ✅ Todas las imágenes relacionadas con cdd_app"
echo "   ✅ Todos los volúmenes de datos"
echo "   ✅ Todas las redes personalizadas"
echo "   ✅ Todos los cachés de construcción"
echo "   ✅ Datos de PostgreSQL (si estaban en volúmenes nombrados)"

echo ""
echo "🚀 PRÓXIMOS PASOS POSIBLES:"
echo "   - Crear nuevo proyecto: docker-compose up --build"
echo "   - Restaurar desde backup si es necesario"
echo "   - Verificar que el VPS esté completamente limpio"