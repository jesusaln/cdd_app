#!/bin/bash

echo "🔍 DEBUGGING DE PostgreSQL PDO - CDD APP"
echo "========================================="
echo "Fecha: $(date)"
echo ""

# Función para verificar errores
check_error() {
    if [ $? -ne 0 ]; then
        echo "❌ ERROR: $1"
        exit 1
    fi
}

echo "📋 PASO 1: Verificar si pg_config existe..."
which pg_config && pg_config --version || echo "pg_config no encontrado"
check_error "verificación de pg_config"

echo ""
echo "📋 PASO 2: Verificar headers de PostgreSQL..."
find /usr -name "libpq-fe.h" 2>/dev/null | head -5 || echo "libpq-fe.h no encontrado"
check_error "búsqueda de headers"

echo ""
echo "📋 PASO 3: Verificar bibliotecas de PostgreSQL..."
find /usr -name "libpq.so*" 2>/dev/null | head -5 || echo "libpq.so no encontrado"
check_error "búsqueda de bibliotecas"

echo ""
echo "📋 PASO 4: Verificar pkg-config para PostgreSQL..."
pkg-config --exists libpq && echo "✅ libpq encontrado por pkg-config" || echo "❌ libpq no encontrado por pkg-config"
check_error "verificación pkg-config"

echo ""
echo "📋 PASO 5: Información detallada de PostgreSQL..."
echo "PKG_CONFIG_PATH: $PKG_CONFIG_PATH"
echo "LD_LIBRARY_PATH: $LD_LIBRARY_PATH"
pkg-config --cflags --libs libpq 2>/dev/null || echo "No se pudo obtener flags de libpq"

echo ""
echo "📋 PASO 6: Probar instalación manual de extensión PDO PostgreSQL..."
echo "Creando script de instalación manual..."

cat > /tmp/install_pdo_pgsql.sh << 'EOF'
#!/bin/bash
echo "Instalación manual de PDO PostgreSQL..."

# Variables
PHP_EXT_DIR="/usr/local/lib/php/extensions/no-debug-non-zts-20210902"
PHP_CONFIG="/usr/local/bin/php-config"
PHP_INI_DIR="/usr/local/etc/php"

echo "1. Verificando dependencias..."
apt-get update
apt-get install -y postgresql-server-dev-all pkg-config

echo "2. Verificando pg_config..."
which pg_config
pg_config --libdir
pg_config --includedir

echo "3. Configurando extensión PDO PostgreSQL..."
cd /tmp
wget -O php-8.1-pdo-pgsql.tar.gz https://github.com/php/php-src/archive/refs/heads/PHP-8.1.tar.gz
tar -xzf php-8.1-pdo-pgsql.tar.gz
cd php-src-PHP-8.1/ext/pdo_pgsql

echo "4. Configurando extensión..."
/usr/local/bin/phpize
./configure --with-php-config=/usr/local/bin/php-config --with-pdo-pgsql=/usr/bin/pg_config
make
make install

echo "5. Habilitando extensión..."
echo "extension=pdo_pgsql.so" > /usr/local/etc/php/conf.d/pdo_pgsql.ini

echo "6. Verificando instalación..."
php -m | grep pdo_pgsql
EOF

chmod +x /tmp/install_pdo_pgsql.sh
echo "Script creado en /tmp/install_pdo_pgsql.sh"

echo ""
echo "📋 PASO 7: Información del sistema..."
echo "Sistema operativo: $(uname -a)"
echo "PHP Version: $(php -v | head -1)"
echo "PostgreSQL Client Version: $(psql --version 2>/dev/null || echo 'psql no disponible')"

echo ""
echo "✅ DEBUGGING COMPLETADO"
echo ""
echo "📋 PRÓXIMOS PASOS:"
echo "1. Ejecutar el script manual si es necesario:"
echo "   /tmp/install_pdo_pgsql.sh"
echo ""
echo "2. Si pg_config no existe, instalar:"
echo "   apt-get install -y postgresql-server-dev-all"
echo ""
echo "3. Construir la imagen con el Dockerfile actualizado:"
echo "   docker build --no-cache -t cdd_app_debug ."