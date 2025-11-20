<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Producto;
use App\Models\Almacen;
use App\Models\Inventario;
use App\Models\Traspaso;
use App\Services\InventarioService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class TraspasoController extends Controller
{
    public function __construct(private readonly InventarioService $inventarioService)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) ($request->integer('per_page') ?: 10);
        $page = max(1, (int) $request->get('page', 1));

        $baseQuery = Traspaso::with([
            'producto',
            'almacenOrigen',
            'almacenDestino',
            'usuarioAutoriza',
            'usuarioEnvia',
            'usuarioRecibe',
        ]);

        // Aplicar filtros
        if ($search = trim($request->get('search', ''))) {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                      ->orWhereHas('producto', function ($q) use ($search) {
                          $q->where('nombre', 'like', "%{$search}%");
                      })
                      ->orWhereHas('almacenOrigen', function ($q) use ($search) {
                          $q->where('nombre', 'like', "%{$search}%");
                      })
                      ->orWhereHas('almacenDestino', function ($q) use ($search) {
                          $q->where('nombre', 'like', "%{$search}%");
                      });
            });
        }

        if ($request->filled('producto_id')) {
            $baseQuery->where('producto_id', $request->producto_id);
        }

        if ($request->filled('almacen_origen_id')) {
            $baseQuery->where('almacen_origen_id', $request->almacen_origen_id);
        }

        if ($request->filled('almacen_destino_id')) {
            $baseQuery->where('almacen_destino_id', $request->almacen_destino_id);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSorts = ['created_at', 'cantidad'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $baseQuery->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');

        $paginator = $baseQuery->paginate($perPage, ['*'], 'page', $page);
        $traspasos = collect($paginator->items());

        $transformed = $traspasos->map(function ($traspaso) {
            return [
                'id' => $traspaso->id,
                'producto' => $traspaso->producto ? [
                    'id' => $traspaso->producto->id,
                    'nombre' => $traspaso->producto->nombre,
                ] : null,
                'almacen_origen' => $traspaso->almacenOrigen ? [
                    'id' => $traspaso->almacenOrigen->id,
                    'nombre' => $traspaso->almacenOrigen->nombre,
                ] : null,
                'almacen_destino' => $traspaso->almacenDestino ? [
                    'id' => $traspaso->almacenDestino->id,
                    'nombre' => $traspaso->almacenDestino->nombre,
                ] : null,
                'cantidad' => (int) $traspaso->cantidad,
                'observaciones' => $traspaso->observaciones,
                'created_at' => optional($traspaso->created_at)->format('Y-m-d H:i:s'),
                'fecha' => optional($traspaso->created_at)->format('Y-m-d'),
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
            'total' => Traspaso::count(),
            'pendientes' => Traspaso::where('estado', 'pendiente')->count(),
            'en_transito' => Traspaso::where('estado', 'en_transito')->count(),
            'completados' => Traspaso::where('estado', 'completado')->count(),
            'productos_trasladados' => Traspaso::distinct('producto_id')->count('producto_id'),
            'almacenes_origen' => Traspaso::distinct('almacen_origen_id')->count('almacen_origen_id'),
            'almacenes_destino' => Traspaso::distinct('almacen_destino_id')->count('almacen_destino_id'),
        ];

        // Datos para filtros
        $productos = Producto::select('id', 'nombre', 'requiere_serie')->orderBy('nombre')->get();
        $almacenes = Almacen::select('id', 'nombre')->where('estado', 'activo')->orderBy('nombre')->get();

        return Inertia::render('Traspasos/Index', [
            'traspasos' => $paginator,
            'stats' => $stats,
            'filters' => $request->only(['search', 'producto_id', 'almacen_origen_id', 'almacen_destino_id']),
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

    public function create()
    {
        $productos = Producto::select('id', 'nombre', 'requiere_serie')->get();
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
            'series' => 'nullable|array',
            'series.*' => 'integer|exists:producto_series,id',
            'observaciones' => 'nullable|string|max:1000',
            'referencia' => 'nullable|string|max:100',
            'costo_transporte' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $productoId = $request->producto_id;
            $almacenOrigenId = $request->almacen_origen_id;
            $almacenDestinoId = $request->almacen_destino_id;
            $seriesIds = collect($request->input('series', []))
                ->filter()
                ->map(fn($id) => (int) $id)
                ->values();
            $producto = Producto::findOrFail($productoId);

            if ($producto->requiere_serie) {
                if ($seriesIds->isEmpty()) {
                    throw new \Exception('Debe seleccionar las series a traspasar para este producto.');
                }

                $seriesEnOrigen = DB::table('producto_series')
                    ->whereIn('id', $seriesIds)
                    ->where('producto_id', $productoId)
                    ->where('almacen_id', $almacenOrigenId)
                    ->where('estado', 'en_stock')
                    ->pluck('id');

                if ($seriesEnOrigen->count() !== $seriesIds->count()) {
                    throw new \Exception('Algunas series no est\u00e1n disponibles en el almac\u00e9n origen o no est\u00e1n en stock.');
                }

                $cantidad = $seriesIds->count();
            } else {
                $cantidad = $request->cantidad;
            }

            // Obtener almacenes para evitar consultas múltiples
            $almacenOrigen = Almacen::find($almacenOrigenId);
            $almacenDestino = Almacen::find($almacenDestinoId);

            if (!$almacenOrigen || !$almacenDestino) {
                throw new \Exception('Almacén no encontrado');
            }

            // Verificar stock en origen
            $inventarioOrigen = Inventario::where('producto_id', $productoId)
                ->where('almacen_id', $almacenOrigenId)
                ->first();

            if (!$inventarioOrigen || $inventarioOrigen->cantidad < $cantidad) {
                throw new \Exception('Stock insuficiente en el almacén de origen');
            }
            // Crear el traspaso
            $traspaso = Traspaso::create([
                'producto_id' => $productoId,
                'almacen_origen_id' => $almacenOrigenId,
                'almacen_destino_id' => $almacenDestinoId,
                'cantidad' => $cantidad,
                'estado' => 'completado',
                'usuario_autoriza' => auth()->id(),
                'usuario_envia' => auth()->id(),
                'fecha_envio' => now(),
                'fecha_recepcion' => now(),
                'observaciones' => $request->observaciones,
                'referencia' => $request->referencia,
                'costo_transporte' => $request->costo_transporte,
            ]);

            // Actualizar el almacen de las series involucradas
            if ($producto->requiere_serie && $seriesIds->isNotEmpty()) {
                DB::table('producto_series')
                    ->whereIn('id', $seriesIds)
                    ->update([
                        'almacen_id' => $almacenDestinoId,
                        'updated_at' => now(),
                    ]);
            }

            $producto = Producto::find($productoId);

            // Salida del almacen origen
            $this->inventarioService->salida($producto, $cantidad, [
                'almacen_id' => $almacenOrigenId,
                'motivo' => 'Traspaso a almacen ' . $almacenDestino->nombre,
                'detalles' => ['traspaso_id' => $traspaso->id, 'destino' => $almacenDestinoId, 'series' => $seriesIds],
            ]);

            // Entrada al almacen destino
            $this->inventarioService->entrada($producto, $cantidad, [
                'almacen_id' => $almacenDestinoId,
                'motivo' => 'Traspaso desde almacen ' . $almacenOrigen->nombre,
                'detalles' => ['traspaso_id' => $traspaso->id, 'origen' => $almacenOrigenId, 'series' => $seriesIds],
            ]);

            Log::info('Traspaso realizado', [
                'traspaso_id' => $traspaso->id,
                'producto_id' => $productoId,
                'origen' => $almacenOrigenId,
                'destino' => $almacenDestinoId,
                'cantidad' => $cantidad,
            ]);
        });

        return redirect()->route('traspasos.index')->with('success', 'Traspaso realizado correctamente');
    }
}

