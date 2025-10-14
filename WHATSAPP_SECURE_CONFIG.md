# 🔐 Configuración Segura de WhatsApp Business API

## 🚨 Seguridad Primero

**NUNCA** comprometas información sensible como tokens de acceso en el código fuente o archivos de configuración que se suban a repositorios.

## ✅ Arquitectura de Configuración Segura

### **Prioridad de Carga (de más segura a menos segura):**

1. **🏆 Variables de Entorno (.env)** - Más seguro para producción
2. **💾 Base de Datos (Empresa model)** - Seguro, encriptado
3. **🔧 Archivo de Desarrollo (whatsapp.dev.json)** - Solo desarrollo local

### **Ejemplo de Jerarquía:**
```php
// 1. Variables de entorno (prioridad máxima)
WHATSAPP_ACCESS_TOKEN=token_desde_env

// 2. Base de datos (si no hay en env)
empresa.whatsapp_access_token = encrypt('token_desde_bd')

// 3. Archivo desarrollo (solo en local)
whatsapp.dev.json = { "access_token": "token_dev" }
```

## 📋 Configuración Paso a Paso

### **Paso 1: Variables de Entorno (.env)**

```bash
# Copia el archivo de ejemplo
cp .env.example .env

# Edita .env y agrega configuración de WhatsApp
WHATSAPP_BUSINESS_ACCOUNT_ID=tu_business_account_id
WHATSAPP_PHONE_NUMBER_ID=tu_phone_number_id
WHATSAPP_ACCESS_TOKEN=tu_access_token
```

### **Paso 2: Configuración en Base de Datos (Producción)**

```php
// En producción, configura en la interfaz web:
// http://127.0.0.1:8000/empresa/configuracion

// O directamente en la BD:
$empresa = App\Models\Empresa::first();
$empresa->whatsapp_business_account_id = 'tu_business_account_id';
$empresa->whatsapp_phone_number_id = 'tu_phone_number_id';
$empresa->whatsapp_access_token = encrypt('tu_access_token');
$empresa->save();
```

### **Paso 3: Archivo de Desarrollo (Solo Local)**

```json
// whatsapp.dev.json
{
  "empresa_1": {
    "business_account_id": "122996247574932",
    "phone_number_id": "181046901751521",
    "access_token": "EAAtsyGzO6SkBPoXDZCmAeFeE5mQasMSm1ENZC4d92YWlKZCHosDCiZCB18z1ajmfLgdaNmA23ZAJfUWk8QX8AzxeSdzJjbkFqvv0fXlIgdSF2brM5pRp9ssZB595GO2VuxlHZAHM4FDi9yTp0TlA7UPHe8JgWZAZBol6IoyZAOzu5YTn4e3LtQHxvduJoCOCi11gZDZD",
    "phone_number": "+15551005496"
  }
}
```

## 🛠️ Comandos de Gestión Segura

### **Ver Configuración Segura:**
```bash
# Muestra configuración sin exponer información sensible
php artisan whatsapp:show-config

# Con detalles sensibles (solo desarrollo)
php artisan whatsapp:show-config --show-sensitive
```

### **Validar Token:**
```bash
php artisan whatsapp:validate-token
```

### **Limpiar Caché:**
```bash
php artisan whatsapp:clear-cache
```

### **Diagnosticar Problemas:**
```bash
php artisan whatsapp:diagnose-connection
```

## 🔒 Mejores Prácticas de Seguridad

### **✅ Producción:**
- 🔐 **Tokens encriptados** en base de datos
- 🌐 **Variables de entorno** para configuración
- 🚫 **Sin archivos de configuración** con información sensible
- 📊 **Logs sin información sensible**

### **✅ Desarrollo:**
- 📁 **Archivo separado** (`whatsapp.dev.json`)
- 🔒 **Tokens encriptados** también en desarrollo
- 🚫 **No comprometer** archivos de configuración
- 📝 **Comentarios claros** sobre seguridad

### **❌ Evitar:**
- 🚫 **Tokens en código fuente**
- 🚫 **Información sensible en logs**
- 🚫 **Archivos de configuración en repositorios**
- 🚫 **Compartir tokens por email/mensajes**

## 📊 Monitoreo de Seguridad

### **Logs Seguros:**
```php
// ✅ Información segura en logs
'phone_number_id' => '1810469****521'
'token_length' => 201
'token_prefix' => 'EAAt****'

// ❌ Información sensible en logs
'phone_number_id' => '181046901751521'
'access_token' => 'EAAtsyGzO6SkBPoXDZCmAeFeE5mQasMSm1ENZC4d92YWlKZCHosDCiZCB18z1ajmfLgdaNmA23ZAJfUWk8QX8AzxeSdzJjbkFqvv0fXlIgdSF2brM5pRp9ssZB595GO2VuxlHZAHM4FDi9yTp0TlA7UPHe8JgWZAZBol6IoyZAOzu5YTn4e3LtQHxvduJoCOCi11gZDZD'
```

### **Validación Automática:**
```php
// El sistema valida automáticamente:
// ✅ Formato de token correcto
// ✅ Permisos necesarios
// ✅ Asociación con Phone Number ID
// ✅ Encriptación activa
```

## 🚨 Resolución de Problemas de Seguridad

### **Token Expuesto:**
```bash
# 1. Generar nuevo token inmediatamente
php artisan whatsapp:validate-token

# 2. Invalidar token anterior en Meta Business
# 3. Limpiar caché
php artisan optimize:clear

# 4. Verificar que no esté en logs
grep -r "EAAt" storage/logs/
```

### **Configuración Incompleta:**
```bash
# Verificar configuración segura
php artisan whatsapp:show-config

# Completar configuración faltante
# En .env o en interfaz web
```

## 🎯 Configuración por Entorno

### **Desarrollo Local:**
```bash
# Usa archivo whatsapp.dev.json
WHATSAPP_DEV_CONFIG_FILE=whatsapp.dev.json

# Tokens encriptados en BD
php artisan tinker
$empresa->whatsapp_access_token = encrypt('token_dev');
```

### **Producción:**
```bash
# Todo en variables de entorno
WHATSAPP_BUSINESS_ACCOUNT_ID=prod_business_id
WHATSAPP_PHONE_NUMBER_ID=prod_phone_id
WHATSAPP_ACCESS_TOKEN=prod_token

# Encriptación obligatoria
WHATSAPP_ENCRYPT_TOKENS=true
```

## 📚 Recursos Adicionales

- 📋 `WHATSAPP_TOKEN_GUIDE.md` - Gestión detallada de tokens
- 📱 `WHATSAPP_TEMPLATE_GUIDE.md` - Configuración de plantillas
- 🔧 Scripts de utilidad en raíz del proyecto

---
**Recuerda**: La seguridad es un proceso continuo. Revisa periódicamente que no haya información sensible expuesta.