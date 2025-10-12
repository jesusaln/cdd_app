# 🚀 Migración de SQLite a PostgreSQL

## 📋 Guía Completa de Migración

Esta guía te ayudará a migrar tu aplicación Laravel de SQLite a PostgreSQL de forma segura y eficiente.

## ✅ Ventajas de PostgreSQL

- 🚀 **Mejor rendimiento** para aplicaciones grandes
- 🔒 **Mayor seguridad** y control de acceso
- 📊 **Características avanzadas** (JSON, arrays, full-text search)
- 🌐 **Mejor soporte** para aplicaciones web modernas
- 📈 **Escalabilidad** para crecimiento futuro

## 🛠️ Método 1: Docker (Recomendado)

### **Paso 1: Preparar Docker**
```bash
# Crear directorios para datos persistentes
mkdir -p docker\pgdata docker\pgadmin docker\redis

# Iniciar servicios
docker compose up -d

# Verificar que esté corriendo
docker compose ps
```

### **Paso 2: Configurar Laravel**
```bash
# Ejecutar migración automática
php migrate_to_postgresql.php
```

### **Paso 3: Verificar Instalación**
```bash
# Ver configuración segura
php artisan whatsapp:show-config

# Probar conexión a BD
php artisan tinker
>>> DB::connection()->getPdo();
```

## 🛠️ Método 2: Instalación Nativa

### **Paso 1: Instalar PostgreSQL**
```bash
# Windows (con Chocolatey)
choco install postgresql

# O descargar desde: https://postgresql.org/download/
```

### **Paso 2: Crear Base de Datos**
```sql
-- Conectar como postgres
createdb cdd_local
createuser cdd
ALTER USER cdd PASSWORD 'secret';
GRANT ALL PRIVILEGES ON DATABASE cdd_local TO cdd;
```

### **Paso 3: Configurar Laravel**
```bash
# Actualizar .env manualmente
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cdd_local
DB_USERNAME=cdd
DB_PASSWORD=secret

# Ejecutar migraciones
php artisan config:clear
php artisan migrate
```

## 🔄 Migración de Datos (Opcional)

### **Si tienes datos en SQLite:**

```bash
# 1. Exportar datos de SQLite
php artisan db:seed  # Si usas seeders

# 2. O usar herramienta de migración
# Nota: Laravel no tiene comando built-in para migrar datos
# Puedes crear un script personalizado o usar herramientas externas
```

## 🚨 Configuración de Seguridad

### **Variables de Entorno (.env):**
```env
# Base de datos
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cdd_local
DB_USERNAME=cdd
DB_PASSWORD=secret

# WhatsApp (ya configurado)
WHATSAPP_ACCESS_TOKEN=tu_token_seguro
WHATSAPP_BUSINESS_ACCOUNT_ID=tu_business_id
```

### **Configuración de Producción:**
```bash
# Usa variables de entorno reales
DB_PASSWORD=tu_password_seguro_de_produccion
WHATSAPP_ACCESS_TOKEN=token_de_produccion_seguro

# Encripta información sensible
php artisan tinker
$empresa->whatsapp_access_token = encrypt('token_real');
```

## 🛠️ Comandos Útiles

### **Gestión de Base de Datos:**
```bash
# Estado de migraciones
php artisan migrate:status

# Crear nueva migración
php artisan make:migration nombre_migracion

# Hacer rollback
php artisan migrate:rollback

# Reset completo
php artisan migrate:reset
```

### **Gestión de WhatsApp:**
```bash
# Configuración segura
php artisan whatsapp:show-config

# Validar token
php artisan whatsapp:validate-token

# Listar plantillas
php artisan whatsapp:list-templates

# Diagnóstico completo
php artisan whatsapp:diagnose-connection
```

## 🔧 Docker Management

### **Gestión de Contenedores:**
```bash
# Ver logs
docker compose logs pg
docker compose logs pgadmin

# Reiniciar servicios
docker compose restart

# Detener todo
docker compose down

# Destruir y recrear
docker compose down -v
docker compose up -d
```

### **Acceso a pgAdmin:**
- 🌐 **URL**: http://localhost:8081
- 👤 **Usuario**: admin@local.test
- 🔑 **Password**: admin
- 💾 **Servidor**: Agregar servidor PostgreSQL en pgAdmin

## 🚨 Solución de Problemas

### **Error: "PDO exception"**
```bash
# Verificar que PostgreSQL esté corriendo
docker compose ps

# Ver logs de PostgreSQL
docker compose logs pg

# Probar conexión manual
php -r "new PDO('pgsql:host=127.0.0.1;dbname=cdd_local', 'cdd', 'secret'); echo 'OK';"
```

### **Error: "Extension pdo_pgsql not found"**
```bash
# En Laragon, habilitar extensión en php.ini
extension=pdo_pgsql
extension=pgsql

# Reiniciar servidor web
```

### **Error: "Relation does not exist"**
```bash
# Ejecutar migraciones
php artisan migrate

# Verificar tablas creadas
php artisan tinker
>>> DB::select('SELECT table_name FROM information_schema.tables');
```

## 📊 Comparación de Rendimiento

| Característica | SQLite | PostgreSQL |
|---------------|--------|------------|
| **Velocidad lectura** | ✅ Rápido | ✅ Muy rápido |
| **Concurrencia** | ❌ Limitada | ✅ Excelente |
| **Tamaño máximo** | ❌ 140TB límite | ✅ Sin límite práctico |
| **Backup/Restore** | ⚠️ Manual | ✅ Herramientas avanzadas |
| **JSON support** | ✅ Básico | ✅ Avanzado |
| **Full-text search** | ❌ No | ✅ Sí |
| **Arrays** | ❌ No | ✅ Sí |

## 🎯 Próximos Pasos Después de Migrar

1. ✅ **Probar aplicación** completamente
2. ✅ **Verificar WhatsApp** sigue funcionando
3. ✅ **Hacer backup** de la nueva BD PostgreSQL
4. ✅ **Configurar monitoreo** si es producción
5. ✅ **Documentar procedimiento** para futuro

## 📚 Recursos Adicionales

- 📖 [Documentación PostgreSQL](https://postgresql.org/docs/)
- 🐘 [pgAdmin Documentation](https://pgadmin.org/docs/)
- 🚀 [Laravel PostgreSQL Guide](https://laravel.com/docs/database#postgresql)
- 🛠️ [Docker PostgreSQL](https://hub.docker.com/_/postgres)

---
**Estado**: Listo para migrar
**Método recomendado**: Docker (más limpio y portátil)
**Tiempo estimado**: 15-30 minutos