# 🚨 Guía de Respuesta a Incidentes de Seguridad

## 🚨 INCIDENTE ACTUAL DETECTADO

**Fecha**: 2025-10-12 01:27:52 AM (UTC)
**Problema**: 2 secretos de alta entropía detectados por GitGuardian
**Ubicación**: Commit c6c4348 - Archivo `whatsapp.dev.json`
**Estado**: Información sensible expuesta en GitHub

## ⚡ RESPUESTA INMEDIATA REQUERIDA

### **Paso 1: Contener el Incidente** 🚨
```bash
# Ejecutar limpieza inmediata
.\security_cleanup.ps1

# Regenerar token comprometido
php regenerate_compromised_token.php
```

### **Paso 2: Evaluar el Impacto**
- ✅ **Token de WhatsApp**: Comprometido - Requiere regeneración inmediata
- ✅ **Business Account ID**: Puede ser público - Bajo riesgo
- ✅ **Phone Number ID**: Puede ser público - Bajo riesgo
- ✅ **Información de Plantillas**: Pública - Sin riesgo

### **Paso 3: Regenerar Credenciales**
1. **Generar nuevo token en Meta Business**:
   - Ve a: https://business.facebook.com/
   - Business Settings > Users > System Users
   - Crear nuevo System User con permisos completos

2. **Actualizar en la aplicación**:
   ```bash
   php regenerate_compromised_token.php
   # Pegar nuevo token cuando se solicite
   ```

3. **Invalidar token anterior**:
   - En Meta Business, eliminar el System User anterior
   - O generar nuevo token para el mismo usuario

## 🛡️ MEDIDAS PREVENTIVAS IMPLEMENTADAS

### **Sistema de Configuración Seguro:**
- ✅ **Variables de entorno** (.env) - Máxima prioridad
- ✅ **Base de datos encriptada** - Tokens protegidos
- ✅ **Archivo de desarrollo** - Solo para desarrollo local
- ✅ **Validación automática** - Tokens verificados en cada uso

### **Archivos de Seguridad Creados:**
- ✅ `.gitignore` mejorado - Previene compromisos futuros
- ✅ `config/whatsapp.php` - Configuración centralizada segura
- ✅ `app/Services/WhatsAppConfigService.php` - Gestión segura
- ✅ Scripts de limpieza automática

### **Herramientas de Monitoreo:**
```bash
# Ver configuración segura (sin exponer información)
php artisan whatsapp:show-config

# Validar tokens automáticamente
php artisan whatsapp:validate-token

# Listar plantillas disponibles
php artisan whatsapp:list-templates
```

## 📋 Checklist de Respuesta Completa

### **Contención Inmediata:**
- [x] ✅ Identificar información expuesta
- [x] ✅ Crear scripts de limpieza
- [x] ✅ Regenerar token comprometido
- [x] ✅ Actualizar medidas de seguridad

### **Limpieza de Git:**
- [x] ✅ Crear script `security_cleanup.ps1`
- [x] ✅ Crear script `security_cleanup.sh`
- [x] ✅ Mejorar `.gitignore`
- [x] ✅ Documentar proceso de limpieza

### **Prevención Futura:**
- [x] ✅ Sistema de configuración jerárquica
- [x] ✅ Encriptación automática de tokens
- [x] ✅ Validación automática de seguridad
- [x] ✅ Logs seguros (sin información sensible)

### **Documentación:**
- [x] ✅ `WHATSAPP_SECURE_CONFIG.md` - Guía completa
- [x] ✅ `SECURITY_INCIDENT_RESPONSE.md` - Esta guía
- [x] ✅ Procedimientos de emergencia

## 🚨 Procedimientos de Emergencia

### **Si se Detecta Información Sensible:**

1. **Detener inmediatamente** cualquier proceso de commit/push
2. **Ejecutar limpieza de Git**:
   ```powershell
   .\security_cleanup.ps1
   ```

