# Mejoras al Módulo de Citas

## Resumen
Se han aplicado mejoras significativas al módulo de Citas para hacerlo más funcional, limpio y fácil de mantener.

## Cambios Realizados

### 1. Limpieza de Código Legado

#### `Cita.php` (Modelo)
**Eliminado:**
-   Relaciones `productosUtilizados()`, `productosVendidos()`, `serviciosRealizados()` (tablas pivot antiguas)
-   Atributos `productos_utilizados`, `productos_vendidos`, `monto_productos_vendidos`, `requiere_venta`, `venta_id`, `activo` de `$fillable`
-   Casts para `productos_utilizados`, `productos_vendidos`, `monto_productos_vendidos`, `requiere_venta`

**Mantenido:**
-   Relación `items()` - fuente de verdad para productos y servicios
-   Relaciones polimórficas `productos()` y `servicios()`
-   Todos los scopes y métodos de utilidad

### 2. Mejoras en el Flujo de Estados

#### `CitaController.php`
**Agregado:**
-   Método `changeStatus(Request $request, Cita $cita)` - Endpoint AJAX para cambios rápidos de estado
    -   Valida transiciones de estado usando el método `cambiarEstado()` del modelo
    -   Intenta convertir a venta automáticamente al completar (si falla, no bloquea el cambio de estado)
    -   Retorna respuestas JSON para integración con UI

#### `Show.vue`
**Agregado:**
-   Sección "Acciones de Estado" con botones contextuales:
    -   **Iniciar Cita** (visible cuando estado = 'pendiente')
    -   **Completar Cita** (visible cuando estado = 'en_proceso')
    -   **Cancelar Cita** (visible para estados 'pendiente', 'en_proceso', 'programado')
    -   **Editar** (siempre visible)
-   Método `cambiarEstado(nuevoEstado)` que llama al endpoint AJAX
-   Confirmación antes de cambiar estado
-   Recarga automática de página tras cambio exitoso

**Eliminado:**
-   Campos obsoletos "¿Requiere Venta?" y "Monto de Productos Vendidos"

## Beneficios
1.  **Código más limpio**: Eliminación de relaciones y atributos no utilizados
2.  **Mejor UX**: Cambios de estado con un solo clic desde la vista de detalles
3.  **Consistencia**: Uso exclusivo de `CitaItem` para productos/servicios
4.  **Mantenibilidad**: Menos código duplicado y lógica más clara

## Archivos Modificados
-   `app/Models/Cita.php`
-   `app/Http/Controllers/CitaController.php`
-   `resources/js/Pages/Citas/Show.vue`

## Próximos Pasos (Pendientes)
-   Implementar vista de calendario en `Index.vue`
-   Agregar ruta para `changeStatus` en `web.php` (si no existe)
