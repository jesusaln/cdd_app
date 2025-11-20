<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class OrdenCompraController extends Controller
{
    public function __construct(private readonly \App\Services\InventarioService $inventarioService)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) ($request->integer('per_page') ?: 10);
        $ordenes = OrdenCompra::with(['proveedor', 'productos'])->orderByDesc('id')->paginate($perPage);

        return Inertia::render('OrdenesCompra/Index', [
            'ordenesCompra' => $ordenes,
        ]);
    }

    public function create()
    {
        return Inertia::render('OrdenesCompra/Create');
    }

    public function store(Request $request)
    {
        Log::info('OrdenCompraController@store - Datos recibidos:', $request->all());

        DB::beginTransaction();
        try {
            $req = $request->all();
            if (empty($req['almacen_id']) || $req['almacen_id'] === '0' || $req['almacen_id'] === 0) {
                $req['almacen_id'] = null;
            }
            $request->merge($req);

            $validated = $request->validate([
                'numero_orden' => 'nullable|string',
                'fecha_orden' => 'required|date',
                'fecha_entrega_esperada' => 'nullable|date',
                'prioridad' => 'required|in:baja,media,alta,urgente',
                'proveedor_id' => 'required|exists:proveedores,id',
                'almacen_id' => 'nullable|exists:almacenes,id',
                'direccion_entrega' => 'nullable|string',
                'terminos_pago' => 'required|in:contado,15_dias,30_dias,45_dias,60_dias,90_dias',
                'metodo_pago' => 'required|in:transferencia,cheque,efectivo,tarjeta',
                'subtotal' => 'required|numeric|min:0',
                'descuento_items' => 'required|numeric|min:0',
                'descuento_general' => 'required|numeric|min:0|max:100',
                'iva' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'observaciones' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|integer|exists:productos,id',
                'items.*.tipo' => 'required|in:producto',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.precio' => 'required|numeric|min:0',
                'items.*.descuento' => 'required|numeric|min:0|max:100',
            ]);

            $data = [
                'numero_orden' => $validated['numero_orden'] ?? null,
                'fecha_orden' => $validated['fecha_orden'],
                'fecha_entrega_esperada' => $validated['fecha_entrega_esperada'] ?? null,
                'prioridad' => $validated['prioridad'],
                'proveedor_id' => $validated['proveedor_id'],
                'direccion_entrega' => $validated['direccion_entrega'] ?? null,
                'terminos_pago' => $validated['terminos_pago'],
                'metodo_pago' => $validated['metodo_pago'],
                'subtotal' => $validated['subtotal'],
                'descuento_items' => $validated['descuento_items'],
                'descuento_general' => $validated['descuento_general'],
                'iva' => $validated['iva'],
                'total' => $validated['total'],
                'observaciones' => $validated['observaciones'] ?? null,
                'estado' => 'pendiente',
            ];
            if (Schema::hasColumn('orden_compras', 'almacen_id')) {
                $data['almacen_id'] = $validated['almacen_id'] ?? null;
            }

            $orden = OrdenCompra::create($data);

            foreach ($validated['items'] as $item) {
                if (($item['tipo'] ?? null) === 'producto') {
                    $orden->productos()->attach($item['id'], [
                        'cantidad' => $item['cantidad'],
                        'precio' => $item['precio'],
                        'descuento' => $item['descuento'] ?? 0,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('ordenescompra.index')->with('success', 'Orden de compra creada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear la orden de compra', ['msg' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            return redirect()->back()->with('error', 'Ocurrió un error al crear la orden de compra.');
        }
    }
}

