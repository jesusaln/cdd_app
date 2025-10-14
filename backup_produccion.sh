#!/bin/bash

# Script de respaldo para producción
# Uso: ./backup_produccion.sh

echo "=== INICIANDO RESPALDO DE PRODUCCIÓN ==="
echo "Fecha: $(date)"
echo "Servidor: $(hostname)"

# Variables de configuración
DB_NAME="cdd_app_prod"
DB_USER="cdd_user"
BACKUP_DIR="/backup"
LOG_FILE="/var/log/backup_produccion.log"

# Crear directorio de respaldo si no existe
sudo mkdir -p $BACKUP_DIR
sudo chmod 755 $BACKUP_DIR

# Nombre del archivo de respaldo
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_$(date +%Y%m%d_%H%M%S).sql"

# Función para logging
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a $LOG_FILE
}

log "Iniciando respaldo de base de datos..."

# Crear respaldo de PostgreSQL
if pg_dump -h localhost -U $DB_USER -d $DB_NAME -F c -f $BACKUP_FILE 2>>$LOG_FILE; then
    log "✅ Respaldo creado exitosamente: $BACKUP_FILE"

    # Comprimir el respaldo
    gzip $BACKUP_FILE
    log "✅ Respaldo comprimido: ${BACKUP_FILE}.gz"

    # Calcular tamaño
    SIZE=$(du -h ${BACKUP_FILE}.gz | cut -f1)
    log "📦 Tamaño del respaldo: $SIZE"
else
    log "❌ Error al crear respaldo de base de datos"
    exit 1
fi

# Crear respaldo de Laravel (storage y archivos importantes)
LARAVEL_BACKUP="$BACKUP_DIR/laravel_files_$(date +%Y%m%d_%H%M%S).tar.gz"
tar -czf $LARAVEL_BACKUP -C /var/www/cdd_app storage bootstrap/cache public/storage 2>>$LOG_FILE

if [ $? -eq 0 ]; then
    log "✅ Respaldo de archivos Laravel creado: $LARAVEL_BACKUP"
else
    log "❌ Error al crear respaldo de archivos Laravel"
fi

# Limpiar respaldos antiguos (mantener últimos 7 días)
log "🧹 Limpiando respaldos antiguos..."
find $BACKUP_DIR -name "*.gz" -type f -mtime +7 -delete 2>>$LOG_FILE
find $BACKUP_DIR -name "*.sql" -type f -mtime +7 -delete 2>>$LOG_FILE

COUNT=$(find $BACKUP_DIR -name "*.gz" -type f | wc -l)
log "📊 Respaldo completado. Archivos restantes: $COUNT"

# Verificar espacio disponible
DISK_USAGE=$(df $BACKUP_DIR | tail -1 | awk '{print $5}')
log "💾 Uso de disco: $DISK_USAGE"

log "=== RESPALDO COMPLETADO ==="

# Enviar notificación (opcional)
# curl -X POST -H 'Content-type: application/json' \
#     --data '{"text":"Respaldo de producción completado exitosamente"}' \
#     https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK