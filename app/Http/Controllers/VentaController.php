<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['cliente', 'productos', 'servicios'])->get()->map(function ($venta) {
            $productos = $venta->productos->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'tipo' => 'producto',
                    'pivot' => [
                        'cantidad' => $producto->pivot->cantidad,
                        'precio' => $producto->pivot->precio,
                    ],
                ];
            });

            $servicios = $venta->servicios->map(function ($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'tipo' => 'servicio',
                    'pivot' => [
                        'cantidad' => $servicio->pivot->cantidad,
                        'precio' => $servicio->pivot->precio,
                    ],
                ];
            });

            $items = collect($productos->all())->merge($servicios->all());

            return [
                'id' => $venta->id,
                'cliente' => $venta->cliente,
                'productos' => $items,
                'total' => $venta->total,
            ];
        });

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
        ]);
    }

    public function create()
    {
        $clientes = Cliente::all();
        $productos = Producto::all();
        $servicios = Servicio::all();

        return Inertia::render('Ventas/Create', [
            'clientes' => $clientes,
            'productos' => $productos,
            'servicios' => $servicios,
        ]);
    }

    public function store(Request $request)
    {
        // Validar los datos de entrada, incluyendo tipo
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        // Verificar stock para productos
        foreach ($validatedData['productos'] as $itemData) {
            if ($itemData['tipo'] === 'producto') {
                $producto = Producto::find($itemData['id']);
                if (!$producto) {
                    return redirect()->back()->withErrors(['id' => "El ID {$itemData['id']} no corresponde a un producto válido."]);
                }
                if ($producto->stock < $itemData['cantidad']) {
                    return redirect()->back()->withErrors(['stock' => "No hay suficiente stock para el producto: {$producto->nombre}"]);
                }
            } elseif ($itemData['tipo'] === 'servicio') {
                $servicio = Servicio::find($itemData['id']);
                if (!$servicio) {
                    return redirect()->back()->withErrors(['id' => "El ID {$itemData['id']} no corresponde a un servicio válido."]);
                }
            }
        }

        // Crear la venta
        $venta = Venta::create([
            'cliente_id' => $validatedData['cliente_id'],
            'total' => array_sum(array_map(function ($item) {
                return $item['cantidad'] * $item['precio'];
            }, $validatedData['productos'])),
        ]);

        // Asociar productos y servicios a la venta
        foreach ($validatedData['productos'] as $itemData) {
            if ($itemData['tipo'] === 'producto') {
                $producto = Producto::find($itemData['id']);
                if ($producto) {
                    $producto->stock -= $itemData['cantidad'];
                    $producto->save();
                    $venta->productos()->attach($itemData['id'], [
                        'cantidad' => $itemData['cantidad'],
                        'precio' => $itemData['precio'],
                    ]);
                }
            } elseif ($itemData['tipo'] === 'servicio') {
                $servicio = Servicio::find($itemData['id']);
                if ($servicio) {
                    $venta->servicios()->attach($itemData['id'], [
                        'cantidad' => $itemData['cantidad'],
                        'precio' => $itemData['precio'],
                    ]);
                }
            }
        }

        return redirect()->route('ventas.index')->with('success', 'Venta creada exitosamente.');
    }

    public function show($id)
    {
        $venta = Venta::with('cliente', 'productos', 'servicios')->findOrFail($id);
        $productos = $venta->productos->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'tipo' => 'producto',
                'pivot' => [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                ],
            ];
        });
        $servicios = $venta->servicios->map(function ($servicio) {
            return [
                'id' => $servicio->id,
                'nombre' => $servicio->nombre,
                'tipo' => 'servicio',
                'pivot' => [
                    'cantidad' => $servicio->pivot->cantidad,
                    'precio' => $servicio->pivot->precio,
                ],
            ];
        });
        $items = collect($productos->all())->merge($servicios->all());

        return Inertia::render('Ventas/Show', [
            'venta' => [
                'id' => $venta->id,
                'cliente' => $venta->cliente,
                'productos' => $items,
                'total' => $venta->total,
            ],
        ]);
    }

    public function edit($id)
    {
        $venta = Venta::with('cliente', 'productos', 'servicios')->findOrFail($id);
        $productos = $venta->productos->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'tipo' => 'producto',
                'pivot' => [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                ],
            ];
        });
        $servicios = $venta->servicios->map(function ($servicio) {
            return [
                'id' => $servicio->id,
                'nombre' => $servicio->nombre,
                'tipo' => 'servicio',
                'pivot' => [
                    'cantidad' => $servicio->pivot->cantidad,
                    'precio' => $servicio->pivot->precio,
                ],
            ];
        });
        $items = collect($productos->all())->merge($servicios->all());

        $clientes = Cliente::all();
        $productosDisponibles = Producto::all();
        $serviciosDisponibles = Servicio::all();

        return Inertia::render('Ventas/Edit', [
            'venta' => [
                'id' => $venta->id,
                'cliente' => $venta->cliente,
                'productos' => $items,
                'total' => $venta->total,
            ],
            'clientes' => $clientes,
            'productos' => $productosDisponibles,
            'servicios' => $serviciosDisponibles,
        ]);
    }

    public function update(Request $request, $id)
    {
        $venta = Venta::findOrFail($id);

        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.original_cantidad' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($venta, $validatedData) {
            $total = array_sum(array_map(function ($item) {
                return $item['cantidad'] * $item['precio'];
            }, $validatedData['productos']));

            $venta->update([
                'cliente_id' => $validatedData['cliente_id'],
                'total' => $total,
            ]);

            $productosActuales = $venta->productos()->get()->pluck('pivot.cantidad', 'id')->all();
            $serviciosActuales = $venta->servicios()->get()->pluck('pivot.cantidad', 'id')->all();

            $syncProductos = [];
            $syncServicios = [];
            foreach ($validatedData['productos'] as $itemData) {
                if ($itemData['tipo'] === 'producto') {
                    $producto = Producto::find($itemData['id']);
                    if ($producto) {
                        $originalCantidad = $productosActuales[$itemData['id']] ?? 0;
                        $diferencia = $itemData['cantidad'] - $originalCantidad;
                        if ($diferencia != 0) {
                            if ($producto->stock - $diferencia < 0) {
                                throw new \Exception("Stock insuficiente para el producto: {$producto->nombre}");
                            }
                            $producto->stock -= $diferencia;
                            $producto->save();
                        }
                        $syncProductos[$itemData['id']] = [
                            'cantidad' => $itemData['cantidad'],
                            'precio' => $itemData['precio'],
                        ];
                    }
                } elseif ($itemData['tipo'] === 'servicio') {
                    $servicio = Servicio::find($itemData['id']);
                    if ($servicio) {
                        $syncServicios[$itemData['id']] = [
                            'cantidad' => $itemData['cantidad'],
                            'precio' => $itemData['precio'],
                        ];
                    }
                }
            }

            $venta->productos()->sync($syncProductos);
            $venta->servicios()->sync($syncServicios);
        });

        return redirect()->route('ventas.index')->with('success', 'Venta actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $venta = Venta::with('productos', 'servicios')->findOrFail($id);

        foreach ($venta->productos as $producto) {
            $producto->stock += $producto->pivot->cantidad;
            $producto->save();
        }

        $venta->productos()->detach();
        $venta->servicios()->detach();
        $venta->delete();

        return redirect()->route('ventas.index')->with('success', 'Venta eliminada exitosamente.');
    }
}
