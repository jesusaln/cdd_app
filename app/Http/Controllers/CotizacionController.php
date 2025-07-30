<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use App\Services\CotizacionService;
use App\Http\Requests\CotizacionRequest;
use Inertia\Inertia;
use Carbon\Carbon;

class CotizacionController extends Controller
{
    private $cotizacionService;

    public function __construct(CotizacionService $cotizacionService)
    {
        $this->cotizacionService = $cotizacionService;
    }

    public function index()
    {
        $cotizaciones = $this->cotizacionService->getAllCotizaciones()->map(function ($cotizacion) {
            return $this->mapCotizacion($cotizacion);
        });

        return Inertia::render('Cotizaciones/Index', [
            'cotizaciones' => $cotizaciones,
        ]);
    }

    public function create()
    {
        $clientes = Cliente::all();
        $productos = Producto::all();
        $servicios = Servicio::all();

        return Inertia::render('Cotizaciones/Create', [
            'clientes' => $clientes,
            'productos' => $productos,
            'servicios' => $servicios,
        ]);
    }

    public function store(CotizacionRequest $request)
    {
        $cotizacion = $this->cotizacionService->createCotizacion($request->validated());

        return redirect()->route('cotizaciones.index')->with('success', 'Cotización creada exitosamente.');
    }

    public function show($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'productos', 'servicios'])->findOrFail($id);

        return Inertia::render('Cotizaciones/Show', [
            'cotizacion' => $this->mapCotizacion($cotizacion),
        ]);
    }

    public function edit($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'productos', 'servicios'])->findOrFail($id);
        $clientes = Cliente::all();
        $productos = Producto::all();
        $servicios = Servicio::all();

        return Inertia::render('Cotizaciones/Edit', [
            'cotizacion' => array_merge($this->mapCotizacion($cotizacion), ['cliente_id' => $cotizacion->cliente_id]),
            'clientes' => $clientes,
            'productos' => $productos,
            'servicios' => $servicios,
        ]);
    }

    public function update(CotizacionRequest $request, $id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        $this->cotizacionService->updateCotizacion($cotizacion, $request->validated());

        return redirect()->route('cotizaciones.index')->with('success', 'Cotización actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        $this->cotizacionService->deleteCotizacion($cotizacion);

        return redirect()->route('cotizaciones.index')->with('success', 'Cotización eliminada exitosamente.');
    }

    public function convertirAPedido($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'productos', 'servicios'])->findOrFail($id);
        $this->cotizacionService->convertirAPedido($cotizacion);

        return redirect()->route('pedidos.index')->with('success', 'Cotización convertida a pedido exitosamente.');
    }

    public function convertirAVenta($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'productos', 'servicios'])->findOrFail($id);
        $this->cotizacionService->convertirAVenta($cotizacion);

        return redirect()->route('ventas.index')->with('success', 'Cotización convertida a venta exitosamente.');
    }

    private function mapCotizacion($cotizacion)
    {
        $items = $cotizacion->productos->map(function ($producto) {
            return $this->mapItem($producto, 'producto');
        })->merge($cotizacion->servicios->map(function ($servicio) {
            return $this->mapItem($servicio, 'servicio');
        }));

        return [
            'id' => $cotizacion->id,
            'cliente' => $cotizacion->cliente,
            'productos' => $items,
            'total' => $cotizacion->total,
            'fecha' => Carbon::parse($cotizacion->created_at)->format('Y-m-d'),
        ];
    }

    private function mapItem($item, $tipo)
    {
        return [
            'id' => $item->id,
            'nombre' => $item->nombre,
            'tipo' => $tipo,
            'pivot' => [
                'cantidad' => $item->pivot->cantidad,
                'precio' => $item->pivot->precio,
            ],
        ];
    }
}
