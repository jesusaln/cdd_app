# Módulo de Configuración de Empresa

Este módulo permite personalizar completamente la información y apariencia de tu sistema CDD.

## Características

### ✅ Información de Empresa
- Nombre de la empresa
- RFC y razón social
- Dirección completa
- Información de contacto (teléfono, email, sitio web)
- Descripción de la empresa

### ✅ Personalización Visual
- Logo de la empresa (para el sistema)
- Favicon personalizado
- Logo específico para reportes y documentos
- Colores principales y secundarios del sistema
- Vista previa de colores en tiempo real

### ✅ Configuración de Documentos
- Porcentaje de IVA configurable
- Moneda del sistema
- Formato de números (español, inglés, alemán)
- Pie de página personalizado para facturas
- Pie de página personalizado para cotizaciones
- Términos y condiciones generales
- Política de privacidad

### ✅ Configuración del Sistema
- Formato de fecha y hora
- Modo de mantenimiento
- Registro de usuarios
- Notificaciones por email
- Configuración de backups automáticos
- Retención de backups

### ✅ Seguridad
- Número de intentos de login permitidos
- Tiempo de bloqueo por intentos fallidos
- Autenticación de dos factores (2FA)

## Instalación

1. **Ejecutar la migración:**
   ```bash
   php artisan migrate
   ```

2. **Crear configuración inicial:**
   ```php
   \App\Models\EmpresaConfiguracion::inicializarConfiguracionPorDefecto();
   ```

3. **Acceder al módulo:**
   - Ve a `/empresa/configuracion` en tu aplicación
   - Solo usuarios con permisos de administrador pueden acceder

## Uso Básico

### En Controladores

```php
<?php

use App\Http\Controllers\Concerns\ConfiguracionEmpresa;

class MiControlador extends Controller
{
    use ConfiguracionEmpresa;

    public function generarFactura($id)
    {
        $factura = Factura::findOrFail($id);

        // Obtener configuración completa
        $configuracion = $this->getConfiguracionEmpresa();

        // Obtener solo información básica
        $infoEmpresa = $this->getInfoEmpresa();

        // Formatear moneda según configuración
        $totalFormateado = $this->formatearMoneda($factura->total);

        // Formatear fecha según configuración
        $fechaFormateada = $this->formatearFecha($factura->fecha);

        return view('factura', compact('factura', 'configuracion'));
    }
}
```

### En Vistas Blade

```php
{{-- Usar colores de empresa --}}
<style>
    .btn-primary {
        background-color: {{ $empresaConfig->color_principal }};
    }
    .btn-secondary {
        background-color: {{ $empresaConfig->color_secundario }};
    }
</style>

{{-- Información de empresa --}}
<div class="empresa-info">
    <h2>{{ $empresaConfig->nombre_empresa }}</h2>
    <p>{{ $empresaConfig->direccion_completa }}</p>
    <p>Tel: {{ $empresaConfig->telefono }}</p>
    <p>Email: {{ $empresaConfig->email }}</p>
</div>

{{-- Logo de empresa --}}
@if($empresaConfig->logo_url)
    <img src="{{ $empresaConfig->logo_url }}" alt="Logo" class="empresa-logo">
@endif

{{-- Formateo de moneda --}}
<p>Total: {{ \App\Services\EmpresaConfiguracionService::formatearMoneda($total) }}</p>

{{-- Formateo de fecha --}}
<p>Fecha: {{ \App\Services\EmpresaConfiguracionService::formatearFecha($fecha) }}</p>
```

### En Componentes Vue.js

```javascript
// Cargar configuración de empresa
const empresaConfig = ref({
  nombre_empresa: 'CDD Sistema',
  color_principal: '#3B82F6',
  logo_url: null,
});

const cargarConfiguracion = async () => {
  try {
    const response = await axios.get('/empresa/configuracion/api');
    empresaConfig.value = response.data.configuracion;

    // Aplicar colores dinámicamente
    aplicarColoresDinamicos();
  } catch (error) {
    console.error('Error al cargar configuración:', error);
  }
};

const aplicarColoresDinamicos = () => {
  const root = document.documentElement;
  root.style.setProperty('--empresa-primary', empresaConfig.value.color_principal);
  root.style.setProperty('--empresa-secondary', empresaConfig.value.color_secundario);
};
```

## Configuración de Documentos

### Plantilla de Factura

El módulo incluye una plantilla de factura (`resources/views/factura.blade.php`) que utiliza automáticamente:

- Información de empresa (nombre, dirección, contacto)
- Logo de empresa
- Colores del sistema
- Configuración financiera (IVA, moneda)
- Pie de página personalizado
- Formateo de números y fechas

### Ejemplo de Uso en Facturas

