# 🚀 Comandos Rápidos para Despliegue Asistencia Vircom

## 🌐 ACCESO A LA APLICACIÓN:

### Producción (Pública con Dominio)

-   **Dirección:** `https://admin.asistenciavircom.com` (puerto 443 - SSL)
-   **Puerto externo:** `8080` (HTTP) y `443` (HTTPS)
-   **Puerto interno:** `80` (Laravel dentro del contenedor)

### Desarrollo Local

-   **Laravel:** `http://localhost:8000`
-   **Vue.js:** `http://localhost:8080`

## 🔥 UN SOLO COMANDO para desplegar completamente:

```bash
# Clonar y desplegar en un solo comando
git clone https://github.com/jesusaln/cdd_app.git /opt/cdd_app && cd /opt/cdd_app && chmod +x deploy.sh && ./deploy.sh cdd_app
```

## 🔒 Configuración SSL/HTTPS con Let's Encrypt:

```bash
# 1. Configurar dominio apuntando a tu servidor
# 2. Instalar Certbot
sudo apt install certbot python3-certbot-nginx

# 3. Obtener certificado SSL
sudo certbot --nginx -d admin.asistenciavircom.com

# 4. Configurar renovación automática
sudo crontab -e
# Agregar: 0 12 * * * /usr/bin/certbot renew --quiet

# 5. Abrir puertos en firewall
sudo ufw allow 80   # HTTP
sudo ufw allow 443  # HTTPS
```

### Script Automático SSL:

```bash
# Usar script incluido
chmod +x setup_ssl.sh && ./setup_ssl.sh admin.asistenciavircom.com
```

## ✨ Características de la Nueva Configuración:

### Mejoras Implementadas:

-   **🔄 restart: always** - Reinicio automático de contenedores
-   **💚 healthcheck** - Monitoreo de salud de servicios
-   **🌐 cdd_network** - Red dedicada para aislamiento
-   **⏱️ intervals ajustados** - Reduce falsos positivos
-   **📝 Comentarios claros** - Facilita mantenimiento
-   **🔒 Sin version** - Elimina warnings obsoletos

## 📋 Comandos individuales paso a paso:

```bash
# 1. Preparar entorno
cd /opt && sudo mkdir -p cdd_app && cd cdd_app

# 2. Clonar repositorio
git clone https://github.com/jesusaln/cdd_app.git .

# 3. Detener servicios existentes (si los hay)
docker compose down -v --remove-orphans || true

# 4. Construir e iniciar
DOCKER_BUILDKIT=1 docker compose up -d --build

# 5. Ejecutar migraciones
docker compose exec app php artisan migrate:fresh --force

# 6. Crear enlaces
docker compose exec app php artisan storage:link || true
```

## 🔄 Para actualizar en servidores existentes:

```bash
# Comando único para actualizar
cd /opt/cdd_app && git pull origin master && docker compose down && DOCKER_BUILDKIT=1 docker compose up -d --build && docker compose exec app php artisan migrate:fresh --force && docker compose exec app php artisan storage:link || true
```

### Para configurar SSL/HTTPS público:

```bash
# Hacer aplicación pública con dominio
chmod +x setup_ssl.sh && ./setup_ssl.sh admin.asistenciavircom.com

# Luego accede en: https://admin.asistenciavircom.com
```

## 🆘 Si hay problemas (solución completa):

```bash
# Solución de emergencia - borra todo y vuelve a empezar
cd /opt/cdd_app && docker compose down -v --remove-orphans && docker image prune -f && git pull origin master && DOCKER_BUILDKIT=1 docker compose up -d --build && docker compose exec app php artisan migrate:fresh --force && docker compose exec app php artisan storage:link || true
```

## 📊 Comandos de verificación:

```bash
# Ver estado de servicios
docker compose ps

# Ver logs en tiempo real
docker compose logs -f app

# Ver estado de migraciones
docker compose exec app php artisan migrate:status

# Ver información de la aplicación
docker compose exec app php artisan about
```

## ⚡ Comandos más rápidos (una línea cada uno):

```bash
# Desplegar desde cero
git clone https://github.com/jesusaln/cdd_app.git /opt/cdd_app && cd /opt/cdd_app && DOCKER_BUILDKIT=1 docker compose up -d --build && sleep 10 && docker compose exec app php artisan migrate:fresh --force

# Actualizar existente
cd /opt/cdd_app && git pull && docker compose down && DOCKER_BUILDKIT=1 docker compose up -d --build && docker compose exec app php artisan migrate:fresh --force

# Solo migraciones (si no hay cambios de código)
cd /opt/cdd_app && docker compose exec app php artisan migrate:fresh --force
```

## 🎯 El más rápido de todos:

```bash
# Una sola línea para desplegar completamente
curl -s https://raw.githubusercontent.com/jesusaln/cdd_app/master/deploy.sh | bash -s cdd_app

# Una sola línea con SSL automático (necesitas dominio)
# curl -s https://raw.githubusercontent.com/jesusaln/cdd_app/master/setup_ssl.sh | bash -s admin.asistenciavircom.com
```
