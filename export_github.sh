#!/bin/bash

# =====================================================
# EXPORTAR PROYECTO PARA GITHUB
# =====================================================

echo "📦 EXPORTANDO PROYECTO PARA GITHUB..."
echo "===================================="

# Función para colores
green() { echo -e "\033[32m$1\033[0m"; }
blue() { echo -e "\033[34m$1\033[0m"; }

# =====================================================
# 1. CREAR PAQUETE LIMPIO
# =====================================================

green "1/4 - Creando paquete limpio para GitHub..."

# Crear paquete excluyendo archivos innecesarios
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
    --exclude=*.zip \
    --exclude=backup_*.sql \
    --exclude=postgresql_config_backup_* \
    --exclude=test_* \
    --exclude=temp_* \
    --exclude=comandos_rapidos.md \
    --exclude=fix_*.php \
    --exclude=fix_*.sql \
    --exclude=fix_*.sh \
    --exclude=check_*.php \
    --exclude=db_info.php \
    --exclude=cookies.txt \
    --exclude=migrate_to_postgresql.php \
    --exclude=modales_patch.diff \
    --exclude=nombre_razon_social\} \
    --exclude=SECURITY_INCIDENT_RESPONSE.md \
    --exclude=security_cleanup.* \
    --exclude=setup_ssl.sh \
    --exclude=stack.env \
    --exclude=switch-env.sh \
    --exclude=temp_controller.php \
    --exclude=update_*.php \
    --exclude=update_*.sh \
    --exclude=WHATSAPP_* \
    --exclude=whatsapp.dev.json \
    --exclude=android \
    --exclude=docs \
    --exclude=tests \
    .

# Verificar tamaño del paquete
PACKAGE_SIZE=$(du -h cdd_app_github.tar.gz | cut -f1)
echo "✅ Paquete creado: $PACKAGE_SIZE"

# =====================================================
# 2. CREAR ARCHIVOS NECESARIOS
# =====================================================

green "2/4 - Creando archivos necesarios..."

# Crear .env.example si no existe
if [ ! -f ".env.example" ]; then
    cp .env .env.example 2>/dev/null || echo "Archivo .env.example no encontrado"
fi

# Crear README para GitHub
cat > README.md << 'EOF'
# CDD App - Sistema de Gestión

Aplicación Laravel para gestión empresarial de Climas del Desierto.

## 🚀 Despliegue Automático con Docker

Esta aplicación se despliega automáticamente usando Docker en cualquier VPS Ubuntu.

### Requisitos Previos
- VPS Ubuntu 20.04 o superior
- Git instalado

### Despliegue en un Solo Comando
```bash
# Clonar repositorio
git clone https://github.com/TU-USUARIO/cdd-app.git
cd cdd-app

# Desplegar automáticamente
./deploy_github.sh
```

### Servicios Incluidos
- **Laravel** (PHP 8.1-FPM) - Aplicación principal
- **PostgreSQL 15** - Base de datos optimizada
- **Redis 7** - Cache y sesiones
- **Nginx** - Servidor web

### Acceso Después del Despliegue
- 🌐 **Aplicación principal**: http://TU-IP
- 📊 **Estado de servicios**: docker-compose ps
- 🔧 **Logs**: docker-compose logs -f

## Características
- ✅ Gestión de clientes y proveedores
- ✅ Control de inventario
- ✅ Sistema de ventas y cotizaciones
- ✅ Reportes avanzados
- ✅ Optimizado para alto volumen de datos
- ✅ Configuración de producción lista

## Mantenimiento
```bash
# Ver estado
docker-compose ps

# Ver logs
docker-compose logs -f

# Hacer respaldo
docker-compose exec db pg_dump -U cdd_user cdd_app_prod > backup.sql

# Detener aplicación
docker-compose down
```

## Soporte
- Documentación: README_PRODUCCION.md
- Configuración: README_CONFIGURACION.md
EOF

echo "✅ README.md actualizado"

# =====================================================
# 3. VERIFICAR CONTENIDO DEL PAQUETE
# =====================================================

