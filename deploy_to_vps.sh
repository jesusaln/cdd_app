#!/bin/bash

# Script de despliegue para VPS Ubuntu
# Uso: ./deploy_to_vps.sh usuario@servidor.com

if [ -z "$1" ]; then
    echo "Uso: $0 usuario@servidor.com"
    exit 1
fi

VPS_SERVER="$1"
PROJECT_NAME="cdd_app"
REMOTE_PATH="/var/www/$PROJECT_NAME"

echo "=== DESPLIEGUE A VPS: $VPS_SERVER ==="
echo "Fecha: $(date)"

# Función para ejecutar comandos en remoto
run_remote() {
    ssh $VPS_SERVER "$1"
}

# Función para logging
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1"
}

log "Verificando conexión con VPS..."
ssh -o ConnectTimeout=10 $VPS_SERVER "echo 'Conexión exitosa'" || {
    log "❌ Error: No se puede conectar al VPS"
    exit 1
}

log "✅ Conexión establecida"

# =====================================================
# PREPARACIÓN DEL VPS
# =====================================================

log "Instalando dependencias en VPS..."

run_remote "
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar servicios básicos
sudo apt install -y curl wget git htop iotop ufw

# Instalar Nginx
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx

# Instalar PHP 8.2 y extensiones
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-pgsql php8.2-redis
sudo apt install -y php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar Node.js y npm
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Instalar PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Instalar Redis
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Crear usuario para la aplicación
sudo adduser $PROJECT_NAME --disabled-password --gecos ''
sudo usermod -aG www-data $PROJECT_NAME

# Crear directorio de la aplicación
sudo mkdir -p $REMOTE_PATH
sudo chown $PROJECT_NAME:www-data $REMOTE_PATH
"

# =====================================================
# CONFIGURACIÓN DE BASE DE DATOS
# =====================================================

log "Configurando PostgreSQL..."

run_remote "
# Crear usuario y base de datos
sudo -u postgres psql -c \"CREATE USER cdd_user WITH PASSWORD 'Contpaqi1.';\"
sudo -u postgres psql -c \"CREATE DATABASE cdd_app_prod OWNER cdd_user;\"
sudo -u postgres psql -c \"GRANT ALL PRIVILEGES ON DATABASE cdd_app_prod TO cdd_user;\"

# Configurar acceso remoto (si es necesario)
echo 'host cdd_app_prod cdd_user 0.0.0.0/0 md5' | sudo tee -a /etc/postgresql/16/main/pg_hba.conf
sudo systemctl restart postgresql
"

# =====================================================
# SUBIR ARCHIVOS DE LA APLICACIÓN
# =====================================================

log "Subiendo archivos de la aplicación..."

