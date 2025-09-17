<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Producto;
use App\Models\Servicio;
use App\Enums\EstadoPedido;
use App\Enums\EstadoVenta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PedidoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pedidos = Pedido::with(['cliente', 'items.pedible', 'createdBy', 'updatedBy', 'deletedBy'])
            ->get()
            ->filter(function ($pedido) {
                // Filtrar pedidos con cliente y al menos un item válido
                return $pedido->cliente !== null && $pedido->items->isNotEmpty();
            })
            ->map(function ($pedido) {
                $items = $pedido->items->map(function ($item) {
                    $pedible = $item->pedible;
                    return [
                        'id' => $pedible->id,
                        'nombre' => $pedible->nombre ?? 'Sin nombre',
                        'tipo' => $item->pedible_type === Producto::class ? 'producto' : 'servicio',
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                        'descuento' => $item->descuento ?? 0,
                    ];
                });

                $createdAtIso = optional($pedido->created_at)->toIso8601String();
                $updatedAtIso = optional($pedido->updated_at)->toIso8601String();

                return [
                    'id' => $pedido->id,
                    'numero_pedido' => $pedido->numero_pedido,
                    'fecha' => $pedido->fecha ? $pedido->fecha->format('Y-m-d') : $pedido->created_at->format('Y-m-d'),
                    'created_at' => $createdAtIso,
                    'updated_at' => $updatedAtIso,
                    'cliente' => [
                        'id' => $pedido->cliente->id,
                        'nombre' => $pedido->cliente->nombre_razon_social ?? 'Sin nombre',
                        'email' => $pedido->cliente->email,
                        'telefono' => $pedido->cliente->telefono,
                        'rfc' => $pedido->cliente->rfc,
                        'regimen_fiscal' => $pedido->cliente->regimen_fiscal,
                        'uso_cfdi' => $pedido->cliente->uso_cfdi,
                        'calle' => $pedido->cliente->calle,
                        'numero_exterior' => $pedido->cliente->numero_exterior,
                        'numero_interior' => $pedido->cliente->numero_interior,
                        'colonia' => $pedido->cliente->colonia,
                        'codigo_postal' => $pedido->cliente->codigo_postal,
                        'municipio' => $pedido->cliente->municipio,
                        'estado' => $pedido->cliente->estado,
                        'pais' => $pedido->cliente->pais,
                    ],
                    'productos' => $items->toArray(),
                    'subtotal' => $pedido->subtotal,
                    'descuento_general' => $pedido->descuento_general,
                    'iva' => $pedido->iva,
                    'total' => $pedido->total,
                    'estado' => $pedido->estado->value,
                    'numero_pedido' => $pedido->numero_pedido,
                    'cotizacion_id' => $pedido->cotizacion_id,

                    // Auditoría
                    'creado_por_nombre' => $pedido->createdBy?->name,
                    'actualizado_por_nombre' => $pedido->updatedBy?->name,
                    'eliminado_por_nombre' => $pedido->deletedBy?->name,

                    // Redundancia segura para el modal
                    'metadata' => [
                        'creado_por' => $pedido->createdBy?->name,
                        'actualizado_por' => $pedido->updatedBy?->name,
                        'eliminado_por' => $pedido->deletedBy?->name,
                        'creado_en' => $createdAtIso,
                        'actualizado_en' => $updatedAtIso,
                        'eliminado_en' => optional($pedido->deleted_at)->toIso8601String(),
                    ],
                ];
            });

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos->values(),
            'estados' => collect(EstadoPedido::cases())->map(fn($estado) => [
                'value' => $estado->value,
                'label' => $estado->label(),
                'color' => $estado->color()
            ]),
            'filters' => request()->only(['search', 'estado', 'fecha_inicio', 'fecha_fin'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Pedidos/Create', [
            'clientes' => Cliente::select('id', 'nombre_razon_social', 'email', 'telefono')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_venta', 'descripcion')->get(),
            'servicios' => Servicio::select('id', 'nombre', 'precio', 'descripcion')->get(),
            'defaults' => [
                'fecha' => now()->format('Y-m-d'),
                'moneda' => 'MXN'
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
            'descuento_general' => 'nullable|numeric|min:0|max:100',
            'notas' => 'nullable|string',
        ]);

        $subtotal = 0;
        $descuentoItems = 0;
        foreach ($validated['productos'] as $item) {
            $subtotalItem = $item['cantidad'] * $item['precio'];
            $descuentoItems += $subtotalItem * ($item['descuento'] / 100);
            $subtotal += $subtotalItem;
        }

        $descuentoGeneralMonto = ($subtotal - $descuentoItems) * ($request->descuento_general / 100);
        $subtotalFinal = ($subtotal - $descuentoItems) - $descuentoGeneralMonto;
        $iva = $subtotalFinal * 0.16;
        $total = $subtotalFinal + $iva;

        $numero_pedido = $this->generarNumeroPedido();

        $pedido = Pedido::create([
            'cliente_id' => $validated['cliente_id'],
            'cotizacion_id' => null, // Puede llenarse si se crea desde una cotización
            'numero_pedido' => $numero_pedido,
            'subtotal' => $subtotal,
            'descuento_general' => $descuentoGeneralMonto,
            'iva' => $iva,
            'total' => $total,
            'fecha' => now(),
            'estado' => EstadoPedido::Borrador,
            'notas' => $request->notas,
        ]);

        foreach ($validated['productos'] as $item) {
            $class = $item['tipo'] === 'producto' ? Producto::class : Servicio::class;
            $modelo = $class::find($item['id']);

            if (!$modelo) {
                Log::warning("Ítem no encontrado: {$class} con ID {$item['id']}");
                continue;
            }

            $subtotalItem = $item['cantidad'] * $item['precio'];
            $descuentoMontoItem = $subtotalItem * ($item['descuento'] / 100);

            PedidoItem::create([
                'pedido_id' => $pedido->id,
                'pedible_id' => $item['id'],
                'pedible_type' => $class,
                'cantidad' => $item['cantidad'],
                'precio' => $item['precio'],
                'descuento' => $item['descuento'],
                'subtotal' => $subtotalItem,
                'descuento_monto' => $descuentoMontoItem,
            ]);
        }

        return redirect()->route('pedidos.index')->with('success', 'Pedido creado con éxito');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pedido = Pedido::with(['cliente', 'items.pedible'])->findOrFail($id);

        $items = $pedido->items->map(function ($item) {
            $pedible = $item->pedible;
            return [
                'id' => $pedible->id,
                'nombre' => $pedible->nombre ?? $pedible->descripcion,
                'tipo' => $item->pedible_type === Producto::class ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Pedidos/Show', [
            'pedido' => [
                'id' => $pedido->id,
                'cliente' => $pedido->cliente,
                'productos' => $items,
                'subtotal' => $pedido->subtotal,
                'descuento_general' => $pedido->descuento_general,
                'iva' => $pedido->iva,
                'total' => $pedido->total,
                'fecha' => $pedido->fecha ? $pedido->fecha->format('Y-m-d') : $pedido->created_at->format('Y-m-d'),
                'notas' => $pedido->notas,
                'estado' => $pedido->estado->value,
                'numero_pedido' => $pedido->numero_pedido,
                'cotizacion_id' => $pedido->cotizacion_id,
            ],
            'canEdit' => $pedido->estado === EstadoPedido::Borrador || $pedido->estado === EstadoPedido::Pendiente,
            'canDelete' => $pedido->estado === EstadoPedido::Borrador || $pedido->estado === EstadoPedido::Pendiente,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $pedido = Pedido::with(['cliente', 'items.pedible'])->findOrFail($id);

        // Permitir edición solo si está en Borrador o Pendiente
        if (!in_array($pedido->estado, [EstadoPedido::Borrador, EstadoPedido::Pendiente], true)) {
            return Redirect::route('pedidos.show', $pedido->id)
                ->with('warning', 'Solo pedidos en borrador o pendientes pueden ser editados');
        }

        $items = $pedido->items->map(function ($item) {
            $pedible = $item->pedible;
            return [
                'id' => $pedible->id,
                'nombre' => $pedible->nombre ?? $pedible->descripcion,
                'tipo' => $item->pedible_type === Producto::class ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Pedidos/Edit', [
            'pedido' => [
                'id' => $pedido->id,
                'cliente_id' => $pedido->cliente_id,
                'cliente' => $pedido->cliente,
                'productos' => $items,
                'subtotal' => $pedido->subtotal,
                'descuento_general' => $pedido->descuento_general,
                'iva' => $pedido->iva,
                'total' => $pedido->total,
                'fecha' => $pedido->fecha ? $pedido->fecha->format('Y-m-d') : $pedido->created_at->format('Y-m-d'),
                'notas' => $pedido->notas,
                'numero_pedido' => $pedido->numero_pedido,
                'cotizacion_id' => $pedido->cotizacion_id,
            ],
            'clientes' => Cliente::select('id', 'nombre_razon_social', 'email', 'telefono')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_venta', 'descripcion')->get(),
            'servicios' => Servicio::select('id', 'nombre', 'precio', 'descripcion')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        // Permitir edición solo si está en Borrador o Pendiente
        if (!in_array($pedido->estado, [EstadoPedido::Borrador, EstadoPedido::Pendiente], true)) {
            return Redirect::back()->with('error', 'Solo pedidos en borrador o pendientes pueden ser actualizados');
        }

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'numero_pedido' => 'required|string|unique:pedidos,numero_pedido,' . $pedido->id,
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
            'descuento_general' => 'nullable|numeric|min:0|max:100',
            'notas' => 'nullable|string',
        ]);

        $subtotal = 0;
        $descuentoItems = 0;
        foreach ($validated['productos'] as $item) {
            $subtotalItem = $item['cantidad'] * $item['precio'];
            $descuentoItems += $subtotalItem * ($item['descuento'] / 100);
            $subtotal += $subtotalItem;
        }

        $descuentoGeneralMonto = ($subtotal - $descuentoItems) * ($request->descuento_general / 100);
        $subtotalFinal = ($subtotal - $descuentoItems) - $descuentoGeneralMonto;
        $iva = $subtotalFinal * 0.16;
        $total = $subtotalFinal + $iva;

        // Guarda el estado ANTES de actualizar (clave del fix)
        $estadoAnterior = $pedido->estado;

        // Determinar el nuevo estado: si está en Borrador, cambiarlo a Pendiente
        $nuevoEstado = $pedido->estado === EstadoPedido::Borrador
            ? EstadoPedido::Pendiente
            : $pedido->estado;

        // Atomicidad: actualización + refresco de items
        DB::transaction(function () use (&$pedido, $validated, $subtotal, $descuentoGeneralMonto, $iva, $total, $nuevoEstado, $request) {
            $pedido->update([
                'cliente_id' => $validated['cliente_id'],
                'numero_pedido' => $validated['numero_pedido'],
                'subtotal' => $subtotal,
                'descuento_general' => $descuentoGeneralMonto,
                'iva' => $iva,
                'total' => $total,
                'fecha' => now(),
                'estado' => $nuevoEstado,
                'notas' => $request->notas,
            ]);

            // Eliminar ítems anteriores
            $pedido->items()->delete();

            // Guardar nuevos ítems
            foreach ($validated['productos'] as $itemData) {
                $class = $itemData['tipo'] === 'producto' ? Producto::class : Servicio::class;
                $modelo = $class::find($itemData['id']);

                if (!$modelo) {
                    Log::warning("Ítem no encontrado: {$class} con ID {$itemData['id']}");
                    continue;
                }

                $subtotalItem = $itemData['cantidad'] * $itemData['precio'];
                $descuentoMontoItem = $subtotalItem * ($itemData['descuento'] / 100);

                PedidoItem::create([
                    'pedido_id' => $pedido->id,
                    'pedible_id' => $itemData['id'],
                    'pedible_type' => $class,
                    'cantidad' => $itemData['cantidad'],
                    'precio' => $itemData['precio'],
                    'descuento' => $itemData['descuento'],
                    'subtotal' => $subtotalItem,
                    'descuento_monto' => $descuentoMontoItem,
                ]);
            }
        });

        // Mensaje basado en el estado ANTERIOR
        $mensajeExito = ($estadoAnterior === EstadoPedido::Borrador && $nuevoEstado === EstadoPedido::Pendiente)
            ? 'Pedido actualizado y cambiado a estado pendiente exitosamente'
            : 'Pedido actualizado exitosamente';

        return Redirect::route('pedidos.index')
            ->with('success', $mensajeExito);
    }

    /**
     * Cancel the specified resource (soft cancel).
     */
    public function cancel($id)
    {
        $pedido = Pedido::findOrFail($id);

        // Permitir cancelar en cualquier estado excepto ya cancelado
        if ($pedido->estado === EstadoPedido::Cancelado) {
            return Redirect::back()->with('error', 'El pedido ya está cancelado');
        }

        // Actualizar estado a cancelado y registrar quién lo canceló
        $pedido->update([
            'estado' => EstadoPedido::Cancelado,
            'deleted_by' => Auth::id(),
            'deleted_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido cancelado exitosamente',
            'eliminado_por' => Auth::user()->name ?? 'Usuario actual'
        ]);

        return Redirect::route('pedidos.index')
            ->with('success', 'Pedido cancelado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            try {
                $pedido = Pedido::with('cotizacion')->findOrFail($id);

                // Verificar que el pedido puede ser eliminado
                if (!in_array($pedido->estado, [EstadoPedido::Borrador, EstadoPedido::Pendiente], true)) {
                    return Redirect::back()->with('error', 'Solo pedidos en borrador o pendientes pueden ser eliminados');
                }

                // Guardar información de la cotización antes de eliminar
                $cotizacionId = $pedido->cotizacion_id;
                $cotizacion = $pedido->cotizacion;

                // Eliminar los items del pedido primero
                $pedido->items()->delete();

                // Eliminar el pedido
                $pedido->delete();

                // Revertir el estado de la cotización asociada DESPUÉS de eliminar el pedido
                if ($cotizacionId && $cotizacion) {
                    $cotizacion->estado = 'pendiente';
                    $cotizacion->save();

                    Log::info("Pedido ID {$id} eliminado y Cotización ID {$cotizacionId} revertida a estado pendiente");
                }

                return Redirect::route('pedidos.index')
                    ->with('success', 'Pedido eliminado exitosamente y cotización revertida a pendiente');
            } catch (\Exception $e) {
                Log::error('Error al eliminar pedido: ' . $e->getMessage());

                // La transacción se revertirá automáticamente
                return Redirect::back()
                    ->with('error', 'Error al eliminar el pedido: ' . $e->getMessage());
            }
        });
    }

    /**
     * Duplicate a pedido.
     */
    public function duplicate(Request $request, $id)
    {
        $pedido = Pedido::with('cliente', 'items.pedible')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Duplicar el pedido
            $nuevo = $pedido->replicate();
            $nuevo->estado = EstadoPedido::Borrador;
            $nuevo->numero_pedido = $this->generarNumeroPedido();
            $nuevo->fecha = now();
            $nuevo->created_at = now();
            $nuevo->updated_at = now();
            $nuevo->save();

            // Duplicar los ítems
            foreach ($pedido->items as $item) {
                $nuevo->items()->create([
                    'pedible_id' => $item->pedible_id,
                    'pedible_type' => $item->pedible_type,
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                    'subtotal' => $item->subtotal,
                    'descuento_monto' => $item->descuento_monto,
                ]);
            }

            DB::commit();

            return Redirect::route('pedidos.index')
                ->with('success', 'Pedido duplicado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicando pedido: ' . $e->getMessage());

            return Redirect::back()
                ->with('error', 'Error al duplicar el pedido.');
        }
    }

    /**
     * Convertir un pedido en venta.
     * - Se unifican nombres con VentaController (numero_venta, fecha, ventable_*).
     */
    public function enviarAVenta(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Obtener el pedido con relaciones completas
            $pedido = Pedido::with([
                'items.pedible',  // producto o servicio
                'cliente',
            ])->findOrFail($id);

            // Validar estado del pedido
            if (!$this->puedeEnviarseAVenta($pedido)) {
                return response()->json([
                    'success' => false,
                    'error' => 'El pedido no está en un estado válido para convertirse en venta',
                    'estado_actual' => $pedido->estado->value,
                    'requiere_confirmacion' => false
                ], 400);
            }

            // Validar que tenga items
            if ($pedido->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'El pedido no contiene ítems para convertir en venta',
                    'requiere_confirmacion' => false
                ], 400);
            }

            // Validar cliente
            if (!$pedido->cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'El pedido no tiene cliente asociado',
                    'requiere_confirmacion' => false
                ], 400);
            }

            // Verificar si ya fue convertido (opcional: permitir reenvío con forzar)
            $ventaExistente = Venta::where('pedido_id', $pedido->id)->first();

            if ($ventaExistente && !$request->has('forzar_reenvio')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Este pedido ya fue convertido en venta',
                    'requiere_confirmacion' => true,
                    'venta_id' => $ventaExistente->id,
                    'numero_venta' => $ventaExistente->numero_venta
                ], 409); // 409 Conflict
            }

            // Generar número de venta (formato propio de este controlador)
            $numeroVenta = $this->generarNumeroVenta();

            // Crear la venta (nombres alineados con VentaController)
            $venta = new Venta();
            $venta->fill([
                'cliente_id' => $pedido->cliente_id,
                'pedido_id' => $pedido->id,
                'numero_venta' => $numeroVenta,
                'fecha' => now(),
                'estado' => EstadoVenta::Borrador,
                'subtotal' => $pedido->subtotal,
                'descuento_general' => $pedido->descuento_general,
                'iva' => $pedido->iva,
                'total' => $pedido->total,
                'notas' => "Generado desde pedido #{$pedido->numero_pedido}",
                'user_id' => $request->user()->id ?? null,
            ]);
            $venta->save();

            // Copiar ítems del pedido a la venta
            foreach ($pedido->items as $item) {
                VentaItem::create([
                    'venta_id' => $venta->id,
                    'ventable_id' => $item->pedible_id,     // ← unificado con VentaController
                    'ventable_type' => $item->pedible_type, // ← unificado con VentaController
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                    'descuento_monto' => $item->descuento_monto,
                    'subtotal' => $item->subtotal,
                ]);
            }

            // Actualizar estado del pedido a enviado a venta
            $pedido->update(['estado' => EstadoPedido::EnviadoVenta]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta creada exitosamente',
                'venta_id' => $venta->id,
                'numero_venta' => $venta->numero_venta,
                'items_count' => $venta->items()->count(),
                'total' => $venta->total
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al convertir pedido a venta: ' . $e->getMessage(), [
                'pedido_id' => $id,
                'user_id' => $request->user()->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno al procesar la conversión a venta',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Determina si el pedido puede convertirse en venta.
     */
    private function puedeEnviarseAVenta(Pedido $pedido): bool
    {
        $estadosValidos = [
            EstadoPedido::Confirmado,
            EstadoPedido::EnPreparacion,
            EstadoPedido::ListoEntrega,
            EstadoPedido::Entregado,
            EstadoPedido::Borrador,
        ];

        return in_array($pedido->estado, $estadosValidos, true);
    }

    /**
     * Genera un número de venta único.
     * (Formato local a este controlador: VEN-######)
     */
    private function generarNumeroVenta(): string
    {
        $ultimo = Venta::orderBy('id', 'desc')->first();
        $numero = $ultimo ? $ultimo->id + 1 : 1;
        return 'VEN-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Genera un número de pedido único.
     */
    private function generarNumeroPedido()
    {
        $ultimo = Pedido::orderBy('id', 'desc')->first();
        $numero = $ultimo ? $ultimo->id + 1 : 1;
        return 'PED-' . date('Ymd') . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}
