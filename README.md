Tips para commits más rápidos

cuando haga una actualizacion corro este en el vps

cd /srv/laravel-app/src
./update-from-github.sh

No subas node_modules ni vendor, deja que otros reconstruyan dependencias con:

composer install
npm install

Usa git add . solo después de limpiar archivos temporales o ignorados.

Para cambios frecuentes en frontend (Vite), solo sube el código fuente (resources/js, resources/css, vite.config.js) y deja que la compilación se haga en cada entorno.

# 🏢 Asistencia Vircom

Aplicación desarrollada con Laravel + Vue.js para la gestión y administración de asistencia técnica y servicios de CDD.

## Características principales

-   Interfaz moderna y responsiva con Vue.js.
-   Estructura modular para facilitar el mantenimiento y la escalabilidad.
-   Integración con servicios backend.
-   Gestión de usuarios y permisos.
-   Registro y consulta de datos relevantes.
-   Soporte para múltiples roles y funcionalidades extendidas.

## 🚀 Despliegue Rápido (Producción)

### 🔥 Comando Más Rápido (Una Sola Línea)

```bash
# Desplegar completamente desde cero
git clone https://github.com/jesusaln/cdd_app.git /opt/cdd_app && cd /opt/cdd_app && DOCKER_BUILDKIT=1 docker compose up -d --build && sleep 10 && docker compose exec app php artisan migrate:fresh --force
```

### 📋 Scripts Automáticos

#### Despliegue Completo (Nuevo Servidor)
```bash
cd /opt && git clone https://github.com/jesusaln/cdd_app.git && cd cdd_app && chmod +x deploy.sh && ./deploy.sh cdd_app
```

#### Actualización Rápida (Servidores Existentes)
```bash
cd /opt/cdd_app && git pull && docker compose down && DOCKER_BUILDKIT=1 docker compose up -d --build && docker compose exec app php artisan migrate:fresh --force
```

### 🛠️ Comandos Paso a Paso

```bash
# 1. Preparar entorno
cd /opt && sudo mkdir -p cdd_app && cd cdd_app

# 2. Obtener código
git clone https://github.com/jesusaln/cdd_app.git .

# 3. Construir e iniciar
DOCKER_BUILDKIT=1 docker compose up -d --build

# 4. Esperar y migrar
sleep 10 && docker compose exec app php artisan migrate:fresh --force

# 5. Finalizar
docker compose exec app php artisan storage:link || true
```

### 🆘 Solución de Problemas

```bash
# Borrado completo y nuevo inicio (si hay problemas)
cd /opt/cdd_app && docker compose down -v --remove-orphans && docker image prune -f && git pull origin master && DOCKER_BUILDKIT=1 docker compose up -d --build && docker compose exec app php artisan migrate:fresh --force
```

### 📊 Comandos de Verificación

```bash
# Ver estado de servicios
docker compose ps

# Ver logs en tiempo real
docker compose logs -f app

# Ver estado de migraciones
docker compose exec app php artisan migrate:status

# Información de la aplicación
docker compose exec app php artisan about
```

## Instalación (Desarrollo Local)

1. Clona el repositorio:
    ```bash
    git clone https://github.com/jesusaln/cdd_app.git
    ```
2. Ingresa al directorio del proyecto:
    ```bash
    cd cdd_app
    ```
3. Instala las dependencias:
    ```bash
    npm install
    ```
4. Inicia la aplicación en modo desarrollo:
    ```bash
    npm run serve
    ```

## Testing

### Comandos de Test

Para ejecutar tests con datos frescos (recomendado para desarrollo):

```bash
# Usando comandos directos de PHP
php artisan migrate:fresh --seed && php artisan test --filter=Cliente

# Usando npm scripts
npm run test:fresh && npm run test:cliente
```

### Tests Disponibles

- **ClienteControllerTest**: Tests de integración para el controlador de clientes
- **CrudTest**: Tests básicos de CRUD para clientes
- **ModelTest**: Tests del modelo Cliente
- **SchemaTest**: Tests de estructura de base de datos

### Funcionalidades del Módulo de Clientes

#### ✅ Características Implementadas