# Crear archivo tar de la aplicación (excluyendo archivos innecesarios)
tar -czf ~/cdd_app_deploy.tar.gz \
    --exclude=node_modules \
    --exclude=vendor \
    --exclude=storage/app/* \
    --exclude=storage/logs/* \
    --exclude=.git \
    --exclude=backup_env \
    --exclude=*.log \
    --exclude=.env.local* \
    --exclude=backup* \
    .

# Subir al VPS
scp ~/cdd_app_deploy.tar.gz $VPS_SERVER:~/

# Extraer en el VPS
run_remote "
cd ~
tar -xzf cdd_app_deploy.tar.gz
sudo rm -rf $REMOTE_PATH/*
sudo cp -r cdd_app/* $REMOTE_PATH/
sudo cp cdd_app/.env* $REMOTE_PATH/
sudo chown -R $PROJECT_NAME:www-data $REMOTE_PATH
sudo chmod -R 755 $REMOTE_PATH
sudo chmod -R 777 $REMOTE_PATH/storage
sudo chmod -R 777 $REMOTE_PATH/bootstrap/cache

# Instalar dependencias PHP
cd $REMOTE_PATH
composer install --no-dev --optimize-autoloader

# Instalar dependencias Node.js
npm ci --production

# Compilar assets
npm run build

# Ejecutar migraciones
php artisan migrate --force

# Crear enlace simbólico de storage
php artisan storage:link

# Configurar permisos finales
sudo chown -R www-data:www-data $REMOTE_PATH/storage
sudo chown -R www-data:www-data $REMOTE_PATH/bootstrap/cache
"

# =====================================================
# CONFIGURACIÓN DE NGINX
# =====================================================

log "Configurando Nginx..."

# Crear configuración de Nginx
cat > ~/nginx_cdd_app.conf << 'EOF'
server {
    listen 80;
    server_name _;
    root /var/www/cdd_app/public;

    # Logs
    access_log /var/log/nginx/cdd_app_access.log;
    error_log /var/log/nginx/cdd_app_error.log;

    # Configuración de seguridad
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # PHP
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_read_timeout 300;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Assets estáticos con cache
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Archivos sensibles
    location ~ /\.ht {
        deny all;
    }

    # Protección contra ataques comunes
    location ~* \.(engine|inc|info|install|make|module|profile|test|po|sh|.*sql|theme|tpl(\.php)?|xtmpl)$|^(\..*| Entries.*|Repository|Root|Tag|Template)$ {
        deny all;
    }
}
EOF

# Subir configuración de Nginx
scp ~/nginx_cdd_app.conf $VPS_SERVER:~/

# Aplicar configuración en VPS
run_remote "
sudo mv ~/nginx_cdd_app.conf /etc/nginx/sites-available/cdd_app
sudo ln -sf /etc/nginx/sites-available/cdd_app /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
"

# =====================================================
# CONFIGURACIÓN DE FIREWALL
# =====================================================

log "Configurando firewall..."

run_remote "
sudo ufw allow 'OpenSSH'
sudo ufw allow 'Nginx Full'
sudo ufw --force enable
sudo ufw status
"

# =====================================================
# CONFIGURACIÓN SSL (OPCIONAL)
# =====================================================

log "¿Deseas configurar SSL con Let's Encrypt? (s/n)"
read -r configurar_ssl

if [[ $configurar_ssl =~ ^[Ss]$ ]]; then
    log "Instalando Certbot para SSL..."

    run_remote "
    sudo apt install -y certbot python3-certbot-nginx
    sudo certbot --nginx -d \$1 2>/dev/null || echo 'Configurar dominio manualmente'
    "
fi

# =====================================================
# LIMPIEZA Y VERIFICACIÓN
# =====================================================

log "Limpiando archivos temporales..."
run_remote "
rm -f ~/cdd_app_deploy.tar.gz
rm -rf ~/cdd_app
"

# Verificación final
log "=== VERIFICACIÓN FINAL ==="

run_remote "
echo '=== Estado de servicios ==='
sudo systemctl status nginx --no-pager -l
sudo systemctl status php8.2-fpm --no-pager -l
sudo systemctl status postgresql --no-pager -l
sudo systemctl status redis-server --no-pager -l

echo ''
echo '=== Verificación de aplicación ==='
cd $REMOTE_PATH
php artisan about | head -20

echo ''
echo '=== Permisos de archivos ==='
ls -la $REMOTE_PATH/.env
ls -la $REMOTE_PATH/storage/
ls -la $REMOTE_PATH/bootstrap/cache/
"

log "=== DESPLIEGUE COMPLETADO ==="
log "🌐 Aplicación disponible en: http://$(echo $VPS_SERVER | cut -d'@' -f2)"
log "📁 Ruta de instalación: $REMOTE_PATH"
log "✅ Servicios configurados: Nginx, PHP 8.2, PostgreSQL, Redis"

# =====================================================
# RECORDATORIOS IMPORTANTES
# =====================================================

echo ""
echo "=== RECORDATORIOS IMPORTANTES ==="
echo "1. 🔒 Cambiar contraseñas por defecto en producción"
echo "2. 📧 Configurar SMTP para envío de correos"
echo "3. 🔐 Configurar SSL con: sudo certbot --nginx -d TU-DOMINIO.com"
echo "4. 📊 Configurar monitoreo y logs"
echo "5. ⏰ Configurar respaldos automáticos con backup_produccion.sh"
echo "6. 🎯 Optimizar base de datos con optimize_database.sql"
echo ""
echo "=== PRÓXIMOS PASOS ==="
echo "1. Probar la aplicación: http://$(echo $VPS_SERVER | cut -d'@' -f2)"
echo "2. Configurar dominio personalizado"
echo "3. Ejecutar optimizaciones de base de datos"
echo "4. Configurar monitoreo"
echo ""
echo "¡Despliegue completado exitosamente! 🚀"