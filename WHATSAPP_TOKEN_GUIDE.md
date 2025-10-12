# 📱 Guía de Configuración de WhatsApp Business API

## 🚨 Problema Actual
**Token expirado**: El token de acceso expiró el 11-Oct-25 18:00:00 PDT

## ✅ Solución Inmediata

### 1. Generar Nuevo Token

**Opción A: System User (Recomendado - Larga duración)**
```bash
# 1. Ir a Meta Business Manager
# 2. Business Settings > Users > System Users
# 3. "Add" > Crear nuevo System User
# 4. "Generate New Token"
# 5. Seleccionar tu App
# 6. Permisos requeridos:
#    - whatsapp_business_messaging
#    - business_management
# 7. Copiar el token generado
```

**Opción B: Token de Usuario (Caduca en 60 días)**
```bash
# 1. Ir a Facebook Developers
# 2. Tu App > Messenger > Settings
# 3. "Generate Access Token"
# 4. Seleccionar permisos necesarios
```

### 2. Actualizar Token en la Aplicación

**Método 1: Interfaz Web**
```bash
# 1. Ir a: http://127.0.0.1:8000/empresa/configuracion
# 2. Buscar sección "WhatsApp Business"
# 3. Pegar nuevo token en "Access Token"
# 4. Guardar cambios
```

**Método 2: Línea de comandos**
```bash
php artisan tinker
```
```php
$empresa = App\Models\Empresa::first();
$empresa->whatsapp_access_token = encrypt('TU_NUEVO_TOKEN');
$empresa->save();
echo "Token actualizado exitosamente";
```

### 3. Validar Token
```bash
php artisan whatsapp:validate-token
```

### 4. Limpiar Caché
```bash
php artisan optimize:clear
```

## 🔧 Configuración Técnica

### Tokens de System User (Recomendado)

**Ventajas:**
- ✅ Duración: Hasta 1 año
- ✅ No expira automáticamente
- ✅ Más seguros
- ✅ Ideales para producción

**Desventajas:**
- ❌ Requiere configuración inicial más compleja
- ❌ Necesita permisos específicos

### Tokens de Usuario

**Ventajas:**
- ✅ Fácil de generar
- ✅ Rápido de configurar

**Desventajas:**
- ❌ Expira en 60 días
- ❌ Puede caducar inesperadamente
- ❌ Menos seguro

## 🛠️ Herramientas Disponibles

### Comandos Artisan

```bash
# Validar token actual
php artisan whatsapp:validate-token

# Diagnosticar conexión completa
php artisan whatsapp:diagnose-connection

# Probar formateo de números
php artisan whatsapp:test-phone-formatting

# Configurar WhatsApp inicial
php artisan whatsapp:configurar
```

### Scripts de Utilidad

```bash
# Ver configuración actual
php check_whatsapp_config.php

# Probar payload de WhatsApp
php test_whatsapp_payload.php

# Corregir plantilla
php fix_whatsapp_template.php
```

## 📋 Checklist de Configuración

- [ ] ✅ Generé nuevo token de acceso
- [ ] ✅ Actualicé token en la aplicación
- [ ] ✅ Validé token con `php artisan whatsapp:validate-token`
- [ ] ✅ Verifiqué Phone Number ID en Meta Business
- [ ] ✅ Confirmé que la plantilla existe y está aprobada
- [ ] ✅ Probé envío con número real

## 🚨 Manejo de Errores

### Error 190: Token Inválido/Expirado
```php
// El sistema detectará automáticamente este error
// y lo registrará en los logs para fácil identificación
```

### Error 100: Número No Válido
```php
// Números deben estar en formato E.164
// Ejemplo: +52551234567
```

### Error 1300: Plantilla No Encontrada
```php
// Verificar que la plantilla exista en Meta Business
// y esté en estado "Aprobada"
```

## 🔒 Seguridad

### Encriptación de Tokens
```php
// Los tokens se encriptan automáticamente en la BD
$empresa->whatsapp_access_token = encrypt('token_sin_encriptar');
```

### Validación Automática
```php
// El sistema valida automáticamente:
// - Formato del token
// - Permisos necesarios
// - Asociación con Phone Number ID
```

## 📞 Soporte

Si persisten los problemas:
1. Verificar logs: `storage/logs/laravel.log`
2. Ejecutar diagnóstico: `php artisan whatsapp:diagnose-connection`
3. Validar token: `php artisan whatsapp:validate-token`

## 🎯 Próximos Pasos

1. **Inmediato**: Generar y actualizar token
2. **Corto plazo**: Configurar System User para evitar expiraciones
3. **Mediano plazo**: Implementar monitoreo automático de tokens
4. **Largo plazo**: Sistema de rotación automática de tokens

---
*Última actualización: 11-Oct-25*
*Estado: Token expirado - Requiere actualización inmediata*