- **Autocompletado de Dirección**: Al ingresar un código postal válido, se autocompletan automáticamente:
  - Estado (clave SAT)
  - Municipio
  - Lista de colonias disponibles
- **Validaciones Avanzadas**: CFDI 4.0, RFC único, email en minúsculas
- **Valores por Defecto**: Estado "SON" (Sonora), Uso CFDI "G03" (Gastos en general)
- **Teléfono de 10 Dígitos**: Validación estricta de exactamente 10 números
- **Soft Deletes**: Eliminación suave con posibilidad de restaurar
- **Sistema de Permisos**: Control de acceso granular
- **Integración Facturapi**: Sincronización automática con servicio de facturación
- **UX Optimizada**: Botón cambiar estado solo en modal (no en tabla principal)
- **Props Vue Corregidos**: Eliminados warnings de atributos no heredados

## ⚠️ Solución de Problemas Comunes

### ❌ Error: "columna regimen_fiscal_receptor ya existe"
**Solución:** Este error ocurre por una migración duplicada. Usa este comando:
```bash
cd /opt/cdd_app && docker compose exec app rm -f database/migrations/2025_09_18_152246_add_regimen_fiscal_receptor_to_sat_usos_cfdi_table.php && docker compose exec app php artisan migrate:fresh --force
```

### ❌ Error: "function datetime() does not exist" (PostgreSQL)
**Solución:** Este error ocurre cuando se usa sintaxis de SQLite en PostgreSQL. Usa este comando:
```bash
cd /opt/cdd_app && git pull origin master && docker compose exec app php artisan migrate:fresh --force
```

### ❌ Error: Puerto ocupado o servicios no responden
**Solución:** Limpiar completamente y reiniciar:
```bash
cd /opt/cdd_app && docker compose down -v --remove-orphans && docker image prune -f && DOCKER_BUILDKIT=1 docker compose up -d --build
```

## 🌐 Acceso a la Aplicación

### Producción (con Docker + Nginx)
- **Dirección externa:** `http://localhost:8080` o `https://admin.asistenciavircom.com`
- **Puerto externo:** `8080` (puerto HTTP estándar para esta aplicación)
- **Puerto interno:** `80` (Laravel dentro del contenedor)
- **HTTPS (recomendado):** `https://admin.asistenciavircom.com` (puerto 443)

### ☁️ Despliegue Público con Dominio

Para hacer la aplicación accesible públicamente:

1. **Configurar dominio** apuntando a la IP de tu servidor
2. **Abrir puertos** en el firewall:
   ```bash
   sudo ufw allow 80   # HTTP
   sudo ufw allow 443  # HTTPS
   ```
3. **Configurar SSL** con Let's Encrypt (ver arriba)
4. **Acceso público:** `https://admin.asistenciavircom.com`

### Desarrollo Local
- **Dirección:** `http://localhost:8000` (Laravel integrado)
- **Dirección alternativa:** `http://localhost:8080` (Vue.js independiente)

### Configuración Nginx
- Nginx actúa como proxy reverso
- Escucha en puerto 8080 externamente (y 443 para SSL)
- Redirige al contenedor `app:80` internamente
- Archivos estáticos servidos directamente por Nginx
- Red interna dedicada `cdd_network` para aislamiento

### 🌐 Configuración SSL/HTTPS con Let's Encrypt

Para hacer la aplicación pública con HTTPS:

1. **Configurar dominio** apuntando a tu servidor
2. **Instalar Certbot** para Let's Encrypt:
   ```bash
   # En el servidor host (no en Docker)
   sudo apt install certbot python3-certbot-nginx
   sudo certbot --nginx -d admin.asistenciavircom.com
   ```
3. **Configurar renovación automática**:
   ```bash
   sudo crontab -e
   # Agregar: 0 12 * * * /usr/bin/certbot renew --quiet
   ```

## Uso

Una vez desplegada, accede a la aplicación según tu configuración:

- **Producción:** `http://localhost:8080` (puerto 8080 por defecto)
- **Desarrollo:** `http://localhost:8000` (Laravel) o `http://localhost:8080` (Vue.js)

Explora las secciones y funcionalidades disponibles según tu rol.

