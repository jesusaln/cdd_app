<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\Venta;
use App\Models\Servicio;
use App\Services\CotizacionService;
use App\Http\Requests\CotizacionRequest;
use App\Models\CotizacionProducto;
use App\Http\Resources\CotizacionResource;
use App\Enums\EstadoCotizacion;
use App\Enums\EstadoPedido;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class CotizacionController extends Controller
{
    private $cotizacionService;

    public function __construct(CotizacionService $cotizacionService)
    {
        $this->cotizacionService = $cotizacionService;
    }

    public function index()
    {
        $cotizaciones = $this->cotizacionService->getAllCotizaciones()
            ->map(fn($cotizacion) => (new CotizacionResource($cotizacion))->resolve());
        //                                                              Se añadió este ^

        return Inertia::render('Cotizaciones/Index', [
            'cotizaciones' => $cotizaciones,
            'estados' => collect(EstadoCotizacion::cases())->map(fn($estado) => [
                'value' => $estado->value,
                'label' => $estado->label(),
                'color' => $estado->color()
            ]),
            'filters' => request()->only(['search', 'estado', 'fecha_inicio', 'fecha_fin'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Cotizaciones/Create', [
            'clientes' => Cliente::select([
                'id',
                'nombre_razon_social',
                'rfc',
                'email',
                'telefono',
                'empresa',
                'calle',
                'numero_exterior',
                'colonia',
                'codigo_postal',
                'ciudad',
                'estado'
            ])->get(),
            'productos' => Producto::select([
                'id',
                'nombre',
                'categoria',
                'precio_venta',
                'costo',
                'codigo', // ✅ Asegúrate de incluir este campo
                'sku',
                'clave',
                'stock',
                // ... otros campos
            ])->get(),
            'servicios' => Servicio::active()->get(['id', 'nombre', 'precio', 'codigo', 'categoria', 'descripcion', 'duracion']),
            'defaults' => [
                'fecha' => now()->format('Y-m-d'),
                'validez' => 30,
                'moneda' => 'MXN'
            ]
        ]);
    }

    public function guardarBorrador(Request $request)
    {
        // Validar datos mínimos
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
        ]);

        // Si tiene ID, es una edición
        if ($request->has('id')) {
            $cotizacion = Cotizacion::findOrFail($request->id);
            $cotizacion->update([
                'estado' => 'borrador',
                'datos' => $request->except(['_token', 'id']),
                'subtotal' => $request->subtotal ?? 0,
                'total' => $request->total ?? 0,
            ]);
        } else {
            // Es un nuevo borrador
            $cotizacion = Cotizacion::create([
                'cliente_id' => $request->cliente_id,
                'estado' => 'borrador',
                'datos' => $request->except(['_token']),
                'subtotal' => $request->subtotal ?? 0,
                'total' => $request->total ?? 0,
            ]);
        }

        return redirect()->back()->with('success', 'Borrador guardado con éxito');
    }

    public function store(Request $request)
    {
        Log::info('Iniciando creación de cotización', $request->all());

        try {
            $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'productos' => 'required|array',
                'productos.*.id' => 'required|integer',
                'productos.*.tipo' => 'required|in:producto,servicio',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.precio' => 'required|numeric|min:0',
                'productos.*.descuento' => 'required|numeric|min:0|max:100',
            ]);

            Log::info('Validación pasada');

            $cotizacion = Cotizacion::create([
                'cliente_id' => $request->cliente_id,
                'subtotal' => $request->subtotal,
                'descuento_general' => $request->descuento_general,
                'iva' => $request->iva,
                'total' => $request->total,
            ]);

            Log::info('Cotización creada', ['id' => $cotizacion->id]);

            foreach ($request->productos as $item) {
                $class = $item['tipo'] === 'producto' ? Producto::class : Servicio::class;

                $subtotal = $item['cantidad'] * $item['precio'];
                $descuento_monto = $subtotal * ($item['descuento'] / 100);

                $pivot = new CotizacionProducto([
                    'cotizacion_id' => $cotizacion->id,
                    'cotizable_id' => $item['id'],
                    'cotizable_type' => $class,
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio'],
                    'descuento' => $item['descuento'],
                    'subtotal' => $subtotal,
                    'descuento_monto' => $descuento_monto,
                ]);

                $pivot->save(); // ❌ Aquí puede estar el error

                Log::info('Ítem guardado', [
                    'tipo' => $item['tipo'],
                    'id' => $item['id'],
                    'class' => $class,
                    'subtotal' => $subtotal,
                ]);
            }

            Log::info('Cotización guardada con éxito', ['cotizacion_id' => $cotizacion->id]);

            return redirect()->route('cotizaciones.index')
                ->with('success', 'Cotización creada con éxito');
        } catch (\Exception $e) {
            Log::error('Error al crear cotización', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'línea' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Error al crear la cotización: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function storeDraft(Request $request)
    {
        // Validar datos mínimos
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'array',
        ]);

        $cotizacion = Cotizacion::create([
            'cliente_id' => $request->cliente_id,
            'estado' => 'borrador',
            'datos' => $request->except(['_token']),
            'subtotal' => $request->subtotal ?? 0,
            'total' => $request->total ?? 0,
        ]);

        return redirect()->route('cotizaciones.create')
            ->with('success', 'Borrador guardado temporalmente');
    }

    public function show($id)
    {
        $cotizacion = Cotizacion::with([
            'cliente',
            'productos' => fn($query) => $query->withPivot('cantidad', 'precio', 'descuento'),
            'servicios' => fn($query) => $query->withPivot('cantidad', 'precio', 'descuento')
        ])->findOrFail($id);

        return Inertia::render('Cotizaciones/Show', [
            'cotizacion' => $this->formatCotizacionData($cotizacion),
            'canConvert' => $cotizacion->estado === EstadoCotizacion::Aprobada,
            'canEdit' => $cotizacion->estado === EstadoCotizacion::Pendiente,
            'canDelete' => $cotizacion->estado === EstadoCotizacion::Pendiente
        ]);
    }

    public function edit($id)
    {
        // Cargar la cotización con las relaciones de Eloquent
        $cotizacion = Cotizacion::with([
            'cliente',
            'productos',
            'servicios'
        ])->findOrFail($id);

        // Verificar si la cotización puede ser editada
        if ($cotizacion->estado !== EstadoCotizacion::Pendiente) {
            return Redirect::route('cotizaciones.show', $cotizacion->id)
                ->with('warning', 'Solo cotizaciones pendientes pueden ser editadas');
        }

        // Obtener datos adicionales para clientes, productos y servicios
        $clientes = Cliente::select([
            'id',
            'nombre_razon_social',
            'rfc',
            'email',
            'telefono',
            'empresa',
            'calle',
            'numero_exterior',
            'colonia',
            'codigo_postal',
            'ciudad',
            'estado'
        ])->get();

        $productos = Producto::select([
            'id',
            'nombre',
            'categoria',
            'precio_venta',
            'costo',
            'codigo',
            'sku',
            'clave',
            'stock'
        ])->get();

        $servicios = Servicio::active()->get(['id', 'nombre', 'precio', 'codigo', 'categoria', 'descripcion', 'duracion']);

        // Añadir atributos adicionales manualmente
        $cotizacion->defaults = [
            'fecha' => now()->format('Y-m-d'),
            'validez' => 30,
            'moneda' => 'MXN'
        ];

        return Inertia::render('Cotizaciones/Edit', [
            'cotizacion' => $this->formatCotizacionData($cotizacion),
            'clientes' => $clientes,
            'productos' => $productos,
            'servicios' => $servicios
        ]);
    }

    public function update(CotizacionRequest $request, $id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        if ($cotizacion->estado !== EstadoCotizacion::Pendiente) {
            return Redirect::back()
                ->with('error', 'Solo cotizaciones pendientes pueden ser actualizadas');
        }

        try {
            DB::transaction(function () use ($request, $cotizacion) {
                $this->cotizacionService->updateCotizacion($cotizacion, $request->validated());
            });

            return Redirect::route('cotizaciones.show', $cotizacion->id)
                ->with('success', 'Cotización actualizada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar cotización', [
                'cotizacion_id' => $id,
                'error' => $e->getMessage()
            ]);

            return Redirect::back()
                ->with('error', 'Error al actualizar la cotización: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        if ($cotizacion->estado !== EstadoCotizacion::Pendiente) {
            return Redirect::back()
                ->with('error', 'Solo cotizaciones pendientes pueden ser eliminadas');
        }

        try {
            DB::transaction(function () use ($cotizacion) {
                $this->cotizacionService->deleteCotizacion($cotizacion);
            });

            return Redirect::route('cotizaciones.index')
                ->with('success', 'Cotización eliminada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar cotización', [
                'cotizacion_id' => $id,
                'error' => $e->getMessage()
            ]);

            return Redirect::back()
                ->with('error', 'Error al eliminar la cotización: ' . $e->getMessage());
        }
    }

    public function convertirAPedido(Request $request, $id)
    {
        $cotizacion = Cotizacion::with([
            'cliente',
            'productos' => fn($query) => $query->withPivot('cantidad', 'precio', 'descuento')
        ])->findOrFail($id);

        if ($cotizacion->estado !== EstadoCotizacion::Aprobada) {
            return Redirect::back()
                ->with('error', 'Solo cotizaciones aprobadas pueden convertirse a pedido');
        }

        try {
            DB::beginTransaction();

            $pedido = $this->cotizacionService->convertirAPedido($cotizacion);

            DB::commit();

            return Redirect::route('pedidos.show', $pedido->id)
                ->with('success', 'Pedido creado exitosamente a partir de la cotización');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al convertir cotización a pedido', [
                'cotizacion_id' => $id,
                'error' => $e->getMessage()
            ]);

            return Redirect::back()
                ->with('error', 'Error al crear el pedido: ' . $e->getMessage());
        }
    }

    public function convertirAVenta($id)
    {
        $cotizacion = Cotizacion::with([
            'cliente',
            'productos' => fn($query) => $query->withPivot('cantidad', 'precio', 'descuento'),
            'servicios' => fn($query) => $query->withPivot('cantidad', 'precio', 'descuento')
        ])->findOrFail($id);

        if ($cotizacion->estado !== EstadoCotizacion::Aprobada) {
            return Redirect::back()
                ->with('error', 'Solo cotizaciones aprobadas pueden convertirse a venta');
        }

        try {
            DB::beginTransaction();

            $venta = $this->cotizacionService->convertirAVenta($cotizacion);

            DB::commit();

            return Redirect::route('ventas.show', $venta->id)
                ->with('success', 'Venta creada exitosamente a partir de la cotización');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al convertir cotización a venta', [
                'cotizacion_id' => $id,
                'error' => $e->getMessage()
            ]);

            return Redirect::back()
                ->with('error', 'Error al crear la venta: ' . $e->getMessage());
        }
    }

    private function formatCotizacionData(Cotizacion $cotizacion): array
    {
        // Combinar productos y servicios usando collect() para evitar problemas con merge
        $items = collect($cotizacion->productos->map(fn($producto) => $this->formatItem($producto, 'producto')))
            ->concat($cotizacion->servicios->map(fn($servicio) => $this->formatItem($servicio, 'servicio')))
            ->values()
            ->toArray();

        return [
            'id' => $cotizacion->id,
            'cliente' => $cotizacion->cliente ? [
                'id' => $cotizacion->cliente->id,
                'nombre_razon_social' => $cotizacion->cliente->nombre_razon_social,
                'email' => $cotizacion->cliente->email ?? null,
            ] : null,
            'cliente_id' => $cotizacion->cliente_id,
            'items' => $items,
            'subtotal' => $cotizacion->subtotal,
            'descuento' => $cotizacion->descuento ?? $cotizacion->descuento_general, // Ajuste para consistencia
            'iva' => $cotizacion->iva,
            'total' => $cotizacion->total,
            'fecha' => $cotizacion->created_at->format('Y-m-d'),
            'validez' => $cotizacion->validez_dias,
            'moneda' => $cotizacion->moneda,
            'notas' => $cotizacion->notas,
            'estado' => [
                'value' => $cotizacion->estado->value,
                'label' => $cotizacion->estado->label(),
                'color' => $cotizacion->estado->color(),
            ],
            'created_at' => $cotizacion->created_at->format('Y-m-d H:i'),
            'updated_at' => $cotizacion->updated_at->format('Y-m-d H:i'),
        ];
    }

    private function formatItem($item, string $tipo): array
    {
        return [
            'id' => $item->id,
            'nombre' => $item->nombre,
            'descripcion' => $item->descripcion ?? null,
            'tipo' => $tipo,
            'cantidad' => $item->pivot->cantidad,
            'precio' => $item->pivot->precio,
            'descuento' => $item->pivot->descuento,
            'subtotal' => $item->pivot->cantidad * $item->pivot->precio * (1 - ($item->pivot->descuento / 100))
        ];
    }
}
