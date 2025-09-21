<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Cita;
use App\Models\Mantenimiento;
use App\Models\Renta;
use App\Models\Cobranza;
use App\Models\User;
use Carbon\Carbon;

class ReportesDashboardController extends Controller
{
    /**
     * Mostrar el dashboard de reportes organizado por categorías
     */
    public function index(Request $request)
    {
        $periodo = $request->get('periodo', 'mes'); // dia, semana, mes, trimestre, año

        // Determinar rango de fechas
        $rangoFechas = $this->determinarRangoFechas($periodo);
        $fechaInicio = $rangoFechas['inicio'];
        $fechaFin = $rangoFechas['fin'];

        // Estadísticas generales
        $estadisticasGenerales = $this->obtenerEstadisticasGenerales($fechaInicio, $fechaFin);

        // Categorías de reportes
        $categorias = [
            'ventas' => [
                'titulo' => 'Ventas',
                'descripcion' => 'Reportes de ventas, productos y utilidades',
                'reportes' => [
                    ['nombre' => 'Ventas Generales', 'ruta' => 'reportes.ventas', 'icono' => 'fas fa-shopping-cart'],
                    ['nombre' => 'Productos Más Vendidos', 'ruta' => 'reportes.productos', 'icono' => 'fas fa-box'],
                ],
                'estadisticas' => [
                    'total' => $estadisticasGenerales['ventas']['total'],
                    'utilidad' => $estadisticasGenerales['ventas']['utilidad'],
                    'productos_vendidos' => $estadisticasGenerales['ventas']['productos_vendidos'],
                ],
            ],
            'pagos' => [
                'titulo' => 'Pagos y Cobranzas',
                'descripcion' => 'Control de pagos, cortes diarios y cobranzas',
                'reportes' => [
                    ['nombre' => 'Corte de Pagos', 'ruta' => 'reportes.corte-diario', 'icono' => 'fas fa-cash-register'],
                    ['nombre' => 'Cobranzas', 'ruta' => 'reportes.cobranzas', 'icono' => 'fas fa-money-bill-wave'],
                ],
                'estadisticas' => [
                    'total_cobrado' => $estadisticasGenerales['rentas']['total_cobrado'],
                    'cobranzas_pendientes' => $estadisticasGenerales['rentas']['pendiente_cobrar'],
                ],
            ],
            'clientes' => [
                'titulo' => 'Clientes',
                'descripcion' => 'Información de clientes y comportamiento',
                'reportes' => [
                    ['nombre' => 'Clientes Activos', 'ruta' => 'reportes.clientes', 'icono' => 'fas fa-users'],
                    ['nombre' => 'Clientes Deudores', 'ruta' => 'reportes.clientes', 'icono' => 'fas fa-user-times'],
                ],
                'estadisticas' => [
                    'total' => $estadisticasGenerales['clientes']['total'],
                    'activos' => $estadisticasGenerales['clientes']['activos'],
                    'deudores' => $estadisticasGenerales['clientes']['deudores'],
                ],
            ],
            'inventario' => [
                'titulo' => 'Inventario',
                'descripcion' => 'Control de stock y productos',
                'reportes' => [
                    ['nombre' => 'Productos en Stock', 'ruta' => 'reportes.inventario', 'icono' => 'fas fa-warehouse'],
                    ['nombre' => 'Productos Bajos', 'ruta' => 'reportes.inventario', 'icono' => 'fas fa-exclamation-triangle'],
                ],
                'estadisticas' => [
                    'total_productos' => $estadisticasGenerales['inventario']['total_productos'],
                    'productos_bajos' => $estadisticasGenerales['inventario']['productos_bajos'],
                    'valor_inventario' => $estadisticasGenerales['inventario']['valor_inventario'],
                ],
            ],
            'servicios' => [
                'titulo' => 'Servicios',
                'descripcion' => 'Citas, mantenimientos y servicios técnicos',
                'reportes' => [
                    ['nombre' => 'Servicios Más Vendidos', 'ruta' => 'reportes.servicios', 'icono' => 'fas fa-tools'],
                    ['nombre' => 'Citas Programadas', 'ruta' => 'reportes.citas', 'icono' => 'fas fa-calendar-check'],
                    ['nombre' => 'Mantenimientos', 'ruta' => 'reportes.mantenimientos', 'icono' => 'fas fa-wrench'],
                ],
                'estadisticas' => [
                    'citas_completadas' => $estadisticasGenerales['servicios']['citas_completadas'],
                    'mantenimientos' => $estadisticasGenerales['servicios']['mantenimientos'],
                    'ingresos_servicios' => $estadisticasGenerales['servicios']['ingresos_servicios'],
                ],
            ],
            'rentas' => [
                'titulo' => 'Rentas y Equipos',
                'descripcion' => 'Gestión de rentas de equipos',
                'reportes' => [
                    ['nombre' => 'Rentas Activas', 'ruta' => 'reportes.rentas', 'icono' => 'fas fa-handshake'],
                ],
                'estadisticas' => [
                    'rentas_activas' => $estadisticasGenerales['rentas']['rentas_activas'],
                    'total_cobrado' => $estadisticasGenerales['rentas']['total_cobrado'],
                    'pendiente_cobrar' => $estadisticasGenerales['rentas']['pendiente_cobrar'],
                ],
            ],
            'finanzas' => [
                'titulo' => 'Finanzas',
                'descripcion' => 'Análisis financiero y ganancias',
                'reportes' => [
                    ['nombre' => 'Ganancias Generales', 'ruta' => 'reportes.ganancias', 'icono' => 'fas fa-chart-line'],
                    ['nombre' => 'Corte de Pagos', 'ruta' => 'reportes.corte-diario', 'icono' => 'fas fa-cash-register'],
                    ['nombre' => 'Compras', 'ruta' => 'compras.index', 'icono' => 'fas fa-shopping-bag'],
                    ['nombre' => 'Proveedores', 'ruta' => 'reportes.proveedores', 'icono' => 'fas fa-truck'],
                ],
                'estadisticas' => [
                    'ingresos_totales' => $estadisticasGenerales['finanzas']['ingresos_totales'],
                    'gastos_totales' => $estadisticasGenerales['finanzas']['gastos_totales'],
                    'ganancia_neta' => $estadisticasGenerales['finanzas']['ganancia_neta'],
                ],
            ],
            'personal' => [
                'titulo' => 'Personal',
                'descripcion' => 'Empleados, técnicos y rendimiento',
                'reportes' => [
                    ['nombre' => 'Técnicos', 'ruta' => 'reportes.tecnicos.index', 'icono' => 'fas fa-user-cog'],
                    ['nombre' => 'Empleados', 'ruta' => 'reportes.empleados', 'icono' => 'fas fa-users-cog'],
                ],
                'estadisticas' => [
                    'total_empleados' => $estadisticasGenerales['personal']['total_empleados'],
                    'tecnicos_activos' => $estadisticasGenerales['personal']['tecnicos_activos'],
                    'ventas_por_tecnico' => $estadisticasGenerales['personal']['ventas_por_tecnico'],
                ],
            ],
            'auditoria' => [
                'titulo' => 'Auditoría',
                'descripcion' => 'Registro de actividades del sistema',
                'reportes' => [
                    ['nombre' => 'Bitácora de Actividades', 'ruta' => 'reportes.auditoria', 'icono' => 'fas fa-history'],
                ],
                'estadisticas' => [
                    'actividades_hoy' => $estadisticasGenerales['auditoria']['actividades_hoy'],
                    'usuarios_activos' => $estadisticasGenerales['auditoria']['usuarios_activos'],
                ],
            ],
        ];

        return Inertia::render('Reportes/Dashboard', [
            'categorias' => $categorias,
            'periodo' => $periodo,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'periodo_label' => $this->obtenerLabelPeriodo($periodo),
        ]);
    }

