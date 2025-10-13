# 🚀 Despliegue en Portainer - Guía Completa (2024)

Este documento proporciona instrucciones detalladas para desplegar **Climas del Desierto** en Portainer, incluyendo limpieza de configuraciones anteriores.

## ⚠️ ANTES DE EMPEZAR

### 1. Limpieza de Configuración Anterior

**IMPORTANTE**: Ejecuta el script de limpieza antes de proceder:

```bash
# Desde el directorio del proyecto
./docker/cleanup_docker.sh
```

Este script eliminará:
- Contenedores antiguos relacionados con el proyecto
- Imágenes obsoletas
- Volúmenes huérfanos
- Redes antiguas

### 2. Preparar Variables de Entorno

1. **Copia el archivo de producción**:
   ```bash
   cp .env.production .env
   ```

2. **Edita el archivo `.env`** con valores reales:
   ```bash
   # Variables CRÍTICAS que debes cambiar:
   DB_PASSWORD=tu_password_seguro_aqui
   REDIS_PASSWORD=tu_password_redis_seguro
   PGADMIN_PASSWORD=tu_password_pgadmin_seguro
   APP_KEY=tu_app_key_de_laravel_64
   ```

## 🏗️ DESPLIEGUE PASO A PASO EN PORTAINER

### Paso 1: Acceso a Portainer

