<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GarantiaController extends Controller
{
    private function getSeriesVendidasQuery()
    {
        return DB::table('producto_series as ps')
            ->join('venta_item_series as vis', 'vis.producto_serie_id', '=', 'ps.id')
            ->join('venta_items as vi', 'vi.id', '=', 'vis.venta_item_id')
            ->join('ventas as v', 'v.id', '=', 'vi.venta_id')
            ->join('clientes as c', 'c.id', '=', 'v.cliente_id')
            ->join('productos as p', 'p.id', '=', 'ps.producto_id')
            ->leftJoin('almacenes as a', 'ps.almacen_id', '=', 'a.id')
            ->select(
                'ps.id as producto_serie_id',
                'ps.numero_serie',
                'ps.estado as estado_serie',
                'ps.created_at as serie_creada',
                'ps.cita_id',
                'p.id as producto_id',
                'p.nombre as producto_nombre',
                'p.codigo as producto_codigo',
                'v.id as venta_id',
                'v.numero_venta',
                'v.created_at as venta_fecha',
                'c.id as cliente_id',
                'c.nombre_razon_social as cliente_nombre',
                'c.email as cliente_email',
                'c.telefono as cliente_telefono',
                'c.calle',
                'c.numero_exterior',
                'c.numero_interior',
                'c.colonia',
                'c.municipio',
                'c.estado',
                'c.codigo_postal',
                'a.nombre as almacen_nombre',
                'vi.precio',
                'vi.cantidad'
            )
            // Excluir ventas canceladas
            ->where('v.estado', '!=', 'cancelada');
    }

    public function index(Request $request)
    {
        $serie = trim((string) $request->query('serie', ''));

        // Si hay búsqueda por serie específica
        if ($serie !== '') {
            $resultado = DB::table('producto_series as ps')
                ->leftJoin('venta_item_series as vis', 'vis.producto_serie_id', '=', 'ps.id')
                ->leftJoin('venta_items as vi', 'vi.id', '=', 'vis.venta_item_id')
                ->leftJoin('ventas as v', 'v.id', '=', 'vi.venta_id')
                ->leftJoin('clientes as c', 'c.id', '=', 'v.cliente_id')
                ->leftJoin('productos as p', 'p.id', '=', 'ps.producto_id')
                ->select(
                    'ps.id as producto_serie_id',
                    'ps.numero_serie',
                    'ps.estado as estado_serie',
                    'ps.cita_id',
                    'p.id as producto_id',
                    'p.nombre as producto_nombre',
                    'v.id as venta_id',
                    'v.numero_venta',
                    'v.created_at as venta_fecha',
                    'c.id as cliente_id',
                    'c.nombre_razon_social as cliente_nombre',
                    'c.email as cliente_email',
                    'c.telefono as cliente_telefono'
                )
                ->where('ps.numero_serie', $serie)
                ->first();

            return Inertia::render('Garantias/BuscarSerie', [
                'serie' => $serie,
                'resultado' => $resultado,
                'seriesVendidas' => $this->getSeriesVendidasQuery()->orderBy('v.created_at', 'desc')->paginate(10),
            ]);
        }

        // Lista de todas las series vendidas (excluyendo ventas canceladas)
        $query = $this->getSeriesVendidasQuery();

        // Aplicar filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ps.numero_serie', 'like', "%{$search}%")
                  ->orWhere('p.nombre', 'like', "%{$search}%")
                  ->orWhere('p.codigo', 'like', "%{$search}%")
                  ->orWhere('c.nombre_razon_social', 'like', "%{$search}%")
                  ->orWhere('c.email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('ps.estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('v.created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('v.created_at', '<=', $request->fecha_hasta);
        }

        $seriesVendidas = $query->orderBy('v.created_at', 'desc')->paginate(50);

        return Inertia::render('Garantias/Index', [
            'seriesVendidas' => $seriesVendidas,
            'filters' => $request->only(['search', 'estado', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function create(Request $request)
    {
        // Obtener series vendidas para mostrar en la lista
        $query = $this->getSeriesVendidasQuery();
        $resultado = null;
        $search = $request->input('search');

        // Aplicar búsqueda si existe
        if ($search) {
            // 1. Intentar buscar coincidencia exacta para el card de detalle
            $resultado = DB::table('producto_series as ps')
                ->leftJoin('venta_item_series as vis', 'vis.producto_serie_id', '=', 'ps.id')
                ->leftJoin('venta_items as vi', 'vi.id', '=', 'vis.venta_item_id')
                ->leftJoin('ventas as v', 'v.id', '=', 'vi.venta_id')
                ->leftJoin('clientes as c', 'c.id', '=', 'v.cliente_id')
                ->leftJoin('productos as p', 'p.id', '=', 'ps.producto_id')
                ->select(
                    'ps.id as producto_serie_id',
                    'ps.numero_serie',
                    'ps.estado as estado_serie',
                    'ps.cita_id',
                    'p.id as producto_id',
                    'p.nombre as producto_nombre',
                    'v.id as venta_id',
                    'v.numero_venta',
                    'v.created_at as venta_fecha',
                    'c.id as cliente_id',
                    'c.nombre_razon_social as cliente_nombre',
                    'c.email as cliente_email',
                    'c.telefono as cliente_telefono'
                )
                ->where('ps.numero_serie', $search)
                ->first();

            // 2. Filtrar la lista general
            $query->where(function ($q) use ($search) {
                $q->where('ps.numero_serie', 'like', "%{$search}%")
                  ->orWhere('p.nombre', 'like', "%{$search}%")
                  ->orWhere('p.codigo', 'like', "%{$search}%")
                  ->orWhere('c.nombre_razon_social', 'like', "%{$search}%")
                  ->orWhere('c.email', 'like', "%{$search}%");
            });
        }

        $seriesVendidas = $query->orderBy('v.created_at', 'desc')->paginate(20);

        // Página para buscar series de garantía
        return Inertia::render('Garantias/BuscarSerie', [
            'serie' => $search,
            'resultado' => $resultado,
            'seriesVendidas' => $seriesVendidas,
            'filters' => $request->only(['search']),
        ]);
    }

    public function crearCitaGarantia($serieId)
    {
        try {
            // Obtener información de la serie vendida
            $serieInfo = DB::table('producto_series as ps')
                ->join('venta_item_series as vis', 'vis.producto_serie_id', '=', 'ps.id')
                ->join('venta_items as vi', 'vi.id', '=', 'vis.venta_item_id')
                ->join('ventas as v', 'v.id', '=', 'vi.venta_id')
                ->join('clientes as c', 'c.id', '=', 'v.cliente_id')
                ->join('productos as p', 'p.id', '=', 'ps.producto_id')
                ->where('ps.id', $serieId)
                ->select(
                    'ps.id as producto_serie_id',
                    'ps.numero_serie',
                    'ps.cita_id',
                    'p.nombre as producto_nombre',
                    'c.id as cliente_id',
                    'c.nombre_razon_social as cliente_nombre',
                    'c.email as cliente_email',
                    'c.telefono as cliente_telefono',
                    'c.calle',
                    'c.numero_exterior',
                    'c.numero_interior',
                    'c.colonia',
                    'c.municipio',
                    'c.estado',
                    'c.codigo_postal'
                )
                ->first();

            if (!$serieInfo) {
                return response()->json(['error' => 'Serie no encontrada'], 404);
            }
            
            // Verificar si ya existe una cita para esta serie
            if ($serieInfo->cita_id) {
                return response()->json([
                    'error' => 'Esta serie ya tiene una cita asociada',
                    'cita_id' => $serieInfo->cita_id,
                    'mensaje' => 'Esta garantía ya tiene una cita creada (Cita #' . $serieInfo->cita_id . '). No se pueden crear múltiples citas para la misma garantía.'
                ], 400);
            }

            // Construir dirección completa
            $direccion = trim(implode(' ', array_filter([
                $serieInfo->calle,
                $serieInfo->numero_exterior,
                $serieInfo->numero_interior ? 'Int. ' . $serieInfo->numero_interior : null,
                $serieInfo->colonia,
                $serieInfo->municipio,
                $serieInfo->estado,
                $serieInfo->codigo_postal
            ])));

            // Crear descripción para la cita
            $descripcion = "Garantía - Serie: {$serieInfo->numero_serie} - Producto: {$serieInfo->producto_nombre}";

            // Preparar parámetros para la URL de creación de cita
            $params = [
                'cliente_id' => $serieInfo->cliente_id,
                'numero_serie' => $serieInfo->numero_serie,
                'descripcion' => $descripcion,
                'direccion' => $direccion,
                'tipo_servicio' => 'garantia',
                'producto_serie_id' => $serieId,
            ];

            $queryString = http_build_query($params);
            $url = route('citas.create') . '?' . $queryString;

            return response()->json([
                'success' => true,
                'url' => $url,
                'cliente' => $serieInfo->cliente_nombre,
                'serie' => $serieInfo->numero_serie,
                'direccion' => $direccion
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al crear cita de garantía: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}

