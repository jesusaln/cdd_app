<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Producto;
use App\Models\Almacen;
use Illuminate\Support\Facades\DB;

class MovimientoInventarioController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->integer('per_page') ?: 15);

        // Obtener movimientos de inventario
        $query = DB::table('inventario_logs')
            ->join('productos', 'inventario_logs.producto_id', '=', 'productos.id')
            ->join('almacenes', 'inventario_logs.almacen_id', '=', 'almacenes.id')
            ->leftJoin('users', 'inventario_logs.user_id', '=', 'users.id')
            ->select(
                'inventario_logs.id',
                'inventario_logs.tipo',
                'inventario_logs.cantidad',
                'inventario_logs.motivo',
                'inventario_logs.created_at',
                'productos.nombre as producto_nombre',
                'almacenes.nombre as almacen_nombre',
                'users.name as usuario_nombre'
            )
            ->orderBy('inventario_logs.created_at', 'desc');

        // Filtros
        if ($request->filled('producto_id')) {
            $query->where('inventario_logs.producto_id', $request->producto_id);
        }

        if ($request->filled('almacen_id')) {
            $query->where('inventario_logs.almacen_id', $request->almacen_id);
        }

        if ($request->filled('tipo')) {
            $query->where('inventario_logs.tipo', $request->tipo);
        }

        if ($search = trim($request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('productos.nombre', 'like', "%{$search}%")
                  ->orWhere('almacenes.nombre', 'like', "%{$search}%")
                  ->orWhere('inventario_logs.motivo', 'like', "%{$search}%");
            });
        }

        $movimientos = $query->paginate($perPage);

        // Estadísticas
        $stats = [
            'total_movimientos' => DB::table('inventario_logs')->count(),
            'entradas' => DB::table('inventario_logs')->where('tipo', 'entrada')->count(),
            'salidas' => DB::table('inventario_logs')->where('tipo', 'salida')->count(),
            'traspasos' => DB::table('inventario_logs')->where('motivo', 'like', '%traspaso%')->count(),
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
