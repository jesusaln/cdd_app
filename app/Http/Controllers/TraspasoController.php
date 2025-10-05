<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Producto;
use App\Models\Almacen;
use App\Models\Inventario;
use App\Models\Traspaso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class TraspasoController extends Controller
{
    public function index()
    {
        return Inertia::render('Traspasos/Index');
    }

    public function create()
    {
        $productos = Producto::select('id', 'nombre')->get();
        $almacenes = Almacen::select('id', 'nombre')->get();
        $inventarios = Inventario::with(['producto', 'almacen'])->get();

        return Inertia::render('Traspasos/Create', [
            'productos' => $productos,
            'almacenes' => $almacenes,
            'inventarios' => $inventarios,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'almacen_origen_id' => 'required|exists:almacenes,id',
            'almacen_destino_id' => 'required|exists:almacenes,id|different:almacen_origen_id',
            'cantidad' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $productoId = $request->producto_id;
            $almacenOrigenId = $request->almacen_origen_id;
            $almacenDestinoId = $request->almacen_destino_id;
            $cantidad = $request->cantidad;

            // Verificar stock en origen
            $inventarioOrigen = Inventario::where('producto_id', $productoId)
                ->where('almacen_id', $almacenOrigenId)
                ->first();

            if (!$inventarioOrigen || $inventarioOrigen->cantidad < $cantidad) {
                throw new \Exception('Stock insuficiente en el almacén de origen');
            }

            // Reducir stock en origen
            $inventarioOrigen->decrement('cantidad', $cantidad);

            // Aumentar stock en destino (o crear si no existe)
            $inventarioDestino = Inventario::firstOrCreate(
                [
                    'producto_id' => $productoId,
                    'almacen_id' => $almacenDestinoId,
                ],
                [
                    'cantidad' => 0,
                    'stock_minimo' => 0,
                ]
            );

            $inventarioDestino->increment('cantidad', $cantidad);

            // Actualizar stock total en producto
            $producto = Producto::find($productoId);
            $totalStock = Inventario::where('producto_id', $productoId)->sum('cantidad');
            $producto->update(['stock' => $totalStock]);

            // Registrar movimientos en inventario_logs
            DB::table('inventario_logs')->insert([
                [
                    'producto_id' => $productoId,
                    'almacen_id' => $almacenOrigenId,
                    'user_id' => auth()->id(),
                    'tipo' => 'salida',
                    'cantidad' => $cantidad,
                    'motivo' => 'Traspaso a almacén ' . Almacen::find($almacenDestinoId)->nombre,
                    'detalles' => json_encode(['traspaso_id' => $traspaso->id, 'destino' => $almacenDestinoId]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'producto_id' => $productoId,
                    'almacen_id' => $almacenDestinoId,
                    'user_id' => auth()->id(),
                    'tipo' => 'entrada',
                    'cantidad' => $cantidad,
                    'motivo' => 'Traspaso desde almacén ' . Almacen::find($almacenOrigenId)->nombre,
                    'detalles' => json_encode(['traspaso_id' => $traspaso->id, 'origen' => $almacenOrigenId]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);

            Log::info('Traspaso realizado', [
                'producto_id' => $productoId,
                'origen' => $almacenOrigenId,
                'destino' => $almacenDestinoId,
                'cantidad' => $cantidad,
                'stock_total_actualizado' => $totalStock,
            ]);
        });

        return redirect()->route('traspasos.index')->with('success', 'Traspaso realizado correctamente');
    }
}
