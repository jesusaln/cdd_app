# 🚀 Despliegue en Portainer - Climas del Desierto

Este documento explica cómo desplegar la aplicación **Climas del Desierto** en Portainer usando Docker Compose.

## 📋 Prerrequisitos

- Acceso a [Portainer](https://portainer.asistenciavircom.com/)
- Cuenta con permisos para crear stacks
- Conocimientos básicos de Docker y Portainer

## 🏗️ Arquitectura del Stack

El stack incluye los siguientes servicios:

- **app**: Aplicación Laravel (PHP 8.2 + Node.js)
- **nginx**: Proxy reverso y servidor web
- **pg**: Base de datos PostgreSQL 16
- **redis**: Servidor Redis para caché y sesiones
- **pgadmin**: Interfaz web para gestión de PostgreSQL (opcional)

## 🚀 Despliegue Paso a Paso

### 1. Preparar Archivos

Asegúrate de tener todos los archivos necesarios en tu repositorio:

```
cdd-app/
├── docker-compose.yml          # Configuración del stack
├── Dockerfile                  # Imagen de la aplicación
├── docker/
│   ├── php.ini                # Configuración PHP
│   ├── nginx.conf             # Configuración Nginx
│   ├── deploy.sh              # Script de despliegue
│   ├── update.sh              # Script de actualización
│   └── backup.sh              # Script de respaldos
├── .env.production            # Variables de entorno (plantilla)
└── [todos los archivos de Laravel]
```

### 2. Configurar Variables de Entorno

1. Crea una copia del archivo `.env.production`:
   ```bash
   cp .env.production .env
   ```

2. Edita el archivo `.env` con tus valores específicos:
   ```env
   DB_PASSWORD=tu_password_seguro_aqui
   REDIS_PASSWORD=tu_password_redis_seguro
   PGADMIN_PASSWORD=tu_password_pgadmin_seguro
   APP_KEY=tu_app_key_de_laravel
   ```

### 3. Crear Stack en Portainer

1. **Iniciar sesión** en [Portainer](https://portainer.asistenciavircom.com/)

2. **Navegar** a "Stacks" en el menú lateral

3. **Crear nuevo Stack**:
   - Nombre: `cdd-app` o `climas-del-desierto`
   - Tipo: "Web editor"

4. **Pegar configuración** del `docker-compose.yml`

5. **Configurar variables de entorno**:
   - Ve a la pestaña "Environment variables"
   - Agrega todas las variables necesarias desde tu `.env`

6. **Desplegar** el stack

### 4. Ejecutar Migraciones

Después del despliegue inicial, ejecuta las migraciones:

```bash
# Desde Portainer, ve al contenedor "cdd-app"
# Ejecuta el comando:
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

## 🔧 Gestión y Mantenimiento

### Ver Logs
```bash
# Desde Portainer, ve al servicio correspondiente
# Logs -> Enable logs
```

### Actualizar Aplicación
```bash
# Ejecutar script de actualización
./docker/update.sh
```

### Crear Respaldos
```bash
# Ejecutar script de respaldo
./docker/backup.sh
```

### Reiniciar Servicios
```bash
# Desde Portainer: Stack -> cdd-app -> Restart
```

## 🌐 Acceso a la Aplicación

Después del despliegue exitoso:

- **Aplicación principal**: `https://portainer.asistenciavircom.com/`
- **pgAdmin**: `https://portainer.asistenciavircom.com:8081/`
  - Usuario: Configurado en `PGADMIN_EMAIL`
  - Password: Configurado en `PGADMIN_PASSWORD`

## 🔒 Seguridad

### Certificados SSL (Opcional)

Para habilitar HTTPS:

1. Crea certificados SSL:
   ```bash
   openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
     -keyout docker/ssl/key.pem \
     -out docker/ssl/cert.pem
   ```

2. Actualiza `docker-compose.yml` para exponer el puerto 443

### Variables Sensibles

- Nunca commitear archivos `.env` con datos reales
- Usa variables de entorno en Portainer para datos sensibles
- Cambia passwords por defecto antes del despliegue

## 🛠️ Solución de Problemas

### Problemas Comunes

1. **Error de conexión a base de datos**:
   - Verificar variables `DB_*` en `.env`
   - Asegurar que PostgreSQL esté corriendo
   - Revisar logs del contenedor `pg`

2. **Error 502 Bad Gateway**:
   - Verificar que el contenedor `app` esté corriendo
   - Revisar configuración de Nginx
   - Verificar que PHP-FPM esté funcionando

3. **Assets no cargan**:
   - Ejecutar `npm run build` en el contenedor `app`
   - Verificar permisos de storage

### Comandos de Debug

```bash
# Ver estado de servicios
docker-compose ps

# Ver logs de aplicación
docker-compose logs -f app

# Ejecutar comandos en contenedor
docker-compose exec app php artisan --version

# Ver variables de entorno
docker-compose exec app env
```

## 📞 Soporte

Para problemas o dudas:
- Revisa los logs en Portainer
- Consulta la documentación oficial de Laravel
- Contacta al equipo de desarrollo

---

**¡Éxito con el despliegue! 🚀**