<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use App\Enums\EstadoVenta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VentaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ventas = Venta::with(['cliente', 'items.ventable'])
            ->get()
            ->filter(function ($venta) {
                // Filtrar ventas con cliente y al menos un item válido
                return $venta->cliente !== null && $venta->items->isNotEmpty();
            })
            ->map(function ($venta) {
                $items = $venta->items->map(function ($item) {
                    $ventable = $item->ventable;
                    return [
                        'id' => $ventable->id,
                        'nombre' => $ventable->nombre ?? 'Sin nombre',
                        'tipo' => $item->ventable_type === Producto::class ? 'producto' : 'servicio',
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                        'descuento' => $item->descuento ?? 0,
                        'descuento_monto' => $item->descuento_monto ?? 0,
                        'subtotal' => $item->subtotal,
                    ];
                });

                return [
                    'id' => $venta->id,
                    'fecha' => $venta->fecha ? $venta->fecha->format('Y-m-d') : $venta->created_at->format('Y-m-d'),
                    'created_at' => $venta->created_at->format('Y-m-d\TH:i:sP'),
                    'cliente' => [
                        'id' => $venta->cliente->id,
                        'nombre' => $venta->cliente->nombre_razon_social ?? 'Sin nombre',
                        'email' => $venta->cliente->email,
                        'telefono' => $venta->cliente->telefono,
                        'rfc' => $venta->cliente->rfc,
                        'regimen_fiscal' => $venta->cliente->regimen_fiscal,
                        'uso_cfdi' => $venta->cliente->uso_cfdi,
                        'calle' => $venta->cliente->calle,
                        'numero_exterior' => $venta->cliente->numero_exterior,
                        'numero_interior' => $venta->cliente->numero_interior,
                        'colonia' => $venta->cliente->colonia,
                        'codigo_postal' => $venta->cliente->codigo_postal,
                        'municipio' => $venta->cliente->municipio,
                        'estado' => $venta->cliente->estado,
                        'pais' => $venta->cliente->pais,
                    ],
                    'productos' => $items->toArray(),
                    'subtotal' => $venta->subtotal,
                    'descuento_general' => $venta->descuento_general ?? 0,
                    'iva' => $venta->iva,
                    'total' => $venta->total,
                    'estado' => $venta->estado->value,
                    'numero_venta' => $venta->numero_venta,
                    'pedido_id' => $venta->pedido_id ?? null,
                ];
            });

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas->values(),
            'estados' => collect(EstadoVenta::cases())->map(fn($estado) => [
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
        return Inertia::render('Ventas/Create', [
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

        $numero_venta = $this->generarNumeroVenta();

        $venta = Venta::create([
            'cliente_id' => $validated['cliente_id'],
            'factura_id' => null, // Puede llenarse si se asocia con una factura
            'numero_venta' => $numero_venta,
            'subtotal' => $subtotal,
            'descuento_general' => $descuentoGeneralMonto,
            'iva' => $iva,
            'total' => $total,
            'fecha' => now(),
            'estado' => EstadoVenta::Borrador,
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

            VentaItem::create([
                'venta_id' => $venta->id,
                'ventable_id' => $item['id'],
                'ventable_type' => $class,
                'cantidad' => $item['cantidad'],
                'precio' => $item['precio'],
                'descuento' => $item['descuento'],
                'subtotal' => $subtotalItem,
                'descuento_monto' => $descuentoMontoItem,
            ]);
        }

        return redirect()->route('ventas.index')->with('success', 'Venta creada con éxito');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $venta = Venta::with(['cliente', 'items.ventable'])->findOrFail($id);

        $items = $venta->items->map(function ($item) {
            $ventable = $item->ventable;
            return [
                'id' => $ventable->id,
                'nombre' => $ventable->nombre ?? $ventable->descripcion,
                'tipo' => $item->ventable_type === Producto::class ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Ventas/Show', [
            'venta' => [
                'id' => $venta->id,
                'cliente' => $venta->cliente,
                'productos' => $items,
                'subtotal' => $venta->subtotal,
                'descuento_general' => $venta->descuento_general,
                'iva' => $venta->iva,
                'total' => $venta->total,
                'fecha' => $venta->fecha ? $venta->fecha->format('Y-m-d') : $venta->created_at->format('Y-m-d'),
                'notas' => $venta->notas,
                'estado' => $venta->estado->value,
                'numero_venta' => $venta->numero_venta,
                'factura_id' => $venta->factura_id,
            ],
            'canEdit' => $venta->estado === EstadoVenta::Borrador || $venta->estado === EstadoVenta::Pendiente,
            'canDelete' => $venta->estado === EstadoVenta::Borrador || $venta->estado === EstadoVenta::Pendiente,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $venta = Venta::with(['cliente', 'items.ventable'])->findOrFail($id);

        // Permitir edición solo si está en Borrador o Pendiente
        if (!in_array($venta->estado, [EstadoVenta::Borrador, EstadoVenta::Pendiente])) {
            return Redirect::route('ventas.show', $venta->id)
                ->with('warning', 'Solo ventas en borrador o pendientes pueden ser editadas');
        }

        $items = $venta->items->map(function ($item) {
            $ventable = $item->ventable;
            return [
                'id' => $ventable->id,
                'nombre' => $ventable->nombre ?? $ventable->descripcion,
                'tipo' => $item->ventable_type === Producto::class ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Ventas/Edit', [
            'venta' => [
                'id' => $venta->id,
                'cliente_id' => $venta->cliente_id,
                'cliente' => $venta->cliente,
                'productos' => $items,
                'subtotal' => $venta->subtotal,
                'descuento_general' => $venta->descuento_general,
                'iva' => $venta->iva,
                'total' => $venta->total,
                'fecha' => $venta->fecha ? $venta->fecha->format('Y-m-d') : $venta->created_at->format('Y-m-d'),
                'notas' => $venta->notas,
                'numero_venta' => $venta->numero_venta,
                'factura_id' => $venta->factura_id,
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
        $venta = Venta::findOrFail($id);

        // Permitir edición solo si está en Borrador o Pendiente
        if (!in_array($venta->estado, [EstadoVenta::Borrador, EstadoVenta::Pendiente])) {
            return Redirect::back()->with('error', 'Solo ventas en borrador o pendientes pueden ser actualizadas');
        }

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'numero_venta' => 'required|string|unique:ventas,numero_venta,' . $venta->id,
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

        // Determinar el nuevo estado: si está en Borrador, cambiarlo a Pendiente
        $nuevoEstado = $venta->estado === EstadoVenta::Borrador
            ? EstadoVenta::Pendiente
            : $venta->estado;

        $venta->update([
            'cliente_id' => $validated['cliente_id'],
            'numero_venta' => $validated['numero_venta'],
            'subtotal' => $subtotal,
            'descuento_general' => $descuentoGeneralMonto,
            'iva' => $iva,
            'total' => $total,
            'fecha' => now(),
            'estado' => $nuevoEstado,
            'notas' => $request->notas,
        ]);

        // Eliminar ítems anteriores
        $venta->items()->delete();

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

            VentaItem::create([
                'venta_id' => $venta->id,
                'ventable_id' => $itemData['id'],
                'ventable_type' => $class,
                'cantidad' => $itemData['cantidad'],
                'precio' => $itemData['precio'],
                'descuento' => $itemData['descuento'],
                'subtotal' => $subtotalItem,
                'descuento_monto' => $descuentoMontoItem,
            ]);
        }

        $mensajeExito = $nuevoEstado === EstadoVenta::Pendiente && $venta->estado === EstadoVenta::Borrador
            ? 'Venta actualizada y cambiada a estado pendiente exitosamente'
            : 'Venta actualizada exitosamente';

        return Redirect::route('ventas.index')
            ->with('success', $mensajeExito);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            try {
                $venta = Venta::with('factura')->findOrFail($id);

                // Verificar que la venta puede ser eliminada
                if (!in_array($venta->estado, [EstadoVenta::Borrador, EstadoVenta::Pendiente])) {
                    return Redirect::back()->with('error', 'Solo ventas en borrador o pendientes pueden ser eliminadas');
                }

                // Guardar información de la factura antes de eliminar
                $facturaId = $venta->factura_id;
                $factura = $venta->factura;

                // Eliminar los ítems de la venta primero
                $venta->items()->delete();

                // Eliminar la venta
                $venta->delete();

                // Revertir el estado de la factura asociada DESPUÉS de eliminar la venta
                if ($facturaId && $factura) {
                    $factura->estado = 'pendiente';
                    $factura->save();

                    Log::info("Venta ID {$id} eliminada y Factura ID {$facturaId} revertida a estado pendiente");
                }

                return Redirect::route('ventas.index')
                    ->with('success', 'Venta eliminada exitosamente y factura revertida a pendiente');
            } catch (\Exception $e) {
                Log::error('Error al eliminar venta: ' . $e->getMessage());

                // La transacción se revertirá automáticamente
                return Redirect::back()
                    ->with('error', 'Error al eliminar la venta: ' . $e->getMessage());
            }
        });
    }

    /**
     * Duplicate a venta.
     */
    public function duplicate(Request $request, $id)
    {
        $venta = Venta::with('cliente', 'items.ventable')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Duplicar la venta
            $nueva = $venta->replicate();
            $nueva->estado = EstadoVenta::Borrador;
            $nueva->numero_venta = $this->generarNumeroVenta();
            $nueva->fecha = now();
            $nueva->created_at = now();
            $nueva->updated_at = now();
            $nueva->save();

            // Duplicar los ítems
            foreach ($venta->items as $item) {
                $nueva->items()->create([
                    'ventable_id' => $item->ventable_id,
                    'ventable_type' => $item->ventable_type,
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                    'subtotal' => $item->subtotal,
                    'descuento_monto' => $item->descuento_monto,
                ]);
            }

            DB::commit();

            return Redirect::route('ventas.index')
                ->with('success', 'Venta duplicada correctamente.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error duplicando venta: ' . $e->getMessage());

            return Redirect::back()
                ->with('error', 'Error al duplicar la venta.');
        }
    }

    /**
     * Generate a unique numero_venta.
     */
    private function generarNumeroVenta()
    {
        $ultima = Venta::orderBy('id', 'desc')->first();
        $numero = $ultima ? $ultima->id + 1 : 1;
        return 'VEN-' . date('Ymd') . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}