    private function determinarRangoFechas(string $periodo): array
    {
        $hoy = Carbon::now();

        switch ($periodo) {
            case 'dia':
                return [
                    'inicio' => $hoy->copy()->startOfDay(),
                    'fin' => $hoy->copy()->endOfDay(),
                ];
            case 'semana':
                return [
                    'inicio' => $hoy->copy()->startOfWeek(),
                    'fin' => $hoy->copy()->endOfWeek(),
                ];
            case 'mes':
                return [
                    'inicio' => $hoy->copy()->startOfMonth(),
                    'fin' => $hoy->copy()->endOfMonth(),
                ];
            case 'trimestre':
                return [
                    'inicio' => $hoy->copy()->startOfQuarter(),
                    'fin' => $hoy->copy()->endOfQuarter(),
                ];
            case 'año':
                return [
                    'inicio' => $hoy->copy()->startOfYear(),
                    'fin' => $hoy->copy()->endOfYear(),
                ];
            default:
                return [
                    'inicio' => $hoy->copy()->startOfMonth(),
                    'fin' => $hoy->copy()->endOfMonth(),
                ];
        }
    }

    private function obtenerLabelPeriodo(string $periodo): string
    {
        $labels = [
            'dia' => 'Hoy',
            'semana' => 'Esta Semana',
            'mes' => 'Este Mes',
            'trimestre' => 'Este Trimestre',
            'año' => 'Este Año',
        ];

        return $labels[$periodo] ?? 'Este Mes';
    }

