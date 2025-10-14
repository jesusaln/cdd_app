#!/bin/bash

# =====================================================
# SETUP GITHUB + DESPLIEGUE AUTOMÁTICO
# =====================================================

echo "🚀 SETUP GITHUB PARA DESPLIEGUE AUTOMÁTICO"
echo "=========================================="

# Función para colores
green() { echo -e "\033[32m$1\033[0m"; }
blue() { echo -e "\033[34m$1\033[0m"; }
red() { echo -e "\033[31m$1\033[0m"; }

# =====================================================
# 1. PREPARAR PROYECTO PARA GITHUB
# =====================================================

green "1/5 - Preparando proyecto para GitHub..."

# Crear .gitignore específico para Laravel + Docker
cat > .gitignore << 'EOF'
/node_modules
/vendor
/storage/app/*
/storage/framework/*
/storage/logs/*
/bootstrap/cache/*
/.env
/.env.local
/.env.remote
/.env.network
/.env.zerotier
/.env.hybrid.backup
.env.docker
backup_env/
*.log
.DS_Store
Thumbs.db
.vscode/
.idea/
*.zip
backup_*.sql
EOF

echo "✅ .gitignore actualizado"

# Crear README para GitHub
cat > README_GITHUB.md << 'EOF'
# CDD App - Sistema de Gestión

Aplicación Laravel para gestión de climas del desierto.

## Despliegue Automático

Esta aplicación se despliega automáticamente usando Docker.

### Servicios incluidos:
- Laravel (PHP 8.1)
- PostgreSQL (base de datos)
- Redis (cache)
- Nginx (servidor web)

### Despliegue en VPS:
```bash
git clone https://github.com/TU-USUARIO/cdd-app.git
cd cdd-app
chmod +x deploy_github.sh
./deploy_github.sh
```

## Características
- Gestión de clientes
- Control de inventario
- Sistema de ventas
- Reportes avanzados
- Optimizado para miles de registros
EOF

echo "✅ README_GITHUB.md creado"

# =====================================================
# 2. CREAR SCRIPTS DE DESPLIEGUE
# =====================================================

green "2/5 - Creando scripts de despliegue..."

# Crear script de instalación automática para VPS
cat > install_from_github.sh << 'EOF'
#!/bin/bash
# INSTALACIÓN AUTOMÁTICA DESDE GITHUB

echo "🐳 INSTALANDO DESDE GITHUB..."

# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh && sudo sh get-docker.sh

# Instalar Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose && sudo chmod +x /usr/local/bin/docker-compose

# Clonar repositorio (cambiar TU-USUARIO por tu usuario de GitHub)
git clone https://github.com/TU-USUARIO/cdd-app.git /var/www/cdd_app

# Desplegar aplicación
cd /var/www/cdd_app
chmod +x deploy_github.sh
./deploy_github.sh

echo "✅ APLICACIÓN DESPLEGADA!"
EOF

echo "✅ Script install_from_github.sh creado"

# =====================================================
# 3. CREAR ARCHIVO .env EJEMPLO
# =====================================================

green "3/5 - Creando archivo .env de ejemplo..."

cat > .env.example << 'EOF'
APP_NAME="Climas del Desierto"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=cdd_app_prod
DB_USERNAME=cdd_user
DB_PASSWORD=

BROADCAST_CONNECTION=redis
CACHE_STORE=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
VITE_APP_NAME="${APP_NAME}"
EOF

echo "✅ .env.example creado"

# =====================================================
# 4. PREPARAR ARCHIVOS DOCKER
# =====================================================

green "4/5 - Preparando archivos Docker..."

# Crear Dockerfile simplificado
cat > Dockerfile.simple << 'EOF'
FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip nodejs npm postgresql-client \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN useradd -G www-data,root -u 1000 -d /home/cdd_app cdd_app
RUN mkdir -p /home/cdd_app/.composer && chown -R cdd_app:cdd_app /home/cdd_app

WORKDIR /var/www/cdd_app

# Copiar archivos de aplicación
COPY . .

# Establecer permisos
RUN chown -R cdd_app:www-data /var/www/cdd_app && \
    chmod -R 755 /var/www/cdd_app && \
    chmod -R 777 /var/www/cdd_app/storage && \
    chmod -R 777 /var/www/cdd_app/bootstrap/cache

# Instalar dependencias
USER cdd_app
RUN composer install --no-dev --optimize-autoloader
RUN npm install --production && npm run build

EXPOSE 8000
CMD php artisan serve --host=0.0.0.0 --port=8000
EOF

echo "✅ Dockerfile.simple creado"

# =====================================================
# 5. CREAR ARCHIVO DE EXPORTACIÓN
# =====================================================

green "5/5 - Creando archivo de exportación..."

# Crear script para exportar proyecto completo
cat > export_for_github.sh << 'EOF'
#!/bin/bash
# EXPORTAR PROYECTO PARA GITHUB

echo "📦 EXPORTANDO PROYECTO PARA GITHUB..."

# Crear paquete con solo archivos necesarios
tar -czf cdd_app_github.tar.gz \
    --exclude=node_modules \
    --exclude=vendor \
    --exclude=storage/app/* \
    --exclude=storage/framework/* \
    --exclude=storage/logs/* \
    --exclude=bootstrap/cache/* \
    --exclude=.git \
    --exclude=*.log \
    --exclude=.env* \
    --exclude=backup* \
    --exclude=__pycache__ \
    --exclude=*.pyc \
    --exclude=.vscode \
    --exclude=backup_env \
    .

echo "✅ Paquete creado: cdd_app_github.tar.gz"
echo ""
echo "SUBIR A GITHUB:"
echo "1. Crear repositorio en GitHub"
echo "2. Subir archivos: cdd_app_github.tar.gz"
echo "3. Extraer en el repositorio"
echo "4. Hacer commit y push"
echo ""
echo "DESPLEGAR EN VPS:"
echo "git clone https://github.com/TU-USUARIO/cdd-app.git"
echo "cd cdd-app"
echo "./deploy_github.sh"
EOF

chmod +x export_for_github.sh

echo "✅ Script export_for_github.sh creado"

# =====================================================
# RESULTADO FINAL
# =====================================================

green "✅ SETUP GITHUB COMPLETADO!"

echo ""
blue "=== ARCHIVOS CREADOS ==="
echo "📋 .gitignore - Archivos a ignorar"
echo "📖 README_GITHUB.md - Documentación para GitHub"
echo "🚀 install_from_github.sh - Instalación automática"
echo "⚙️ .env.example - Configuración de ejemplo"
echo "🐳 Dockerfile.simple - Imagen Docker simplificada"
echo "📦 export_for_github.sh - Script de exportación"

echo ""
green "=== PRÓXIMOS PASOS ==="
echo "1. Crear repositorio en GitHub"
echo "2. Ejecutar: ./export_for_github.sh"
echo "3. Subir cdd_app_github.tar.gz a GitHub"
echo "4. En VPS: git clone https://github.com/TU-USUARIO/cdd-app.git"
echo "5. En VPS: cd cdd-app && ./deploy_github.sh"

echo ""
blue "=== VENTAJAS DE ESTE ENFOQUE ==="
echo "✅ Despliegue ultra-rápido"
echo "✅ Sin transferencia manual de archivos"
echo "✅ Version control completo"
echo "✅ Fácil mantenimiento"
echo "✅ Rollback fácil"
echo "✅ Colaboración sencilla"

echo ""
green "🎯 COMANDO PARA EXPORTAR:"
echo "Ejecuta: ./export_for_github.sh"
echo "Luego sube cdd_app_github.tar.gz a GitHub"

echo ""
green "¡LISTO PARA DESPLEGAR VIA GITHUB! 🚀"