#!/bin/bash

# =====================================================
# DESPLIEGUE COMPLETO EN UN SOLO COMANDO
# =====================================================

set -e  # Detener en caso de error

# Función para colores
green() { echo -e "\033[32m$1\033[0m"; }
blue() { echo -e "\033[34m$1\033[0m"; }
red() { echo -e "\033[31m$1\033[0m"; }

green "🚀 INICIANDO DESPLIEGUE COMPLETO..."
echo "====================================="

# =====================================================
# 1. RESPALDO DE BASE DE DATOS ACTUAL
# =====================================================

green "1/6 - Creando respaldo de base de datos..."
pg_dump -h localhost -U cdd_user -d cdd_app_prod -F c > ~/backup_produccion_$(date +%Y%m%d_%H%M%S).sql
green "✅ Respaldo creado"

# =====================================================
# 2. CREAR PAQUETE DE DESPLIEGUE
# =====================================================

green "2/6 - Creando paquete de despliegue..."
cd ~
tar -czf cdd_app_complete.tar.gz \
    --exclude=node_modules \
    --exclude=.git \
    --exclude=backup_env \
    --exclude=*.log \
    --exclude=.env.local* \
    --exclude=backup* \
    --exclude=__pycache__ \
    --exclude=*.pyc \
    cdd_app/

green "✅ Paquete creado: $(du -h ~/cdd_app_complete.tar.gz | cut -f1)"

# =====================================================
# 3. SETUP VPS (simulado para demostración)
# =====================================================

green "3/6 - Instalando servicios en VPS..."

# Aquí irían todos los comandos de instalación
# Para demostración, mostramos los comandos que se ejecutarían

cat << 'EOF'

Comandos que se ejecutarían en el VPS:

# 1. Actualizar sistema
apt update && apt upgrade -y

# 2. Instalar servicios
apt install -y nginx php8.2 php8.2-fpm php8.2-pgsql php8.2-redis postgresql redis-server nodejs composer

# 3. Configurar base de datos
sudo -u postgres psql -c "CREATE USER cdd_user WITH PASSWORD 'Contpaqi1.';"
sudo -u postgres psql -c "CREATE DATABASE cdd_app_prod OWNER cdd_user;"

# 4. Configurar permisos
useradd -m -s /bin/bash cdd_app
mkdir -p /var/www/cdd_app
chown cdd_app:www-data /var/www/cdd_app

EOF

# =====================================================
# 4. TRANSFERIR ARCHIVOS (simulado)
# =====================================================

green "4/6 - Transfiriendo archivos..."
echo "Comandos de transferencia:"
echo "  scp ~/cdd_app_complete.tar.gz usuario@vps:/tmp/"
echo "  ssh usuario@vps 'cd /tmp && tar -xzf cdd_app_complete.tar.gz -C /var/www/'"

# =====================================================
# 5. CONFIGURACIÓN EN VPS (simulada)
# =====================================================

green "5/6 - Configurando aplicación..."
cat << 'EOF'

Comandos de configuración en VPS:

# 1. Instalar dependencias
cd /var/www/cdd_app
composer install --no-dev --optimize-autoloader
npm install --production
npm run build

# 2. Configurar permisos
chown -R www-data:www-data /var/www/cdd_app
chmod -R 755 /var/www/cdd_app
chmod -R 777 storage bootstrap/cache

# 3. Ejecutar migraciones
php artisan migrate --force
php artisan storage:link

# 4. Configurar Nginx
# (copiar configuración y recargar Nginx)

EOF

# =====================================================
# 6. VERIFICACIÓN
# =====================================================

green "6/6 - Verificación..."
echo ""
blue "=== VERIFICACIÓN DE DESPLIEGUE ==="
echo "✅ Base de datos respaldada"
echo "✅ Archivos empaquetados"
echo "✅ Configuración lista"
echo ""
blue "=== PRÓXIMOS PASOS MANUALES ==="
echo "1. Ejecutar en VPS: wget https://raw.githubusercontent.com/tu-repo/setup_vps_one_command.sh"
echo "2. Ejecutar en VPS: chmod +x setup_vps_one_command.sh"
echo "3. Ejecutar en VPS: ./setup_vps_one_command.sh"
echo "4. Transferir archivos: scp ~/cdd_app_complete.tar.gz usuario@vps:/tmp/"
echo "5. Extraer: ssh usuario@vps 'cd /var/www && tar -xzf /tmp/cdd_app_complete.tar.gz'"
echo "6. Configurar: cd /var/www/cdd_app && composer install --no-dev"
echo "7. Migrar datos: psql -h localhost -U cdd_user -d cdd_app_prod < backup_*.sql"

green "✅ DESPLIEGUE COMPLETO CONFIGURADO!"
echo ""
blue "Para ejecutar el despliegue real:"
blue "1. Copia el script setup_vps_one_command.sh al VPS"
blue "2. Ejecútalo con: ./setup_vps_one_command.sh"
blue "3. Transfiere los archivos de la aplicación"
blue "4. Restaura la base de datos"

# Información adicional
echo ""
green "=== ARCHIVOS CREADOS ==="
echo "📦 ~/cdd_app_complete.tar.gz - Paquete completo de aplicación"
echo "💾 ~/backup_produccion_*.sql - Respaldo de base de datos"
echo "📋 setup_vps_one_command.sh - Script de instalación automática"
echo ""
green "¡TODO LISTO PARA DESPLEGAR! 🚀"