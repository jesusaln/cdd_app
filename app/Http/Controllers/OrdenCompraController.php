<?php

namespace App\Http\Controllers;

use App\Enums\EstadoCompra;
use App\Models\Compra; // <-- Importa el modelo Compra aquí
use App\Models\CuentasPorPagar;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\ProductoPrecioHistorial;
use App\Models\Proveedor;
use App\Services\InventarioService;
use App\Mail\OrdenCompraEnviada;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Para transacciones de base de datos
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdenCompraController extends Controller
{
    public function __construct(private readonly InventarioService $inventarioService)
    {
    }

    /**
     * Muestra una lista de las órdenes de compra.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = (int) ($request->integer('per_page') ?: 10);

        $baseQuery = OrdenCompra::with([
            'proveedor' => function ($query) {
                $query->select('id', 'nombre_razon_social', 'email', 'rfc', 'telefono');
            },
            'productos',
            'almacen',
            'emailEnviadoPor' => function ($query) {
                $query->select('id', 'name');
            },
        ]);

        // Aplicar filtros
        if ($search = trim($request->get('search', ''))) {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('numero_orden', 'like', "%{$search}%")
                    ->orWhere('observaciones', 'like', "%{$search}%")
                    ->orWhereHas('proveedor', function ($q) use ($search) {
                        $q->where('nombre_razon_social', 'like', "%{$search}%")
                            ->orWhere('rfc', 'like', "%{$search}%");
                    })
                    ->orWhereHas('productos', function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%")
                            ->orWhere('descripcion', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('estado')) {
            $baseQuery->where('estado', $request->estado);
        }

        if ($request->filled('prioridad')) {
            $baseQuery->where('prioridad', $request->prioridad);
        }

        if ($request->filled('proveedor_id')) {
            $baseQuery->where('proveedor_id', $request->proveedor_id);
        }

        if ($request->filled('fecha_desde')) {
            $baseQuery->whereDate('fecha_orden', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $baseQuery->whereDate('fecha_orden', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

             = [
                'numero_orden' => ['numero_orden'] ?: null,
                'fecha_orden' => ['fecha_orden'],
                'fecha_entrega_esperada' => ['fecha_entrega_esperada'],
                'prioridad' => ['prioridad'],
                'proveedor_id' => ['proveedor_id'],
                'direccion_entrega' => ['direccion_entrega'],
                'terminos_pago' => ['terminos_pago'],
                'metodo_pago' => ['metodo_pago'],
                'subtotal' => ['subtotal'],
                'descuento_items' => ['descuento_items'],
                'descuento_general' => ['descuento_general'],
                'iva' => ['iva'],
                'total' => ['total'],
                'observaciones' => ['observaciones'],
                'estado' => 'pendiente',
            ];
            if (Schema::hasColumn('orden_compras', 'almacen_id')) {
                ['almacen_id'] = ['almacen_id'];
            }
             = OrdenCompra::create();

namespace App\Http\Controllers;

use App\Enums\EstadoCompra;
use App\Models\Compra; // <-- Importa el modelo Compra aquí
use App\Models\CuentasPorPagar;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\ProductoPrecioHistorial;
use App\Models\Proveedor;
use App\Services\InventarioService;
use App\Mail\OrdenCompraEnviada;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Para transacciones de base de datos
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdenCompraController extends Controller
{
    public function __construct(private readonly InventarioService $inventarioService)
    {
    }

    /**
     * Muestra una lista de las órdenes de compra.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = (int) ($request->integer('per_page') ?: 10);

        $baseQuery = OrdenCompra::with([
            'proveedor' => function ($query) {
                $query->select('id', 'nombre_razon_social', 'email', 'rfc', 'telefono');
            },
            'productos',
            'almacen',
            'emailEnviadoPor' => function ($query) {
                $query->select('id', 'name');
            },
        ]);

        // Aplicar filtros
        if ($search = trim($request->get('search', ''))) {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('numero_orden', 'like', "%{$search}%")
                    ->orWhere('observaciones', 'like', "%{$search}%")
                    ->orWhereHas('proveedor', function ($q) use ($search) {
                        $q->where('nombre_razon_social', 'like', "%{$search}%")
                            ->orWhere('rfc', 'like', "%{$search}%");
                    })
                    ->orWhereHas('productos', function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%")
                            ->orWhere('descripcion', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('estado')) {
            $baseQuery->where('estado', $request->estado);
        }

        if ($request->filled('prioridad')) {
            $baseQuery->where('prioridad', $request->prioridad);
        }

        if ($request->filled('proveedor_id')) {
            $baseQuery->where('proveedor_id', $request->proveedor_id);
        }

        if ($request->filled('fecha_desde')) {
            $baseQuery->whereDate('fecha_orden', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $baseQuery->whereDate('fecha_orden', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSorts = ['created_at', 'numero_orden', 'total', 'estado', 'prioridad', 'fecha_orden'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $baseQuery->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc')
            ->orderBy('id', 'desc');

        $ordenes = $baseQuery->paginate($perPage)->appends($request->query());

        // Transformar la colección del paginator
        $collection = collect($ordenes->items())->map(function ($orden) {
            // Asegurarse de trabajar con colecciones para productos
            $orden->productos = collect($orden->productos)->map(function ($p) {
                return array_merge($p->toArray(), [
                    'cantidad' => $p->pivot->cantidad ?? 0,
                    'precio' => $p->pivot->precio ?? 0,
                    'descuento' => $p->pivot->descuento ?? 0,
                    'unidad_medida' => $p->pivot->unidad_medida ?? '',
                ]);
            });
            $items = $orden->productos;

            // Crear resumen de productos para tooltip
            $productosTooltip = $items->map(function ($item) {
                return $item['nombre'] . ' (' . $item['cantidad'] . ' ' . ($item['unidad_medida'] ?: 'u') . ')';
            })->join(', ');

            // Etiquetas legibles para estados (coincidiendo con BD)
            $estadoLabels = [
                'pendiente' => 'Pendiente',
                'aprobada' => 'Aprobada',
                'enviada' => 'Enviada',
                'recibida' => 'Recibida',
                'cancelada' => 'Cancelada',
                // Estados del sistema (por si acaso)
                'borrador' => 'Borrador',
                'enviado_a_compra' => 'Enviado a Compra',
                'convertida' => 'Recibida',
            ];

            return [
                'id'                 => $orden->id,
                'numero_orden'       => $orden->numero_orden ?? $orden->id,
                'proveedor'          => $orden->proveedor ? [
                    'id'                  => $orden->proveedor->id,
                    'nombre_razon_social' => $orden->proveedor->nombre_razon_social,
                    'rfc'                 => $orden->proveedor->rfc ?? null,
                    'email'               => $orden->proveedor->email ?? null,
                ] : null,
                'almacen'            => $orden->almacen ? [
                    'id'   => $orden->almacen->id,
                    'nombre' => $orden->almacen->nombre,
                ] : null,
                'productos'           => $items,
                'productos_count'    => $items->count(),
                'productos_tooltip'  => $productosTooltip ?: 'Sin productos',
                'subtotal'           => (float) ($orden->subtotal ?? 0),
                'descuento_items'    => (float) ($orden->descuento_items ?? 0),
                'descuento_general'  => (float) ($orden->descuento_general ?? 0),
                'iva'                => (float) ($orden->iva ?? 0),
                'total'              => (float) ($orden->total ?? 0),
                'estado'             => $orden->estado ?? 'pendiente',
                'estado_label'       => $estadoLabels[$orden->estado] ?? 'Desconocido',
                'prioridad'          => $orden->prioridad ?? 'baja',
                'fecha_orden'        => $orden->fecha_orden?->format('d/m/Y'),
                'fecha_entrega_esperada' => $orden->fecha_entrega_esperada?->format('d/m/Y'),
                'created_at'         => optional($orden->created_at)->format('Y-m-d H:i:s'),
                'fecha'              => optional($orden->created_at)->format('Y-m-d'),
                // Información de email
                'email_enviado'      => (bool) ($orden->email_enviado ?? false),
                'email_enviado_fecha' => $orden->email_enviado_fecha?->format('d/m/Y H:i'),
                'email_enviado_por'  => $orden->emailEnviadoPor?->name,
                // URLs para acciones
                'urls' => [
                    'show' => route('ordenescompra.show', $orden->id),
                    'edit' => route('ordenescompra.edit', $orden->id),
                    'duplicate' => route('ordenescompra.duplicate', $orden->id),
                    'enviar' => route('ordenescompra.enviar-compra', $orden->id),
                    'convertir_directo' => route('ordenescompra.convertir-directo', $orden->id),
                    'recibir' => route('ordenescompra.recibir-mercancia', $orden->id),
                    'cancelar' => route('ordenescompra.cancelar', $orden->id),
                    'delete' => route('ordenescompra.destroy', $orden->id),
                ],
                // Permisos de acciones
                'can' => [
                    'edit' => in_array($orden->estado, ['borrador', 'pendiente', 'aprobada']),
                    'enviar' => in_array($orden->estado, ['pendiente', 'aprobada']),
                    'convertir_directo' => $orden->estado === 'pendiente',
                    'recibir' => $orden->estado === 'enviado_a_compra',
                    'cancelar' => in_array($orden->estado, ['pendiente', 'aprobada', 'enviado_a_compra', 'convertida']),
                    'delete' => in_array($orden->estado, ['borrador', 'pendiente', 'aprobada']),
                ],
            ];
        });

        // Reemplaza la colección interna del paginador por la colección transformada
        // Algunos analizadores o versiones no reconocen setCollection; en su lugar creamos
        // un nuevo LengthAwarePaginator con la colección transformada y conservamos metadata.
        $ordenes = new LengthAwarePaginator(
            $collection,
            $ordenes->total(),
            $ordenes->perPage(),
            $ordenes->currentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // Estadísticas para el dashboard (usando estados de BD)
        $stats = [
            'total' => OrdenCompra::count(),
            'pendientes' => OrdenCompra::where('estado', 'pendiente')->count(),
            'procesadas' => OrdenCompra::where('estado', 'convertida')->count(),
            'enviadas_a_proveedor' => OrdenCompra::where('estado', 'enviado_a_proveedor')->count(),
            'recibidas' => OrdenCompra::where('estado', 'recibida')->count(),
            'canceladas' => OrdenCompra::where('estado', 'cancelada')->count(),
        ];

        // Etiquetas para estados (coincidiendo con BD)
        $estadoLabels = [
            'pendiente' => 'Pendiente',
            'procesada' => 'Procesada',
            'enviada' => 'Enviada',
            'enviado_a_proveedor' => 'Enviado a Proveedor',
            'recibida' => 'Recibida',
            'cancelada' => 'Cancelada',
            // Estados del sistema (por compatibilidad)
            'borrador' => 'Borrador',
            'convertida' => 'Recibida',
        ];

        // Opciones para filtros
        $proveedores = Proveedor::select('id', 'nombre_razon_social', 'rfc')
            ->orderBy('nombre_razon_social')
            ->get()
            ->mapWithKeys(function ($proveedor) {
                return [$proveedor->id => $proveedor->nombre_razon_social . ' (' . $proveedor->rfc . ')'];
            });

        $filterOptions = [
            'estados' => $estadoLabels,
            'prioridades' => [
                'baja' => 'Baja',
                'media' => 'Media',
                'alta' => 'Alta',
                'urgente' => 'Urgente',
            ],
            'proveedores' => $proveedores,
            'per_page_options' => [10, 15, 25, 50],
        ];

        return Inertia::render('OrdenesCompra/Index', [
            'ordenesCompra' => $ordenes,
            'stats' => $stats,
            'estadoLabels' => $estadoLabels,
            'filterOptions' => $filterOptions,
            'filters' => $request->only(['search', 'estado', 'prioridad', 'proveedor_id', 'fecha_desde', 'fecha_hasta']),
            'sorting' => [
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
                'allowed_sorts' => $allowedSorts,
            ],
            'pagination' => [
                'current_page' => $ordenes->currentPage(),
                'last_page' => $ordenes->lastPage(),
                'per_page' => $perPage,
                'total' => $ordenes->total(),
                'from' => $ordenes->firstItem(),
                'to' => $ordenes->lastItem(),
            ],
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva orden de compra.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Obtiene todos los proveedores, productos y almacenes para los selectores/búsquedas en el frontend
        $proveedores = Proveedor::all();
        $productos = Producto::all();
        $almacenes = \App\Models\Almacen::all();

        // Obtener el próximo número de orden
        $proximoNumero = OrdenCompra::getProximoNumero();

        // Renderiza la vista de creación de órdenes de compra con Inertia
        return Inertia::render('OrdenesCompra/Create', [
            'proveedores' => $proveedores,
            'productos' => $productos,
            'almacenes' => $almacenes,
            'proximoNumero' => $proximoNumero,
        ]);
    }

    /**
     * Almacena una orden de compra recién creada en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Log de depuración
        Log::info('OrdenCompraController@store - Datos recibidos:', $request->all());

        // Inicia una transacción de base de datos para asegurar la integridad
        DB::beginTransaction();
        try {
            // Preparar y normalizar datos antes de validar
            $requestData = $request->all();
            if (!array_key_exists('almacen_id', $requestData) || $requestData['almacen_id'] === '' || $requestData['almacen_id'] === 0 || $requestData['almacen_id'] === '0') {
                $requestData['almacen_id'] = null;
            }
            // Mezclar de vuelta al request para que la validación use los valores normalizados
            $request->merge($requestData);

            // Valida los datos de entrada del formulario
            $validatedData = $request->validate([
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
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.tipo' => 'required|in:producto',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.precio' => 'required|numeric|min:0',
                'items.*.descuento' => 'required|numeric|min:0|max:100',
            ]);

            Log::info('OrdenCompraController@store - Datos validados:', $validatedData);

            // Crea la nueva orden de compra en la tabla \u0027orden_compras\u0027
            $data = [
                'numero_orden' => ['numero_orden'] ?: null,
                'fecha_orden' => ['fecha_orden'],
                'fecha_entrega_esperada' => ['fecha_entrega_esperada'],
                'prioridad' => ['prioridad'],
                'proveedor_id' => ['proveedor_id'],
                'direccion_entrega' => ['direccion_entrega'],
                'terminos_pago' => ['terminos_pago'],
                'metodo_pago' => ['metodo_pago'],
                'subtotal' => ['subtotal'],
                'descuento_items' => ['descuento_items'],
                'descuento_general' => ['descuento_general'],
                'iva' => ['iva'],
                'total' => ['total'],
                'observaciones' => ['observaciones'],
                'estado' => 'pendiente',
            ];
            if (\\Illuminate\\Support\\Facades\\Schema::hasColumn('orden_compras', 'almacen_id')) {
                ['almacen_id'] = ['almacen_id'];
            }
            $ordenCompra = OrdenCompra::create($data);


