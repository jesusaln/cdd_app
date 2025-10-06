<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Producto;
use App\Models\Almacen;
use App\Models\InventarioMovimiento;

class MovimientoInventarioController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->integer('per_page') ?: 15);

        // Obtener movimientos de inventario
        $query = InventarioMovimiento::with(['producto', 'almacen', 'user'])
            ->select(
                'id',
                'tipo',
                'cantidad',
                'motivo',
                'created_at'
            )
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($search = trim($request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('producto', function ($subQ) use ($search) {
                    $subQ->where('nombre', 'like', "%{$search}%");
                })
                ->orWhereHas('almacen', function ($subQ) use ($search) {
                    $subQ->where('nombre', 'like', "%{$search}%");
                })
                ->orWhere('motivo', 'like', "%{$search}%");
            });
        }

        $movimientos = $query->paginate($perPage)->through(function ($movimiento) {
            return [
                'id' => $movimiento->id,
                'tipo' => $movimiento->tipo,
                'cantidad' => $movimiento->cantidad,
                'motivo' => $movimiento->motivo,
                'created_at' => $movimiento->created_at,
                'producto_nombre' => $movimiento->producto->nombre ?? 'Producto no encontrado',
                'almacen_nombre' => $movimiento->almacen->nombre ?? 'Almacén no encontrado',
                'usuario_nombre' => $movimiento->user->name ?? 'Usuario no encontrado',
            ];
        });

        // Estadísticas
        $stats = [
            'total_movimientos' => InventarioMovimiento::count(),
            'entradas' => InventarioMovimiento::where('tipo', 'entrada')->count(),
            'salidas' => InventarioMovimiento::where('tipo', 'salida')->count(),
            'traspasos' => InventarioMovimiento::where('motivo', 'like', '%traspaso%')->count(),
        ];

        return Inertia::render('MovimientosInventario/Index', [
            'movimientos' => $movimientos,
            'stats' => $stats,
            'productos' => Producto::select('id', 'nombre')->get(),
            'almacenes' => Almacen::select('id', 'nombre')->get(),
            'filters' => $request->only(['search', 'producto_id', 'almacen_id', 'tipo']),
        ]);
    }
}