## 📁 Archivos de Despliegue

Hemos incluido scripts para facilitar el despliegue:

- **`deploy.sh`** - Script completo de despliegue automático para nuevos servidores
- **`update.sh`** - Script para actualizar instalaciones existentes
- **`comandos_rapidos.md`** - Documentación detallada con todos los comandos disponibles

## Estructura del proyecto

```
cdd_app/
├── deploy.sh              # 🚀 Script de despliegue automático
├── update.sh              # 🔄 Script de actualización rápida
├── comandos_rapidos.md    # 📋 Documentación de comandos
├── docker-compose.yml     # ⚙️ Configuración Docker
├── database/migrations/   # 🗄️ Migraciones de base de datos
├── resources/             # 🎨 Frontend (Vue.js)
├── app/                   # 🛠️ Código backend (Laravel)
└── README.md              # 📖 Esta documentación
```

## Contribución

1. Haz un fork del repositorio.
2. Crea una nueva rama con tu mejora: `git checkout -b mejora/feature-nueva`.
3. Realiza el commit de tus cambios: `git commit -m 'Agrega nueva funcionalidad'`.
4. Haz push a la rama: `git push origin mejora/feature-nueva`.
5. Abre un Pull Request.

## 🎯 Resumen - Inicio Rápido

### Para Nuevo Servidor (Producción)
```bash
git clone https://github.com/jesusaln/cdd_app.git /opt/cdd_app && cd /opt/cdd_app && DOCKER_BUILDKIT=1 docker compose up -d --build && sleep 10 && docker compose exec app php artisan migrate:fresh --force
```
**🌐 Acceso:** `http://localhost:8080` (puerto 8080) o `https://admin.asistenciavircom.com` (puerto 443)

### Para Servidor Existente (Actualización)
```bash
cd /opt/cdd_app && git pull && docker compose down && DOCKER_BUILDKIT=1 docker compose up -d --build && docker compose exec app php artisan migrate:fresh --force
```
**🌐 Acceso:** `http://localhost:8080` (puerto 8080) o `https://admin.asistenciavircom.com` (puerto 443)

### Para Desarrollo Local
```bash
git clone https://github.com/jesusaln/cdd_app.git && cd cdd_app && npm install && npm run dev
```
**🌐 Acceso:** `http://localhost:8000` (Laravel) o `http://localhost:8080` (Vue.js)

### Problemas Comunes
- **Columna duplicada:** `rm -f database/migrations/2025_09_18_152246_add_regimen_fiscal_receptor_to_sat_usos_cfdi_table.php`
- **Error PostgreSQL:** `git pull` para obtener correcciones automáticas
- **Puerto ocupado:** Usa `http://localhost:8080` (no `http://localhost:8000`) en producción

## Licencia

Consulta el archivo LICENSE para detalles sobre la licencia del proyecto.

---

# cdd_app

Application developed with Vue.js for management and administration of information related to CDD.

## Main features

-   Modern, responsive interface built with Vue.js.
-   Modular structure for easy maintenance and scalability.
-   Integration with backend services.
-   User and permission management.
-   Registration and consultation of relevant data.
-   Support for multiple roles and extended functionalities.

## Installation

1. Clone the repository:
    ```bash
    git clone https://github.com/jesusaln/cdd_app.git
    ```
2. Enter the project directory:
    ```bash
    cd cdd_app
    ```
3. Install dependencies:
    ```bash
    npm install
    ```
4. Start the application in development mode:
    ```bash
    npm run serve
    ```

## Usage

-   Go to `http://localhost:8080` in your browser.
-   Explore the available sections and features according to your user role.

## Project Structure

```
cdd_app/
├── src/
│   ├── assets/
│   ├── components/
│   ├── views/
│   ├── router/
│   ├── store/
│   └── App.vue
├── public/
├── package.json
└── README.md
```

## Contribution

1. Fork the repository.
2. Create a new branch for your improvement: `git checkout -b feature/new-feature`.
3. Commit your changes: `git commit -m 'Add new functionality'`.
4. Push the branch: `git push origin feature/new-feature`.
5. Open a Pull Request.

## License

See the LICENSE file for project license details.
