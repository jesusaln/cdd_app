<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Tecnico;
use App\Models\Herramienta;
use App\Models\AsignacionMasiva;
use App\Models\HistorialHerramienta;
use App\Models\ResponsabilidadHerramienta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TecnicoHerramientasController extends Controller
{
    /**
     * Dashboard principal de herramientas por técnico
     */
    public function index(Request $request)
    {
        try {
            $query = Tecnico::with(['herramientasAsignadas.categoriaHerramienta'])
                           ->where('activo', true);

            // Filtros
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('apellido', 'like', "%{$search}%");
                });
            }

            $tecnicos = $query->get();

            // Enriquecer datos de técnicos con información de herramientas
            $tecnicosConHerramientas = $tecnicos->map(function ($tecnico) {
                $herramientasAsignadas = $tecnico->herramientasAsignadas;
                $responsabilidad = ResponsabilidadHerramienta::where('tecnico_id', $tecnico->id)->first();

                return [
                    'id' => $tecnico->id,
                    'nombre' => $tecnico->nombre,
                    'apellido' => $tecnico->apellido,
                    'email' => $tecnico->email,
                    'telefono' => $tecnico->telefono,
                    'total_herramientas' => $herramientasAsignadas->count(),
                    'valor_total' => $herramientasAsignadas->sum('costo_reemplazo') ?? 0,
                    'herramientas_vencidas' => $herramientasAsignadas->filter(function ($h) {
                        return $h->necesitaMantenimiento() || $h->vidaUtilProximaAVencer();
                    })->count(),
                    'asignaciones_activas' => AsignacionMasiva::where('tecnico_id', $tecnico->id)
                                                            ->where('estado', AsignacionMasiva::ESTADO_ACTIVA)
                                                            ->count(),
                    'dias_promedio_uso' => $responsabilidad->dias_promedio_uso ?? 0,
                    'ultima_actualizacion' => $responsabilidad->ultima_actualizacion ?? null,
                    'alertas' => $responsabilidad->alertas ?? [],
                    'herramientas_preview' => $herramientasAsignadas->take(3)->map(function ($h) {
                        return [
                            'id' => $h->id,
                            'nombre' => $h->nombre,
                            'numero_serie' => $h->numero_serie,
                            'categoria' => $h->categoriaHerramienta->nombre ?? 'Sin categoría',
                            'estado' => $h->estado,
                            'necesita_atencion' => $h->necesitaMantenimiento() || $h->vidaUtilProximaAVencer()
                        ];
                    })
                ];
            });

            // Estadísticas generales
            $stats = [
                'total_tecnicos' => $tecnicos->count(),
                'tecnicos_con_herramientas' => $tecnicosConHerramientas->where('total_herramientas', '>', 0)->count(),
                'total_herramientas_asignadas' => $tecnicosConHerramientas->sum('total_herramientas'),
                'valor_total_asignado' => $tecnicosConHerramientas->sum('valor_total'),
                'tecnicos_con_alertas' => $tecnicosConHerramientas->filter(function ($t) {
                    return count($t['alertas']) > 0;
                })->count(),
                'herramientas_vencidas_total' => $tecnicosConHerramientas->sum('herramientas_vencidas')
            ];

            return Inertia::render('Herramientas/TecnicosHerramientas/Index', [
                'tecnicos' => $tecnicosConHerramientas,
                'stats' => $stats,
                'filters' => $request->only(['search'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error en TecnicoHerramientasController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el dashboard de técnicos.');
        }
    }

    /**
     * Mostrar herramientas específicas de un técnico
     */
    public function show(Tecnico $tecnico, Request $request)
    {
        try {
            // Cargar herramientas asignadas al técnico
            $herramientasQuery = $tecnico->herramientasAsignadas()
                                        ->with(['categoriaHerramienta', 'estados' => function ($query) {
                                            $query->latest('fecha_inspeccion');
                                        }]);

            // Filtros
            if ($search = $request->input('search')) {
                $herramientasQuery->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('numero_serie', 'like', "%{$search}%");
                });
            }

            if ($categoria = $request->input('categoria')) {
                $herramientasQuery->where('categoria_id', $categoria);
            }

            if ($estado = $request->input('estado')) {
                $herramientasQuery->where('estado', $estado);
            }

            $herramientas = $herramientasQuery->paginate(15)->appends($request->query());

            // Asignaciones masivas activas del técnico
            $asignacionesMasivas = AsignacionMasiva::where('tecnico_id', $tecnico->id)
                                                 ->with(['detalles.herramienta'])
                                                 ->whereIn('estado', [
                                                     AsignacionMasiva::ESTADO_ACTIVA,
                                                     AsignacionMasiva::ESTADO_PENDIENTE
                                                 ])
                                                 ->orderBy('fecha_asignacion', 'desc')
                                                 ->get();

            // Historial reciente del técnico
            $historialReciente = HistorialHerramienta::where('tecnico_id', $tecnico->id)
                                                   ->with(['herramienta', 'asignadoPor', 'recibidoPor'])
                                                   ->orderBy('fecha_asignacion', 'desc')
                                                   ->limit(10)
                                                   ->get();

            // Responsabilidad actual
            $responsabilidad = ResponsabilidadHerramienta::where('tecnico_id', $tecnico->id)->first();

            // Estadísticas del técnico
            $estadisticasTecnico = [
                'total_herramientas' => $herramientas->total(),
                'valor_total' => $tecnico->herramientasAsignadas->sum('costo_reemplazo') ?? 0,
                'herramientas_vencidas' => $tecnico->herramientasAsignadas->filter(function ($h) {
                    return $h->necesitaMantenimiento() || $h->vidaUtilProximaAVencer();
                })->count(),
                'asignaciones_masivas_activas' => $asignacionesMasivas->count(),
                'total_asignaciones_historicas' => HistorialHerramienta::where('tecnico_id', $tecnico->id)->count(),
                'promedio_dias_uso' => $responsabilidad->dias_promedio_uso ?? 0,
                'ultima_asignacion' => $historialReciente->first()?->fecha_asignacion,
                'ultima_devolucion' => HistorialHerramienta::where('tecnico_id', $tecnico->id)
                                                         ->whereNotNull('fecha_devolucion')
                                                         ->latest('fecha_devolucion')
                                                         ->first()?->fecha_devolucion
            ];

            return Inertia::render('Herramientas/TecnicosHerramientas/Show', [
                'tecnico' => $tecnico,
                'herramientas' => $herramientas,
                'asignaciones_masivas' => $asignacionesMasivas,
                'historial_reciente' => $historialReciente,
                'responsabilidad' => $responsabilidad,
                'estadisticas' => $estadisticasTecnico,
                'filters' => $request->only(['search', 'categoria', 'estado'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error en TecnicoHerramientasController@show: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las herramientas del técnico.');
        }
    }

    /**
     * Actualizar responsabilidades de un técnico
     */
    public function actualizarResponsabilidad(Tecnico $tecnico)
    {
        try {
            ResponsabilidadHerramienta::actualizarParaTecnico($tecnico->id);

            return redirect()->back()->with('success', 'Responsabilidades actualizadas correctamente.');

        } catch (\Exception $e) {
            Log::error('Error en TecnicoHerramientasController@actualizarResponsabilidad: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar las responsabilidades.');
        }
    }

    /**
     * Generar reporte de herramientas por técnico
     */
    public function reporte(Tecnico $tecnico)
    {
        try {
            $herramientas = $tecnico->herramientasAsignadas()
                                  ->with(['categoriaHerramienta', 'estados'])
                                  ->get();

            $asignacionesMasivas = AsignacionMasiva::where('tecnico_id', $tecnico->id)
                                                 ->with(['detalles.herramienta'])
                                                 ->get();

            $historial = HistorialHerramienta::where('tecnico_id', $tecnico->id)
                                           ->with(['herramienta', 'asignadoPor', 'recibidoPor'])
                                           ->orderBy('fecha_asignacion', 'desc')
                                           ->get();

            $responsabilidad = ResponsabilidadHerramienta::where('tecnico_id', $tecnico->id)->first();

            return Inertia::render('Herramientas/TecnicosHerramientas/Reporte', [
                'tecnico' => $tecnico,
                'herramientas' => $herramientas,
                'asignaciones_masivas' => $asignacionesMasivas,
                'historial' => $historial,
                'responsabilidad' => $responsabilidad
            ]);

        } catch (\Exception $e) {
            Log::error('Error en TecnicoHerramientasController@reporte: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el reporte.');
        }
    }

    /**
     * Dashboard de alertas y herramientas que requieren atención
     */
    public function alertas(Request $request)
    {
        try {
            // Técnicos con herramientas vencidas
            $tecnicosConVencidas = ResponsabilidadHerramienta::with('tecnico')
                                                           ->where('tiene_herramientas_vencidas', true)
                                                           ->get();

            // Herramientas que necesitan mantenimiento
            $herramientasMantenimiento = Herramienta::with(['tecnico', 'categoriaHerramienta'])
                                                   ->where('estado', Herramienta::ESTADO_ASIGNADA)
                                                   ->get()
                                                   ->filter(function ($h) {
                                                       return $h->necesitaMantenimiento();
                                                   });

            // Herramientas próximas a vencer vida útil
            $herramientasVidaUtil = Herramienta::with(['tecnico', 'categoriaHerramienta'])
                                              ->where('estado', Herramienta::ESTADO_ASIGNADA)
                                              ->get()
                                              ->filter(function ($h) {
                                                  return $h->vidaUtilProximaAVencer();
                                              });

            // Asignaciones masivas vencidas
            $asignacionesVencidas = AsignacionMasiva::with(['tecnico'])
                                                  ->where('estado', AsignacionMasiva::ESTADO_ACTIVA)
                                                  ->whereNotNull('fecha_devolucion_programada')
                                                  ->where('fecha_devolucion_programada', '<', now())
                                                  ->get();

            // Técnicos con alto valor en herramientas
            $tecnicosAltoValor = ResponsabilidadHerramienta::with('tecnico')
                                                         ->where('valor_total_herramientas', '>', 50000)
                                                         ->orderBy('valor_total_herramientas', 'desc')
                                                         ->get();

            return Inertia::render('Herramientas/TecnicosHerramientas/Alertas', [
                'tecnicos_con_vencidas' => $tecnicosConVencidas,
                'herramientas_mantenimiento' => $herramientasMantenimiento,
                'herramientas_vida_util' => $herramientasVidaUtil,
                'asignaciones_vencidas' => $asignacionesVencidas,
                'tecnicos_alto_valor' => $tecnicosAltoValor
            ]);

        } catch (\Exception $e) {
            Log::error('Error en TecnicoHerramientasController@alertas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las alertas.');
        }
    }
}
