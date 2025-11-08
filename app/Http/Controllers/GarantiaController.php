<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GarantiaController extends Controller
{
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
            ]);
        }

        // Lista de todas las series vendidas
        $query = DB::table('producto_series as ps')
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
            );

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
                    'ps.numero_serie',
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
                'tipo_equipo' => 'boiler' // Asumiendo que es boiler según el contexto
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