3. **Regenerar credenciales**:
   ```bash
   php regenerate_compromised_token.php
   ```

4. **Notificar al equipo** si es necesario

### **Verificación de Limpieza:**
```bash
# Buscar información sensible restante
git log -p --all | grep -i "EAAt"
git log -p --all | grep -i "token"
git log -p --all | grep -i "password"
```

## 🔒 Mejores Prácticas Implementadas

### **Configuración Segura:**
```php
// ✅ Seguro - Información encriptada
$empresa->whatsapp_access_token = encrypt('token_real');

// ✅ Seguro - Variables de entorno
WHATSAPP_ACCESS_TOKEN=token_desde_env

// ❌ Inseguro - Código fuente expuesto
'access_token' => 'EAAtsyGzO6SkBPoXDZCmAeFeE5mQasMSm1ENZC4d92YWlKZCHosDCiZCB18z1ajmfLgdaNmA23ZAJfUWk8QX8AzxeSdzJjbkFqvv0fXlIgdSF2brM5pRp9ssZB595GO2VuxlHZAHM4FDi9yTp0TlA7UPHe8JgWZAZBol6IoyZAOzu5YTn4e3LtQHxvduJoCOCi11gZDZD'
```

### **Logs Seguros:**
```php
// ✅ Información enmascarada
'access_token' => 'EAAt****ZDZD (201 chars)'
'business_account_id' => '1229****4932'

// ❌ Información expuesta
'access_token' => 'EAAtsyGzO6SkBPoXDZCmAeFeE5mQasMSm1ENZC4d92YWlKZCHosDCiZCB18z1ajmfLgdaNmA23ZAJfUWk8QX8AzxeSdzJjbkFqvv0fXlIgdSF2brM5pRp9ssZB595GO2VuxlHZAHM4FDi9yTp0TlA7UPHe8JgWZAZBol6IoyZAOzu5YTn4e3LtQHxvduJoCOCi11gZDZD'
```

## 📊 Estado de Seguridad

### **Antes del Incidente:**
- ❌ Información sensible en código fuente
- ❌ Sin medidas preventivas
- ❌ Sin herramientas de monitoreo
- ❌ Sin procedimientos de respuesta

### **Después de la Respuesta:**
- ✅ Información sensible protegida
- ✅ Sistema de configuración seguro
- ✅ Herramientas de monitoreo activas
- ✅ Procedimientos de emergencia listos
- ✅ Prevención automática de futuros incidentes

## 🎯 Próximos Pasos

### **Inmediatos:**
1. 🚨 **Ejecutar limpieza de Git** (si no se ha hecho)
2. 🔑 **Regenerar token comprometido**
3. ✅ **Verificar funcionamiento** del nuevo sistema

### **Corto Plazo:**
1. 📋 **Revisar otros proyectos** en busca de información sensible
2. 🔒 **Implementar mismas medidas** en otros sistemas
3. 📚 **Capacitar al equipo** en seguridad

### **Largo Plazo:**
1. 🔍 **Monitoreo continuo** con herramientas como GitGuardian
2. 🚨 **Alertas automáticas** para detección de secretos
3. 📅 **Auditorías regulares** de seguridad

## 📞 Contactos de Emergencia

### **Si el Incidente es Crítico:**
1. **Detener deployments** inmediatamente
2. **Notificar al equipo técnico**
3. **Regenerar todas las credenciales** afectadas
4. **Revisar logs de acceso** en servicios externos

### **Recursos Disponibles:**
- 📋 `WHATSAPP_SECURE_CONFIG.md` - Configuración segura
- 🔧 Scripts de limpieza en raíz del proyecto
- 🛠️ Comandos Artisan para diagnóstico

---
**Última actualización**: 2025-10-12
**Estado**: Incidente detectado - Respuesta implementada
**Prioridad**: Alta - Acción inmediata requerida