green "3/4 - Verificando contenido del paquete..."

# Listar archivos incluidos
echo ""
blue "=== ARCHIVOS INCLUIDOS EN EL PAQUETE ==="
tar -tzf cdd_app_github.tar.gz | head -20
echo "... (más archivos)"

# Contar archivos por tipo
TOTAL_FILES=$(tar -tzf cdd_app_github.tar.gz | wc -l)
PHP_FILES=$(tar -tzf cdd_app_github.tar.gz | grep -c "\.php$")
JS_FILES=$(tar -tzf cdd_app_github.tar.gz | grep -c "\.js$")
VUE_FILES=$(tar -tzf cdd_app_github.tar.gz | grep -c "\.vue$")

echo ""
blue "=== ESTADÍSTICAS ==="
echo "📊 Total de archivos: $TOTAL_FILES"
echo "🐘 Archivos PHP: $PHP_FILES"
echo "🟨 Archivos JavaScript: $JS_FILES"
echo "💚 Archivos Vue: $VUE_FILES"

# =====================================================
# 4. INSTRUCCIONES PARA GITHUB
# =====================================================

green "4/4 - Instrucciones para GitHub..."

echo ""
green "=== PASOS PARA SUBIR A GITHUB ==="
echo ""
echo "1️⃣ Crear repositorio en GitHub:"
echo "   - Ir a https://github.com/new"
echo "   - Nombre: cdd-app"
echo "   - Descripción: Sistema de gestión Laravel con Docker"
echo "   - Marcar 'Add a README file'"
echo ""
echo "2️⃣ Subir archivos:"
echo "   - Ejecutar estos comandos en tu máquina local:"
echo ""
echo "   # Crear directorio temporal"
echo "   mkdir temp_github && cd temp_github"
echo ""
echo "   # Extraer paquete"
echo "   tar -xzf ../cdd_app_github.tar.gz"
echo ""
echo "   # Inicializar git"
echo "   git init"
echo "   git add ."
echo "   git commit -m 'Initial commit - CDD App Laravel'"
echo ""
echo "   # Conectar con GitHub (cambiar TU-USUARIO)"
echo "   git remote add origin https://github.com/TU-USUARIO/cdd-app.git"
echo "   git branch -M main"
echo "   git push -u origin main"
echo ""
echo "3️⃣ Desplegar en VPS:"
echo "   # En el VPS Ubuntu:"
echo "   git clone https://github.com/TU-USUARIO/cdd-app.git"
echo "   cd cdd-app"
echo "   ./deploy_github.sh"
echo ""

# =====================================================
# RESULTADO FINAL
# =====================================================

green "✅ EXPORTACIÓN COMPLETADA!"

echo ""
blue "=== ARCHIVO CREADO ==="
echo "📦 cdd_app_github.tar.gz ($PACKAGE_SIZE)"

echo ""
blue "=== ARCHIVOS PREPARADOS ==="
echo "📋 .gitignore - Archivos a ignorar en GitHub"
echo "📖 README.md - Documentación para GitHub"
echo "🚀 deploy_github.sh - Script de despliegue automático"
echo "🐳 Dockerfile - Imagen Docker"
echo "⚙️ docker-compose.yml - Orquestación"
echo "🌐 docker/nginx.conf - Configuración web"

echo ""
green "=== VENTAJAS DE ESTE ENFOQUE ==="
echo "✅ Despliegue ultra-rápido (un comando)"
echo "✅ Sin transferencia manual de archivos"
echo "✅ Control de versiones completo"
echo "✅ Fácil mantenimiento y actualizaciones"
echo "✅ Rollback fácil"
echo "✅ Colaboración en equipo"

echo ""
green "🎯 PRÓXIMO PASO:"
echo "Ejecuta los comandos de git para subir a GitHub"
echo "Luego despliega en el VPS con: ./deploy_github.sh"

echo ""
green "¡LISTO PARA DESPLEGAR VIA GITHUB! 🚀"