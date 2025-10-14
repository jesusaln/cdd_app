# Guía de Producción - Migración a VPS Ubuntu

## Situación Actual
- **Entorno**: Windows local con PostgreSQL
- **Objetivo**: Migrar a VPS Ubuntu con misma base de datos
- **Datos**: Miles de registros en crecimiento

## Estrategia de Migración de Datos

### 1. Respaldo de Base de Datos
```bash
# Crear respaldo completo de PostgreSQL
pg_dump -h localhost -U cdd_user -d cdd_app_prod -F c > backup_produccion_$(date +%Y%m%d_%H%M%S).sql

# O usando Laravel
php artisan schema:dump
php artisan backup:run --only-db
```

### 2. Migración a VPS Ubuntu
```bash
# En el VPS Ubuntu:
# 1. Instalar PostgreSQL
sudo apt update
sudo apt install postgresql postgresql-contrib

# 2. Crear usuario y base de datos
sudo -u postgres psql
CREATE USER cdd_user WITH PASSWORD 'Contpaqi1.';
CREATE DATABASE cdd_app_prod OWNER cdd_user;
\q

# 3. Restaurar datos
psql -h localhost -U cdd_user -d cdd_app_prod < backup_produccion_YYYYMMDD_HHMMSS.sql
```

## Configuración para Miles de Registros

### Optimizaciones de Base de Datos
```sql
-- Crear índices para mejorar rendimiento
CREATE INDEX idx_clientes_created_at ON clientes (created_at DESC);
CREATE INDEX idx_productos_stock ON productos (stock);
CREATE INDEX idx_ventas_fecha ON ventas (fecha DESC);
CREATE INDEX idx_pedidos_estado ON pedidos (estado);

-- Configurar mantenimiento automático
ALTER TABLE clientes SET (autovacuum_vacuum_scale_factor = 0.1);
ALTER TABLE productos SET (autovacuum_vacuum_scale_factor = 0.1);
ALTER TABLE ventas SET (autovacuum_vacuum_scale_factor = 0.1);
```

### Configuración Laravel para Producción
```php
// config/database.php - Configuración PostgreSQL optimizada
'pgsql' => [
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', 5432),
    'database' => env('DB_DATABASE', 'cdd_app_prod'),
    'username' => env('DB_USERNAME', 'cdd_user'),
    'password' => env('DB_PASSWORD', 'Contpaqi1.'),
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
    'sslmode' => 'prefer', // Para conexiones seguras
    // Optimizaciones para producción
    'options' => [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_TIMEOUT => 30,
    ],
],
```

## Configuración VPS Ubuntu

### 1. Archivo .env para Producción
```bash
cp .env .env.produccion
# Editar .env.produccion con:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=cdd_app_prod
DB_USERNAME=cdd_user
DB_PASSWORD=Contpaqi1.

# Deshabilitar servicios innecesarios
QUEUE_CONNECTION=database
CACHE_STORE=redis
SESSION_DRIVER=redis
BROADCAST_CONNECTION=redis
```

### 2. Servidor Web Nginx (Recomendado)
```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/cdd_app/public;

    # Logs
    access_log /var/log/nginx/cdd_app_access.log;
    error_log /var/log/nginx/cdd_app_error.log;

    # PHP
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_read_timeout 300;
    }

    # Assets estáticos
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Archivos sensibles
    location ~ /\.ht {
        deny all;
    }
}
```

### 3. Redis para Cache y Sesiones
```bash
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

## Monitoreo y Mantenimiento

### 1. Logs de Laravel
```bash
# Rotación automática de logs
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error  # Solo errores en producción
```

### 2. Backup Automático
```bash
# Script de respaldo diario
#!/bin/bash
pg_dump -h localhost -U cdd_user -d cdd_app_prod > /backup/cdd_app_$(date +%Y%m%d).sql
php artisan backup:run --only-db
```

### 3. Monitoreo de Recursos
```bash
# Instalar herramientas de monitoreo
sudo apt install htop iotop nethogs

# Configurar límites de PHP
# /etc/php/8.2/fpm/pool.d/www.conf
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
```

## Seguridad en Producción

### 1. SSL/HTTPS
```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d tu-dominio.com

# Auto renovación
sudo crontab -e
0 12 * * * /usr/bin/certbot renew --quiet
```

### 2. Firewall
```bash
sudo ufw allow 'OpenSSH'
sudo ufw allow 'Nginx Full'
sudo ufw --force enable
```

### 3. Usuarios y Permisos
```bash
# Crear usuario para la aplicación
sudo adduser cdd_app --disabled-password --gecos ""
sudo usermod -aG www-data cdd_app

# Permisos correctos
sudo chown -R cdd_app:www-data /var/www/cdd_app
sudo chmod -R 755 /var/www/cdd_app
sudo chmod -R 777 /var/www/cdd_app/storage
sudo chmod -R 777 /var/www/cdd_app/bootstrap/cache
```

## Rendimiento con Miles de Registros

### Optimizaciones Implementadas
- ✅ Índices estratégicos en tablas principales
- ✅ Configuración autovacuum optimizada
- ✅ Redis para cache y sesiones
- ✅ Compresión de respuestas
- ✅ Lazy loading deshabilitado donde no se necesita

### Monitoreo de Rendimiento
```bash
# Comandos para monitorear
htop                    # Uso de CPU y memoria
iotop                   # Uso de disco
df -h                   # Espacio disponible
free -h                 # Memoria disponible
```

## Checklist Pre-Migración

### Antes de Migrar al VPS
- [ ] Crear respaldo completo de base de datos
- [ ] Probar restauración en ambiente de pruebas
- [ ] Configurar variables de entorno de producción
- [ ] Probar aplicación completa en ambiente local
- [ ] Documentar procedimientos de respaldo
- [ ] Configurar monitoreo básico

### Después de Migrar al VPS
- [ ] Verificar que todos los servicios estén corriendo
- [ ] Probar acceso desde diferentes ubicaciones
- [ ] Verificar funcionamiento de todas las funcionalidades
- [ ] Configurar respaldos automáticos
- [ ] Probar restauración de respaldos
- [ ] Configurar SSL
- [ ] Optimizar configuración según recursos del VPS

## Recursos Recomendados para VPS

### VPS Mínimos Recomendados
- **CPU**: 2 cores
- **RAM**: 4 GB
- **Almacenamiento**: 50 GB SSD
- **Red**: 100 Mbps

### Proveedores Recomendados
- DigitalOcean (Droplets)
- Linode (Linodes)
- Vultr (Cloud Compute)
- Hetzner Cloud

## Soporte y Mantenimiento

### Comandos de Emergencia
```bash
# Reiniciar servicios
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart postgresql
sudo systemctl restart redis

# Verificar estado
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status postgresql
sudo systemctl status redis
```

### Contactos de Soporte
- **Base de datos**: PostgreSQL logs en `/var/log/postgresql/`
- **Aplicación**: Laravel logs en `storage/logs/`
- **Servidor web**: Nginx logs en `/var/log/nginx/`