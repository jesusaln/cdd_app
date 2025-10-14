#!/bin/bash

# =====================================================
# SETUP COMPLETO VPS - UN SOLO COMANDO
# =====================================================

echo "🚀 INICIANDO SETUP COMPLETO DE VPS - UN SOLO COMANDO"
echo "=================================================="

# Función para colores
green() { echo -e "\033[32m$1\033[0m"; }
blue() { echo -e "\033[34m$1\033[0m"; }
red() { echo -e "\033[31m$1\033[0m"; }

# =====================================================
# 1. ACTUALIZAR SISTEMA
# =====================================================

green "1/8 - Actualizando sistema..."
apt update && apt upgrade -y
apt install -y curl wget git htop iotop ufw software-properties-common

# =====================================================
# 2. INSTALAR NGINX
# =====================================================

green "2/8 - Instalando Nginx..."
apt install -y nginx
systemctl enable nginx
systemctl start nginx

# =====================================================
# 3. INSTALAR PHP 8.2
# =====================================================

green "3/8 - Instalando PHP 8.2..."
apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-pgsql php8.2-redis
apt install -y php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-intl

# =====================================================
# 4. INSTALAR POSTGRESQL
# =====================================================

green "4/8 - Instalando PostgreSQL..."
apt install -y postgresql postgresql-contrib

# Crear usuario y base de datos
sudo -u postgres psql -c "CREATE USER cdd_user WITH PASSWORD 'Contpaqi1.';"
sudo -u postgres psql -c "CREATE DATABASE cdd_app_prod OWNER cdd_user;"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE cdd_app_prod TO cdd_user;"

# =====================================================
# 5. INSTALAR REDIS
# =====================================================

green "5/8 - Instalando Redis..."
apt install -y redis-server
systemctl enable redis-server
systemctl start redis-server

# =====================================================
# 6. INSTALAR NODE.JS Y COMPOSER
# =====================================================

green "6/8 - Instalando Node.js y Composer..."

# Node.js 18.x
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# =====================================================
# 7. CONFIGURAR FIREWALL
# =====================================================

green "7/8 - Configurando firewall..."
ufw allow 'OpenSSH'
ufw allow 'Nginx Full'
ufw --force enable

# =====================================================
# 8. CREAR USUARIO Y PERMISOS
# =====================================================

green "8/8 - Configurando usuario y permisos..."
useradd -m -s /bin/bash cdd_app
usermod -aG www-data cdd_app

# Crear directorio de aplicación
mkdir -p /var/www/cdd_app
chown cdd_app:www-data /var/www/cdd_app

# =====================================================
# CONFIGURACIÓN COMPLETA
# =====================================================

green "✅ SETUP COMPLETO!"
blue ""
blue "=== RESUMEN DE INSTALACIÓN ==="
blue "🌐 Nginx: Instalado y corriendo"
blue "🐘 PHP 8.2: Instalado con extensiones"
blue "🐘 PostgreSQL: Instalado y configurado"
blue "🔴 Redis: Instalado y corriendo"
blue "🟢 Node.js: Instalado"
blue "🎼 Composer: Instalado"
blue "🔥 Firewall: Configurado"
blue ""
blue "=== PRÓXIMOS PASOS ==="
blue "1. Subir archivos de aplicación a /var/www/cdd_app"
blue "2. Configurar .env con datos de producción"
blue "3. Ejecutar: composer install --no-dev"
blue "4. Ejecutar: php artisan migrate --force"
blue "5. Configurar Nginx: cp /etc/nginx/sites-available/default /etc/nginx/sites-available/cdd_app"
blue ""
blue "=== ACCESO ==="
blue "📍 IP del servidor: $(hostname -I | awk '{print $1}')"
blue "🌐 Acceso web: http://$(hostname -I | awk '{print $1}')"

# Información adicional
echo ""
green "=== COMANDOS ÚTILES ==="
echo "Ver estado de servicios:"
echo "  systemctl status nginx postgresql redis-server"
echo ""
echo "Ver logs:"
echo "  tail -f /var/log/nginx/access.log"
echo "  tail -f /var/log/postgresql/postgresql-*.log"
echo ""
echo "Reiniciar servicios:"
echo "  systemctl restart nginx php8.2-fpm postgresql redis-server"
echo ""
green "✅ VPS CONFIGURADO Y LISTO PARA PRODUCCIÓN!"