1. **Inicia sesión** en [Portainer](https://portainer.asistenciavircom.com/)
2. **Navega** a "Stacks" en el menú lateral
3. **Crear nuevo Stack**:
   - Nombre: `cdd-app-production`
   - Tipo: "Web editor"

### Paso 2: Configuración del Stack

**⚠️ IMPORTANTE**: El archivo `docker-compose.yml` ha sido actualizado para usar variables de entorno directamente en lugar de archivos `.env`. Esto evita problemas comunes de despliegue.

**Copia el siguiente contenido en el editor web de Portainer**:

```yaml
# ======================================================
# 🧱 CDD App - Producción (Portainer)
# Laravel + PostgreSQL + Redis + Queue
# ======================================================
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
      args:
        APP_ENV: production
        BUILD_ASSETS: false
    env_file:
      - .env
    depends_on:
      db:
        condition: service_healthy
      redis:
        condition: service_started
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s
    networks:
      - cdd_network
    volumes: []
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.cdd.rule=Host(`portainer.asistenciavircom.com`)"
      - "traefik.http.routers.cdd.entrypoints=websecure"
      - "traefik.http.routers.cdd.tls.certresolver=letsencrypt"
      - "traefik.http.services.cdd.loadbalancer.server.port=80"

  queue:
    build:
      context: .
      dockerfile: Dockerfile
      args:
        APP_ENV: production
        BUILD_ASSETS: false
    command: php artisan queue:work --sleep=1 --tries=3
    env_file:
      - .env
    depends_on:
      db:
        condition: service_healthy
      redis:
        condition: service_started
    restart: unless-stopped
    networks:
      - cdd_network
    volumes: []

  db:
    image: postgres:16
    env_file:
      - .env
    volumes:
      - cdd_db_data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U $$POSTGRES_USER -d $$POSTGRES_DB -h 127.0.0.1"]
      interval: 10s
      timeout: 5s
      retries: 10
      start_period: 30s
    restart: unless-stopped
    networks:
      - cdd_network
    labels:
      - "traefik.enable=false"

  redis:
    image: redis:7
    command: ["redis-server", "--appendonly", "yes", "--requirepass", "$$REDIS_PASSWORD"]
    env_file:
      - .env
    volumes:
      - cdd_redis_data:/data
    restart: unless-stopped
    networks:
      - cdd_network
    labels:
      - "traefik.enable=false"

# ======================================================
# 🧩 Volúmenes persistentes (Portainer)
# ======================================================
volumes:
  cdd_db_data:
    driver: local
    labels:
      - "traefik.enable=false"
  cdd_redis_data:
    driver: local
    labels:
      - "traefik.enable=false"

# ======================================================
# 🌐 Red interna del stack (Portainer)
# ======================================================
networks:
  cdd_network:
    driver: bridge
    labels:
      - "traefik.enable=false"
```

### Paso 3: Variables de Entorno en Portainer

**IMPORTANTE**: Ahora las variables se configuran directamente en el formulario de Portainer, no mediante archivos `.env`.

**Ve a la pestaña "Environment variables" y agrega todas las siguientes variables**:

```bash
# ======================================================
# 📱 Información de la Aplicación
# ======================================================
APP_NAME=Climas del Desierto
APP_ENV=production
APP_KEY=base64:CDD_20241012_abcdef1234567890
APP_DEBUG=false
APP_TIMEZONE=America/Hermosillo
APP_URL=https://portainer.asistenciavircom.com

# ======================================================
# 🗄️ Base de Datos PostgreSQL
# ======================================================
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=cdd_production
DB_USERNAME=cdd_user
DB_PASSWORD=CAMBIA_ESTE_PASSWORD_SEGURO_2024!

POSTGRES_DB=cdd_production
POSTGRES_USER=cdd_user
POSTGRES_PASSWORD=CAMBIA_ESTE_PASSWORD_SEGURO_2024!

# ======================================================
# ⚡ Redis (Cache y Sesiones)
# ======================================================
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=CAMBIA_ESTE_PASSWORD_REDIS_SEGURO_2024!

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# ======================================================
# 🔒 Seguridad de Cookies
# ======================================================
APP_CSRF_COOKIE_SECURE=true
APP_SESSION_COOKIE_SECURE=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
SESSION_DOMAIN=portainer.asistenciavircom.com

# ======================================================
# 📧 Configuración de Correo
# ======================================================
MAIL_MAILER=smtp
MAIL_HOST=mail.asistenciavircom.com
MAIL_PORT=587
MAIL_USERNAME=tu_correo@asistenciavircom.com
MAIL_PASSWORD=CAMBIA_ESTE_PASSWORD_EMAIL_2024!
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu_correo@asistenciavircom.com
MAIL_FROM_NAME=Climas del Desierto

# ======================================================
# 🔧 Configuración Adicional
# ======================================================
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error
FILESYSTEM_DISK=local
BROADCAST_DRIVER=log

# ======================================================
# 📊 Configuración de Servicios Externos
# ======================================================
WHATSAPP_TOKEN=tu_token_whatsapp_aqui
WHATSAPP_VERIFY_TOKEN=tu_verify_token_aqui
```

**⚠️ IMPORTANTE**: Cambia TODOS los passwords antes del despliegue:
- `DB_PASSWORD` y `POSTGRES_PASSWORD`
- `REDIS_PASSWORD`
- `MAIL_PASSWORD`

### Paso 4: Desplegar el Stack

1. **Haz clic en "Deploy the stack"**
2. **Espera** a que todos los servicios estén en estado "running"
3. **Verifica** los logs si hay errores

### Paso 5: Ejecutar Migraciones

**Desde el contenedor `cdd-app`** ejecuta:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Paso 6: Verificación Final

**URLs después del despliegue**:
- **Aplicación principal**: `https://portainer.asistenciavircom.com/`
- **pgAdmin**: `https://portainer.asistenciavircom.com:8081/`

## 🔧 GESTIÓN DIARIA

### Verificar Estado de Servicios
```bash
# Desde Portainer: Stacks → cdd-app-production → Services
```

### Ver Logs
```bash
# Desde Portainer: Servicio → Logs → Enable logs
```

### Ejecutar Comandos
```bash
# Desde Portainer: Servicio → Console → Ejecutar comando
php artisan migrate
php artisan queue:restart
php artisan config:clear
```

### Respaldos
```bash
# Desde Portainer: Servicio → Console
./docker/backup.sh
```

## 🚨 SOLUCIÓN DE PROBLEMAS

### Problema: Error 502 Bad Gateway
1. Verificar que el contenedor `app` esté corriendo
2. Revisar logs del servicio `app`
3. Verificar configuración de Nginx

### Problema: Error de conexión a base de datos
1. Verificar variables `DB_*` en las variables de entorno del stack
2. Revisar logs del servicio `db`
3. Verificar que PostgreSQL esté healthy

### Problema: Assets no cargan
1. Ejecutar `npm run build` en el contenedor `app`
2. Verificar permisos de storage
3. Limpiar cache: `php artisan view:clear`

### Problema: Variables de entorno no se cargan
1. Verificar que todas las variables estén en "Environment variables" en Portainer
2. Usar el formato correcto: `NOMBRE_VARIABLE=valor`
3. Reiniciar el stack después de cambiar variables

### Problema: Redis connection refused
1. Verificar que `REDIS_PASSWORD` esté configurado correctamente
2. Revisar logs del servicio `redis`
3. Verificar que el servicio `redis` esté corriendo

## 📞 SOPORTE

Para problemas o dudas:
1. Revisa los logs en Portainer
2. Consulta este documento
3. Contacta al equipo de desarrollo

---

**¡Éxito con el despliegue! 🚀**

*Última actualización: Octubre 2024*