    private function obtenerEstadisticasGenerales(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        // Ventas
        $ventas = Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])->get();
        $totalVentas = $ventas->sum('total');
        $utilidadVentas = $ventas->sum(function ($venta) {
            return $venta->total - $venta->calcularCostoTotal();
        });
        $productosVendidos = $ventas->flatMap->productos->sum('pivot.cantidad');

        // Clientes
        $clientes = Cliente::all();
        $clientesActivos = $clientes->filter(function ($cliente) {
            return $cliente->ventas()->exists() || $cliente->rentas()->exists();
        })->count();
        $clientesDeudores = Renta::where('estado', 'activa')
            ->with('cobranzas')
            ->get()
            ->filter(function ($renta) {
                $pagado = $renta->cobranzas->whereIn('estado', ['pagado', 'parcial'])->sum('monto_pagado');
                return $renta->monto_total > $pagado;
            })->count();

        // Inventario
        $productos = Producto::all();
        $productosBajos = $productos->where('stock', '<=', 'stock_minimo')->where('stock', '>', 0)->count();
        $valorInventario = $productos->sum(function ($producto) {
            return $producto->stock * $producto->precio_compra;
        });

        // Servicios
        $citasCompletadas = Cita::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('estado', 'completada')->count();
        $mantenimientos = Mantenimiento::whereBetween('fecha', [$fechaInicio, $fechaFin])->count();
        $ingresosServicios = Cita::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('estado', 'completada')->sum('precio');

        // Rentas
        $rentasActivas = Renta::where('estado', 'activa')->count();
        $totalCobrado = Cobranza::whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['pagado', 'parcial'])->sum('monto_pagado');
        $pendienteCobrar = Renta::where('estado', 'activa')
            ->with('cobranzas')
            ->get()
            ->sum(function ($renta) {
                $pagado = $renta->cobranzas->whereIn('estado', ['pagado', 'parcial'])->sum('monto_pagado');
                return $renta->monto_total - $pagado;
            });

        // Finanzas
        $compras = Compra::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('total');
        $ingresosTotales = $totalVentas + $ingresosServicios + $totalCobrado;
        $gastosTotales = $compras + $ventas->sum(function ($venta) {
            return $venta->calcularCostoTotal();
        });
        $gananciaNeta = $ingresosTotales - $gastosTotales;

        // Personal
        $totalEmpleados = User::count();
        $tecnicosActivos = User::whereHas('tecnico')->count();
        $ventasPorTecnico = Venta::where('vendedor_type', 'App\Models\Tecnico')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])->count();

        // Auditoría
        $actividadesHoy = \App\Models\BitacoraActividad::whereDate('created_at', Carbon::today())->count();
        $usuariosActivos = User::where('activo', true)->count();

        return [
            'ventas' => [
                'total' => $totalVentas,
                'utilidad' => $utilidadVentas,
                'productos_vendidos' => $productosVendidos,
            ],
            'clientes' => [
                'total' => $clientes->count(),
                'activos' => $clientesActivos,
                'deudores' => $clientesDeudores,
            ],
            'inventario' => [
                'total_productos' => $productos->count(),
                'productos_bajos' => $productosBajos,
                'valor_inventario' => $valorInventario,
            ],
            'servicios' => [
                'citas_completadas' => $citasCompletadas,
                'mantenimientos' => $mantenimientos,
                'ingresos_servicios' => $ingresosServicios,
            ],
            'rentas' => [
                'rentas_activas' => $rentasActivas,
                'total_cobrado' => $totalCobrado,
                'pendiente_cobrar' => $pendienteCobrar,
            ],
            'finanzas' => [
                'ingresos_totales' => $ingresosTotales,
                'gastos_totales' => $gastosTotales,
                'ganancia_neta' => $gananciaNeta,
            ],
            'personal' => [
                'total_empleados' => $totalEmpleados,
                'tecnicos_activos' => $tecnicosActivos,
                'ventas_por_tecnico' => $ventasPorTecnico,
            ],
            'auditoria' => [
                'actividades_hoy' => $actividadesHoy,
                'usuarios_activos' => $usuariosActivos,
            ],
        ];
    }
}
