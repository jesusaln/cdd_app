<?php

namespace App\Http\Controllers;

use App\Models\AjusteInventario;
use App\Models\Producto;
use App\Models\Almacen;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AjusteInventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) ($request->integer('per_page') ?: 10);
        $page = max(1, (int) $request->get('page', 1));

        $baseQuery = AjusteInventario::with([
            'producto',
            'almacen',
            'usuario',
        ]);

        // Aplicar filtros
        if ($search = trim($request->get('search', ''))) {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                      ->orWhere('motivo', 'like', "%{$search}%")
                      ->orWhereHas('producto', function ($q) use ($search) {
                          $q->where('nombre', 'like', "%{$search}%");
                      })
                      ->orWhereHas('almacen', function ($q) use ($search) {
                          $q->where('nombre', 'like', "%{$search}%");
                      })
                      ->orWhereHas('usuario', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
            });
        }

        if ($request->filled('producto_id')) {
            $baseQuery->where('producto_id', $request->producto_id);
        }

        if ($request->filled('almacen_id')) {
            $baseQuery->where('almacen_id', $request->almacen_id);
        }

        if ($request->filled('tipo')) {
            $baseQuery->where('tipo', $request->tipo);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSorts = ['created_at', 'cantidad_ajuste'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $baseQuery->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');

        $paginator = $baseQuery->paginate($perPage, ['*'], 'page', $page);
        $ajustes = collect($paginator->items());

        $transformed = $ajustes->map(function ($ajuste) {
            return [
                'id' => $ajuste->id,
                'producto' => $ajuste->producto ? [
                    'id' => $ajuste->producto->id,
                    'nombre' => $ajuste->producto->nombre,
                    'codigo' => $ajuste->producto->codigo,
                ] : null,
                'almacen' => $ajuste->almacen ? [
                    'id' => $ajuste->almacen->id,
                    'nombre' => $ajuste->almacen->nombre,
                ] : null,
                'usuario' => $ajuste->usuario ? [
                    'id' => $ajuste->usuario->id,
                    'name' => $ajuste->usuario->name,
                ] : null,
                'tipo' => $ajuste->tipo,
                'cantidad_anterior' => (int) $ajuste->cantidad_anterior,
                'cantidad_ajuste' => (int) $ajuste->cantidad_ajuste,
                'cantidad_nueva' => (int) $ajuste->cantidad_nueva,
                'motivo' => $ajuste->motivo,
                'observaciones' => $ajuste->observaciones,
                'created_at' => optional($ajuste->created_at)->format('Y-m-d H:i:s'),
                'fecha' => optional($ajuste->created_at)->format('Y-m-d'),
            ];
        });

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed,
            $paginator->total(),
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => 'page']
        );

        // Estadísticas
        $stats = [
            'total' => AjusteInventario::count(),
            'incrementos' => AjusteInventario::where('tipo', 'incremento')->count(),
            'decrementos' => AjusteInventario::where('tipo', 'decremento')->count(),
            'productos_ajustados' => AjusteInventario::distinct('producto_id')->count('producto_id'),
            'almacenes_afectados' => AjusteInventario::distinct('almacen_id')->count('almacen_id'),
        ];

        // Datos para filtros
        $productos = Producto::select('id', 'nombre')->orderBy('nombre')->get();
        $almacenes = Almacen::select('id', 'nombre')->where('estado', 'activo')->orderBy('nombre')->get();

        return Inertia::render('AjustesInventario/Index', [
            'ajustes' => $paginator,
            'stats' => $stats,
            'filters' => $request->only(['search', 'producto_id', 'almacen_id', 'tipo']),
            'sorting' => [
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
                'allowed_sorts' => $allowedSorts,
            ],
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $perPage,
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'productos' => $productos,
            'almacenes' => $almacenes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $productos = Producto::select('id', 'nombre', 'codigo')->orderBy('nombre')->get();
        $almacenes = Almacen::select('id', 'nombre')->where('estado', 'activo')->orderBy('nombre')->get();

        return Inertia::render('AjustesInventario/Create', [
            'productos' => $productos,
            'almacenes' => $almacenes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'tipo' => 'required|in:incremento,decremento',
            'cantidad_ajuste' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request) {
            $productoId = $request->producto_id;
            $almacenId = $request->almacen_id;
            $tipo = $request->tipo;
            $cantidadAjuste = $request->cantidad_ajuste;

            // Obtener el inventario actual
            $inventario = Inventario::where('producto_id', $productoId)
                ->where('almacen_id', $almacenId)
                ->first();

            $cantidadAnterior = $inventario ? $inventario->cantidad : 0;

            // Calcular nueva cantidad
            $cantidadNueva = $tipo === 'incremento'
                ? $cantidadAnterior + $cantidadAjuste
                : $cantidadAnterior - $cantidadAjuste;

            // Validar que no quede stock negativo
            if ($cantidadNueva < 0) {
                throw new \Exception('El ajuste resultaría en stock negativo. Stock actual: ' . $cantidadAnterior);
            }

            // Crear o actualizar inventario
            if (!$inventario) {
                $inventario = Inventario::create([
                    'producto_id' => $productoId,
                    'almacen_id' => $almacenId,
                    'cantidad' => $cantidadNueva,
                    'stock_minimo' => 0,
                ]);
            } else {
                $inventario->update(['cantidad' => $cantidadNueva]);
            }

            // Actualizar stock total en producto
            $producto = Producto::find($productoId);
            $totalStock = Inventario::where('producto_id', $productoId)->sum('cantidad');
            $producto->update(['stock' => $totalStock]);

            // Registrar el ajuste
            AjusteInventario::create([
                'producto_id' => $productoId,
                'almacen_id' => $almacenId,
                'user_id' => auth()->id(),
                'tipo' => $tipo,
                'cantidad_anterior' => $cantidadAnterior,
                'cantidad_ajuste' => $cantidadAjuste,
                'cantidad_nueva' => $cantidadNueva,
                'motivo' => $request->motivo,
                'observaciones' => $request->observaciones,
            ]);

            // Registrar movimiento en inventario_logs
            DB::table('inventario_logs')->insert([
                'producto_id' => $productoId,
                'almacen_id' => $almacenId,
                'user_id' => auth()->id(),
                'tipo' => $tipo === 'incremento' ? 'entrada' : 'salida',
                'cantidad' => $cantidadAjuste,
                'motivo' => 'Ajuste de inventario: ' . $request->motivo,
                'detalles' => json_encode([
                    'tipo_ajuste' => $tipo,
                    'cantidad_anterior' => $cantidadAnterior,
                    'cantidad_nueva' => $cantidadNueva,
                    'observaciones' => $request->observaciones,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Ajuste de inventario realizado', [
                'producto_id' => $productoId,
                'almacen_id' => $almacenId,
                'tipo' => $tipo,
                'cantidad_anterior' => $cantidadAnterior,
                'cantidad_ajuste' => $cantidadAjuste,
                'cantidad_nueva' => $cantidadNueva,
                'stock_total_actualizado' => $totalStock,
            ]);
        });

        return redirect()->route('ajustes-inventario.index')->with('success', 'Ajuste de inventario realizado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ajuste = AjusteInventario::with(['producto', 'almacen', 'usuario'])->findOrFail($id);

        return Inertia::render('AjustesInventario/Show', [
            'ajuste' => $ajuste,
        ]);
    }
}
