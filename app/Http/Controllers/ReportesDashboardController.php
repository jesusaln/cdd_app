<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Cita;
use App\Models\Mantenimiento;
use App\Models\Renta;
use App\Models\Cobranza;
use Spatie\Activitylog\Models\Activity as ActivityLog;
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
                    ['nombre' => 'Ventas Pendientes', 'ruta' => 'reportes.ventas-pendientes', 'icono' => 'fas fa-clock'],
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
                    ['nombre' => 'Movimientos Inventario', 'url' => '/reportes?tab=movimientos', 'icono' => 'fas fa-exchange-alt'],
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

        return Inertia::render('Reportes/Dashboard');
    }

    /**
     * Mostrar el centro de reportes con tabs
     */
    public function indexTabs(Request $request)
    {
        $tab = $request->get('tab', 'ventas');

        // Si es el tab de corte, mostrar vista específica de corte diario
        if ($tab === 'corte') {
            return $this->mostrarCorteDiario($request);
        }

        // Obtener datos para las tabs
        // Evitar eager load conflictivo: cargamos cliente e items (productos/servicios se resuelven al calcular costo)
        $ventas = Venta::with(['cliente', 'items.ventable'])->get()->map(function ($venta) {
            $venta->costo_total = $venta->calcularCostoTotal();
            return $venta;
        });
        $compras = Compra::with(['proveedor', 'productos'])->get();
        $inventario = Producto::with('categoria')->get();
        $movimientos = \App\Models\InventarioMovimiento::with(['producto', 'user'])->latest()->get();

        // Corte diario de hoy (ventas/cobranzas/egresos)
        $hoy = now()->format('Y-m-d');
        $ventasPagadas = Venta::with(['cliente', 'pagadoPor'])
            ->where('pagado', true)
            ->whereBetween('fecha_pago', [$hoy . ' 00:00:00', $hoy . ' 23:59:59'])
            ->get()
            ->map(function ($venta) {
                return [
                    'id' => $venta->id,
                    'tipo' => 'venta',
                    'numero' => $venta->numero_venta,
                    'cliente' => $venta->cliente->nombre_razon_social ?? 'Sin cliente',
                    'concepto' => 'Venta de productos/servicios',
                    'total' => (float) $venta->total,
                    'metodo_pago' => $venta->metodo_pago,
                    'fecha_pago' => optional($venta->fecha_pago)->toIso8601String(),
                    'notas_pago' => $venta->notas_pago,
                    'cobrado_por' => optional($venta->pagadoPor)->name ?? 'Sistema',
                    'pagado_por' => $venta->pagado_por,
                ];
            });

        $cobranzasPagadas = Cobranza::with(['renta.cliente', 'responsableCobro'])
            ->whereIn('estado', ['pagado', 'parcial'])
            ->whereBetween('fecha_pago', [$hoy . ' 00:00:00', $hoy . ' 23:59:59'])
            ->get()
            ->map(function ($cobranza) {
                return [
                    'id' => $cobranza->id,
                    'tipo' => 'renta',
                    'numero' => $cobranza->renta->numero_contrato ?? 'N/A',
                    'cliente' => $cobranza->renta->cliente->nombre_razon_social ?? 'Sin cliente',
                    'concepto' => $cobranza->concepto ?? 'Cobranza de renta',
                    'total' => (float) $cobranza->monto_pagado,
                    'metodo_pago' => $cobranza->metodo_pago,
                    'fecha_pago' => optional($cobranza->fecha_pago)->toIso8601String(),
                    'notas_pago' => $cobranza->notas_pago,
                    'cobrado_por' => optional($cobranza->responsableCobro)->name ?? 'Sistema',
                    'pagado_por' => $cobranza->user_id,
                ];
            });

        $comprasPagadas = \App\Models\CuentasPorPagar::with(['compra.proveedor'])
            ->where('estado', 'pagado')
            ->whereBetween('updated_at', [$hoy . ' 00:00:00', $hoy . ' 23:59:59'])
            ->get()
            ->map(function ($cpp) {
                return [
                    'id' => $cpp->id,
                    'tipo' => 'compra',
                    'numero' => optional($cpp->compra)->numero_compra ?? 'N/A',
                    'cliente' => optional(optional($cpp->compra)->proveedor)->nombre_razon_social ?? 'Proveedor',
                    'concepto' => 'Compra pagada',
                    'total' => -1 * (float) $cpp->monto_total,
                    'metodo_pago' => 'otros',
                    'fecha_pago' => optional($cpp->updated_at)->toIso8601String(),
                    'notas_pago' => $cpp->notas,
                    'cobrado_por' => 'Sistema',
                    'pagado_por' => null,
                ];
            });

        $entregas = \App\Models\EntregaDinero::with(['usuario'])
            ->where('estado', 'recibido')
            ->whereBetween('fecha_entrega', [$hoy, $hoy])
            ->get()
            ->map(function ($e) {
                return [
                    'id' => $e->id,
                    'tipo' => 'entrega',
                    'numero' => 'ENT-' . str_pad($e->id, 4, '0', STR_PAD_LEFT),
                    'cliente' => optional($e->usuario)->name ?? 'Usuario',
                    'concepto' => 'Entrega de dinero',
                    'total' => -1 * (float) $e->total,
                    'metodo_pago' => 'otros',
                    'fecha_pago' => optional($e->fecha_entrega)->format('Y-m-d') . ' 00:00:00',
                    'notas_pago' => $e->notas,
                    'cobrado_por' => optional($e->usuario)->name ?? 'Usuario',
                    'pagado_por' => $e->user_id,
                ];
            });

        $corteDiario = collect()
            ->merge($ventasPagadas)
            ->merge($cobranzasPagadas)
            ->merge($comprasPagadas)
            ->merge($entregas)
            ->sortByDesc('fecha_pago')
            ->values();

        return Inertia::render('Reportes/Index', [
            'reportesVentas' => $ventas,
            'corteVentas' => $ventas->sum('total'),
            'utilidadVentas' => $ventas->sum(function ($venta) {
                return $venta->total - $venta->calcularCostoTotal();
            }),
            'reportesCompras' => $compras,
            'totalCompras' => $compras->sum('total'),
            'inventario' => $inventario,
            'movimientosInventario' => $movimientos,
            'corteDiario' => $corteDiario,
            'usuarios' => User::select('id', 'name')->get(),
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

    /**
     * Obtener estadísticas generales para el dashboard entre dos fechas (Carbon)
     *
     * Devuelve un arreglo con claves usadas por el controlador; cada cálculo está envuelto
     * en try/catch para evitar errores si alguna columna o relación no existe.
     */
    private function obtenerEstadisticasGenerales($inicio, $fin): array
    {
        // Valores por defecto
        $defaults = [
            'ventas' => ['total' => 0, 'utilidad' => 0, 'productos_vendidos' => 0],
            'rentas' => ['rentas_activas' => 0, 'total_cobrado' => 0, 'pendiente_cobrar' => 0],
            'clientes' => ['total' => 0, 'activos' => 0, 'deudores' => 0],
            'inventario' => ['total_productos' => 0, 'productos_bajos' => 0, 'valor_inventario' => 0],
            'servicios' => ['citas_completadas' => 0, 'mantenimientos' => 0, 'ingresos_servicios' => 0],
            'finanzas' => ['ingresos_totales' => 0, 'gastos_totales' => 0, 'ganancia_neta' => 0],
            'personal' => ['total_empleados' => 0, 'tecnicos_activos' => 0, 'ventas_por_tecnico' => []],
            'auditoria' => ['actividades_hoy' => 0, 'usuarios_activos' => 0],
        ];

        try {
            // Ventas
            $ventasCol = Venta::with('items.ventable')
                ->whereBetween('created_at', [$inicio, $fin])
                ->get();

            $totalVentas = (float) $ventasCol->sum('total');
            $utilidad = (float) $ventasCol->sum(function ($v) {
                try {
                    return $v->total - $v->calcularCostoTotal();
                } catch (\Throwable $e) {
                    return 0;
                }
            });
            $productosVendidos = (int) $ventasCol->pluck('items')->flatten()->sum('cantidad');

            $defaults['ventas'] = [
                'total' => $totalVentas,
                'utilidad' => $utilidad,
                'productos_vendidos' => $productosVendidos,
            ];
        } catch (\Throwable $e) {
            // keep defaults
        }

        try {
            // Rentas / cobranzas
            $totalCobrado = (float) Cobranza::whereBetween('fecha_pago', [$inicio, $fin])->sum('monto_pagado');
            $pendiente = 0;
            try {
                $pendiente = (float) Renta::whereBetween('created_at', [$inicio, $fin])->sum('saldo');
            } catch (\Throwable $_) {
                $pendiente = 0;
            }
            $rentasActivas = 0;
            try {
                $rentasActivas = Renta::where('estado', 'activa')->count();
            } catch (\Throwable $_) {
                $rentasActivas = Renta::count() ?? 0;
            }

            $defaults['rentas'] = [
                'rentas_activas' => $rentasActivas,
                'total_cobrado' => $totalCobrado,
                'pendiente_cobrar' => $pendiente,
            ];
        } catch (\Throwable $e) {
            // keep defaults
        }

        try {
            // Clientes
            $totalClientes = Cliente::count();
            $activos = 0;
            $deudores = 0;
            try {
                $activos = Cliente::where('activo', 1)->count();
            } catch (\Throwable $_) {
                $activos = 0;
            }
            try {
                $deudores = Cliente::where('saldo', '>', 0)->count();
            } catch (\Throwable $_) {
                $deudores = 0;
            }

            $defaults['clientes'] = [
                'total' => $totalClientes,
                'activos' => $activos,
                'deudores' => $deudores,
            ];
        } catch (\Throwable $e) {
            // keep defaults
        }

        try {
            // Inventario
            $productos = Producto::all();
            $totalProductos = $productos->count();
            $productosBajos = 0;
            try {
                // attempt to detect common stock/reorder fields safely
                $productosBajos = $productos->filter(function ($p) {
                    if (isset($p->stock) && isset($p->reorder_point)) {
                        return $p->stock <= $p->reorder_point;
                    }
                    return false;
                })->count();
            } catch (\Throwable $_) {
                $productosBajos = 0;
            }
            $valorInventario = (float) $productos->sum(function ($p) {
                $stock = $p->stock ?? 0;
                $costo = $p->costo ?? ($p->precio_compra ?? 0);
                return $stock * $costo;
            });

            $defaults['inventario'] = [
                'total_productos' => $totalProductos,
                'productos_bajos' => $productosBajos,
                'valor_inventario' => $valorInventario,
            ];
        } catch (\Throwable $e) {
            // keep defaults
        }

        try {
            // Servicios (citas/mantenimientos/ingresos)
            $citasCompletadas = 0;
            try {
                $citasCompletadas = Cita::whereBetween('fecha', [$inicio, $fin])->where('estado', 'completada')->count();
            } catch (\Throwable $_) {
                $citasCompletadas = Cita::whereBetween('fecha', [$inicio, $fin])->count();
            }
            $mantenimientos = 0;
            try {
                $mantenimientos = Mantenimiento::whereBetween('created_at', [$inicio, $fin])->count();
            } catch (\Throwable $_) {
                $mantenimientos = 0;
            }
            $ingresosServicios = 0;
            try {
                $ingresosServicios = (float) Servicio::whereBetween('created_at', [$inicio, $fin])->sum('precio');
            } catch (\Throwable $_) {
                $ingresosServicios = 0;
            }

            $defaults['servicios'] = [
                'citas_completadas' => $citasCompletadas,
                'mantenimientos' => $mantenimientos,
                'ingresos_servicios' => $ingresosServicios,
            ];
        } catch (\Throwable $e) {
            // keep defaults
        }

        try {
            // Finanzas
            $ingresos = $defaults['ventas']['total'] + $defaults['rentas']['total_cobrado'];
            $gastos = 0;
            try {
                $gastos = (float) Compra::whereBetween('created_at', [$inicio, $fin])->sum('total');
            } catch (\Throwable $_) {
                $gastos = 0;
            }
            $defaults['finanzas'] = [
                'ingresos_totales' => $ingresos,
                'gastos_totales' => $gastos,
                'ganancia_neta' => $ingresos - $gastos,
            ];
        } catch (\Throwable $e) {
            // keep defaults
        }

        try {
            // Personal
            $totalEmpleados = 0;
            try {
                $totalEmpleados = User::where('is_employee', 1)->count();
            } catch (\Throwable $_) {
                $totalEmpleados = User::count();
            }
            $tecnicosActivos = 0;
            try {
                $tecnicosActivos = User::where('role', 'tecnico')->count();
            } catch (\Throwable $_) {
                $tecnicosActivos = 0;
            }
            $ventasPorTecnico = [];
            try {
                $ventasPorTecnico = Venta::select('responsable_id', DB::raw('SUM(total) as total'))
                    ->whereBetween('created_at', [$inicio, $fin])
                    ->groupBy('responsable_id')
                    ->get()
                    ->mapWithKeys(function ($r) {
                        return [$r->responsable_id => (float) $r->total];
                    })->toArray();
            } catch (\Throwable $_) {
                $ventasPorTecnico = [];
            }

            $defaults['personal'] = [
                'total_empleados' => $totalEmpleados,
                'tecnicos_activos' => $tecnicosActivos,
                'ventas_por_tecnico' => $ventasPorTecnico,
            ];
        } catch (\Throwable $e) {
            // keep defaults
        }

        try {
            // Auditoría
            $actividadesHoy = 0;
            try {
                // Use the DB table directly to avoid depending on the Spatie model class (which may be missing)
                $actividadesHoy = DB::table('activity_log')
                    ->whereBetween('created_at', [$inicio, $fin])
                    ->count();
            } catch (\Throwable $_) {
                $actividadesHoy = 0;
            }
            $usuariosActivos = 0;
            try {
                $usuariosActivos = User::whereBetween('last_login_at', [$inicio, $fin])->count();
            } catch (\Throwable $_) {
                $usuariosActivos = 0;
            }

            $defaults['auditoria'] = [
                'actividades_hoy' => $actividadesHoy,
                'usuarios_activos' => $usuariosActivos,
            ];
        } catch (\Throwable $e) {
            // keep defaults
        }

        return $defaults;
    }

    /**
     * Mostrar corte de pagos por período
     */
    private function mostrarCorteDiario(Request $request)
    {
        $periodo = $request->get('periodo', 'diario');
        $fecha = $request->get('fecha', now()->format('Y-m-d'));

        // Calcular fechas de inicio y fin según el período
        $fecha_inicio = $fecha_fin = $fecha;
        $periodoLabel = 'Diario';

        if ($periodo === 'diario') {
            $fecha_inicio = $fecha_fin = $fecha;
            $periodoLabel = 'Diario';
        } elseif ($periodo === 'semanal') {
            $carbon = Carbon::parse($fecha);
            $fecha_inicio = $carbon->startOfWeek()->format('Y-m-d');
            $fecha_fin = $carbon->endOfWeek()->format('Y-m-d');
            $periodoLabel = 'Semanal';
        } elseif ($periodo === 'mensual') {
            $carbon = Carbon::parse($fecha);
            $fecha_inicio = $carbon->startOfMonth()->format('Y-m-d');
            $fecha_fin = $carbon->endOfMonth()->format('Y-m-d');
            $periodoLabel = 'Mensual';
        } elseif ($periodo === 'anual') {
            $carbon = Carbon::parse($fecha);
            $fecha_inicio = $carbon->startOfYear()->format('Y-m-d');
            $fecha_fin = $carbon->endOfYear()->format('Y-m-d');
            $periodoLabel = 'Anual';
        } elseif ($periodo === 'personalizado') {
            $fecha_inicio = $request->get('fecha_inicio', $fecha);
            $fecha_fin = $request->get('fecha_fin', $fecha);
            $periodoLabel = 'Personalizado';
        }

        // Obtener ventas pagadas en el período especificado
        $ventasPagadas = Venta::with(['cliente', 'items.ventable'])
            ->where('pagado', true)
            ->where('fecha_pago', '>=', $fecha_inicio . ' 00:00:00')
            ->where('fecha_pago', '<=', $fecha_fin . ' 23:59:59')
            ->orderBy('fecha_pago', 'desc')
            ->get();

        // Obtener cobranzas pagadas en el período especificado
        $cobranzasPagadas = Cobranza::with(['renta.cliente', 'responsableCobro'])
            ->whereIn('estado', ['pagado', 'parcial'])
            ->where('fecha_pago', '>=', $fecha_inicio . ' 00:00:00')
            ->where('fecha_pago', '<=', $fecha_fin . ' 23:59:59')
            ->orderBy('fecha_pago', 'desc')
            ->get();

        // Calcular totales por método de pago
        $totalesPorMetodo = [
            'efectivo' => 0,
            'transferencia' => 0,
            'cheque' => 0,
            'tarjeta' => 0,
            'otros' => 0,
        ];

        $totalGeneral = 0;

        // Procesar ventas
        foreach ($ventasPagadas as $venta) {
            $metodo = $venta->metodo_pago ?? 'otros';
            if (isset($totalesPorMetodo[$metodo])) {
                $totalesPorMetodo[$metodo] += $venta->total;
            } else {
                $totalesPorMetodo['otros'] += $venta->total;
            }
            $totalGeneral += $venta->total;
        }

        // Procesar cobranzas
        foreach ($cobranzasPagadas as $cobranza) {
            $metodo = $cobranza->metodo_pago ?? 'otros';
            if (isset($totalesPorMetodo[$metodo])) {
                $totalesPorMetodo[$metodo] += $cobranza->monto_pagado;
            } else {
                $totalesPorMetodo['otros'] += $cobranza->monto_pagado;
            }
            $totalGeneral += $cobranza->monto_pagado;
        }

        // Formatear datos para la vista
        $ventasFormateadas = $ventasPagadas->map(function ($venta) {
            return [
                'id' => $venta->id,
                'tipo' => 'venta',
                'numero' => $venta->numero_venta,
                'cliente' => $venta->cliente->nombre_razon_social ?? 'Sin cliente',
                'concepto' => 'Venta',
                'total' => $venta->total,
                'metodo_pago' => $venta->metodo_pago,
                'fecha_pago' => $venta->fecha_pago ? $venta->fecha_pago->toIso8601String() : null,
                'notas_pago' => $venta->notas_pago,
                'pagado_por' => $venta->pagadoPor?->name ?? 'Sistema',
            ];
        });

        $cobranzasFormateadas = $cobranzasPagadas->map(function ($cobranza) {
            return [
                'id' => $cobranza->id,
                'tipo' => 'cobranza',
                'numero' => $cobranza->renta->numero_contrato ?? 'N/A',
                'cliente' => $cobranza->renta->cliente->nombre_razon_social ?? 'Sin cliente',
                'concepto' => $cobranza->concepto ?? 'Cobranza',
                'total' => $cobranza->monto_pagado,
                'metodo_pago' => $cobranza->metodo_pago,
                'fecha_pago' => $cobranza->fecha_pago ? $cobranza->fecha_pago->toIso8601String() : null,
                'notas_pago' => $cobranza->notas_pago,
                'pagado_por' => $cobranza->responsableCobro?->name ?? 'Sistema',
            ];
        });

        // Combinar y ordenar por fecha de pago
        $pagosFormateados = collect([...$ventasFormateadas, ...$cobranzasFormateadas])
            ->sortByDesc('fecha_pago')
            ->values();

        return Inertia::render('Reportes/CorteDiario', [
            'pagos' => $pagosFormateados,
            'totalesPorMetodo' => $totalesPorMetodo,
            'totalGeneral' => $totalGeneral,
            'periodo' => $periodo,
            'periodoLabel' => $periodoLabel,
            'fecha' => $fecha,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'fechaFormateada' => $periodo === 'personalizado'
                ? "Del " . Carbon::parse($fecha_inicio)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') . " al " . Carbon::parse($fecha_fin)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
                : Carbon::parse($fecha)->locale('es')->isoFormat($periodo === 'diario' ? 'dddd, D [de] MMMM [de] YYYY' : ($periodo === 'semanal' ? '[Semana del] D [de] MMMM [de] YYYY' : ($periodo === 'mensual' ? 'MMMM [de] YYYY' : 'YYYY'))),
        ]);
    }
}
