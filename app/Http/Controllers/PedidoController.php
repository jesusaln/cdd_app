<?php

namespace App\Http\Controllers;

use App\Enums\EstadoCotizacion;
use App\Enums\EstadoPedido;
use App\Enums\EstadoVenta;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\CuentasPorCobrar;
use App\Services\InventarioService;
use App\Services\MarginService;
use App\Models\SatEstado;
use App\Models\SatRegimenFiscal;
use App\Models\SatUsoCfdi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PedidoController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly InventarioService $inventarioService)
    {
    }

    /**
      * Display a listing of the resource.
      */
     public function index(Request $request)
     {
         $perPage = (int) ($request->integer('per_page') ?: 10);

         // Validar elementos por página
         $validPerPages = [10, 15, 25, 50, 100];
         if (!in_array($perPage, $validPerPages)) {
             $perPage = 10;
         }

         $baseQuery = Pedido::with([
             'cliente',
             // Filtrar ítems a tipos conocidos para evitar fallos por tipos inválidos
             'items' => function ($q) {
                 $q->whereIn('pedible_type', [Producto::class, Servicio::class]);
             },
             'items.pedible',
             'createdBy', 'updatedBy', 'deletedBy', 'emailEnviadoPor'
         ]);

         // Aplicar filtros
         if ($search = trim($request->get('search', ''))) {
             $baseQuery->where(function ($query) use ($search) {
                 $query->where('numero_pedido', 'like', "%{$search}%")
                       ->orWhere('id', 'like', "%{$search}%")
                       ->orWhereHas('cliente', function ($q) use ($search) {
                           $q->where('nombre_razon_social', 'like', "%{$search}%")
                             ->orWhere('rfc', 'like', "%{$search}%");
                       })
                       ->orWhereHas('items.pedible', function ($q) use ($search) {
                           $q->where('nombre', 'like', "%{$search}%")
                             ->orWhere('descripcion', 'like', "%{$search}%");
                       });
             });
         }

         if ($request->filled('estado')) {
             $baseQuery->where('estado', $request->estado);
         }

         if ($request->filled('cliente_id')) {
             $baseQuery->where('cliente_id', $request->cliente_id);
         }

         if ($request->filled('fecha_desde')) {
             $baseQuery->whereDate('fecha', '>=', $request->fecha_desde);
         }

         if ($request->filled('fecha_hasta')) {
             $baseQuery->whereDate('fecha', '<=', $request->fecha_hasta);
         }

         // Ordenamiento
         $sortBy = $request->get('sort_by', 'created_at');
         $sortDirection = $request->get('sort_direction', 'desc');

         $allowedSorts = ['created_at', 'fecha', 'total', 'estado', 'numero_pedido'];
         if (!in_array($sortBy, $allowedSorts)) {
             $sortBy = 'created_at';
         }

         $baseQuery->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc')
                   ->orderBy('id', 'desc');

         $paginator = $baseQuery->paginate($perPage)->appends($request->query());
         $pedidos = collect($paginator->items());

         $transformed = $pedidos->filter(function ($pedido) {
             // Filtrar pedidos con cliente y al menos un item válido
             return $pedido->cliente !== null && $pedido->items->isNotEmpty();
         })->map(function ($pedido) {
                $items = $pedido->items->map(function ($item) {
                    $pedible = $item->pedible;
                    return [
                        'id' => $pedible->id,
                        'nombre' => $pedible->nombre ?? 'Sin nombre',
                        'tipo' => ($item->pedible_type === Producto::class || $item->pedible_type === 'producto') ? 'producto' : 'servicio',
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                        'descuento' => $item->descuento ?? 0,
                    ];
                });

                $createdAtIso = optional($pedido->created_at)->toIso8601String();
                $updatedAtIso = optional($pedido->updated_at)->toIso8601String();

                return [
                    'id' => $pedido->id,
                    'numero_pedido' => $pedido->numero_pedido,
                    'fecha' => $pedido->fecha ? $pedido->fecha->format('Y-m-d') : $pedido->created_at->format('Y-m-d'),
                    'created_at' => $createdAtIso,
                    'updated_at' => $updatedAtIso,
                    'cliente' => [
                        'id' => $pedido->cliente->id,
                        'nombre' => $pedido->cliente->nombre_razon_social ?? 'Sin nombre',
                        'email' => $pedido->cliente->email,
                        'telefono' => $pedido->cliente->telefono,
                        'rfc' => $pedido->cliente->rfc,
                        'regimen_fiscal' => $pedido->cliente->regimen_fiscal,
                        'uso_cfdi' => $pedido->cliente->uso_cfdi,
                        'calle' => $pedido->cliente->calle,
                        'numero_exterior' => $pedido->cliente->numero_exterior,
                        'numero_interior' => $pedido->cliente->numero_interior,
                        'colonia' => $pedido->cliente->colonia,
                        'codigo_postal' => $pedido->cliente->codigo_postal,
                        'municipio' => $pedido->cliente->municipio,
                        'estado' => $pedido->cliente->estado,
                        'pais' => $pedido->cliente->pais,
                    ],
                    'productos' => $items->toArray(),
                    'subtotal' => $pedido->subtotal,
                    'descuento_general' => $pedido->descuento_general,
                    'iva' => $pedido->iva,
                    'total' => $pedido->total,
                    'estado' => $pedido->estado->value,
                    'numero_pedido' => $pedido->numero_pedido,
                    'cotizacion_id' => $pedido->cotizacion_id,

                    // Información de email
                    'email_enviado' => (bool) ($pedido->email_enviado ?? false),
                    'email_enviado_fecha' => $pedido->email_enviado_fecha?->format('d/m/Y H:i'),
                    'email_enviado_por' => $pedido->emailEnviadoPor?->name,

                    // Auditoría
                    'creado_por_nombre' => $pedido->createdBy?->name,
                    'actualizado_por_nombre' => $pedido->updatedBy?->name,
                    'eliminado_por_nombre' => $pedido->deletedBy?->name,

                    // Redundancia segura para el modal
                    'metadata' => [
                        'creado_por' => $pedido->createdBy?->name,
                        'actualizado_por' => $pedido->updatedBy?->name,
                        'eliminado_por' => $pedido->deletedBy?->name,
                        'creado_en' => $createdAtIso,
                        'actualizado_en' => $updatedAtIso,
                        'eliminado_en' => optional($pedido->deleted_at)->toIso8601String(),
                    ],
                ];
            });

        // Estadísticas para el dashboard
        $stats = [
            'total' => Pedido::count(),
            'borradores' => Pedido::where('estado', EstadoPedido::Borrador)->count(),
            'pendientes' => Pedido::where('estado', EstadoPedido::Pendiente)->count(),
            'confirmados' => Pedido::where('estado', EstadoPedido::Confirmado)->count(),
            'enviados_venta' => Pedido::where('estado', EstadoPedido::EnviadoVenta)->count(),
            'cancelados' => Pedido::where('estado', EstadoPedido::Cancelado)->count(),
            'con_cotizacion' => Pedido::whereNotNull('cotizacion_id')->count(),
            'sin_cotizacion' => Pedido::whereNull('cotizacion_id')->count(),
        ];

        // Opciones para filtros
        $clientes = Cliente::select('id', 'nombre_razon_social', 'rfc')
            ->orderBy('nombre_razon_social')
            ->get()
            ->mapWithKeys(function ($cliente) {
                return [$cliente->id => $cliente->nombre_razon_social . ' (' . $cliente->rfc . ')'];
            });

        $filterOptions = [
            'estados' => collect(EstadoPedido::cases())->map(fn($estado) => [
                'value' => $estado->value,
                'label' => $estado->label(),
                'color' => $estado->color()
            ])->toArray(),
            'clientes' => $clientes,
            'per_page_options' => [10, 15, 25, 50, 100],
        ];

        return Inertia::render('Pedidos/IndexNew', [
            'pedidos' => $paginator,
            'stats' => $stats,
            'filterOptions' => $filterOptions,
            'filters' => $request->only(['search', 'estado', 'cliente_id', 'fecha_desde', 'fecha_hasta']),
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
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Pedidos/Create', [
            'clientes' => Cliente::activos()->select('id', 'nombre_razon_social', 'email', 'telefono')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_venta', 'descripcion')->get(),
            'servicios' => Servicio::select('id', 'nombre', 'precio', 'descripcion')->get(),
            'catalogs' => [
                'tiposPersona' => [
                    ['value' => 'fisica', 'text' => 'Persona Física'],
                    ['value' => 'moral', 'text' => 'Persona Moral'],
                ],
                'estados' => SatEstado::orderBy('nombre')
                    ->get(['clave', 'nombre'])
                    ->map(function ($estado) {
                        return [
                            'value' => $estado->clave,
                            'text' => $estado->clave . ' — ' . $estado->nombre
                        ];
                    })
                    ->toArray(),
                'regimenesFiscales' => SatRegimenFiscal::orderBy('clave')
                    ->get(['clave', 'descripcion', 'persona_fisica', 'persona_moral'])
                    ->toArray(),
                'usosCFDI' => SatUsoCfdi::orderBy('clave')
                    ->get(['clave', 'descripcion'])
                    ->map(function ($uso) {
                        return [
                            'value' => $uso->clave,
                            'text' => $uso->clave . ' — ' . $uso->descripcion
                        ];
                    })
                    ->toArray(),
            ],
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
            'ajustar_margen' => 'nullable|boolean',
        ]);

        // Validar márgenes de ganancia
        $marginService = new MarginService();
        $validacionMargen = $marginService->validarMargenesProductos($validated['productos']);

        if (!$validacionMargen['todos_validos']) {
            // Si hay productos con margen insuficiente, verificar si el usuario aceptó el ajuste
            if ($request->has('ajustar_margen') && $request->ajustar_margen === 'true') {
                // Ajustar precios automáticamente
                foreach ($validated['productos'] as &$item) {
                    if ($item['tipo'] === 'producto') {
                        $producto = Producto::find($item['id']);
                        if ($producto) {
                            $item['precio'] = $marginService->ajustarPrecioAlMargen($producto, $item['precio']);
                        }
                    }
                }
            } else {
                // Mostrar advertencia y permitir al usuario decidir
                $mensaje = $marginService->generarMensajeAdvertencia($validacionMargen['productos_bajo_margen']);
                return redirect()->back()
                    ->withInput()
                    ->with('warning', $mensaje)
                    ->with('requiere_confirmacion_margen', true)
                    ->with('productos_bajo_margen', $validacionMargen['productos_bajo_margen']);
            }
        }

        $subtotal = 0;
        $descuentoItems = 0;
        foreach ($validated['productos'] as $item) {
            $subtotalItem = $item['cantidad'] * $item['precio'];
            $descuentoItems += $subtotalItem * ($item['descuento'] / 100);
            $subtotal += $subtotalItem;
        }

        $descuentoGeneralMonto = ($subtotal - $descuentoItems) * ($request->descuento_general / 100);
        $subtotalFinal = ($subtotal - $descuentoItems) - $descuentoGeneralMonto;
        $ivaRate = \App\Services\EmpresaConfiguracionService::getIvaPorcentaje() / 100;
        $iva = $subtotalFinal * $ivaRate;
        $total = $subtotalFinal + $iva;

        $numero_pedido = $this->generarNumeroPedido();

        $pedido = Pedido::create([
            'cliente_id' => $validated['cliente_id'],
            'cotizacion_id' => null, // Puede llenarse si se crea desde una cotización
            'numero_pedido' => $numero_pedido,
            'subtotal' => $subtotal,
            'descuento_general' => $descuentoGeneralMonto,
            'iva' => $iva,
            'total' => $total,
            'fecha' => now(),
            'estado' => EstadoPedido::Borrador,
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

            PedidoItem::create([
                'pedido_id' => $pedido->id,
                'pedible_id' => $item['id'],
                'pedible_type' => $class,
                'cantidad' => $item['cantidad'],
                'precio' => $item['precio'],
                'descuento' => $item['descuento'],
                'subtotal' => $subtotalItem,
                'descuento_monto' => $descuentoMontoItem,
            ]);
        }

        return redirect()->route('pedidos.index')->with('success', 'Pedido creado con éxito');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pedido = Pedido::with([
            'cliente',
            'items' => function ($q) { $q->whereIn('pedible_type', [Producto::class, Servicio::class]); },
            'items.pedible'
        ])->findOrFail($id);

        $items = $pedido->items->map(function ($item) {
            $pedible = $item->pedible;
            return [
                'id' => $pedible->id,
                'nombre' => $pedible->nombre ?? $pedible->descripcion,
                'tipo' => ($item->pedible_type === Producto::class || $item->pedible_type === 'producto') ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Pedidos/Show', [
            'pedido' => [
                'id' => $pedido->id,
                'cliente' => $pedido->cliente,
                'productos' => $items,
                'subtotal' => $pedido->subtotal,
                'descuento_general' => $pedido->descuento_general,
                'iva' => $pedido->iva,
                'total' => $pedido->total,
                'fecha' => $pedido->fecha ? $pedido->fecha->format('Y-m-d') : $pedido->created_at->format('Y-m-d'),
                'notas' => $pedido->notas,
                'estado' => $pedido->estado->value,
                'numero_pedido' => $pedido->numero_pedido,
                'cotizacion_id' => $pedido->cotizacion_id,
            ],
            'canEdit' => $pedido->estado === EstadoPedido::Borrador || $pedido->estado === EstadoPedido::Pendiente,
            'canDelete' => $pedido->estado === EstadoPedido::Borrador || $pedido->estado === EstadoPedido::Pendiente,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $pedido = Pedido::with(['cliente', 'items.pedible'])->findOrFail($id);

        // Permitir edición solo si está en Borrador o Pendiente
        if (!in_array($pedido->estado, [EstadoPedido::Borrador, EstadoPedido::Pendiente], true)) {
            return Redirect::route('pedidos.show', $pedido->id)
                ->with('warning', 'Solo pedidos en borrador o pendientes pueden ser editados');
        }

        $items = $pedido->items->map(function ($item) {
            $pedible = $item->pedible;
            return [
                'id' => $pedible->id,
                'nombre' => $pedible->nombre ?? $pedible->descripcion,
                'tipo' => ($item->pedible_type === Producto::class || $item->pedible_type === 'producto') ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Pedidos/Edit', [
            'pedido' => [
                'id' => $pedido->id,
                'cliente_id' => $pedido->cliente_id,
                'cliente' => $pedido->cliente,
                'productos' => $items,
                'subtotal' => $pedido->subtotal,
                'descuento_general' => $pedido->descuento_general,
                'iva' => $pedido->iva,
                'total' => $pedido->total,
                'fecha' => $pedido->fecha ? $pedido->fecha->format('Y-m-d') : $pedido->created_at->format('Y-m-d'),
                'notas' => $pedido->notas,
                'numero_pedido' => $pedido->numero_pedido,
                'cotizacion_id' => $pedido->cotizacion_id,
                'informacion_general' => [
                    'numero' => [
                        'label' => 'Número de Pedido',
                        'value' => $pedido->numero_pedido,
                        'tipo' => 'fijo',
                        'descripcion' => 'Este número es fijo para todas los pedidos'
                    ],
                    'fecha' => [
                        'label' => 'Fecha de Pedido',
                        'value' => $pedido->fecha ? $pedido->fecha->format('d/m/Y') : now()->format('d/m/Y'),
                        'tipo' => 'automatica',
                        'descripcion' => 'Esta fecha se establece automáticamente con la fecha de creación'
                    ]
                ]
            ],
            'clientes' => Cliente::activos()->select('id', 'nombre_razon_social', 'email', 'telefono')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_venta', 'descripcion')->get(),
            'servicios' => Servicio::select('id', 'nombre', 'precio', 'descripcion')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        // Permitir edición solo si está en Borrador o Pendiente
        if (!in_array($pedido->estado, [EstadoPedido::Borrador, EstadoPedido::Pendiente], true)) {
            return Redirect::back()->with('error', 'Solo pedidos en borrador o pendientes pueden ser actualizados');
        }

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'numero_pedido' => 'required|string|unique:pedidos,numero_pedido,' . $pedido->id,
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
            'descuento_general' => 'nullable|numeric|min:0|max:100',
            'notas' => 'nullable|string',
            'ajustar_margen' => 'nullable|boolean',
        ]);

        // Validar márgenes de ganancia antes de calcular totales
        $marginService = new MarginService();
        $validacionMargen = $marginService->validarMargenesProductos($validated['productos']);

        if (!$validacionMargen['todos_validos']) {
            // Si hay productos con margen insuficiente, verificar si el usuario aceptó el ajuste
            if ($request->has('ajustar_margen') && $request->ajustar_margen === 'true') {
                // Ajustar precios automáticamente
                foreach ($validated['productos'] as &$item) {
                    if ($item['tipo'] === 'producto') {
                        $producto = Producto::find($item['id']);
                        if ($producto) {
                            $item['precio'] = $marginService->ajustarPrecioAlMargen($producto, $item['precio']);
                        }
                    }
                }
            } else {
                // Mostrar advertencia y permitir al usuario decidir
                $mensaje = $marginService->generarMensajeAdvertencia($validacionMargen['productos_bajo_margen']);
                return Redirect::back()
                    ->withInput()
                    ->with('warning', $mensaje)
                    ->with('requiere_confirmacion_margen', true)
                    ->with('productos_bajo_margen', $validacionMargen['productos_bajo_margen']);
            }
        }

        $subtotal = 0;
        $descuentoItems = 0;
        foreach ($validated['productos'] as $item) {
            $subtotalItem = $item['cantidad'] * $item['precio'];
            $descuentoItems += $subtotalItem * ($item['descuento'] / 100);
            $subtotal += $subtotalItem;
        }

        $descuentoGeneralMonto = ($subtotal - $descuentoItems) * ($request->descuento_general / 100);
        $subtotalFinal = ($subtotal - $descuentoItems) - $descuentoGeneralMonto;
        $ivaRate = \App\Services\EmpresaConfiguracionService::getIvaPorcentaje() / 100;
        $iva = $subtotalFinal * $ivaRate;
        $total = $subtotalFinal + $iva;

        // Guarda el estado ANTES de actualizar (clave del fix)
        $estadoAnterior = $pedido->estado;

        // Determinar el nuevo estado: si está en Borrador, cambiarlo a Pendiente
        $nuevoEstado = $pedido->estado === EstadoPedido::Borrador
            ? EstadoPedido::Pendiente
            : $pedido->estado;

        // Atomicidad: actualización + refresco de items
        DB::transaction(function () use (&$pedido, $validated, $subtotal, $descuentoGeneralMonto, $iva, $total, $nuevoEstado, $request) {
            $pedido->update([
                'cliente_id' => $validated['cliente_id'],
                'numero_pedido' => $validated['numero_pedido'],
                'subtotal' => $subtotal,
                'descuento_general' => $descuentoGeneralMonto,
                'iva' => $iva,
                'total' => $total,
                'fecha' => now(),
                'estado' => $nuevoEstado,
                'notas' => $request->notas,
            ]);

            // Eliminar ítems anteriores
            $pedido->items()->delete();

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

                PedidoItem::create([
                    'pedido_id' => $pedido->id,
                    'pedible_id' => $itemData['id'],
                    'pedible_type' => $class,
                    'cantidad' => $itemData['cantidad'],
                    'precio' => $itemData['precio'],
                    'descuento' => $itemData['descuento'],
                    'subtotal' => $subtotalItem,
                    'descuento_monto' => $descuentoMontoItem,
                ]);
            }
        });

        // Mensaje basado en el estado ANTERIOR
        $mensajeExito = ($estadoAnterior === EstadoPedido::Borrador && $nuevoEstado === EstadoPedido::Pendiente)
            ? 'Pedido actualizado y cambiado a estado pendiente exitosamente'
            : 'Pedido actualizado exitosamente';

        return Redirect::route('pedidos.index')
            ->with('success', $mensajeExito);
    }

    /**
     * Confirm the specified resource (reserve inventory).
     */
    public function confirmar($id)
    {
        $pedido = Pedido::with('items.pedible')->findOrFail($id);

        // Permitir confirmar solo si está en Pendiente
        if ($pedido->estado !== EstadoPedido::Pendiente) {
            return response()->json([
                'success' => false,
                'error' => 'Solo pedidos pendientes pueden ser confirmados'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Validar y reservar inventario para productos
            foreach ($pedido->items as $item) {
                if ($item->pedible_type === Producto::class || $item->pedible_type === 'producto') {
                    $producto = Producto::with('inventarios')->find($item->pedible_id);
                    if (!$producto) {
                        return response()->json([
                            'success' => false,
                            'error' => "Producto con ID {$item->pedible_id} no encontrado"
                        ], 400);
                    }

                    if ($producto->stock_disponible < $item->cantidad) {
                        return response()->json([
                            'success' => false,
                            'error' => "Stock insuficiente para '{$producto->nombre}'. Disponible: {$producto->stock_disponible}, Solicitado: {$item->cantidad}"
                        ], 400);
                    }

                    // Nota: Las reservas necesitan extensión del InventarioService para trazabilidad completa
                    // Por ahora mantenemos la lógica directa pero registramos el movimiento
                    $producto->increment('reservado', $item->cantidad);

                    // Registrar movimiento de reserva (tipo especial para trazabilidad)
                    $this->inventarioService->entrada($producto, $item->cantidad, [
                        'motivo' => 'Reserva por pedido confirmado',
                        'referencia' => $pedido,
                        'detalles' => [
                            'pedido_id' => $pedido->id,
                            'tipo_operacion' => 'reserva'
                        ],
                    ]);

                    Log::info("Inventario reservado para producto {$producto->id}", [
                        'producto_id' => $producto->id,
                        'pedido_id' => $pedido->id,
                        'cantidad_reservada' => $item->cantidad,
                        'reservado_anterior' => $producto->reservado - $item->cantidad,
                        'reservado_actual' => $producto->reservado
                    ]);
                }
            }

            // Actualizar estado a confirmado
            $pedido->update(['estado' => EstadoPedido::Confirmado]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido confirmado e inventario reservado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al confirmar pedido: ' . $e->getMessage(), [
                'pedido_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al confirmar el pedido',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Cancel the specified resource (soft cancel).
     */
    public function cancel($id)
    {
        $pedido = Pedido::with('items.pedible')->findOrFail($id);

        // Permitir cancelar en cualquier estado excepto ya cancelado
        if ($pedido->estado === EstadoPedido::Cancelado) {
            return response()->json([
                'success' => false,
                'error' => 'El pedido ya está cancelado'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Liberar reservas si el pedido estaba confirmado
            if (in_array($pedido->estado, [EstadoPedido::Confirmado, EstadoPedido::EnPreparacion, EstadoPedido::ListoEntrega])) {
                foreach ($pedido->items as $item) {
                    if ($item->pedible_type === Producto::class || $item->pedible_type === 'producto') {
                        $producto = Producto::find($item->pedible_id);
                        if ($producto && $producto->reservado >= $item->cantidad) {
                            // Nota: Las reservas necesitan extensión del InventarioService para trazabilidad completa
                            $producto->decrement('reservado', $item->cantidad);

                            // Registrar movimiento de liberación de reserva
                            $this->inventarioService->salida($producto, $item->cantidad, [
                                'motivo' => 'Liberación de reserva por cancelación de pedido',
                                'referencia' => $pedido,
                                'detalles' => [
                                    'pedido_id' => $pedido->id,
                                    'tipo_operacion' => 'liberacion_reserva'
                                ],
                            ]);

                            Log::info("Reserva liberada para producto {$producto->id}", [
                                'producto_id' => $producto->id,
                                'pedido_id' => $pedido->id,
                                'cantidad_liberada' => $item->cantidad,
                                'reservado_anterior' => $producto->reservado + $item->cantidad,
                                'reservado_actual' => $producto->reservado
                            ]);
                        }
                    }
                }
            }

            // Revertir estado de la cotización asociada a pendiente
            if ($pedido->cotizacion) {
                $pedido->cotizacion->update(['estado' => EstadoCotizacion::Pendiente]);
                Log::info("Cotización revertida a pendiente al cancelar pedido", [
                    'pedido_id' => $pedido->id,
                    'cotizacion_id' => $pedido->cotizacion_id
                ]);
            }

            // Actualizar estado a cancelado y registrar quién lo canceló
            $pedido->update([
                'estado' => EstadoPedido::Cancelado,
                'deleted_by' => Auth::id(),
                'deleted_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido cancelado exitosamente',
                'eliminado_por' => Auth::user()->name ?? 'Usuario actual'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cancelar pedido: ' . $e->getMessage(), [
                'pedido_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al cancelar el pedido',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            try {
                $pedido = Pedido::with('cotizacion')->findOrFail($id);

                // Verificar que el pedido puede ser eliminado
                if (!in_array($pedido->estado, [EstadoPedido::Borrador, EstadoPedido::Pendiente], true)) {
                    return Redirect::back()->with('error', 'Solo pedidos en borrador o pendientes pueden ser eliminados');
                }

                // Guardar información de la cotización antes de eliminar
                $cotizacionId = $pedido->cotizacion_id;
                $cotizacion = $pedido->cotizacion;

                // Eliminar los items del pedido primero
                $pedido->items()->delete();

                // Eliminar el pedido
                $pedido->delete();

                // Revertir el estado de la cotización asociada DESPUÉS de eliminar el pedido
                if ($cotizacionId && $cotizacion) {
                    $cotizacion->estado = 'pendiente';
                    $cotizacion->save();

                    Log::info("Pedido ID {$id} eliminado y Cotización ID {$cotizacionId} revertida a estado pendiente");
                }

                return Redirect::route('pedidos.index')
                    ->with('success', 'Pedido eliminado exitosamente y cotización revertida a pendiente');
            } catch (\Exception $e) {
                Log::error('Error al eliminar pedido: ' . $e->getMessage());

                // La transacción se revertirá automáticamente
                return Redirect::back()
                    ->with('error', 'Error al eliminar el pedido: ' . $e->getMessage());
            }
        });
    }

    /**
     * Duplicate a pedido.
     */
    public function duplicate(Request $request, $id)
    {
        try {
            $pedido = Pedido::with('cliente', 'items.pedible')->findOrFail($id);

            // Validar que el pedido tenga ítems
            if ($pedido->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se puede duplicar un pedido sin ítems.'
                ], 400);
            }

            DB::beginTransaction();

            // Crear nuevo pedido con datos básicos
            $nuevoPedido = new Pedido([
                'cliente_id' => $pedido->cliente_id,
                'cotizacion_id' => $pedido->cotizacion_id,
                'numero_pedido' => $this->generarNumeroPedido(),
                'subtotal' => $pedido->subtotal,
                'descuento_general' => $pedido->descuento_general,
                'iva' => $pedido->iva,
                'total' => $pedido->total,
                'notas' => $pedido->notas,
                'estado' => EstadoPedido::Borrador,
                'fecha' => now(),
            ]);

            $nuevoPedido->save();

            // Duplicar los ítems validando que los productos/servicios existan
            $itemsDuplicados = 0;
            foreach ($pedido->items as $item) {
                // Verificar que el producto/servicio aún existe
                $modelo = $item->pedible;
                if (!$modelo) {
                    Log::warning("Producto/Servicio no encontrado al duplicar pedido", [
                        'pedido_id' => $id,
                        'pedible_id' => $item->pedible_id,
                        'pedible_type' => $item->pedible_type
                    ]);
                    continue; // Saltar este ítem
                }

                $nuevoPedido->items()->create([
                    'pedible_id' => $item->pedible_id,
                    'pedible_type' => $item->pedible_type,
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                    'subtotal' => $item->subtotal,
                    'descuento_monto' => $item->descuento_monto,
                ]);

                $itemsDuplicados++;
            }

            // Verificar que al menos se duplicó un ítem
            if ($itemsDuplicados === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'No se pudieron duplicar los ítems del pedido.'
                ], 400);
            }

            DB::commit();

            Log::info('Pedido duplicado exitosamente', [
                'pedido_original_id' => $id,
                'pedido_nuevo_id' => $nuevoPedido->id,
                'items_duplicados' => $itemsDuplicados
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pedido duplicado correctamente.',
                'pedido_id' => $nuevoPedido->id,
                'numero_pedido' => $nuevoPedido->numero_pedido,
                'items_count' => $itemsDuplicados
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Error de base de datos al duplicar pedido: ' . $e->getMessage(), [
                'pedido_id' => $id,
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error de base de datos al duplicar el pedido.',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error general al duplicar pedido: ' . $e->getMessage(), [
                'pedido_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno al duplicar el pedido.',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Convertir un pedido en venta.
     * - Se unifican nombres con VentaController (numero_venta, fecha, ventable_*).
     * - Se valida y descuenta inventario de productos.
     */
    public function enviarAVenta(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Obtener el pedido con relaciones completas
            $pedido = Pedido::with([
                'items.pedible',  // producto o servicio
                'cliente',
            ])->findOrFail($id);

            // Validar estado del pedido
            if (!$this->puedeEnviarseAVenta($pedido)) {
                return response()->json([
                    'success' => false,
                    'error' => 'El pedido no está en un estado válido para convertirse en venta',
                    'estado_actual' => $pedido->estado->value,
                    'requiere_confirmacion' => false
                ], 400);
            }

            // Validar que tenga items
            if ($pedido->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'El pedido no contiene ítems para convertir en venta',
                    'requiere_confirmacion' => false
                ], 400);
            }

            // Validar cliente
            if (!$pedido->cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'El pedido no tiene cliente asociado',
                    'requiere_confirmacion' => false
                ], 400);
            }

            // Verificar si ya fue convertido (opcional: permitir reenvío con forzar)
            $ventaExistente = Venta::where('pedido_id', $pedido->id)->first();

            if ($ventaExistente && !$request->has('forzar_reenvio')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Este pedido ya fue convertido en venta',
                    'requiere_confirmacion' => true,
                    'venta_id' => $ventaExistente->id,
                    'numero_venta' => $ventaExistente->numero_venta
                ], 409); // 409 Conflict
            }

            // ✅ VALIDAR STOCK DISPONIBLE ANTES DE CREAR VENTA (considerando reservas)
            foreach ($pedido->items as $item) {
                if ($item->pedible_type === Producto::class || $item->pedible_type === 'producto') {
                    $producto = Producto::with('inventarios')->find($item->pedible_id);
                    if (!$producto) {
                        return response()->json([
                            'success' => false,
                            'error' => "Producto con ID {$item->pedible_id} no encontrado",
                            'requiere_confirmacion' => false
                        ], 400);
                    }

                    if ($producto->stock < $item->cantidad) {
                        return response()->json([
                            'success' => false,
                            'error' => "Stock insuficiente para '{$producto->nombre}'. Total: {$producto->stock}, Solicitado: {$item->cantidad}",
                            'requiere_confirmacion' => false
                        ], 400);
                    }
                }
            }

            // Generar número de venta (formato propio de este controlador)
            $numeroVenta = $this->generarNumeroVenta();

            // Crear la venta (nombres alineados con VentaController)
            $venta = new Venta();
            $venta->fill([
                'cliente_id' => $pedido->cliente_id,
                'pedido_id' => $pedido->id,
                'numero_venta' => $numeroVenta,
                'fecha' => now(),
                'estado' => EstadoVenta::Pendiente, // Cambiado a Pendiente (Por Pagar)
                'subtotal' => $pedido->subtotal,
                'descuento_general' => $pedido->descuento_general,
                'iva' => $pedido->iva,
                'total' => $pedido->total,
                'notas' => "Generado desde pedido #{$pedido->numero_pedido}",
                'user_id' => $request->user()->id ?? null,
            ]);
            $venta->save();

            // Crear cuenta por cobrar si la venta no está pagada (igual que en ventas directas)
            if (!$venta->pagado) {
                CuentasPorCobrar::create([
                    'venta_id' => $venta->id,
                    'monto_total' => $venta->total,
                    'monto_pagado' => 0,
                    'monto_pendiente' => $venta->total,
                    'fecha_vencimiento' => now()->addDays(30), // 30 días por defecto
                    'estado' => 'pendiente',
                    'notas' => 'Cuenta por cobrar generada automáticamente desde pedido #' . $pedido->numero_pedido,
                ]);
            }

            // Copiar ítems del pedido a la venta y DESCONTAR INVENTARIO
            foreach ($pedido->items as $item) {
                $ventaItem = VentaItem::create([
                    'venta_id' => $venta->id,
                    'ventable_id' => $item->pedible_id,     // ← unificado con VentaController
                    'ventable_type' => $item->pedible_type, // ← unificado con VentaController
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                    'descuento_monto' => $item->descuento_monto,
                    'subtotal' => $item->subtotal,
                ]);

                // 6o. DESCONTAR INVENTARIO SOLO PARA PRODUCTOS (consumir reservas)
                if ($item->pedible_type === Producto::class || $item->pedible_type === 'producto') {
                    $producto = Producto::find($item->pedible_id);
                    if ($producto) {
                        $cantidadRestante = $item->cantidad;

                        // Consumir reservas
                        if ($producto->reservado >= $cantidadRestante) {
                            // Nota: Las reservas necesitan extensión del InventarioService para trazabilidad completa
                            $producto->decrement('reservado', $cantidadRestante);

                            // Registrar movimiento de consumo de reserva
                            $this->inventarioService->salida($producto, $cantidadRestante, [
                                'motivo' => 'Consumo de reserva por conversión pedido a venta',
                                'referencia' => $venta,
                                'detalles' => [
                                    'pedido_id' => $pedido->id,
                                    'venta_id' => $venta->id,
                                    'tipo_operacion' => 'consumo_reserva'
                                ],
                            ]);

                            Log::info("Reserva consumida para producto {$producto->id} (pedido → venta)", [
                                'producto_id' => $producto->id,
                                'pedido_id' => $pedido->id,
                                'venta_id' => $venta->id,
                                'reserva_consumida' => $cantidadRestante,
                                'reservado_anterior' => $producto->reservado + $cantidadRestante,
                                'reservado_actual' => $producto->reservado
                            ]);
                            $cantidadRestante = 0;
                        } else {
                            // Si no hay suficientes reservas, consumir lo disponible y reducir stock
                            $consumirReserva = $producto->reservado;
                            $producto->decrement('reservado', $consumirReserva);
                            $cantidadRestante -= $consumirReserva;

                            $stockAnterior = $producto->stock;
                            $this->inventarioService->salida($producto, $cantidadRestante, [
                                'motivo' => 'Conversión de pedido a venta',
                                'referencia' => $venta,
                                'detalles' => [
                                    'pedido_id' => $pedido->id,
                                    'pedido_item_id' => $item->id,
                                    'venta_item_id' => $ventaItem->id,
                                    'reserva_consumida' => $consumirReserva,
                                ],
                            ]);

                            Log::info("Reserva parcial y stock reducido para producto {$producto->id} (pedido → venta)", [
                                'producto_id' => $producto->id,
                                'pedido_id' => $pedido->id,
                                'venta_id' => $venta->id,
                                'reserva_consumida' => $consumirReserva,
                                'stock_reducido' => $cantidadRestante,
                                'reservado_actual' => $producto->reservado,
                                'stock_anterior' => $stockAnterior,
                                'stock_actual' => $producto->stock
                            ]);
                        }
                    }
                }
            }

            // Actualizar estado del pedido a enviado a venta
            $pedido->update(['estado' => EstadoPedido::EnviadoVenta]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta creada exitosamente con descuento de inventario',
                'venta_id' => $venta->id,
                'numero_venta' => $venta->numero_venta,
                'items_count' => $venta->items()->count(),
                'total' => $venta->total
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al convertir pedido a venta: ' . $e->getMessage(), [
                'pedido_id' => $id,
                'user_id' => $request->user()->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno al procesar la conversión a venta',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Determina si el pedido puede convertirse en venta.
     */
    private function puedeEnviarseAVenta(Pedido $pedido): bool
    {
        $estadosValidos = [
            EstadoPedido::Confirmado,
            EstadoPedido::EnPreparacion,
            EstadoPedido::ListoEntrega,
            EstadoPedido::Entregado,
            EstadoPedido::Borrador,
        ];

        return in_array($pedido->estado, $estadosValidos, true);
    }

    /**
     * Genera un número de venta único.
     * (Formato local a este controlador: VEN-######)
     */
    private function generarNumeroVenta(): string
    {
        $ultimo = Venta::orderBy('id', 'desc')->first();
        $numero = $ultimo ? $ultimo->id + 1 : 1;
        return 'VEN-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Genera un número de pedido único.
     */
    private function generarNumeroPedido()
    {
        $ultimo = Pedido::orderBy('id', 'desc')->first();
        $numero = $ultimo ? $ultimo->id + 1 : 1;
        return 'PED-' . date('Ymd') . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Enviar pedido por email
     */
    public function enviarEmail(Request $request, $id)
    {
        $data = $request->validate([
            'email_destino' => ['nullable','email'],
        ]);

        try {
            // Obtener el pedido con relaciones necesarias y filtrando tipos válidos
            $pedido = Pedido::with([
                'cliente',
                'items' => function ($q) { $q->whereIn('pedible_type', [Producto::class, Servicio::class]); },
                'items.pedible'
            ])->findOrFail($id);

            // Verificar que el cliente tenga email o que se proporcione email_destino
            $emailDestino = $data['email_destino'] ?? $pedido->cliente->email;
            if (!$emailDestino) {
                throw ValidationException::withMessages([
                    'email' => 'El cliente no tiene email configurado y no se proporcionó un email de destino',
                ]);
            }

            // Obtener configuración de empresa para el PDF
            $configuracion = \App\Models\EmpresaConfiguracion::getConfig();

            // Generar PDF del pedido
            $pdf = Pdf::loadView('pedido_pdf', [
                'pedido' => $pedido,
                'configuracion' => $configuracion,
            ]);

            // Configurar opciones del PDF
            $pdf->setPaper('letter', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
            ]);

            // Preparar datos del email
            $datosEmail = [
                'pedido' => $pedido,
                'cliente' => $pedido->cliente,
                'configuracion' => $configuracion,
            ];

            // Configurar SMTP con datos de la base de datos
            config([
                'mail.mailers.smtp.host' => $configuracion->smtp_host,
                'mail.mailers.smtp.port' => $configuracion->smtp_port,
                'mail.mailers.smtp.username' => $configuracion->smtp_username,
                'mail.mailers.smtp.password' => $configuracion->smtp_password,
                'mail.mailers.smtp.encryption' => $configuracion->smtp_encryption,
                'mail.from.address' => $configuracion->email_from_address,
                'mail.from.name' => $configuracion->email_from_name,
            ]);

            // Enviar email con PDF adjunto
            Mail::send('emails.pedido', $datosEmail, function ($message) use ($pedido, $pdf, $configuracion, $emailDestino) {
                $message->to($emailDestino)
                        ->subject("Pedido #{$pedido->numero_pedido} - {$configuracion->nombre_empresa}")
                        ->attachData($pdf->output(), "pedido-{$pedido->numero_pedido}.pdf", [
                            'mime' => 'application/pdf',
                        ]);

                // Agregar reply-to si está configurado
                if ($configuracion->email_reply_to) {
                    $message->replyTo($configuracion->email_reply_to);
                }
            });

            Log::info("PDF de pedido enviado por email", [
                'pedido_id' => $pedido->id,
                'cliente_email' => $emailDestino,
                'numero_pedido' => $pedido->numero_pedido,
                'configuracion_smtp' => [
                    'host' => $configuracion->smtp_host,
                    'port' => $configuracion->smtp_port,
                    'encryption' => $configuracion->smtp_encryption,
                ]
            ]);

            // Registrar el envío en el pedido para mostrar en el frontend
            $pedido->update([
                'email_enviado' => true,
                'email_enviado_fecha' => now(),
                'email_enviado_por' => Auth::id(),
            ]);

            // Si es una petición AJAX, devolver JSON; de lo contrario, redirect
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pedido enviado por email correctamente',
                    'pedido' => [
                        'id' => $pedido->id,
                        'email_enviado' => true,
                        'email_enviado_fecha' => now()->format('d/m/Y H:i'),
                        'estado' => $pedido->estado->value
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Pedido enviado por email correctamente');

        } catch (\Exception $e) {
            Log::error("Error al enviar PDF de pedido por email", [
                'pedido_id' => $id,
                'cliente_email' => $emailDestino ?? 'no disponible',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mensaje más específico para debugging
            $errorMessage = $e->getMessage();
            $mensaje = 'Error al enviar pedido por email';

            if (strpos($errorMessage, 'authentication failed') !== false) {
                $mensaje = 'Error de autenticación SMTP. Verifica usuario/contraseña.';
            } elseif (strpos($errorMessage, 'Connection refused') !== false) {
                $mensaje = 'No se pudo conectar al servidor SMTP. Verifica host/puerto.';
            } elseif (strpos($errorMessage, 'timeout') !== false) {
                $mensaje = 'Timeout de conexión. Servidor no responde.';
            } elseif (strpos($errorMessage, 'View') !== false) {
                $mensaje = 'Error en plantilla de email. Verifica archivos de vistas.';
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => $mensaje,
                    'details' => app()->environment('local') ? $errorMessage : null
                ], 500);
            }

            throw ValidationException::withMessages([
                'email' => $mensaje . ' | Detalle: ' . $errorMessage,
            ]);
        }
    }

    /**
     * Generar PDF de pedido usando plantilla Blade
     */
    public function generarPDF($id)
    {
        try {
            // Obtener el pedido con todas las relaciones necesarias
            $pedido = Pedido::with(['cliente', 'items.pedible'])->findOrFail($id);

            // Obtener configuración de empresa
            $configuracion = \App\Models\EmpresaConfiguracion::getConfig();

            // Generar PDF usando la plantilla Blade
            $pdf = Pdf::loadView('pedido_pdf', [
                'pedido' => $pedido,
                'configuracion' => $configuracion,
            ]);

            // Configurar opciones del PDF
            $pdf->setPaper('letter', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
            ]);

            // Retornar PDF para descarga
            return $pdf->download("pedido-{$pedido->numero_pedido}.pdf");

        } catch (\Exception $e) {
            Log::error("Error al generar PDF de pedido", [
                'pedido_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->with('error', 'Error al generar el PDF del pedido');
        }
    }

    /**
     * Generar ticket térmico de pedido (80mm)
     */
    public function generarTicket($id)
    {
        try {
            // Obtener el pedido con todas las relaciones necesarias
            $pedido = Pedido::with(['cliente', 'items.pedible'])->findOrFail($id);

            // Obtener configuración de empresa
            $configuracion = \App\Models\EmpresaConfiguracion::getConfig();

            // Generar PDF usando la plantilla de ticket térmico
            $pdf = Pdf::loadView('pedido_ticket', [
                'pedido' => $pedido,
                'configuracion' => $configuracion,
            ]);

            // Configurar opciones del PDF para ticket térmico (80mm)
            $pdf->setPaper([0, 0, 226.77, 1000], 'portrait'); // 80mm = 226.77 puntos
            $pdf->setOptions([
                'defaultFont' => 'monospace',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'margin-top' => 5,
                'margin-right' => 5,
                'margin-bottom' => 5,
                'margin-left' => 5,
            ]);

            // Retornar PDF para impresión térmica
            return $pdf->download("ticket-pedido-{$pedido->numero_pedido}.pdf");
        } catch (\Exception $e) {
            Log::error("Error al generar ticket térmico de pedido", [
                'pedido_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->with('error', 'Error al generar el ticket térmico del pedido');
        }
    }
}


