<?php

namespace App\Http\Controllers;

use App\Enums\EstadoVenta;
use App\Models\Almacen;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Tecnico;
use App\Models\User;
use App\Models\Venta;
use App\Models\CuentasPorCobrar;
use App\Models\VentaItem;
use App\Services\InventarioService;
use App\Services\MarginService;
use App\Models\SatEstado;
use App\Models\SatRegimenFiscal;
use App\Models\SatUsoCfdi;
use Illuminate\Http\Request;
use App\Services\EntregaDineroService;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class VentaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly InventarioService $inventarioService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ventas = Venta::with(['cliente', 'items.ventable'])
            ->get()
            ->filter(function ($venta) {
                // Filtrar ventas con cliente y al menos un item vÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡lido
                return $venta->cliente !== null && $venta->items->isNotEmpty();
            })
            ->map(function ($venta) {
                $items = $venta->items->map(function ($item) {
                    $ventable = $item->ventable;
                    return [
                        'id' => $ventable->id,
                        'nombre' => $ventable->nombre ?? 'Sin nombre',
                        'tipo' => $item->ventable_type === Producto::class ? 'producto' : 'servicio',
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                        'descuento' => $item->descuento ?? 0,
                        'descuento_monto' => $item->descuento_monto ?? 0,
                        'subtotal' => $item->subtotal,
                    ];
                });

                return [
                    'id' => $venta->id,
                    'fecha' => $venta->fecha ? $venta->fecha->format('Y-m-d') : $venta->created_at->format('Y-m-d'),
                    'created_at' => $venta->created_at->format('Y-m-d\TH:i:sP'),
                    'cliente' => [
                        'id' => $venta->cliente->id,
                        'nombre' => $venta->cliente->nombre_razon_social ?? 'Sin nombre',
                        'nombre_razon_social' => $venta->cliente->nombre_razon_social ?? 'Sin nombre', // Para compatibilidad
                        'email' => $venta->cliente->email,
                        'telefono' => $venta->cliente->telefono,
                        'rfc' => $venta->cliente->rfc,
                        'regimen_fiscal' => $venta->cliente->regimen_fiscal,
                        'uso_cfdi' => $venta->cliente->uso_cfdi,
                        'calle' => $venta->cliente->calle,
                        'numero_exterior' => $venta->cliente->numero_exterior,
                        'numero_interior' => $venta->cliente->numero_interior,
                        'colonia' => $venta->cliente->colonia,
                        'codigo_postal' => $venta->cliente->codigo_postal,
                        'municipio' => $venta->cliente->municipio,
                        'estado' => $venta->cliente->estado,
                        'pais' => $venta->cliente->pais,
                    ],
                    'productos' => $items->toArray(),
                    'subtotal' => $venta->subtotal,
                    'descuento_general' => $venta->descuento_general ?? 0,
                    'iva' => $venta->iva,
                    'total' => $venta->total,
                    'estado' => $venta->estado->value,
                    'numero_venta' => $venta->numero_venta,
                    'pedido_id' => $venta->pedido_id ?? null,
                    'pagado' => $venta->pagado,
                    'metodo_pago' => $venta->metodo_pago,
                    'fecha_pago' => $venta->fecha_pago ? $venta->fecha_pago->format('Y-m-d') : null,
                    'notas_pago' => $venta->notas_pago,
                ];
            });

        // Calcular estadÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­sticas
        $totalVentas = $ventas->count();
        $estadisticas = [
            'total' => $totalVentas,
            'borrador' => $ventas->where('estado', EstadoVenta::Borrador->value)->count(),
            'pendientes' => $ventas->where('estado', EstadoVenta::Pendiente->value)->count(),
            'aprobadas' => $ventas->where('estado', EstadoVenta::Aprobada->value)->count(),
            'cancelada' => $ventas->where('estado', EstadoVenta::Cancelada->value)->count(),
        ];

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas->values(),
            'estados' => collect(EstadoVenta::cases())->map(fn($estado) => [
                'value' => $estado->value,
                'label' => $estado->label(),
                'color' => $estado->color()
            ]),
            'estadisticas' => $estadisticas,
            'filters' => request()->only(['search', 'estado', 'fecha_inicio', 'fecha_fin'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener productos con informaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n enriquecida (igual que en compras)
        $productosBase = Producto::where('estado', 'activo')->get();
        $almacenes = Almacen::where('estado', 'activo')->get();

        $productos = $productosBase->filter(function ($producto) use ($almacenes) {
            // Solo incluir productos que tienen stock en algÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âºn almacÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©n
            foreach ($almacenes as $almacen) {
                $inventario = \App\Models\Inventario::where('producto_id', $producto->id)
                    ->where('almacen_id', $almacen->id)
                    ->first();

                if ($inventario && $inventario->cantidad > 0) {
                    return true; // Tiene stock en este almacÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©n
                }
            }
            return false; // No tiene stock en ningÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âºn almacÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©n
        })->map(function ($producto) use ($almacenes) {
            // Obtener stock disponible en cada almacÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©n
            $stockPorAlmacen = [];
            foreach ($almacenes as $almacen) {
                $inventario = \App\Models\Inventario::where('producto_id', $producto->id)
                    ->where('almacen_id', $almacen->id)
                    ->first();

                $stockPorAlmacen[$almacen->id] = [
                    'almacen_id' => $almacen->id,
                    'almacen_nombre' => $almacen->nombre,
                    'cantidad' => $inventario ? $inventario->cantidad : 0,
                ];
            }

            return [
                'id' => $producto->id,
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'categoria' => $producto->categoria ? [
                    'id' => $producto->categoria->id,
                    'nombre' => $producto->categoria->nombre,
                ] : null,
                'marca' => $producto->marca ? [
                    'id' => $producto->marca->id,
                    'nombre' => $producto->marca->nombre,
                ] : null,
                'precio_compra' => (float) $producto->precio_compra,
                'precio_venta' => (float) $producto->precio_venta,
                'stock_total' => (int) $producto->stock,
                'stock_por_almacen' => $stockPorAlmacen,
                'expires' => (bool) $producto->expires,
                'unidad_medida' => $producto->unidad_medida,
                'tipo_producto' => $producto->tipo_producto,
                'estado' => $producto->estado,
            ];
        });

        return Inertia::render('Ventas/Create', [
            'clientes' => Cliente::activos()->select('id', 'nombre_razon_social', 'email', 'telefono')->get(),
            // Asegurar arreglo indexado para Vue (no objeto asociativo)
            'productos' => $productos->values()->toArray(),
            'servicios' => Servicio::select('id', 'nombre', 'precio', 'descripcion')->get(),
            'usuarios' => User::select('id', 'name', 'email')->get(),
            'tecnicos' => Tecnico::select('id', 'nombre', 'apellido', 'email')->get(),
            'catalogs' => [
                'tiposPersona' => [
                    ['value' => 'fisica', 'text' => 'Persona FÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­sica'],
                    ['value' => 'moral', 'text' => 'Persona Moral'],
                ],
                'estados' => SatEstado::orderBy('nombre')
                    ->get(['clave', 'nombre'])
                    ->map(function ($estado) {
                        return [
                            'value' => $estado->clave,
                            'text' => $estado->clave . ' ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â ' . $estado->nombre
                        ];
                    })
                    ->toArray(),
                'regimenesFiscales' => SatRegimenFiscal::orderBy('clave')
                    ->get(['clave', 'descripcion', 'persona_fisica', 'persona_moral'])
                    ->toArray(),
                'usosCFDI' => SatUsoCfdi::orderBy('clave')
                    ->get(['clave', 'descripcion', 'persona_fisica', 'persona_moral'])
                    ->map(function ($uso) {
                        return [
                            'value' => $uso->clave,
                            'text' => $uso->clave . ' ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â ' . $uso->descripcion
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
            'vendedor_type' => 'nullable|in:App\\Models\\User,App\\Models\\Tecnico',
            'vendedor_id' => 'nullable|integer',
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
            'descuento_general' => 'nullable|numeric|min:0|max:100',
            'notas' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Validar stock disponible para productos (considerando reservas)
            foreach ($validated['productos'] as $item) {
                if ($item['tipo'] === 'producto') {
                    $producto = Producto::with('inventarios')->find($item['id']);
                    if (!$producto) {
                        return redirect()->back()->with('error', "Producto con ID {$item['id']} no encontrado");
                    }

                    if ($producto->stock_disponible < $item['cantidad']) {
                        return redirect()->back()->with(
                            'error',
                            "Stock insuficiente para '{$producto->nombre}'. Disponible: {$producto->stock_disponible}, Solicitado: {$item['cantidad']}"
                        );
                    }
                }
            }

            // Validar mÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡rgenes de ganancia
            $marginService = new MarginService();
            $validacionMargen = $marginService->validarMargenesProductos($validated['productos']);

            if (!$validacionMargen['todos_validos']) {
                // Si hay productos con margen insuficiente, verificar si el usuario aceptÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³ el ajuste
                if ($request->has('ajustar_margen') && $request->ajustar_margen === 'true') {
                    // Ajustar precios automÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ticamente
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
            foreach ($validated['productos'] as $item) {
                $subtotalItem = $item['cantidad'] * $item['precio'];
                $subtotal += $subtotalItem;
            }

            $descuentoGeneralMonto = $request->descuento_general ?? 0;
            $subtotalDespuesGeneral = $subtotal - $descuentoGeneralMonto;

            $descuentoItems = 0;
            foreach ($validated['productos'] as $item) {
                $subtotalItem = $item['cantidad'] * $item['precio'];
                $descuentoItems += $subtotalItem * ($item['descuento'] / 100);
            }

            $subtotalFinal = $subtotalDespuesGeneral - $descuentoItems;
            $iva = $subtotalFinal * 0.16;
            $total = $subtotalFinal + $iva;

            $numero_venta = $this->generarNumeroVenta();

            $venta = Venta::create([
                'cliente_id' => $validated['cliente_id'],
                'vendedor_type' => $validated['vendedor_type'] ?? null,
                'vendedor_id' => $validated['vendedor_id'] ?? null,
                'factura_id' => null, // Puede llenarse si se asocia con una factura
                'numero_venta' => $numero_venta,
                'subtotal' => $subtotal,
                'descuento_general' => $descuentoGeneralMonto,
                'iva' => $iva,
                'total' => $total,
                'fecha' => now(),
                'estado' => EstadoVenta::Borrador,
                'notas' => $request->notas,
                'pagado' => false, // Asegurar que no esté pagado inicialmente
            ]);

            // Crear cuenta por cobrar si la venta no está pagada
            if (!$venta->pagado) {
                CuentasPorCobrar::create([
                    'venta_id' => $venta->id,
                    'monto_total' => $venta->total,
                    'monto_pagado' => 0,
                    'monto_pendiente' => $venta->total,
                    'fecha_vencimiento' => now()->addDays(30), // 30 días por defecto
                    'estado' => 'pendiente',
                    'notas' => 'Cuenta por cobrar generada automáticamente',
                ]);
            }

            // Crear items y reducir inventario
            foreach ($validated['productos'] as $item) {
                $class = $item['tipo'] === 'producto' ? Producto::class : Servicio::class;
                $modelo = $class::find($item['id']);

                if (!$modelo) {
                    Log::warning("ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âtem no encontrado: {$class} con ID {$item['id']}");
                    continue;
                }

                $subtotalItem = $item['cantidad'] * $item['precio'];
                $descuentoMontoItem = $subtotalItem * ($item['descuento'] / 100);

                // Calcular costo histórico correcto para productos
                $costoUnitario = 0;
                if ($item['tipo'] === 'producto') {
                    // Usar costo histórico basado en lotes o movimientos recientes
                    $costoUnitario = $modelo->calcularCostoPorLotes($item['cantidad']);
                }

                $ventaItem = VentaItem::create([
                    'venta_id' => $venta->id,
                    'ventable_id' => $item['id'],
                    'ventable_type' => $class,
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio'],
                    'descuento' => $item['descuento'],
                    'subtotal' => $subtotalItem,
                    'descuento_monto' => $descuentoMontoItem,
                    'costo_unitario' => $costoUnitario,
                ]);

                // Reducir inventario solo para productos (priorizar reservas)
                if ($item['tipo'] === 'producto') {
                    $cantidadRestante = $item['cantidad'];

                    // Primero consumir reservas si existen
                    if ($modelo->reservado > 0) {
                        $consumirReserva = min($modelo->reservado, $cantidadRestante);
                        $modelo->decrement('reservado', $consumirReserva);
                        $cantidadRestante -= $consumirReserva;

                        Log::info("Reserva consumida para producto {$modelo->id}", [
                            'producto_id' => $modelo->id,
                            'reserva_consumida' => $consumirReserva,
                            'reservado_anterior' => $modelo->reservado + $consumirReserva,
                            'reservado_actual' => $modelo->reservado
                        ]);
                    }

                    // Si queda cantidad por consumir, reducir stock fÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­sico
                    if ($cantidadRestante > 0) {
                        $stockAnterior = $modelo->stock;
                        $this->inventarioService->salida($modelo, $cantidadRestante, [
                            'motivo' => 'Venta creada',
                            'referencia' => $venta,
                            'detalles' => [
                                'venta_item_id' => $ventaItem->id,
                                'cantidad_total' => $item['cantidad'],
                                'reservado_consumido' => $item['cantidad'] - $cantidadRestante,
                            ],
                        ]);

                        Log::info("Stock reducido para producto {$modelo->id}", [
                            'producto_id' => $modelo->id,
                            'cantidad_reducida' => $cantidadRestante,
                            'stock_anterior' => $stockAnterior,
                            'stock_actual' => $modelo->stock,
                        ]);
                    }
                }
            }

            // Crear entrega de dinero con política por método (auto 'recibido' si transferencia)
            EntregaDineroService::crearAutoPorMetodo(
                'venta',
                $venta->id,
                (float) $venta->total,
                $request->metodo_pago,
                $venta->fecha_pago?->format('Y-m-d') ?? now()->toDateString(),
                (int) $request->user()->id,
                'Entrega automática - Venta #' . ($venta->numero_venta ?? $venta->id) . ' - Método: ' . $request->metodo_pago
            );

            DB::commit();

            // ✅ ENVIAR PDF DE VENTA POR EMAIL AUTOMÁTICAMENTE
            try {
                $this->enviarVentaPorEmail($venta);
                Log::info("PDF de venta enviado por email exitosamente", [
                    'venta_id' => $venta->id,
                    'cliente_email' => $venta->cliente->email,
                    'numero_venta' => $venta->numero_venta
                ]);
            } catch (\Exception $e) {
                Log::warning("Error al enviar PDF de venta por email", [
                    'venta_id' => $venta->id,
                    'error' => $e->getMessage()
                ]);
                // No fallar la creación de venta si falla el envío de email
            }

            return redirect()->route('ventas.index')->with('success', 'Venta creada con ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©xito');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear venta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al crear la venta: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $venta = Venta::with(['cliente', 'items.ventable'])->findOrFail($id);

        $items = $venta->items->map(function ($item) {
            $ventable = $item->ventable;
            return [
                'id' => $ventable->id,
                'nombre' => $ventable->nombre ?? $ventable->descripcion,
                'tipo' => $item->ventable_type === Producto::class ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Ventas/Show', [
            'venta' => [
                'id' => $venta->id,
                'cliente' => $venta->cliente,
                'productos' => $items,
                'subtotal' => $venta->subtotal,
                'descuento_general' => $venta->descuento_general,
                'iva' => $venta->iva,
                'total' => $venta->total,
                'fecha' => $venta->fecha ? $venta->fecha->format('Y-m-d') : $venta->created_at->format('Y-m-d'),
                'notas' => $venta->notas,
                'estado' => $venta->estado->value,
                'numero_venta' => $venta->numero_venta,
                'factura_id' => $venta->factura_id,
            ],
            'canEdit' => $venta->estado === EstadoVenta::Borrador || $venta->estado === EstadoVenta::Pendiente,
            'canDelete' => $venta->estado === EstadoVenta::Borrador || $venta->estado === EstadoVenta::Pendiente,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $venta = Venta::with([
            'cliente',
            'items.ventable' // ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â Esto carga productos y servicios
        ])->findOrFail($id);

        $clientes = Cliente::activos()->get();

        // Obtener productos con informaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n enriquecida (igual que en create)
        $productosBase = Producto::where('estado', 'activo')->get();
        $almacenes = Almacen::where('estado', 'activo')->get();

        $productos = $productosBase->filter(function ($producto) use ($almacenes) {
            // Solo incluir productos que tienen stock en algÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âºn almacÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©n
            foreach ($almacenes as $almacen) {
                $inventario = \App\Models\Inventario::where('producto_id', $producto->id)
                    ->where('almacen_id', $almacen->id)
                    ->first();

                if ($inventario && $inventario->cantidad > 0) {
                    return true; // Tiene stock en este almacÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©n
                }
            }
            return false; // No tiene stock en ningÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âºn almacÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©n
        })->map(function ($producto) use ($almacenes) {
            // Obtener stock disponible en cada almacÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©n
            $stockPorAlmacen = [];
            foreach ($almacenes as $almacen) {
                $inventario = \App\Models\Inventario::where('producto_id', $producto->id)
                    ->where('almacen_id', $almacen->id)
                    ->first();

                $stockPorAlmacen[$almacen->id] = [
                    'almacen_id' => $almacen->id,
                    'almacen_nombre' => $almacen->nombre,
                    'cantidad' => $inventario ? $inventario->cantidad : 0,
                ];
            }

            return [
                'id' => $producto->id,
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'categoria' => $producto->categoria ? [
                    'id' => $producto->categoria->id,
                    'nombre' => $producto->categoria->nombre,
                ] : null,
                'marca' => $producto->marca ? [
                    'id' => $producto->marca->id,
                    'nombre' => $producto->marca->nombre,
                ] : null,
                'precio_compra' => (float) $producto->precio_compra,
                'precio_venta' => (float) $producto->precio_venta,
                'stock_total' => (int) $producto->stock,
                'stock_por_almacen' => $stockPorAlmacen,
                'expires' => (bool) $producto->expires,
                'unidad_medida' => $producto->unidad_medida,
                'tipo_producto' => $producto->tipo_producto,
                'estado' => $producto->estado,
            ];
        });

        $servicios = Servicio::all();

        return Inertia::render('Ventas/Edit', [
            'venta' => array_merge($venta->toArray(), [
                'informacion_general' => [
                    'numero' => [
                        'label' => 'Número de Venta',
                        'value' => $venta->numero_venta,
                        'tipo' => 'fijo',
                        'descripcion' => 'Este número es fijo para todas las ventas'
                    ],
                    'fecha' => [
                        'label' => 'Fecha de Venta',
                        'value' => $venta->fecha ? $venta->fecha->format('d/m/Y') : now()->format('d/m/Y'),
                        'tipo' => 'automatica',
                        'descripcion' => 'Esta fecha se establece automáticamente con la fecha de creación'
                    ]
                ]
            ]),
            'clientes' => $clientes,
            'productos' => $productos->toArray(),
            'servicios' => $servicios,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $venta = Venta::with('cuentaPorCobrar')->findOrFail($id);

        // Permitir ediciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n solo si estÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ en Borrador o Pendiente
        if (!in_array($venta->estado, [EstadoVenta::Borrador, EstadoVenta::Pendiente])) {
            return Redirect::back()->with('error', 'Solo ventas en borrador o pendientes pueden ser actualizadas');
        }

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'numero_venta' => 'required|string|unique:ventas,numero_venta,' . $venta->id,
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
            'descuento_general' => 'nullable|numeric|min:0',
            'notas' => 'nullable|string',
            'ajustar_margen' => 'nullable|boolean',
        ]);

        // Validar mÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡rgenes de ganancia antes de calcular totales
        $marginService = new MarginService();
        $validacionMargen = $marginService->validarMargenesProductos($validated['productos']);

        if (!$validacionMargen['todos_validos']) {
            // Si hay productos con margen insuficiente, verificar si el usuario aceptÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³ el ajuste
            if ($request->has('ajustar_margen') && $request->ajustar_margen === 'true') {
                // Ajustar precios automÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ticamente
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
        foreach ($validated['productos'] as $item) {
            $subtotalItem = $item['cantidad'] * $item['precio'];
            $subtotal += $subtotalItem;
        }

        $descuentoGeneralMonto = $request->descuento_general ?? 0;
        $subtotalDespuesGeneral = $subtotal - $descuentoGeneralMonto;

        $descuentoItems = 0;
        foreach ($validated['productos'] as $item) {
            $subtotalItem = $item['cantidad'] * $item['precio'];
            $descuentoItems += $subtotalItem * ($item['descuento'] / 100);
        }

        $subtotalFinal = $subtotalDespuesGeneral - $descuentoItems;
        $iva = $subtotalFinal * 0.16;
        $total = $subtotalFinal + $iva;

        // Guarda el estado ANTES de actualizar (clave para mensaje)
        $estadoAnterior = $venta->estado;

        // Determinar el nuevo estado: si estÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ en Borrador, cambiarlo a Pendiente
        $nuevoEstado = $venta->estado === EstadoVenta::Borrador
            ? EstadoVenta::Pendiente
            : $venta->estado;

        // Atomicidad: actualizaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n + refresco de items
        DB::transaction(function () use (&$venta, $validated, $subtotal, $descuentoGeneralMonto, $iva, $total, $nuevoEstado, $request) {
            $venta->update([
                'cliente_id' => $validated['cliente_id'],
                'numero_venta' => $validated['numero_venta'],
                'subtotal' => $subtotal,
                'descuento_general' => $descuentoGeneralMonto,
                'iva' => $iva,
                'total' => $total,
                'fecha' => now(),
                'estado' => $nuevoEstado,
                'notas' => $request->notas,
            ]);

            // Eliminar ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tems anteriores
            $venta->items()->delete();

            // Guardar nuevos ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tems
            foreach ($validated['productos'] as $itemData) {
                $class = $itemData['tipo'] === 'producto' ? Producto::class : Servicio::class;
                $modelo = $class::find($itemData['id']);

                if (!$modelo) {
                    Log::warning("ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âtem no encontrado: {$class} con ID {$itemData['id']}");
                    continue;
                }

                $subtotalItem = $itemData['cantidad'] * $itemData['precio'];
                $descuentoMontoItem = $subtotalItem * ($itemData['descuento'] / 100);

                // Calcular costo histórico correcto para productos
                $costoUnitario = 0;
                if ($itemData['tipo'] === 'producto') {
                    // Usar costo histórico basado en lotes o movimientos recientes
                    $costoUnitario = $modelo->calcularCostoPorLotes($itemData['cantidad']);
                }

                VentaItem::create([
                    'venta_id' => $venta->id,
                    'ventable_id' => $itemData['id'],
                    'ventable_type' => $class,
                    'cantidad' => $itemData['cantidad'],
                    'precio' => $itemData['precio'],
                    'descuento' => $itemData['descuento'],
                    'subtotal' => $subtotalItem,
                    'descuento_monto' => $descuentoMontoItem,
                    'costo_unitario' => $costoUnitario,
                ]);
            }
        });

        // Usa el estado anterior para construir el mensaje correcto
        $mensajeExito = ($estadoAnterior === EstadoVenta::Borrador) && ($nuevoEstado === EstadoVenta::Pendiente)
            ? 'Venta actualizada y cambiada a estado pendiente exitosamente'
            : 'Venta actualizada exitosamente';

        return Redirect::route('ventas.index')
            ->with('success', $mensajeExito);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            try {
                $venta = Venta::with('factura')->findOrFail($id);

                // Verificar que la venta puede ser eliminada
                if (!in_array($venta->estado, [EstadoVenta::Borrador, EstadoVenta::Pendiente])) {
                    return Redirect::back()->with('error', 'Solo ventas en borrador o pendientes pueden ser eliminadas');
                }

                // Guardar informaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n de la factura antes de eliminar
                $facturaId = $venta->factura_id;
                $factura = $venta->factura;

                // Eliminar los ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tems de la venta primero
                $venta->items()->delete();

                // Eliminar la venta
                $venta->delete();

                // Revertir el estado de la factura asociada DESPUÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â°S de eliminar la venta
                if ($facturaId && $factura) {
                    $factura->estado = 'pendiente';
                    $factura->save();

                    Log::info("Venta ID {$id} eliminada y Factura ID {$facturaId} revertida a estado pendiente");
                }

                return Redirect::route('ventas.index')
                    ->with('success', 'Venta eliminada exitosamente y factura revertida a pendiente');
            } catch (\Exception $e) {
                Log::error('Error al eliminar venta: ' . $e->getMessage());

                // La transacciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n se revertirÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ automÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ticamente
                return Redirect::back()
                    ->with('error', 'Error al eliminar la venta: ' . $e->getMessage());
            }
        });
    }

    /**
     * Duplicate a venta.
     */
    public function duplicate(Request $request, $id)
    {
        try {
            $venta = Venta::with('cliente', 'items.ventable')->findOrFail($id);

            // Solo permitir duplicar ventas que no estÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©n canceladas
            if ($venta->estado === EstadoVenta::Cancelada) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se pueden duplicar ventas canceladas.'
                ], 400);
            }

            // Validar que la venta tenga ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tems
            if ($venta->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se puede duplicar una venta sin ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tems.'
                ], 400);
            }

            DB::beginTransaction();

            // Crear nueva venta con datos bÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡sicos
            $nuevaVenta = new Venta([
                'cliente_id' => $venta->cliente_id,
                'pedido_id' => $venta->pedido_id,
                'numero_venta' => $this->generarNumeroVenta(),
                'subtotal' => $venta->subtotal,
                'descuento_general' => $venta->descuento_general,
                'iva' => $venta->iva,
                'total' => $venta->total,
                'notas' => $venta->notas,
                'estado' => EstadoVenta::Borrador,
                'fecha' => now(),
            ]);

            $nuevaVenta->save();

            // Duplicar los ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tems validando que los productos/servicios existan
            $itemsDuplicados = 0;
            foreach ($venta->items as $item) {
                // Verificar que el producto/servicio aÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âºn existe
                $modelo = $item->ventable;
                if (!$modelo) {
                    Log::warning("Producto/Servicio no encontrado al duplicar venta", [
                        'venta_id' => $id,
                        'ventable_id' => $item->ventable_id,
                        'ventable_type' => $item->ventable_type
                    ]);
                    continue; // Saltar este ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tem
                }

                // Recalcular costo histórico correcto para productos
                $costoUnitario = $item->costo_unitario ?? 0;
                if ($item->ventable_type === Producto::class) {
                    $producto = $item->ventable;
                    if ($producto) {
                        // Usar costo histórico basado en lotes o movimientos recientes
                        $costoUnitario = $producto->calcularCostoPorLotes($item->cantidad);
                    }
                }

                $nuevaVenta->items()->create([
                    'ventable_id' => $item->ventable_id,
                    'ventable_type' => $item->ventable_type,
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                    'subtotal' => $item->subtotal,
                    'descuento_monto' => $item->descuento_monto,
                    'costo_unitario' => $costoUnitario,
                ]);

                $itemsDuplicados++;
            }

            // Verificar que se duplicaron ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tems
            if ($itemsDuplicados === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'No se pudieron duplicar los ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­tems de la venta.'
                ], 400);
            }

            DB::commit();

            Log::info('Venta duplicada exitosamente', [
                'venta_original_id' => $id,
                'venta_nueva_id' => $nuevaVenta->id,
                'items_duplicados' => $itemsDuplicados
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Venta duplicada correctamente.',
                'venta_id' => $nuevaVenta->id,
                'numero_venta' => $nuevaVenta->numero_venta,
                'items_count' => $itemsDuplicados
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Error de base de datos al duplicar venta: ' . $e->getMessage(), [
                'venta_id' => $id,
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error de base de datos al duplicar la venta.',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error general al duplicar venta: ' . $e->getMessage(), [
                'venta_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno al duplicar la venta.',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Cancel the specified resource (soft cancel).
     */
    public function cancel($id)
    {
        $venta = Venta::with('items.ventable')->findOrFail($id);

        // Permitir cancelar en cualquier estado excepto ya cancelada
        if ($venta->estado === EstadoVenta::Cancelada) {
            return response()->json([
                'success' => false,
                'error' => 'La venta ya estÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ cancelada'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Devolver inventario de productos
            foreach ($venta->items as $item) {
                if ($item->ventable_type === Producto::class) {
                    $producto = $item->ventable;
                    if ($producto) {
                        if ($venta->pagado) {
                            // Si la venta estaba pagada, devolver al stock fÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­sico
                            $stockAnterior = $producto->stock;
                            $this->inventarioService->entrada($producto, $item->cantidad, [
                                'motivo' => 'CancelaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n de venta pagada',
                                'referencia' => $venta,
                                'detalles' => [
                                    'venta_item_id' => $item->id,
                                ],
                            ]);
                            Log::info("Stock devuelto para producto {$producto->id} (venta pagada cancelada)", [
                                'producto_id' => $producto->id,
                                'venta_id' => $venta->id,
                                'cantidad_devuelta' => $item->cantidad,
                                'stock_anterior' => $stockAnterior,
                                'stock_actual' => $producto->stock
                            ]);
                        } else {
                            // Si la venta no estaba pagada, devolver a reservas (esto requiere lÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³gica especial)
                            // Nota: El servicio actual no maneja reservas, esto necesitarÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â­a extensiÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n
                            $producto->increment('reservado', $item->cantidad);
                            Log::info("Reserva devuelta para producto {$producto->id} (venta no pagada cancelada)", [
                                'producto_id' => $producto->id,
                                'venta_id' => $venta->id,
                                'cantidad_devuelta' => $item->cantidad,
                                'reservado_anterior' => $producto->reservado - $item->cantidad,
                                'reservado_actual' => $producto->reservado
                            ]);
                        }
                    }
                }
            }

            // Actualizar estado a cancelada, quitar pago y registrar quiÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©n lo cancelÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³
            $venta->update([
                'estado' => EstadoVenta::Cancelada,
                'pagado' => false,
                'metodo_pago' => null,
                'fecha_pago' => null,
                'notas_pago' => 'CANCELADO - ' . ($venta->notas_pago ?? ''),
                'pagado_por' => null,
                'deleted_by' => Auth::id(),
                'deleted_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta cancelada exitosamente',
                'eliminado_por' => Auth::user()->name ?? 'Usuario actual'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cancelar venta: ' . $e->getMessage(), [
                'venta_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al cancelar la venta',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Mark a sale as paid.
     */
    public function marcarPagado(Request $request, $id)
    {
        $request->validate([
            'metodo_pago' => 'required|in:efectivo,transferencia,cheque,tarjeta,otros',
            'notas_pago' => 'nullable|string|max:500'
        ]);

        $venta = Venta::with('cuentaPorCobrar')->findOrFail($id);

        // Verificar que la venta no estÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â© ya pagada
        if ($venta->pagado) {
            return response()->json([
                'success' => false,
                'error' => 'Esta venta ya estÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ marcada como pagada'
            ], 400);
        }

        // Verificar que la venta no estÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â© cancelada
        if ($venta->estado === EstadoVenta::Cancelada) {
            return response()->json([
                'success' => false,
                'error' => 'No se puede marcar como pagada una venta cancelada'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Actualizar la venta con la informaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n de pago
            $venta->update([
                'pagado' => true,
                'estado' => EstadoVenta::Aprobada,
                'metodo_pago' => $request->metodo_pago,
                'fecha_pago' => now(),
                'notas_pago' => $request->notas_pago,
                'pagado_por' => $request->user()->id,
            ]);

            // ✅ SINCRONIZAR CON CUENTA POR COBRAR
            if ($venta->cuentaPorCobrar) {
                $cuentaCobrar = $venta->cuentaPorCobrar;

                // Marcar como completamente pagada
                $cuentaCobrar->registrarPago($venta->total, "Pago recibido - {$request->metodo_pago}");

                Log::info("Cuenta por cobrar actualizada al marcar venta como pagada", [
                    'venta_id' => $venta->id,
                    'cuenta_cobrar_id' => $cuentaCobrar->id,
                    'monto_pagado' => $venta->total,
                    'metodo_pago' => $request->metodo_pago,
                    'usuario_id' => $request->user()->id
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta marcada como pagada exitosamente',
                'venta' => [
                    'id' => $venta->id,
                    'pagado' => true,
                    'metodo_pago' => $venta->metodo_pago,
                    'fecha_pago' => $venta->fecha_pago->format('Y-m-d'),
                    'notas_pago' => $venta->notas_pago,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al marcar venta como pagada: ' . $e->getMessage(), [
                'venta_id' => $id,
                'user_id' => $request->user()->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno al procesar el pago',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Generate a unique numero_venta.
     */
    private function generarNumeroVenta()
    {
        $ultima = Venta::orderBy('id', 'desc')->first();
        $numero = $ultima ? $ultima->id + 1 : 1;
        return 'VEN-' . date('Ymd') . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Enviar venta por email
     */
    public function enviarEmail(Request $request, $id)
    {
        $data = $request->validate([
            'email_destino' => ['required', 'email'],
        ]);

        try {
            // Obtener la venta con todas las relaciones necesarias
            $venta = Venta::with(['cliente', 'items.ventable'])->findOrFail($id);

            // Verificar que el cliente tenga email
            if (!$venta->cliente->email) {
                throw ValidationException::withMessages([
                    'email' => 'El cliente no tiene email configurado',
                ]);
            }

            // Obtener configuración de empresa para el PDF
            $configuracion = \App\Models\EmpresaConfiguracion::getConfig();

            // Generar PDF de la venta
            $pdf = Pdf::loadView('venta_pdf', [
                'venta' => $venta,
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
                'cuenta' => $venta->cuentaPorCobrar,
                'venta' => $venta,
                'cliente' => $venta->cliente,
                'configuracion' => $configuracion,
                'recordatorio' => null, // No hay recordatorio en este contexto
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
            Mail::send('emails.recordatorio_pago', $datosEmail, function ($message) use ($venta, $pdf, $configuracion) {
                $message->to($venta->cliente->email)
                    ->subject("💰 Recordatorio de Pago - Factura #{$venta->numero_venta} - {$configuracion->nombre_empresa}")
                    ->attachData($pdf->output(), "factura-{$venta->numero_venta}.pdf", [
                        'mime' => 'application/pdf',
                    ]);

                // Agregar reply-to si está configurado
                if ($configuracion->email_reply_to) {
                    $message->replyTo($configuracion->email_reply_to);
                }
            });

            Log::info("Recordatorio de pago enviado por email", [
                'venta_id' => $venta->id,
                'cliente_email' => $venta->cliente->email,
                'numero_venta' => $venta->numero_venta,
                'tipo' => 'recordatorio_pago',
                'configuracion_smtp' => [
                    'host' => $configuracion->smtp_host,
                    'port' => $configuracion->smtp_port,
                    'encryption' => $configuracion->smtp_encryption,
                ]
            ]);

            // Si es una petición AJAX, devolver JSON; de lo contrario, redirect
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Recordatorio de pago enviado correctamente',
                    'venta' => [
                        'id' => $venta->id,
                        'estado' => $venta->estado->value
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Recordatorio de pago enviado correctamente');
        } catch (\Exception $e) {
            Log::error("Error al enviar recordatorio de pago por email", [
                'venta_id' => $id,
                'cliente_email' => $venta->cliente->email ?? 'no disponible',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mensaje más específico para debugging
            $errorMessage = $e->getMessage();
            $mensaje = 'Error al enviar recordatorio de pago';

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
     * Enviar PDF de venta por email automáticamente
     */
    private function enviarVentaPorEmail(Venta $venta)
    {
        try {
            // Verificar que el cliente tenga email
            if (!$venta->cliente->email) {
                Log::info("Cliente sin email, omitiendo envío automático", [
                    'venta_id' => $venta->id,
                    'cliente_id' => $venta->cliente_id
                ]);
                return;
            }

            // Obtener configuración de empresa para el PDF
            $configuracion = \App\Models\EmpresaConfiguracion::getConfig();

            // Generar PDF de la venta
            $pdf = Pdf::loadView('venta_pdf', [
                'venta' => $venta,
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
                'venta' => $venta,
                'cliente' => $venta->cliente,
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
            Mail::send('emails.venta', $datosEmail, function ($message) use ($venta, $pdf, $configuracion) {
                $message->to($venta->cliente->email)
                        ->subject("Venta #{$venta->numero_venta} - {$configuracion->nombre_empresa}")
                        ->attachData($pdf->output(), "venta-{$venta->numero_venta}.pdf", [
                            'mime' => 'application/pdf',
                        ]);

                // Agregar reply-to si está configurado
                if ($configuracion->email_reply_to) {
                    $message->replyTo($configuracion->email_reply_to);
                }
            });

            Log::info("PDF de venta enviado por email", [
                'venta_id' => $venta->id,
                'cliente_email' => $venta->cliente->email,
                'numero_venta' => $venta->numero_venta,
                'configuracion_smtp' => [
                    'host' => $configuracion->smtp_host,
                    'port' => $configuracion->smtp_port,
                    'encryption' => $configuracion->smtp_encryption,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error al enviar PDF de venta por email", [
                'venta_id' => $venta->id,
                'cliente_email' => $venta->cliente->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-lanzar para que se maneje en el método padre
        }
    }
}
