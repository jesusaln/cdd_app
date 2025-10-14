#!/bin/bash

echo "🔧 INSTALACIÓN DE DEPENDENCIAS CRÍTICAS ANTES DE DOCKER"
echo "======================================================="
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

echo "📦 PASO 1: ACTUALIZAR SISTEMA..."
apt-get update
check_error "actualización del sistema"

echo ""
echo "📦 PASO 2: INSTALAR HERRAMIENTAS DE DESARROLLO..."
echo "Instalando herramientas básicas de desarrollo..."
apt-get install -y build-essential pkg-config autoconf automake libtool
check_error "instalación de herramientas básicas"

echo ""
echo "📦 PASO 3: INSTALAR DEPENDENCIAS DE POSTGRESQL..."
echo "Instalando postgresql-server-dev-all (contiene pg_config y headers)..."
apt-get install -y postgresql-server-dev-all
check_error "instalación de PostgreSQL development"

echo ""
echo "📦 PASO 4: INSTALAR DEPENDENCIAS DE PHP..."
echo "Instalando dependencias necesarias para extensiones PHP..."
apt-get install -y libpq-dev libpng-dev libonig-dev libxml2-dev libzip-dev
check_error "instalación de dependencias PHP"

echo ""
echo "✅ PASO 5: VERIFICAR INSTALACIÓN..."
echo "Verificando pg_config..."
which pg_config && echo "✅ pg_config encontrado: $(pg_config --version)" || (echo "❌ pg_config no encontrado" && exit 1)

echo ""
echo "Verificando headers de PostgreSQL..."
find /usr -name "libpq-fe.h" 2>/dev/null | head -3 || (echo "❌ Headers no encontrados" && exit 1)

echo ""
echo "Verificando bibliotecas de PostgreSQL..."
find /usr -name "libpq.so*" 2>/dev/null | head -3 || (echo "❌ Bibliotecas no encontradas" && exit 1)

echo ""
echo "📦 PASO 6: INSTALAR DEPENDENCIAS ADICIONALES DEL DOCKERFILE..."
echo "Instalando git, curl, nodejs, npm..."
apt-get install -y git curl nodejs npm postgresql-client
check_error "instalación de dependencias adicionales"

echo ""
echo "🎯 VERIFICACIÓN FINAL..."
echo "Sistema operativo: $(uname -a)"
echo "PostgreSQL Dev: $(pg_config --version 2>/dev/null || echo 'No disponible')"
echo "Herramientas de desarrollo: $(gcc --version | head -1)"
echo "Node.js: $(node --version)"
echo "NPM: $(npm --version)"

echo ""
echo "✅ ¡DEPENDENCIAS INSTALADAS CORRECTAMENTE!"
echo ""
echo "🚀 PRÓXIMOS PASOS:"
echo "Ahora puedes construir la imagen Docker:"
echo "  docker build --no-cache -t cdd_app_ready ."
echo ""
echo "O usar el comando completo:"
echo "  docker-compose down && docker system prune -f && docker build --no-cache -t cdd_app_ready . && docker-compose up --build -d"
echo ""
echo "💡 NOTA: Las dependencias ya están instaladas en el sistema host,"
echo "       por lo que Docker debería encontrar pg_config correctamente."

check_error "proceso completo de instalación"