```php
<?php

use App\Services\EmpresaConfiguracionService;

class FacturaController extends Controller
{
    public function generarPDF($id)
    {
        $factura = Factura::with(['cliente', 'productos'])->findOrFail($id);

        // Obtener configuración completa para PDF
        $configuracion = EmpresaConfiguracionService::getConfiguracionParaPDF();

        $pdf = PDF::loadView('factura', [
            'factura' => $factura,
            'configuracion' => $configuracion,
        ]);

        return $pdf->download("factura-{$factura->numero_venta}.pdf");
    }
}
```

## Configuración de Seguridad

### Modo de Mantenimiento

```php
// Verificar si el sistema está en mantenimiento
if (EmpresaConfiguracionService::enMantenimiento()) {
    return response()->view('mantenimiento', [
        'mensaje' => EmpresaConfiguracionService::getMensajeMantenimiento()
    ]);
}
```

### Verificación de Permisos

```php
// Verificar permisos de administrador
if (!EmpresaConfiguracionService::puedeEditarConfiguracion()) {
    abort(403, 'No tienes permisos para editar la configuración');
}
```

## Configuración Inicial

Al instalar el módulo, se crea automáticamente una configuración por defecto. Puedes modificarla desde la interfaz web o directamente en la base de datos.

### Valores por Defecto

```php
[
    'nombre_empresa' => 'CDD - Sistema de Gestión',
    'rfc' => 'XAXX010101000',
    'razon_social' => 'Empresa de Ejemplo S.A. de C.V.',
    'color_principal' => '#3B82F6',
    'color_secundario' => '#1E40AF',
    'iva_porcentaje' => 16.00,
    'moneda' => 'MXN',
    'formato_fecha' => 'd/m/Y',
    'formato_hora' => 'H:i:s',
]
```

## API Endpoints

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/empresa/configuracion` | Mostrar formulario de configuración |
| POST | `/empresa/configuracion` | Actualizar configuración |
| POST | `/empresa/configuracion/logo` | Subir logo |
| POST | `/empresa/configuracion/favicon` | Subir favicon |
| POST | `/empresa/configuracion/logo-reportes` | Subir logo para reportes |
| DELETE | `/empresa/configuracion/logo` | Eliminar logo |
| GET | `/empresa/configuracion/api` | Obtener configuración (JSON) |

## Servicios Disponibles

### EmpresaConfiguracionService

```php
// Obtener configuración completa
$config = \App\Services\EmpresaConfiguracionService::getConfiguracion();

// Obtener información básica
$info = \App\Services\EmpresaConfiguracionService::getInfoEmpresa();

// Formatear moneda
$formateado = \App\Services\EmpresaConfiguracionService::formatearMoneda(1234.56);

// Formatear fecha
$fecha = \App\Services\EmpresaConfiguracionService::formatearFecha(now());

// Verificar mantenimiento
$mantenimiento = \App\Services\EmpresaConfiguracionService::enMantenimiento();
```

## Personalización Avanzada

### Agregar Nuevos Campos

1. **Agregar a la migración:**
   ```php
   $table->string('nuevo_campo')->nullable();
   ```

2. **Agregar al modelo:**
   ```php
   protected $fillable = ['nuevo_campo'];
   ```

3. **Agregar al servicio:**
   ```php
   public static function getNuevoCampo() {
       $config = self::getConfiguracion();
       return $config->nuevo_campo;
   }
   ```

### Crear Nuevas Pestañas en la Configuración

1. **Agregar pestaña en el componente Vue:**
   ```javascript
   const tabs = [
     // ... pestañas existentes
     { id: 'nueva', nombre: 'Nueva Configuración', icono: 'nuevo-icono' }
   ];
   ```

2. **Agregar contenido en la plantilla:**
   ```vue
   <div v-if="activeTab === 'nueva'" class="space-y-8">
     <!-- Contenido de la nueva pestaña -->
   </div>
   ```

## Mantenimiento y Backups

El módulo incluye configuración automática de backups:

- **Backups automáticos:** Se pueden habilitar/deshabilitar
- **Frecuencia:** Configurable (días entre backups)
- **Retención:** Días de retención de backups antiguos
- **Limpieza automática:** Se ejecuta según la configuración

## Seguridad y Permisos

- Solo usuarios con rol de administrador pueden acceder a la configuración
- Todas las acciones quedan registradas en la bitácora
- Los cambios se aplican inmediatamente en toda la aplicación
- Los colores se aplican dinámicamente sin recargar la página

## Soporte y Ayuda

Para soporte técnico o personalizaciones adicionales, contacta al equipo de desarrollo.

---

**Versión:** 1.0.0
**Última actualización:** Octubre 2025
**Compatible con:** Laravel 10+, Inertia.js, Vue.js 3
