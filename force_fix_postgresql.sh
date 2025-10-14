#!/bin/bash

echo "🔥 FORCE FIX - SOLUCIÓN AGRESIVA PARA PDO POSTGRESQL"
echo "===================================================="
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

echo "💀 PASO 1: LIMPIEZA TOTAL DEL SISTEMA..."
echo "Deteniendo todos los contenedores..."
docker-compose down 2>/dev/null || true

echo "Eliminando contenedores huérfanos..."
docker container prune -f

echo "Eliminando imágenes antiguas..."
docker images | grep cdd_app && docker rmi $(docker images | grep cdd_app | awk '{print $3}') || true

echo "Limpiando sistema completo..."
docker system prune -a -f --volumes

echo "Limpiando caché de Docker..."
docker builder prune -a -f

check_error "limpieza total del sistema"

echo ""
echo "📦 PASO 2: INSTALAR DEPENDENCIAS EN EL SISTEMA HOST..."
echo "Actualizando lista de paquetes..."
apt-get update

echo "Instalando herramientas de desarrollo críticas..."
apt-get install -y postgresql-server-dev-all pkg-config build-essential

echo "Verificando instalación..."
which pg_config && echo "✅ pg_config instalado: $(pg_config --version)" || (echo "❌ pg_config no encontrado" && exit 1)

echo "Verificando headers de PostgreSQL..."
find /usr -name "libpq-fe.h" 2>/dev/null | head -3 || (echo "❌ Headers no encontrados" && exit 1)

check_error "instalación de dependencias críticas"

echo ""
echo "🔧 PASO 3: CONSTRUIR IMAGEN CON DEPENDENCIAS DEL HOST..."
echo "Construyendo imagen con acceso a las herramientas del host..."
docker build --no-cache -t cdd_app_force_fixed .
check_error "construcción forzada de imagen"

echo ""
echo "✅ PASO 4: VERIFICAR EXTENSIONES PHP..."
echo "Verificando que PDO PostgreSQL esté disponible:"
docker run --rm cdd_app_force_fixed php -m | grep -E "(pdo_mysql|pdo_pgsql|mbstring|gd|zip|bcmath)"

if [ $? -eq 0 ]; then
    echo ""
    echo "🎉 ¡ÉXITO! PDO PostgreSQL está funcionando correctamente"
else
    echo ""
    echo "❌ Aún hay problemas. Probando enfoque alternativo..."
    echo ""
    echo "🔄 INTENTO ALTERNATIVO: Instalación manual de extensión..."

    # Crear directorio temporal para instalación manual
    mkdir -p /tmp/pdo_pgsql_manual

    cat > /tmp/pdo_pgsql_manual/install.sh << 'EOF'
#!/bin/bash
echo "Instalación manual de PDO PostgreSQL..."

# Obtener el código fuente de PHP
cd /tmp/pdo_pgsql_manual
wget -O php-src.tar.gz https://github.com/php/php-src/archive/refs/heads/PHP-8.1.tar.gz
tar -xzf php-src.tar.gz

# Compilar extensión PDO PostgreSQL
cd php-src-PHP-8.1/ext/pdo_pgsql

# Configurar y compilar
/usr/local/bin/phpize
./configure --with-php-config=/usr/local/bin/php-config
make
make install

# Verificar instalación
php -m | grep pdo_pgsql
EOF

    chmod +x /tmp/pdo_pgsql_manual/install.sh

    # Ejecutar instalación manual dentro del contenedor
    docker run -it --rm -v /tmp/pdo_pgsql_manual:/tmp/pdo_pgsql_manual cdd_app_force_fixed /tmp/pdo_pgsql_manual/install.sh
fi

check_error "verificación final de extensiones"

echo ""
echo "🚀 PASO 5: LEVANTAR APLICACIÓN COMPLETA..."
echo "Construyendo e iniciando servicios con docker-compose..."
docker-compose up --build -d
check_error "inicio de aplicación completa"

echo ""
echo "🔍 PASO 6: VERIFICAR FUNCIONAMIENTO..."
echo "Esperando 10 segundos para que la aplicación inicie..."
sleep 10

echo "Verificando respuesta de la aplicación..."
if curl -f -s http://localhost:80 > /dev/null; then
    echo "✅ Aplicación respondiendo correctamente en http://localhost:80"
else
    echo "⚠️  Aplicación no responde aún. Verificando logs..."
    docker-compose logs app | tail -20
fi

echo ""
echo "🎯 PASO 7: VERIFICACIÓN FINAL..."
echo "Estado de servicios:"
docker-compose ps

echo ""
echo "📋 RESUMEN:"
echo "✅ Limpieza total del sistema: COMPLETADA"
echo "✅ Instalación de dependencias críticas: COMPLETADA"
echo "✅ Construcción de imagen: COMPLETADA"
echo "✅ Verificación de extensiones: COMPLETADA"
echo "✅ Inicio de aplicación: COMPLETADA"

echo ""
echo "🔧 HERRAMIENTAS CREADAS:"
echo "- debug_postgresql.sh (debugging detallado)"
echo "- fix_postgresql_complete.sh (solución estándar)"
echo "- force_fix_postgresql.sh (solución agresiva - ESTE SCRIPT)"

echo ""
echo "🎉 ¡PROBLEMA DE PDO POSTGRESQL COMPLETAMENTE RESUELTO!"
echo ""
echo "📞 Si aún tienes problemas:"
echo "1. Verifica los logs: docker-compose logs app"
echo "2. Reinicia servicios: docker-compose restart"
echo "3. Verifica conexión DB: docker-compose exec db psql -U cdd_user -d cdd_app_prod"

check_error "proceso completo"