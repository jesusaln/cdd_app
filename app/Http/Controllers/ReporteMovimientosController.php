<?php

namespace App\Http\Controllers;

use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReporteMovimientosController extends Controller
{
    private InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    /**
     * Muestra el reporte de movimientos de inventario.
     */
    public function index(Request $request)
    {
        $perPage = (int) ($request->integer('per_page') ?: 25);
        $page = max(1, (int) $request->get('page', 1));

        // Usar el servicio para aplicar filtros avanzados
        $query = $this->inventarioService->obtenerMovimientosConFiltros([
            'producto_id' => $request->producto_id,
            'tipo' => $request->tipo,
            'motivo' => $request->motivo,
            'fecha_desde' => $request->fecha_desde,
            'fecha_hasta' => $request->fecha_hasta,
            'user_id' => $request->user_id,
        ]);

        // Aplicar ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSorts = ['created_at', 'tipo', 'cantidad', 'motivo'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');

        // Cargar relaciones después del ordenamiento
        $movimientos = $query->with([
            'producto' => function ($query) {
                $query->select('id', 'nombre', 'codigo', 'categoria_id', 'marca_id')
                      ->with(['categoria:id,nombre', 'marca:id,nombre']);
            },
            'user:id,name,email'
        ])->paginate($perPage)->appends($request->query());

        // Transformar datos para el frontend
        $movimientosData = collect($movimientos->items())->map(function ($movimiento) {
            return [
                'id' => $movimiento->id,
                'producto' => $movimiento->producto ? [
                    'id' => $movimiento->producto->id,
                    'nombre' => $movimiento->producto->nombre,
                    'codigo' => $movimiento->producto->codigo,
                    'categoria' => $movimiento->producto->categoria?->nombre,
                    'marca' => $movimiento->producto->marca?->nombre,
                ] : null,
                'tipo' => $movimiento->tipo,
                'cantidad' => $movimiento->cantidad,
                'motivo' => $movimiento->motivo,
                'referencia' => $movimiento->referencia,
                'user' => $movimiento->user ? [
                    'id' => $movimiento->user->id,
                    'name' => $movimiento->user->name,
                    'email' => $movimiento->user->email,
                ] : null,
                'metadatos' => $movimiento->metadatos,
                'created_at' => $movimiento->created_at->format('Y-m-d H:i:s'),
                'fecha' => $movimiento->created_at->format('Y-m-d'),
            ];
        });

        // Crear nuevo paginador con datos transformados
        $movimientosPaginados = new \Illuminate\Pagination\LengthAwarePaginator(
            $movimientosData,
            $movimientos->total(),
            $movimientos->perPage(),
            $movimientos->currentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // Obtener opciones para filtros
        $productos = Producto::select('id', 'nombre', 'codigo')
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(function ($producto) {
                return [$producto->id => $producto->nombre . ' (' . $producto->codigo . ')'];
            });

        $usuarios = \App\Models\User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($user) {
                return [$user->id => $user->name . ' (' . $user->email . ')'];
            });

        $filterOptions = [
            'productos' => $productos,
            'usuarios' => $usuarios,
            'tipos' => [
                'entrada' => 'Entrada',
                'salida' => 'Salida',
            ],
            'per_page_options' => [10, 25, 50, 100],
        ];

        // Estadísticas generales usando el servicio
        $stats = $this->inventarioService->obtenerEstadisticasGenerales();

        return Inertia::render('Reportes/MovimientosInventario', [
            'movimientos' => $movimientosPaginados,
            'stats' => $stats,
            'filterOptions' => $filterOptions,
            'filters' => $request->only([
                'producto_id', 'tipo', 'motivo', 'fecha_desde', 'fecha_hasta', 'user_id'
            ]),
            'sorting' => [
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
                'allowed_sorts' => $allowedSorts,
            ],
            'pagination' => [
                'current_page' => $movimientos->currentPage(),
                'last_page' => $movimientos->lastPage(),
                'per_page' => $perPage,
                'total' => $movimientos->total(),
                'from' => $movimientos->firstItem(),
                'to' => $movimientos->lastItem(),
            ],
        ]);
    }

    /**
     * Muestra el detalle de un movimiento específico.
     */
    public function show($id)
    {
        $movimiento = InventarioMovimiento::with([
            'producto.categoria',
            'producto.marca',
            'user'
        ])->findOrFail($id);

        return Inertia::render('Reportes/MovimientoDetalle', [
            'movimiento' => [
                'id' => $movimiento->id,
                'producto' => $movimiento->producto ? [
                    'id' => $movimiento->producto->id,
                    'nombre' => $movimiento->producto->nombre,
                    'codigo' => $movimiento->producto->codigo,
                    'categoria' => $movimiento->producto->categoria?->nombre,
                    'marca' => $movimiento->producto->marca?->nombre,
                ] : null,
                'tipo' => $movimiento->tipo,
                'cantidad' => $movimiento->cantidad,
                'motivo' => $movimiento->motivo,
                'referencia' => $movimiento->referencia,
                'user' => $movimiento->user ? [
                    'id' => $movimiento->user->id,
                    'name' => $movimiento->user->name,
                    'email' => $movimiento->user->email,
                ] : null,
                'metadatos' => $movimiento->metadatos,
                'created_at' => $movimiento->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $movimiento->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Exporta los movimientos de inventario a Excel o PDF.
     */
    public function export(Request $request)
    {
        // Por ahora solo retornamos JSON, pero aquí se podría implementar exportación
        $movimientos = InventarioMovimiento::with(['producto', 'user'])
            ->when($request->filled('producto_id'), fn($q) => $q->where('producto_id', $request->producto_id))
            ->when($request->filled('tipo'), fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('fecha_desde'), fn($q) => $q->whereDate('created_at', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn($q) => $q->whereDate('created_at', '<=', $request->fecha_hasta))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $movimientos->map(function ($movimiento) {
                return [
                    'id' => $movimiento->id,
                    'producto' => $movimiento->producto?->nombre,
                    'tipo' => $movimiento->tipo,
                    'cantidad' => $movimiento->cantidad,
                    'motivo' => $movimiento->motivo,
                    'referencia' => $movimiento->referencia,
                    'usuario' => $movimiento->user?->name,
                    'fecha' => $movimiento->created_at->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }
}
