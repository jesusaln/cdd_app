#!/bin/bash

echo "=== CONSTRUCCIÓN PASO A PASO DE CDD APP ==="
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

echo "🚀 PASO 1: Limpiar cachés anteriores..."
docker system prune -f
docker builder prune -f
check_error "limpieza de cachés"

echo ""
echo "🔧 PASO 2: Construir la imagen con el Dockerfile mejorado..."
echo "Comando: docker build --no-cache -t cdd_app_fixed ."
docker build --no-cache -t cdd_app_fixed .
check_error "construcción de imagen"

echo ""
echo "✅ PASO 3: Verificar que la imagen se construyó correctamente..."
docker images | grep cdd_app_fixed
check_error "verificación de imagen"

echo ""
echo "🔍 PASO 4: Verificar que las extensiones PHP están instaladas..."
echo "Ejecutar contenedor temporal para verificar extensiones:"
docker run --rm cdd_app_fixed php -m | grep -E "(pdo_mysql|pdo_pgsql|mbstring|gd|zip|bcmath)"
check_error "verificación de extensiones PHP"

echo ""
echo "🎉 ¡CONSTRUCCIÓN COMPLETADA EXITOSAMENTE!"
echo ""
echo "📋 PRÓXIMOS PASOS:"
echo "1. Probar el contenedor completo con docker-compose:"
echo "   docker-compose up --build -d"
echo ""
echo "2. Verificar que la aplicación funciona correctamente"
echo "3. Si todo está bien, puedes eliminar la imagen de prueba:"
echo "   docker rmi cdd_app_fixed"
echo ""
echo "✅ El problema de PDO PostgreSQL debería estar resuelto ahora."