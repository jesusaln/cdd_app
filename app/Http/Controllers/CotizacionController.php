<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use Illuminate\Validation\Rule;

use App\Models\Pedido;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Enums\EstadoCotizacion;
use App\Enums\EstadoVenta;
use App\Enums\EstadoPedido;
use App\Models\CotizacionItem;
use App\Models\Servicio;
use App\Models\SatEstado;
use App\Models\SatRegimenFiscal;
use App\Models\SatUsoCfdi;
use App\Services\InventarioService;
use App\Services\MarginService;
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

class CotizacionController extends Controller
{
    use AuthorizesRequests;

    private InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cotizaciones = \App\Models\Cotizacion::with([
            'cliente:id,nombre_razon_social,email,telefono,rfc,regimen_fiscal,uso_cfdi,calle,numero_exterior,numero_interior,colonia,codigo_postal,municipio,estado,pais',
            'items.cotizable',
            'createdBy:id,name',
            'updatedBy:id,name',
            'emailEnviadoPor:id,name',
            'pedidos:id,cotizacion_id,estado',
            'ventas:id,cotizacion_id,estado',
        ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($cotizacion) {
                // Cotizaciones con cliente y al menos un ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tem
                return $cotizacion->cliente !== null && $cotizacion->items->isNotEmpty();
            })
            ->map(function ($cotizacion) {
                $items = $cotizacion->items->map(function ($item) {
                    $cotizable = $item->cotizable;

                    return [
                        'id'        => $cotizable?->id,
                        'nombre'    => $cotizable->nombre ?? 'Sin nombre',
                        'tipo'      => $item->cotizable_type === \App\Models\Producto::class ? 'producto' : 'servicio',
                        'cantidad'  => (int) $item->cantidad,
                        'precio'    => (float) $item->precio,
                        'descuento' => (float) ($item->descuento ?? 0),
                    ];
                });

                $createdAtIso = optional($cotizacion->created_at)->toIso8601String();
                $updatedAtIso = optional($cotizacion->updated_at)->toIso8601String();

                // Verificar si la cotización ha sido convertida a pedido o venta
                $tienePedido = $cotizacion->pedidos->where('estado', '!=', EstadoPedido::Cancelado)->isNotEmpty();
                $tieneVenta = $cotizacion->ventas->where('estado', '!=', EstadoVenta::Cancelada)->isNotEmpty();
                $haSidoConvertida = $tienePedido || $tieneVenta;

                return [
                    'id'                => $cotizacion->id,
                    'numero_cotizacion' => $cotizacion->numero_cotizacion,

                    // Fechas
                    'fecha'       => optional($cotizacion->created_at)->format('Y-m-d'),
                    'created_at'  => $createdAtIso,   // ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â "nunca modificar" (ISO)
                    'updated_at'  => $updatedAtIso,

                    // Cliente
                    'cliente' => [
                        'id'               => $cotizacion->cliente->id,
                        'nombre'           => $cotizacion->cliente->nombre_razon_social ?? 'Sin nombre',
                        'email'            => $cotizacion->cliente->email,
                        'telefono'         => $cotizacion->cliente->telefono,
                        'rfc'              => $cotizacion->cliente->rfc,
                        'regimen_fiscal'   => $cotizacion->cliente->regimen_fiscal,
                        'uso_cfdi'         => $cotizacion->cliente->uso_cfdi,
                        'calle'            => $cotizacion->cliente->calle,
                        'numero_exterior'  => $cotizacion->cliente->numero_exterior,
                        'numero_interior'  => $cotizacion->cliente->numero_interior,
                        'colonia'          => $cotizacion->cliente->colonia,
                        'codigo_postal'    => $cotizacion->cliente->codigo_postal,
                        'municipio'        => $cotizacion->cliente->municipio,
                        'estado'           => $cotizacion->cliente->estado,
                        'pais'             => $cotizacion->cliente->pais,
                    ],

                    // ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âtems
                    'productos' => $items->toArray(),

                    // Totales/estado
                    'total'  => (float) $cotizacion->total,
                    'estado' => is_object($cotizacion->estado) ? $cotizacion->estado->value : $cotizacion->estado,

                    // Permisos
                    'canEdit' => in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente], true) && !$haSidoConvertida,
                    'canDelete' => in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente, EstadoCotizacion::Aprobada], true) && !$haSidoConvertida,

                    // AuditorÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­a (para tu modal y vistas)
                    'creado_por_nombre'      => $cotizacion->createdBy?->name,
                    'actualizado_por_nombre' => $cotizacion->updatedBy?->name,
                    'email_enviado_por_nombre' => $cotizacion->emailEnviadoPor?->name,

                    // Información de email
                    'email_enviado' => (bool) $cotizacion->email_enviado,
                    'email_enviado_fecha' => $cotizacion->email_enviado_fecha?->format('d/m/Y H:i'),

