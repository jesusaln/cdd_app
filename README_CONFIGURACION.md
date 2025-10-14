# Configuración para Red Local - Laravel + Vite

Este proyecto está configurado específicamente para funcionar en redes locales de manera robusta y estable.

## Configuración Principal

### `.env` (Configuración Híbrida para Red Local)
- **Laravel**: `http://localhost:8000` (URLs válidas) + escucha en `0.0.0.0:8000` (acepta cualquier IP)
- **Vite HMR**: `http://localhost:5173` (WebSocket estable para desarrollo)
- **Uso**: Desarrollo local + acceso desde dispositivos en la misma red
- **Ventaja**: URLs válidas + acceso universal en red local

## Cómo Usar

### Inicio Rápido
```bash
# Ejecutar servidores
npm start
```

### Acceso Desde Diferentes Ubicaciones
Una vez ejecutándose, puedes acceder desde:

- **Misma máquina**: `http://localhost:8000`
- **Otros dispositivos**: `http://[IP-DEL-SERVIDOR]:8000`
- **Red local**: `http://192.168.1.106:8000` (ejemplo)

## Cómo Funciona la Configuración Híbrida

Esta configuración resuelve el problema de URLs inválidas con `0.0.0.0`:

### El Problema
- ❌ `http://0.0.0.0:8000/storage/...` → Error ERR_ADDRESS_INVALID
- ❌ Los navegadores no pueden resolver `0.0.0.0` como URL válida

### La Solución Híbrida
- ✅ **APP_URL**: `http://localhost:8000` (URLs válidas para navegador)
- ✅ **Laravel Server**: `--host=0.0.0.0` (acepta conexiones de cualquier IP)
- ✅ **Resultado**: Funciona localmente y remotamente sin errores

## Características Técnicas

### Laravel Server
- Ejecuta con: `php artisan serve --host=0.0.0.0 --port=8000` (escucha en todas las interfaces)
- **APP_URL**: `http://localhost:8000` (genera URLs válidas para el navegador)
- Acepta conexiones desde cualquier IP en la red local
- Responde correctamente sin importar el origen de la petición

### Vite HMR (Hot Module Replacement)
- WebSocket estable en `localhost:5173`
- El navegador resuelve automáticamente `localhost` a `127.0.0.1`
- Funciona desde cualquier ubicación de acceso

### Configuración de Red
- **Puertos abiertos**: 8000 (Laravel) y 5173 (Vite)
- **Interfaces**: Todas las disponibles (`0.0.0.0`)
- **Firewall**: Asegúrate de permitir estos puertos

## Comandos Útiles

```bash
# Iniciar servidores
npm start

# O ejecutar manualmente
npm run laravel  # Laravel en 0.0.0.0:8000
npm run dev      # Vite con HMR en localhost:5173

# Verificar configuración actual
type .env | findstr -E "(APP_URL|VITE_DEV_SERVER_URL)"

# Reiniciar si es necesario
# Detener con Ctrl+C y ejecutar nuevamente
npm start
```

## Troubleshooting

### Problemas comunes:

1. **Error de conexión WebSocket**
    - La configuración híbrida resuelve automáticamente este problema
    - El navegador se conecta a `localhost:5173` para HMR
    - Laravel responde desde cualquier IP en el puerto 8000

2. **Error ERR_ADDRESS_INVALID con 0.0.0.0**
    - **Problema**: `0.0.0.0` no es una URL válida para navegadores
    - **Solución**: Usar `localhost` en APP_URL para URLs válidas
    - **Laravel sigue escuchando**: En `0.0.0.0:8000` para aceptar conexiones externas

2. **No se puede acceder desde dispositivos externos**
   - Verificar que los puertos 8000 y 5173 estén abiertos en el firewall
   - Confirmar que ZeroTier esté conectado correctamente
   - Probar con `ping` desde el dispositivo remoto

3. **Servidores no responden**
   - Ejecutar: `npm start`
   - Verificar procesos: `netstat -ano | findstr :8000`

## Configuración de Producción

Para producción, considera:

1. Usar un servidor web (Nginx/Apache) en lugar de `php artisan serve`
2. Configurar HTTPS/SSL con certificado válido
3. Usar dominios en lugar de IPs para mejor compatibilidad
4. Configurar variables de entorno específicas para producción

## Seguridad

- Nunca comprometas archivos `.env` en producción
- Usa HTTPS en producción para encriptar comunicaciones
- Configura firewalls para permitir solo puertos necesarios
- Considera usar VPN (como ZeroTier) para acceso remoto seguro
- Los archivos de respaldo están en `backup_env/` si necesitas restaurar configuraciones específicas
