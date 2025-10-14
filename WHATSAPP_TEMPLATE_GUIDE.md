# 📱 Guía de Configuración de Plantillas de WhatsApp

## 🚨 Problema Actual
**Error #132001**: `Template name does not exist in the translation`

La plantilla `recordatorio_de_instalacion` no existe en tu WhatsApp Business Account.

## ✅ Solución: Crear Plantilla en Meta Business

### **Paso 1: Acceder a Meta Business**
1. 🌐 Ve a: https://business.facebook.com/
2. 🔑 Inicia sesión con tu cuenta empresarial
3. 📱 Busca tu WhatsApp Business Account

### **Paso 2: Crear Nueva Plantilla**
1. 📋 Ve a: **Message Templates** (Plantillas de mensaje)
2. ➕ Haz clic en: **"Create Template"** (Crear plantilla)
3. 📝 Completa la información:

**Información Básica:**
```
Nombre de la plantilla: recordatorio_de_instalacion
Categoría: Marketing (o la que corresponda)
Idioma: Español (México) - es_MX
```

### **Paso 3: Configurar Contenido**

**Header (Encabezado):**
```
Tipo: Text
Texto: Recordatorio de Pago
```

**Body (Cuerpo del mensaje):**
```
Texto: Hola {{1}}, le recordamos que tiene un pago pendiente de {{2}} con fecha límite el {{3}}.

Variables:
{{1}} = Nombre del cliente
{{2}} = Monto del pago
{{3}} = Fecha de vencimiento
```

**Footer (Pie - Opcional):**
```
Texto: Gracias por su preferencia
```

**Buttons (Botones - Opcional):**
```
Tipo: Respuesta rápida
Texto: Marcar como pagado
```

### **Paso 4: Configurar Ejemplo**

**Para que Meta apruebe la plantilla, agrega ejemplos:**

```
Nombre del cliente: Juan Pérez
Monto del pago: $1,500.00
Fecha de vencimiento: 15/10/2025
```

### **Paso 5: Enviar para Aprobación**

1. ✅ Revisa toda la información
2. 📤 Haz clic en **"Submit for Approval"**
3. ⏳ Espera la aprobación (puede tomar minutos u horas)

## 🔧 Configuración Técnica

### **Estructura de la Plantilla JSON:**
```json
{
  "name": "recordatorio_de_instalacion",
  "language": "es_MX",
  "category": "MARKETING",
  "components": [
    {
      "type": "HEADER",
      "format": "TEXT",
      "text": "Recordatorio de Pago"
    },
    {
      "type": "BODY",
      "text": "Hola {{1}}, le recordamos que tiene un pago pendiente de {{2}} con fecha límite el {{3}}.",
      "example": {
        "body_text": [
          ["Juan Pérez"],
          ["$1,500.00"],
          ["15/10/2025"]
        ]
      }
    }
  ]
}
```

### **Estados de la Plantilla:**
- ⏳ **PENDING**: En revisión
- ✅ **APPROVED**: Lista para usar
- ❌ **REJECTED**: Necesita correcciones
- 🔄 **PAUSED**: Temporalmente desactivada

## 🛠️ Herramientas Disponibles

### **Comandos para Diagnosticar:**
```bash
# Listar plantillas disponibles
php artisan whatsapp:list-templates

# Validar token
php artisan whatsapp:validate-token

# Diagnóstico completo
php artisan whatsapp:diagnose-connection
```

### **Verificación con cURL:**
```bash
curl -X GET \
  "https://graph.facebook.com/v20.0/181046901751521/message_templates" \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Content-Type: application/json"
```

## 🚨 Solución de Problemas Comunes

### **Error: "Template name already exists"**
- ✅ Usa un nombre diferente
- ✅ O elimina la plantilla existente primero

### **Error: "Template rejected"**
- ✅ Revisa las políticas de Meta
- ✅ Evita palabras prohibidas
- ✅ Proporciona ejemplos claros

### **Error: "Invalid template format"**
- ✅ Verifica que todas las variables estén definidas
- ✅ Usa formato correcto para fechas y números

## 📋 Checklist de Creación

- [ ] ✅ Nombre único y descriptivo
- [ ] ✅ Categoría correcta (Marketing/Utility/Auth)
- [ ] ✅ Idioma correcto (es_MX)
- [ ] ✅ Variables definidas correctamente
- [ ] ✅ Ejemplos proporcionados
- [ ] ✅ Enviada para aprobación
- [ ] ✅ Estado "APPROVED"

## 🎯 Próximos Pasos Después de Crear

1. ✅ **Esperar aprobación** (puede tomar hasta 24 horas)
2. ✅ **Verificar estado**: `php artisan whatsapp:list-templates`
3. ✅ **Probar envío**: Usa un número real que haya chateado
4. ✅ **Monitorear logs**: Revisa `storage/logs/laravel.log`

## 💡 Consejos para Aprobación Rápida

### **✅ Buenas Prácticas:**
- 📝 **Nombres claros**: Usa nombres descriptivos
- 🎯 **Categoría correcta**: Marketing para promociones, Utility para transacciones
- 📱 **Contenido útil**: Proporciona valor real al usuario
- 🔗 **Sin spam**: Evita mensajes demasiado frecuentes

### **❌ Evitar:**
- 🚫 Lenguaje agresivo o urgente
- 💰 Promesas de dinero o premios
- 📢 Contenido exclusivamente publicitario
- 🔗 Enlaces sospechosos

## 🔄 Si la Plantilla es Rechazada

1. 📧 Revisa el email de Meta con razones del rechazo
2. ✏️ Corrige los problemas identificados
3. 📤 Envía nuevamente para aprobación
4. ⏳ Espera nueva revisión

---
**¿Necesitas ayuda con algún paso específico?**

**Recuerda**: La plantilla debe estar en estado **"APPROVED"** antes de poder usarla.