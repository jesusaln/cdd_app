#!/bin/bash

# =============================================================================
# 🔒 Script de Configuración SSL con Let's Encrypt
# =============================================================================
# Uso: ./setup_ssl.sh tu-dominio.com
# Ejemplo: ./setup_ssl.sh cddapp.com

DOMAIN="${1:-localhost}"

if [ "$DOMAIN" = "localhost" ]; then
    echo "⚠️  No se puede configurar SSL para localhost"
    echo "💡 Usa un dominio real: ./setup_ssl.sh tu-dominio.com"
    exit 1
fi

echo "🔒 Configurando SSL para $DOMAIN con Let's Encrypt..."

# =============================================================================
# 1. VERIFICAR DOMINIO
# =============================================================================

echo ""
echo "🌐 Verificando configuración de dominio..."

# Verificar si el dominio apunta al servidor
SERVER_IP=$(curl -s ifconfig.me)
DOMAIN_IP=$(dig +short $DOMAIN)

if [ "$DOMAIN_IP" != "$SERVER_IP" ]; then
    echo "❌ Error: El dominio $DOMAIN no apunta a este servidor"
    echo "   Servidor IP: $SERVER_IP"
    echo "   Dominio IP:  $DOMAIN_IP"
    echo "💡 Configura el registro A de tu dominio apuntando a $SERVER_IP"
    exit 1
fi

echo "✅ Dominio configurado correctamente"

# =============================================================================
# 2. INSTALAR CERTBOT
# =============================================================================

echo ""
echo "📦 Instalando Certbot para Let's Encrypt..."

# Instalar software-properties-common si no existe
sudo apt update
sudo apt install -y software-properties-common

# Agregar repositorio oficial de Certbot
sudo add-apt-repository -y ppa:certbot/certbot
sudo apt update

# Instalar Certbot para Nginx
sudo apt install -y certbot python3-certbot-nginx

# =============================================================================
# 3. CONFIGURAR FIREWALL
# =============================================================================

echo ""
echo "🔥 Configurando firewall..."

# Permitir puertos HTTP y HTTPS
sudo ufw allow 80
sudo ufw allow 443

# Verificar estado del firewall
sudo ufw status

# =============================================================================
# 4. OBTENER CERTIFICADO SSL
# =============================================================================

echo ""
echo "🔐 Obteniendo certificado SSL..."

# Obtener certificado con Nginx
sudo certbot --nginx -d $DOMAIN

if [ $? -eq 0 ]; then
    echo "✅ Certificado SSL obtenido exitosamente"
else
    echo "❌ Error obteniendo certificado SSL"
    exit 1
fi

# =============================================================================
# 5. CONFIGURAR RENOVACIÓN AUTOMÁTICA
# =============================================================================

echo ""
echo "🔄 Configurando renovación automática..."

# Crear script de renovación
sudo tee /etc/cron.weekly/certbot-renew << EOF
#!/bin/bash
certbot renew --quiet --post-hook "systemctl reload nginx"
EOF

sudo chmod +x /etc/cron.weekly/certbot-renew

# Probar renovación
sudo certbot renew --dry-run

# =============================================================================
# 6. VERIFICACIÓN FINAL
# =============================================================================

echo ""
echo "✅ Verificación final..."

# Verificar estado de servicios
sudo systemctl status nginx --no-pager

# Verificar certificado
sudo certbot certificates

# =============================================================================
# 7. FINALIZACIÓN
# =============================================================================

echo ""
echo "🎉 ¡CONFIGURACIÓN SSL COMPLETADA!"
echo ""
echo "📊 Información del despliegue:"
echo "   🌐 Dominio: $DOMAIN"
echo "   🔒 SSL: Configurado con Let's Encrypt"
echo "   📅 Renovación: Automática (semanal)"
echo "   🌍 Acceso público: https://$DOMAIN"
echo ""
echo "🔧 Comandos útiles:"
echo "   🔍 Ver certificados: sudo certbot certificates"
echo "   🔄 Renovar manual: sudo certbot renew"
echo "   📋 Ver logs: sudo tail -f /var/log/letsencrypt/letsencrypt.log"
echo ""
echo "✅ ¡Tu aplicación ahora es accesible públicamente con HTTPS!"
echo ""
echo "🚀 Accede a tu aplicación en: https://$DOMAIN"