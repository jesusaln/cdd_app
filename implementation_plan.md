# Plan de Mejora: Módulo de Citas

## Objetivo
Mejorar la funcionalidad y mantenibilidad del módulo de Citas eliminando código legado, agregando una vista de Calendario y optimizando la experiencia del usuario.

## Revisión del Usuario Requerida
> [!IMPORTANT]
> **Limpieza de Datos Legados**: Eliminaremos las relaciones `productosUtilizados`, `productosVendidos` y `serviciosRealizados` del modelo `Cita`. Asegúrese de que todos los datos activos hayan sido migrados a la estructura `cita_items` (esto se realizó en la corrección anterior).
> **Columnas de Base de Datos**: Ignoraremos/desaprobaremos las columnas JSON `productos_utilizados` y `productos_vendidos` en la tabla `citas` si existen, ya que los datos ahora están normalizados en `cita_items`.

## Cambios Propuestos

### 1. Limpieza y Refactorización
#### [MODIFICAR] [Cita.php](file:///c:/laragon/www/cdd-app/app/Models/Cita.php)
-   Eliminar relaciones `productosUtilizados`, `productosVendidos`, `serviciosRealizados`.
-   Eliminar `productos_utilizados`, `productos_vendidos` de `$fillable` y `$casts`.
-   Mantener `items()`, `productos()` y `servicios()` como la fuente de verdad.

#### [MODIFICAR] [CitaController.php](file:///c:/laragon/www/cdd-app/app/Http/Controllers/CitaController.php)
-   Asegurar que ningún método dependa de las relaciones eliminadas.
-   Limpiar importaciones no utilizadas o métodos privados relacionados con la lógica antigua.

### 2. Nueva Característica: Vista de Calendario
#### [MODIFICAR] [Index.vue](file:///c:/laragon/www/cdd-app/resources/js/Pages/Citas/Index.vue)
-   Agregar un interruptor entre "Vista de Lista" y "Vista de Calendario".
-   Implementar una cuadrícula de calendario mensual/semanal mostrando las citas.
-   Codificar por colores las citas según el estado (usando los colores definidos en el Modelo).
-   Permitir hacer clic en un evento para ver detalles (`Show.vue`).

### 3. Mejoras de UX: Flujo de Estados
#### [MODIFICAR] [Show.vue](file:///c:/laragon/www/cdd-app/resources/js/Pages/Citas/Show.vue)
-   Agregar botones de acción destacados para transiciones de estado basadas en el estado actual (ej. "Iniciar Cita", "Completar", "Cancelar").
-   Asegurar que "Convertir a Venta" solo esté disponible cuando sea apropiado.

#### [MODIFICAR] [CitaController.php](file:///c:/laragon/www/cdd-app/app/Http/Controllers/CitaController.php)
-   Agregar un método `changeStatus` para manejar actualizaciones rápidas de estado vía API/AJAX desde la UI.

## Plan de Verificación
### Verificación Manual
1.  **Calendario**: Verificar que las citas aparezcan en las fechas y horas correctas.
2.  **Estado**: Verificar que al hacer clic en los botones de estado se actualice el estado de la cita correctamente y se refleje en la UI inmediatamente.
3.  **Legado**: Verificar que crear/editar citas siga funcionando correctamente sin las relaciones legadas.