                    // Redundancia segura para el modal (si espera un objeto metadata)
                    'metadata' => [
                        'creado_por'     => $cotizacion->createdBy?->name,
                        'actualizado_por' => $cotizacion->updatedBy?->name,
                        'email_enviado_por' => $cotizacion->emailEnviadoPor?->name,
                        'creado_en'      => $createdAtIso,
                        'actualizado_en' => $updatedAtIso,
                        'email_enviado_en' => $cotizacion->email_enviado_fecha?->format('d/m/Y H:i'),
                    ],
                ];
            });

        return \Inertia\Inertia::render('Cotizaciones/Index', [
            'cotizaciones' => $cotizaciones->values(),
            'estados' => collect(\App\Enums\EstadoCotizacion::cases())->map(fn($estado) => [
                'value' => $estado->value,
                'label' => $estado->label(),
                'color' => $estado->color()
            ]),
            'filters' => request()->only(['search', 'estado', 'fecha_inicio', 'fecha_fin']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Cotizaciones/Create', [
            'clientes' => Cliente::activos()->select('id', 'nombre_razon_social', 'email', 'telefono')->get(),
            'productos' => Producto::with(['categoria:id,nombre', 'inventarios'])
                ->select('id', 'nombre', 'codigo', 'categoria_id', 'precio_venta', 'descripcion', 'estado')
                ->active()
                ->get()
                ->map(function ($producto) {
                    $stockTotal = $producto->stock_total ?? 0;
                    $stockDisponible = $producto->stock_disponible ?? 0;
                    $stockReservado = (int) $producto->reservado;

                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'codigo' => $producto->codigo ?? 'SIN-CODIGO-' . $producto->id,
                        'categoria' => $producto->categoria?->nombre ?? 'Sin categoría',
                        'categoria_id' => $producto->categoria_id,
                        'precio_venta' => (float) $producto->precio_venta,
                        'descripcion' => $producto->descripcion,
                        'estado' => $producto->estado,
                        'stock_total' => $stockTotal,
                        'stock_disponible' => $stockDisponible,
                        'stock_reservado' => $stockReservado,
                    ];
                }),
            'servicios' => Servicio::with('categoria:id,nombre')
                ->select('id', 'nombre', 'codigo', 'categoria_id', 'precio', 'descripcion', 'estado')
                ->active()
                ->get()
                ->map(function ($servicio) {
                    return [
                        'id' => $servicio->id,
                        'nombre' => $servicio->nombre,
                        'codigo' => $servicio->codigo ?? 'SIN-CODIGO-SERV-' . $servicio->id,
                        'categoria' => $servicio->categoria?->nombre ?? 'Sin categoría',
                        'categoria_id' => $servicio->categoria_id,
                        'precio' => (float) $servicio->precio,
                        'descripcion' => $servicio->descripcion,
                        'estado' => $servicio->estado,
                    ];
                }),
            'catalogs' => [
                'tiposPersona' => [
                    ['value' => 'fisica', 'text' => 'Persona FÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­sica'],
                    ['value' => 'moral', 'text' => 'Persona Moral'],
                ],
                'estados' => SatEstado::orderBy('nombre')
                    ->get(['clave', 'nombre'])
                    ->map(function ($estado) {
                        return [
                            'value' => $estado->clave,
                            'text' => $estado->clave . ' ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â ' . $estado->nombre
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
                            'text' => $uso->clave . ' ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â ' . $uso->descripcion
                        ];
                    })
                    ->toArray(),
            ],
            'defaults' => [
                'fecha' => now()->format('Y-m-d'),
                'validez' => 30,
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
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|integer|min:1',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0.01',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
            'descuento_general' => 'nullable|numeric|min:0|max:100',
            'notas' => 'nullable|string|max:1000',
            'ajustar_margen' => 'nullable|boolean',
        ]);

        // Validar que los productos/servicios realmente existen
        foreach ($validated['productos'] as $index => $item) {
            $class = $item['tipo'] === 'producto' ? Producto::class : Servicio::class;
            $modelo = $class::find($item['id']);

            if (!$modelo) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(["productos.{$index}.id" => "El " . $item['tipo'] . " con ID {$item['id']} no existe"])
                    ->with('error', 'Algunos productos o servicios seleccionados no existen');
            }

            // Validar que el producto esté activo
            if ($item['tipo'] === 'producto' && $modelo->estado !== 'activo') {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(["productos.{$index}.id" => "El producto '{$modelo->nombre}' no está activo"])
                    ->with('error', 'Algunos productos seleccionados no están activos');
            }
        }

        // Validar mÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡rgenes de ganancia
        $marginService = new MarginService();
        $validacionMargen = $marginService->validarMargenesProductos($validated['productos']);

        Log::info('ValidaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n de mÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡rgenes en cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n', [
            'productos_count' => count($validated['productos']),
            'todos_validos' => $validacionMargen['todos_validos'],
            'productos_bajo_margen_count' => count($validacionMargen['productos_bajo_margen']),
            'ajustar_margen_request' => $request->has('ajustar_margen') ? $request->ajustar_margen : 'no_presente'
        ]);

        if (!$validacionMargen['todos_validos']) {
            Log::info('Productos con margen insuficiente detectados', [
                'productos_bajo_margen' => $validacionMargen['productos_bajo_margen']
            ]);

            // Si hay productos con margen insuficiente, verificar si el usuario aceptÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³ el ajuste
            // Aceptar bandera booleana para ajustar margen (true/"true"/1)
            if ($request->boolean('ajustar_margen')) {
                Log::info('Usuario aceptÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³ ajuste automÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡tico de mÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡rgenes');
                // Ajustar precios automÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ticamente
                foreach ($validated['productos'] as &$item) {
                    if ($item['tipo'] === 'producto') {
                        $producto = Producto::find($item['id']);
                        if ($producto) {
                            $precioOriginal = $item['precio'];
                            $item['precio'] = $marginService->ajustarPrecioAlMargen($producto, $item['precio']);
                            Log::info('Precio ajustado', [
                                'producto_id' => $producto->id,
                                'precio_original' => $precioOriginal,
                                'precio_ajustado' => $item['precio']
                            ]);
                        }
                    }
                }
            } else {
                Log::info('Mostrando modal de confirmaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n de mÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡rgenes insuficientes');
                // Mostrar advertencia y permitir al usuario decidir
                $mensaje = $marginService->generarMensajeAdvertencia($validacionMargen['productos_bajo_margen']);
                return redirect()->back()
                    ->withInput()
                    ->with('warning', $mensaje)
                    ->with('requiere_confirmacion_margen', true)
                    ->with('productos_bajo_margen', $validacionMargen['productos_bajo_margen']);
            }
        } else {
            Log::info('Todos los productos tienen mÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡rgenes vÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡lidos');
        }

        $subtotal = 0;
        foreach ($validated['productos'] as $item) {
            $cantidad = (float) ($item['cantidad'] ?? 0);
            $precio = (float) ($item['precio'] ?? 0);
            $subtotal += $cantidad * $precio;
        }

        $descuentoItems = 0;
        foreach ($validated['productos'] as $item) {
            $cantidad = (float) ($item['cantidad'] ?? 0);
            $precio = (float) ($item['precio'] ?? 0);
            $descuento = (float) ($item['descuento'] ?? 0);

            $subtotalItem = $cantidad * $precio;
            $descuentoItems += $subtotalItem * ($descuento / 100);
        }

        $descuentoGeneralPorc = (float) ($request->descuento_general ?? 0);
        $descuentoGeneralMonto = ($subtotal - $descuentoItems) * ($descuentoGeneralPorc / 100);
        $subtotalFinal = ($subtotal - $descuentoItems) - $descuentoGeneralMonto;
        $ivaRate = \App\Services\EmpresaConfiguracionService::getIvaPorcentaje() / 100;
        $iva = $subtotalFinal * $ivaRate;
        $total = $subtotalFinal + $iva;

        try {
            $cotizacion = Cotizacion::create([
                'cliente_id' => $validated['cliente_id'],
                'subtotal' => round($subtotal, 2),
                'descuento_general' => round($descuentoGeneralMonto, 2),
                'iva' => round($iva, 2),
                'total' => round($total, 2),
                'notas' => $request->notas,
                'estado' => EstadoCotizacion::Pendiente,
            ]);

            Log::info('Cotización creada exitosamente', [
                'cotizacion_id' => $cotizacion->id,
                'cliente_id' => $validated['cliente_id'],
                'productos_count' => count($validated['productos']),
                'subtotal' => round($subtotal, 2),
                'total' => round($total, 2),
                'estado' => 'pendiente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear cotización en base de datos', [
                'cliente_id' => $validated['cliente_id'],
                'productos_count' => count($validated['productos']),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error interno al crear la cotización. Por favor, inténtelo de nuevo.');
        }

        try {
            foreach ($validated['productos'] as $item) {
                $class = $item['tipo'] === 'producto' ? Producto::class : Servicio::class;
                $modelo = $class::find($item['id']);

                if (!$modelo) {
                    Log::warning("Ítem no encontrado al crear cotización", [
                        'tipo' => $class,
                        'id' => $item['id'],
                        'cotizacion_id' => $cotizacion->id
                    ]);
                    continue;
                }

                // Validar stock para productos
                if ($item['tipo'] === 'producto' && $modelo->stock_disponible < $item['cantidad']) {
                    Log::warning("Stock insuficiente para producto en cotización", [
                        'producto_id' => $item['id'],
                        'producto_nombre' => $modelo->nombre,
                        'categoria' => $modelo->categoria?->nombre ?? 'Sin categoría',
                        'stock_disponible' => $modelo->stock_disponible,
                        'cantidad_solicitada' => $item['cantidad'],
                        'cotizacion_id' => $cotizacion->id
                    ]);
                    // Continuar con la creación pero registrar el problema
                }

                $cantidad = (float) ($item['cantidad'] ?? 0);
                $precio = (float) ($item['precio'] ?? 0);
                $descuento = (float) ($item['descuento'] ?? 0);

                $subtotalItem = $cantidad * $precio;
                $descuentoMontoItem = $subtotalItem * ($descuento / 100);

                CotizacionItem::create([
                    'cotizacion_id' => $cotizacion->id,
                    'cotizable_id' => $item['id'],
                    'cotizable_type' => $class,
                    'cantidad' => (int) $cantidad,
                    'precio' => round($precio, 2),
                    'descuento' => round($descuento, 2),
                    'subtotal' => round($subtotalItem, 2),
                    'descuento_monto' => round($descuentoMontoItem, 2),
                ]);

                Log::info("Ítem agregado a cotización exitosamente", [
                    'cotizacion_id' => $cotizacion->id,
                    'tipo' => $class,
                    'id' => $item['id'],
                    'nombre' => $modelo->nombre,
                    'categoria' => $item['tipo'] === 'producto' ? $modelo->categoria?->nombre : 'Servicio',
                    'cantidad' => $cantidad,
                    'precio' => $precio
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error al procesar ítems de cotización", [
                'cotizacion_id' => $cotizacion->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Si hay error procesando ítems, eliminar la cotización creada
            $cotizacion->delete();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al procesar los productos/servicios de la cotización. Por favor, inténtelo de nuevo.');
        }

        return redirect()->route('cotizaciones.index')->with('success', 'CotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n creada con ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©xito');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'items.cotizable'])->findOrFail($id);

        $items = $cotizacion->items->map(function ($item) {
            $cotizable = $item->cotizable;
            return [
                'id' => $cotizable->id,
                'nombre' => $cotizable->nombre ?? $cotizable->descripcion,
                'tipo' => $item->cotizable_type === Producto::class ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Cotizaciones/Show', [
            'cotizacion' => [
                'id' => $cotizacion->id,
                'cliente' => $cotizacion->cliente,
                'productos' => $items,
                'subtotal' => $cotizacion->subtotal,
                'descuento_general' => $cotizacion->descuento_general,
                'iva' => $cotizacion->iva,
                'total' => $cotizacion->total,
                'notas' => $cotizacion->notas,
                'estado' => $cotizacion->estado->value,
            ],
            'canConvert' => $cotizacion->estado === EstadoCotizacion::Aprobada,
            'canEdit' => in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente], true),
            'canDelete' => in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente], true),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'items.cotizable'])->findOrFail($id);

        // Permitir ediciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n solo si estÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ en Borrador o Pendiente
        if (!in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente], true)) {
            return Redirect::route('cotizaciones.show', $cotizacion->id)
                ->with('warning', 'Solo cotizaciones en borrador o pendientes pueden ser editadas');
        }

        $items = $cotizacion->items->map(function ($item) {
            $cotizable = $item->cotizable;
            return [
                'id' => $cotizable->id,
                'nombre' => $cotizable->nombre ?? $cotizable->descripcion,
                'tipo' => $item->cotizable_type === Producto::class ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Cotizaciones/Edit', [
            'cotizacion' => [
                'id' => $cotizacion->id,
                'cliente_id' => $cotizacion->cliente_id,
                'cliente' => $cotizacion->cliente,
                'productos' => $items,
                'subtotal' => $cotizacion->subtotal,
                'descuento_general' => $cotizacion->descuento_general,
                'iva' => $cotizacion->iva,
                'total' => $cotizacion->total,
                'notas' => $cotizacion->notas,
                'informacion_general' => [
                    'numero' => [
                        'label' => 'NÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âºmero de CotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n',
                        'value' => $cotizacion->numero_cotizacion,
                        'tipo' => 'fijo',
                        'descripcion' => 'Este nÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âºmero es fijo para todas las cotizaciones'
                    ],
                    'fecha' => [
                        'label' => 'Fecha de CotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n',
                        'value' => $cotizacion->fecha_cotizacion ? $cotizacion->fecha_cotizacion->format('d/m/Y') : now()->format('d/m/Y'),
                        'tipo' => 'automatica',
                        'descripcion' => 'Esta fecha se establece automÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ticamente con la fecha de creaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n'
                    ]
                ]
            ],
            'clientes' => Cliente::activos()->select('id', 'nombre_razon_social', 'email', 'telefono')->get(),
            'productos' => Producto::with(['categoria:id,nombre', 'inventarios'])
                ->select('id', 'nombre', 'codigo', 'categoria_id', 'precio_venta', 'descripcion', 'estado')
                ->active()
                ->get()
                ->map(function ($producto) {
                    $stockTotal = $producto->stock_total ?? 0;
                    $stockDisponible = $producto->stock_disponible ?? 0;
                    $stockReservado = (int) $producto->reservado;

                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'codigo' => $producto->codigo ?? 'SIN-CODIGO-' . $producto->id,
                        'categoria' => $producto->categoria?->nombre ?? 'Sin categoría',
                        'categoria_id' => $producto->categoria_id,
                        'precio_venta' => (float) $producto->precio_venta,
                        'descripcion' => $producto->descripcion,
                        'estado' => $producto->estado,
                        'stock_total' => $stockTotal,
                        'stock_disponible' => $stockDisponible,
                        'stock_reservado' => $stockReservado,
                    ];
                }),
            'servicios' => Servicio::with('categoria:id,nombre')
                ->select('id', 'nombre', 'codigo', 'categoria_id', 'precio', 'descripcion', 'estado')
                ->active()
                ->get()
                ->map(function ($servicio) {
                    return [
                        'id' => $servicio->id,
                        'nombre' => $servicio->nombre,
                        'codigo' => $servicio->codigo ?? 'SIN-CODIGO-SERV-' . $servicio->id,
                        'categoria' => $servicio->categoria?->nombre ?? 'Sin categoría',
                        'categoria_id' => $servicio->categoria_id,
                        'precio' => (float) $servicio->precio,
                        'descripcion' => $servicio->descripcion,
                        'estado' => $servicio->estado,
                    ];
                }),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        // (Opcional, si tienes Policy configurada)
        // $this->authorize('update', $cotizacion);

        // Solo permitir ediciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n en Borrador o Pendiente
        if (!in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente], true)) {
            return Redirect::back()->with('error', 'Solo cotizaciones en borrador o pendientes pueden ser actualizadas');
        }

        $validated = $request->validate([
            'cliente_id'           => 'required|exists:clientes,id',
            'productos'            => 'required|array|min:1',
            'productos.*.id'       => 'required|integer',
            'productos.*.tipo'     => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio'   => 'required|numeric|min:0',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
            'descuento_general'    => 'nullable|numeric|min:0|max:100',
            'notas'                => 'nullable|string',
            'ajustar_margen'       => 'nullable|boolean',
            // ValidaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n de estado (si llega desde el front, se controla)
            'estado'               => ['sometimes', Rule::in(array_map(fn($c) => $c->value, EstadoCotizacion::cases()))],
        ]);

        // Validar mÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡rgenes de ganancia antes de calcular totales
        $marginService = new MarginService();
        $validacionMargen = $marginService->validarMargenesProductos($validated['productos']);

        Log::info('ValidaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n de mÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡rgenes en actualizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n de cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n', [
            'cotizacion_id' => $id,
            'productos_count' => count($validated['productos']),
            'todos_validos' => $validacionMargen['todos_validos'],
            'productos_bajo_margen_count' => count($validacionMargen['productos_bajo_margen']),
            'ajustar_margen_request' => $request->has('ajustar_margen') ? $request->ajustar_margen : 'no_presente'
        ]);

        if (!$validacionMargen['todos_validos']) {
            Log::info('Productos con margen insuficiente detectados en actualizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n', [
                'cotizacion_id' => $id,
                'productos_bajo_margen' => $validacionMargen['productos_bajo_margen']
            ]);

            // Si hay productos con margen insuficiente, verificar si el usuario aceptÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³ el ajuste
            // Aceptar bandera booleana para ajustar margen (true/"true"/1)
            if ($request->boolean('ajustar_margen')) {
                Log::info('Usuario aceptÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³ ajuste automÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡tico de mÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡rgenes en actualizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n', ['cotizacion_id' => $id]);
                // Ajustar precios automÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ticamente
                foreach ($validated['productos'] as &$item) {
                    if ($item['tipo'] === 'producto') {
                        $producto = Producto::find($item['id']);
                        if ($producto) {
                            $precioOriginal = $item['precio'];
                            $item['precio'] = $marginService->ajustarPrecioAlMargen($producto, $item['precio']);
                            Log::info('Precio ajustado en actualizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n', [
                                'cotizacion_id' => $id,
                                'producto_id' => $producto->id,
                                'precio_original' => $precioOriginal,
                                'precio_ajustado' => $item['precio']
                            ]);
                        }
                    }
                }
            } else {
                Log::info('Mostrando modal de confirmaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n de mÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡rgenes insuficientes en actualizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n', ['cotizacion_id' => $id]);
                // Mostrar advertencia y permitir al usuario decidir
                $mensaje = $marginService->generarMensajeAdvertencia($validacionMargen['productos_bajo_margen']);
                return Redirect::back()
                    ->withInput()
                    ->with('warning', $mensaje)
                    ->with('requiere_confirmacion_margen', true)
                    ->with('productos_bajo_margen', $validacionMargen['productos_bajo_margen']);
            }
        } else {
            Log::info('Todos los productos tienen mÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡rgenes vÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡lidos en actualizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n', ['cotizacion_id' => $id]);
        }

        // CÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡lculos: redondeamos a 2 decimales para evitar ruido por flotantes
        $subtotal = 0.0;
        $descuentoItems = 0.0;

        foreach ($validated['productos'] as $item) {
            $subtotalItem = (float) $item['cantidad'] * (float) $item['precio'];
            $subtotal += $subtotalItem;
            $descuentoItems += $subtotalItem * ((float) $item['descuento'] / 100);
        }

        $descuentoGeneralPorc = (float) ($request->descuento_general ?? 0);
        $descuentoGeneralMonto = ($subtotal - $descuentoItems) * ($descuentoGeneralPorc / 100);

        $subtotalFinal = ($subtotal - $descuentoItems) - $descuentoGeneralMonto;
        $ivaRate = \App\Services\EmpresaConfiguracionService::getIvaPorcentaje() / 100;
        $iva           = $subtotalFinal * $ivaRate;
        $total         = $subtotalFinal + $iva;

        // Redondeo final
        $subtotal            = round($subtotal, 2);
        $descuentoItems      = round($descuentoItems, 2);
        $descuentoGeneralMonto = round($descuentoGeneralMonto, 2);
        $subtotalFinal       = round($subtotalFinal, 2);
        $iva                 = round($iva, 2);
        $total               = round($total, 2);

        // Guardar estado ANTES de actualizar (para mensaje)
        $estadoAnterior = $cotizacion->estado;

        // Si estaba en Borrador, pasa a Pendiente; si no, conserva
        $nuevoEstado = $cotizacion->estado === EstadoCotizacion::Borrador
            ? EstadoCotizacion::Pendiente
            : $cotizacion->estado;

        // Atomicidad: actualizar cabecera + refrescar items
        DB::transaction(function () use (&$cotizacion, $validated, $subtotal, $descuentoGeneralMonto, $descuentoItems, $iva, $total, $nuevoEstado, $request) {
            $cotizacion->update([
                'cliente_id'        => $validated['cliente_id'],
                'subtotal'          => $subtotal,
                'descuento_general' => $descuentoGeneralMonto,
                'descuento_items'   => $descuentoItems,   // ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â¦ ahora se persiste
                'iva'               => $iva,
                'total'             => $total,
                'notas'             => $request->notas,
                'estado'            => $nuevoEstado,
            ]);

            // Eliminar ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tems anteriores
            $cotizacion->items()->delete();

            // Guardar nuevos ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tems
            foreach ($validated['productos'] as $itemData) {
                $class  = $itemData['tipo'] === 'producto' ? Producto::class : Servicio::class;
                $modelo = $class::find($itemData['id']);

                if (!$modelo) {
                    Log::warning("ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âtem no encontrado: {$class} con ID {$itemData['id']}");
                    continue;
                }

                $subtotalItem      = (float) $itemData['cantidad'] * (float) $itemData['precio'];
                $descuentoMontoItem = $subtotalItem * ((float) $itemData['descuento'] / 100);

                CotizacionItem::create([
                    'cotizacion_id'   => $cotizacion->id,
                    'cotizable_id'    => $itemData['id'],
                    'cotizable_type'  => $class,
                    'cantidad'        => (int) $itemData['cantidad'],
                    'precio'          => (float) $itemData['precio'],
                    'descuento'       => (float) $itemData['descuento'],
                    'subtotal'        => round($subtotalItem, 2),
                    'descuento_monto' => round($descuentoMontoItem, 2),
                ]);
            }
        });

        // Mensaje usando estado anterior (no el ya mutado)
        $mensajeExito = ($estadoAnterior === EstadoCotizacion::Borrador && $nuevoEstado === EstadoCotizacion::Pendiente)
            ? 'CotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n actualizada y cambiada a estado pendiente exitosamente'
            : 'CotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n actualizada exitosamente';

        return Redirect::route('cotizaciones.index')->with('success', $mensajeExito);
    }

    /**
     * Cancel the specified resource (soft cancel).
     */
    public function cancel($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        // Permitir cancelar en cualquier estado excepto ya cancelado
        if ($cotizacion->estado === EstadoCotizacion::Cancelado) {
            return Redirect::back()->with('error', 'La cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n ya estÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ cancelada');
        }

        // Actualizar estado a cancelado y registrar quiÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©n lo cancelÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³
        $cotizacion->update([
            'estado' => EstadoCotizacion::Cancelado,
            'deleted_by' => Auth::id(),
            'deleted_at' => now()
        ]);

        return Redirect::route('cotizaciones.index')
            ->with('success', 'CotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n cancelada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        if (!in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente, EstadoCotizacion::Aprobada], true)) {
            return Redirect::back()->with('error', 'Solo cotizaciones pendientes pueden ser eliminadas');
        }

        $cotizacion->items()->delete();
        $cotizacion->delete();

        return Redirect::route('cotizaciones.index')
            ->with('success', 'CotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n eliminada exitosamente');
    }

    /**
     * Convertir cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n a venta.
     * (Nota: la unificaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n completa con VentaItem/ventable_* la hacemos en el paso #8)
     */
    public function convertirAVenta($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'items.cotizable'])->findOrFail($id);

        if ($cotizacion->estado !== EstadoCotizacion::Aprobada) {
            return Redirect::back()->with('error', 'Solo cotizaciones aprobadas pueden convertirse a venta');
        }

        DB::beginTransaction();
        try {
            // Import ya declarado arriba: use App\Models\Venta;
            $venta = Venta::create([
                'cliente_id' => $cotizacion->cliente_id,
                'cotizacion_id' => $cotizacion->id,
                'total' => $cotizacion->total,
            ]);

            foreach ($cotizacion->items as $item) {
                $class = $item->cotizable_type;
                $id = $item->cotizable_id;

                if ($class === Producto::class) {
                    $venta->productos()->attach($id, [
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                    ]);

                    $productoVenta = Producto::find($id);
                    if ($productoVenta) {
                        app(InventarioService::class)->salida($productoVenta, $item->cantidad, [
                            'motivo' => 'ConversiÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n de cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n a venta',
                            'referencia' => $venta,
                            'detalles' => [
                                'cotizacion_id' => $cotizacion->id,
                                'cotizacion_item_id' => $item->id,
                            ],
                        ]);
                    }
                } elseif ($class === Servicio::class) {
                    $venta->servicios()->attach($id, [
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                    ]);
                }
            }

            DB::commit();
            return Redirect::route('ventas.show', $venta->id)
                ->with('success', 'Venta creada exitosamente a partir de la cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al convertir cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n a venta', ['error' => $e->getMessage()]);
            return Redirect::back()->with('error', 'Error al crear la venta');
        }
    }

    /**
     * Duplicar una cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n.
     */
    public function duplicate(Request $request, $id)
    {
        $original = Cotizacion::with('cliente', 'items.cotizable')->findOrFail($id);

        try {
            return DB::transaction(function () use ($original) {
                // Replicar EXCLUYENDO campos problemÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ticos
                $nueva = $original->replicate([
                    'numero_cotizacion', // ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â evita duplicar el mismo nÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âºmero
                    'created_at',
                    'updated_at',
                    'estado',
                ]);

                // Estado nuevo (borrador) y nÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âºmero ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âºnico
                $nueva->estado = EstadoCotizacion::Borrador;
                $nueva->numero_cotizacion = $this->generarNumeroCotizacionUnico();
                $nueva->created_at = now();
                $nueva->updated_at = now();

                // Si usas descuento_items en el modelo, ya viene replicado.
                // Si no, podrÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­as recalcularlo aquÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­ si lo prefieres.

                $nueva->save();

                // Duplicar ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tems (crea el FK cotizacion_id automÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ticamente)
                foreach ($original->items as $item) {
                    $nueva->items()->create([
                        'cotizable_id'    => $item->cotizable_id,
                        'cotizable_type'  => $item->cotizable_type,
                        'cantidad'        => $item->cantidad,
                        'precio'          => $item->precio,
                        'descuento'       => $item->descuento,
                        'subtotal'        => $item->subtotal,
                        'descuento_monto' => $item->descuento_monto,
                    ]);
                }

                return Redirect::route('cotizaciones.index')
                    ->with('success', 'CotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n duplicada correctamente.');
            });
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error duplicando cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n: ' . $e->getMessage(), ['id' => $id]);
            return Redirect::back()->with('error', 'Error al duplicar la cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n.');
        }
    }

    /**
     * Genera un numero_cotizacion ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âºnico secuencial evitando colisiones.
     */
    private function generarNumeroCotizacionUnico(): string
    {
        // Buscar el último número de cotización existente
        $ultimaCotizacion = Cotizacion::where('numero_cotizacion', 'LIKE', 'C%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$ultimaCotizacion || !$ultimaCotizacion->numero_cotizacion) {
            return 'C001';
        }

        // Extraer el número de la cotización
        $matches = [];
        if (preg_match('/C(\d+)$/', $ultimaCotizacion->numero_cotizacion, $matches)) {
            $ultimoNumero = (int) $matches[1];
            $siguienteNumero = $ultimoNumero + 1;
            $nuevoNumero = 'C' . str_pad($siguienteNumero, 3, '0', STR_PAD_LEFT);

            // Verificar que no exista ya (evitar colisiones)
            while (Cotizacion::where('numero_cotizacion', $nuevoNumero)->exists()) {
                $siguienteNumero++;
                $nuevoNumero = 'C' . str_pad($siguienteNumero, 3, '0', STR_PAD_LEFT);
            }

            return $nuevoNumero;
        }

        // Si no se puede extraer el número, empezar desde C001
        return 'C001';
    }

    /**
     * Enviar a Pedido.
     * (Nota: la unificaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n completa de pivots se atiende en el paso #8)
     */
    public function enviarAPedido($id)
    {
        try {
            DB::beginTransaction();

            // Obtener la cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n con relaciones necesarias
            $cotizacion = Cotizacion::with([
                'items.cotizable',
                'cliente'
            ])->findOrFail($id);

            // Validar estado
            if (!$cotizacion->puedeEnviarseAPedido()) {
                return response()->json([
                    'success' => false,
                    'error' => 'La cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n no estÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ en estado vÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡lido para enviar a pedido',
                    'estado_actual' => $cotizacion->estado->value,
                    'requiere_confirmacion' => false
                ], 400);
            }

            // Validar items
            if ($cotizacion->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'La cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n no contiene items para enviar a pedido',
                    'requiere_confirmacion' => false
                ], 400);
            }

            // Validar cliente
            if (!$cotizacion->cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'La cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n no tiene cliente asociado',
                    'requiere_confirmacion' => false
                ], 400);
            }

            // Crear nuevo pedido confirmado
            $pedido = new Pedido();
            $pedido->fill([
                'cliente_id' => $cotizacion->cliente_id,
                'cotizacion_id' => $cotizacion->id,
                'numero_pedido' => $this->generarNumeroPedido(),
                'fecha' => now(),
                'estado' => EstadoPedido::Confirmado, // ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â Cambiado a confirmado
                'subtotal' => $cotizacion->subtotal,
                'descuento_general' => $cotizacion->descuento_general,
                'iva' => $cotizacion->iva,
                'total' => $cotizacion->total,
                'notas' => "Generado desde Cotizacion #{$cotizacion->id}"
            ]);
            $pedido->save();

            // Copiar items de cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n a pedido
            foreach ($cotizacion->items as $item) {
                $pedido->items()->create([
                    'pedible_id' => $item->cotizable_id,
                    'pedible_type' => $item->cotizable_type,
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                    'subtotal' => $item->subtotal,
                    'descuento_monto' => $item->descuento_monto
                ]);
            }

            // Reservar inventario automÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ticamente (ya que estÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ confirmado)
            foreach ($cotizacion->items as $item) {
                if ($item->cotizable_type === Producto::class) {
                    try {
                        $producto = Producto::find($item->cotizable_id);
                        if (!$producto) {
                            Log::warning("Producto no encontrado al enviar cotización a pedido", [
                                'producto_id' => $item->cotizable_id,
                                'cotizacion_id' => $cotizacion->id
                            ]);
                            continue;
                        }

                        if ($producto->stock_disponible < $item->cantidad) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'error' => "Stock insuficiente para '{$producto->nombre}'. Disponible: {$producto->stock_disponible}, Solicitado: {$item->cantidad}",
                                'requiere_confirmacion' => false
                            ], 400);
                        }

                        // Nota: Las reservas necesitan extensiÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n del InventarioService para trazabilidad completa
                        $producto->increment('reservado', $item->cantidad);

                        // Registrar movimiento de reserva por conversiÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n de cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n a pedido
                        Log::info("Inventario reservado automÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ticamente al enviar cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n a pedido", [
                            'producto_id' => $producto->id,
                            'pedido_id' => $pedido->id,
                            'cotizacion_id' => $cotizacion->id,
                            'cantidad_reservada' => $item->cantidad,
                            'reservado_actual' => $producto->fresh()->reservado
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Error al reservar inventario para producto {$item->cotizable_id}", [
                            'error' => $e->getMessage(),
                            'producto_id' => $item->cotizable_id,
                            'cotizacion_id' => $cotizacion->id
                        ]);
                        return response()->json([
                            'success' => false,
                            'error' => 'Error al procesar el inventario del producto',
                            'details' => app()->environment('local') ? $e->getMessage() : null
                        ], 500);
                    }
                }
            }

            // Actualizar estado de la cotizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n
            $cotizacion->update(['estado' => EstadoCotizacion::EnviadoAPedido]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado exitosamente',
                'pedido_id' => $pedido->id,
                'numero_pedido' => $pedido->numero_pedido,
                'items_count' => $pedido->items()->count()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error interno al enviar cotización a pedido', [
                'cotizacion_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Error interno al procesar el pedido',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function generarNumeroPedido()
    {
        $ultimo = Pedido::orderBy('id', 'desc')->first();
        $numero = $ultimo ? $ultimo->id + 1 : 1;
        return 'PED-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Enviar cotización por email
     */
    public function enviarEmail(Request $request, $id)
    {
        $data = $request->validate([
            'email_destino' => ['nullable', 'email'],
        ]);

        try {
            // Obtener la cotización con todas las relaciones necesarias
            $cotizacion = Cotizacion::with(['cliente', 'items.cotizable'])->findOrFail($id);

            // Verificar que el cliente tenga email o que se proporcione email_destino
            $emailDestino = $data['email_destino'] ?? $cotizacion->cliente->email;
            if (!$emailDestino) {
                throw ValidationException::withMessages([
                    'email' => 'El cliente no tiene email configurado y no se proporcionó un email de destino',
                ]);
            }

            Log::info("Iniciando envío de cotización por email", [
                'cotizacion_id' => $cotizacion->id,
                'numero_cotizacion' => $cotizacion->numero_cotizacion,
                'cliente_id' => $cotizacion->cliente->id,
                'cliente_email' => $emailDestino,
                'cliente_nombre' => $cotizacion->cliente->nombre_razon_social,
            ]);

            // Obtener configuración de empresa para el PDF
            $configuracion = \App\Models\EmpresaConfiguracion::getConfig();

            Log::info("Configuración SMTP obtenida", [
                'smtp_host' => $configuracion->smtp_host,
                'smtp_port' => $configuracion->smtp_port,
                'smtp_username' => $configuracion->smtp_username ? substr($configuracion->smtp_username, 0, 10) . '...' : 'no configurado',
                'smtp_encryption' => $configuracion->smtp_encryption,
                'email_from_address' => $configuracion->email_from_address,
                'email_from_name' => $configuracion->email_from_name,
            ]);

            // Generar PDF de la cotización
            Log::info("Generando PDF de cotización");
            $pdf = Pdf::loadView('cotizacion_pdf', [
                'cotizacion' => $cotizacion,
                'configuracion' => $configuracion,
            ]);

            // Configurar opciones del PDF
            $pdf->setPaper('letter', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
            ]);

            Log::info("PDF generado exitosamente", [
                'pdf_size' => strlen($pdf->output()) . ' bytes'
            ]);

            // Preparar datos del email
            $datosEmail = [
                'cotizacion' => $cotizacion,
                'cliente' => $cotizacion->cliente,
                'configuracion' => $configuracion,
                'fecha_envio' => now()->format('d/m/Y H:i:s'),
                'numero_cotizacion_formateado' => $cotizacion->numero_cotizacion,
            ];

            // Configurar SMTP con datos de la base de datos - FORZAR configuración
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $configuracion->smtp_host,
                'mail.mailers.smtp.port' => $configuracion->smtp_port,
                'mail.mailers.smtp.username' => $configuracion->smtp_username,
                'mail.mailers.smtp.password' => $configuracion->smtp_password,
                'mail.mailers.smtp.encryption' => $configuracion->smtp_encryption,
                'mail.mailers.smtp.timeout' => 30,
                'mail.from.address' => $configuracion->email_from_address,
                'mail.from.name' => $configuracion->email_from_name,
            ]);

            // Limpiar cualquier configuración previa de mail
            \Illuminate\Support\Facades\Mail::purge('smtp');

            Log::info("Configuración SMTP aplicada y forzada", [
                'config_aplicada' => config('mail.mailers.smtp'),
                'config_default' => config('mail.default'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ]);

            // Enviar email con PDF adjunto
            Mail::send('emails.cotizacion', $datosEmail, function ($message) use ($cotizacion, $pdf, $configuracion, $emailDestino) {
                $message->to($emailDestino)
                    ->subject("Cotización #{$cotizacion->numero_cotizacion} - {$configuracion->nombre_empresa}")
                    ->attachData($pdf->output(), "cotizacion-{$cotizacion->numero_cotizacion}.pdf", [
                        'mime' => 'application/pdf',
                    ]);

                // Agregar reply-to si está configurado
                if ($configuracion->email_reply_to) {
                    $message->replyTo($configuracion->email_reply_to);
                }

                // Agregar BCC para seguimiento interno
                if ($configuracion->email_from_address) {
                    $message->bcc($configuracion->email_from_address);
                }

                Log::info("Email preparado para envío", [
                    'to' => $emailDestino,
                    'bcc' => $configuracion->email_from_address,
                    'subject' => "Cotización #{$cotizacion->numero_cotizacion} - {$configuracion->nombre_empresa}",
                    'attachment_name' => "cotizacion-{$cotizacion->numero_cotizacion}.pdf",
                    'reply_to' => $configuracion->email_reply_to ?? 'no configurado',
                ]);
            });

            Log::info("Email enviado exitosamente via Mail::send DESDE INTERFAZ WEB", [
                'cotizacion_id' => $cotizacion->id,
                'cliente_email' => $emailDestino,
                'numero_cotizacion' => $cotizacion->numero_cotizacion,
                'configuracion_smtp' => [
                    'host' => $configuracion->smtp_host,
                    'port' => $configuracion->smtp_port,
                    'encryption' => $configuracion->smtp_encryption,
                ],
                'bcc_enviado' => $configuracion->email_from_address,
                'timestamp' => now()->toISOString(),
                'contexto' => 'web_interface',
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ]);

            // Registrar el envío en la cotización para mostrar en el frontend
            $cotizacion->update([
                'email_enviado' => true,
                'email_enviado_fecha' => now(),
                'email_enviado_por' => Auth::id(),
            ]);

            Log::info("Cotización actualizada con envío de email registrado", [
                'cotizacion_id' => $cotizacion->id,
                'email_enviado' => true,
                'email_enviado_fecha' => now()->format('Y-m-d H:i:s'),
                'email_enviado_por' => Auth::id(),
            ]);

            // Si es una petición AJAX, devolver JSON; de lo contrario, redirect
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cotización enviada por email correctamente. Si no llega, revisa la carpeta de spam.',
                    'cotizacion' => [
                        'id' => $cotizacion->id,
                        'email_enviado' => true,
                        'email_enviado_fecha' => $cotizacion->email_enviado_fecha?->format('d/m/Y H:i'),
                        'estado' => $cotizacion->estado->value
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Cotización enviada por email correctamente. Si no llega, revisa la carpeta de spam.');
        } catch (\Exception $e) {
            Log::error("Error al enviar PDF de cotización por email", [
                'cotizacion_id' => $id,
                'cliente_email' => $emailDestino ?? 'no disponible',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mensaje más específico para debugging
            $errorMessage = $e->getMessage();
            $mensaje = 'Error al enviar cotización por email';

            if (strpos($errorMessage, 'authentication failed') !== false) {
                $mensaje = 'Error de autenticación SMTP. Verifica usuario/contraseña.';
            } elseif (strpos($errorMessage, 'Connection refused') !== false) {
                $mensaje = 'No se pudo conectar al servidor SMTP. Verifica host/puerto.';
            } elseif (strpos($errorMessage, 'timeout') !== false) {
                $mensaje = 'Timeout de conexión. Servidor no responde.';
            } elseif (strpos($errorMessage, 'View') !== false) {
                $mensaje = 'Error en plantilla de email. Verifica archivos de vistas.';
            }

            throw ValidationException::withMessages([
                'email' => $mensaje . ' | Detalle: ' . $errorMessage,
            ]);
        }
    }

    /**
     * Obtener el siguiente número de cotización disponible
     */
    public function obtenerSiguienteNumero()
    {
        $siguienteNumero = $this->generarNumeroCotizacionUnico();
        return response()->json(['siguiente_numero' => $siguienteNumero]);
    }

    /**
     * Generar PDF de cotización usando plantilla Blade
     */
    public function generarPDF($id)
    {
        try {
            // Obtener la cotización con todas las relaciones necesarias
            $cotizacion = Cotizacion::with(['cliente', 'items.cotizable'])->findOrFail($id);

            // Obtener configuración de empresa
            $configuracion = \App\Models\EmpresaConfiguracion::getConfig();

            // Generar PDF usando la plantilla Blade
            $pdf = Pdf::loadView('cotizacion_pdf', [
                'cotizacion' => $cotizacion,
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
            return $pdf->download("cotizacion-{$cotizacion->numero_cotizacion}.pdf");
        } catch (\Exception $e) {
            Log::error("Error al generar PDF de cotización", [
                'cotizacion_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->with('error', 'Error al generar el PDF de la cotización');
        }
    }
}
