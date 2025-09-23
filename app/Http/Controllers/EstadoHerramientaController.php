<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Herramienta;
use App\Models\EstadoHerramienta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EstadoHerramientaController extends Controller
{
    /**
     * Mostrar lista de estados de herramientas
     */
    public function index(Request $request)
    {
        try {
            $query = EstadoHerramienta::with(['herramienta', 'inspeccionadoPor'])
                ->orderBy('fecha_inspeccion', 'desc');

            // Filtros
            if ($search = $request->input('search')) {
                $query->whereHas('herramienta', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('numero_serie', 'like', "%{$search}%");
                });
            }

            if ($request->input('condicion')) {
                $query->where('condicion_general', $request->input('condicion'));
            }

            if ($request->input('prioridad')) {
                $query->where('prioridad_mantenimiento', $request->input('prioridad'));
            }

            if ($request->input('necesita_mantenimiento') !== null) {
                $query->where('necesita_mantenimiento', $request->input('necesita_mantenimiento'));
            }

            if ($request->input('necesita_reemplazo') !== null) {
                $query->where('necesita_reemplazo', $request->input('necesita_reemplazo'));
            }

            $estados = $query->paginate(15)->appends($request->query());

            // Estadísticas
            $stats = [
                'total_inspecciones' => EstadoHerramienta::count(),
                'necesitan_mantenimiento' => EstadoHerramienta::where('necesita_mantenimiento', true)->count(),
                'necesitan_reemplazo' => EstadoHerramienta::where('necesita_reemplazo', true)->count(),
                'por_condicion' => [
                    'excelente' => EstadoHerramienta::where('condicion_general', 'excelente')->count(),
                    'buena' => EstadoHerramienta::where('condicion_general', 'buena')->count(),
                    'regular' => EstadoHerramienta::where('condicion_general', 'regular')->count(),
                    'mala' => EstadoHerramienta::where('condicion_general', 'mala')->count(),
                    'critica' => EstadoHerramienta::where('condicion_general', 'critica')->count(),
                ],
                'por_prioridad' => [
                    'baja' => EstadoHerramienta::where('prioridad_mantenimiento', 'baja')->count(),
                    'media' => EstadoHerramienta::where('prioridad_mantenimiento', 'media')->count(),
                    'alta' => EstadoHerramienta::where('prioridad_mantenimiento', 'alta')->count(),
                    'urgente' => EstadoHerramienta::where('prioridad_mantenimiento', 'urgente')->count(),
                ],
            ];

            return Inertia::render('Herramientas/Estados/Index', [
                'estados' => $estados,
                'stats' => $stats,
                'filters' => $request->only(['search', 'condicion', 'prioridad', 'necesita_mantenimiento', 'necesita_reemplazo'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error en EstadoHerramientaController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los estados de herramientas.');
        }
    }

    /**
     * Mostrar formulario para crear nuevo estado
     */
    public function create(Request $request)
    {
        try {
            $herramientas = Herramienta::select('id', 'nombre', 'numero_serie', 'estado', 'categoria')
                ->orderBy('nombre')
                ->get();

            return Inertia::render('Herramientas/Estados/Create', [
                'herramientas' => $herramientas,
                'herramienta_id' => $request->input('herramienta_id')
            ]);

        } catch (\Exception $e) {
            Log::error('Error en EstadoHerramientaController@create: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Crear nuevo estado de herramienta
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'herramienta_id' => 'required|exists:herramientas,id',
                'condicion_general' => 'required|in:excelente,buena,regular,mala,critica',
                'porcentaje_desgaste' => 'required|numeric|min:0|max:100',
                'necesita_mantenimiento' => 'boolean',
                'necesita_reemplazo' => 'boolean',
                'observaciones' => 'nullable|string|max:2000',
                'foto_estado' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'prioridad_mantenimiento' => 'required|in:baja,media,alta,urgente',
            ]);

            DB::beginTransaction();

            $herramienta = Herramienta::findOrFail($validated['herramienta_id']);

            // Procesar foto si se subió
            if ($request->hasFile('foto_estado')) {
                $validated['foto_estado'] = $this->storeFoto($request->file('foto_estado'));
            }

            // Calcular prioridad automáticamente si no se especifica
            if (empty($validated['prioridad_mantenimiento'])) {
                $validated['prioridad_mantenimiento'] = EstadoHerramienta::calcularPrioridad(
                    $validated['porcentaje_desgaste'],
                    $validated['condicion_general']
                );
            }

            // Crear estado
            $estado = EstadoHerramienta::create([
                ...$validated,
                'fecha_inspeccion' => now(),
                'inspeccionado_por' => auth()->id(),
            ]);

            // Actualizar campos de la herramienta si es necesario
            if ($validated['necesita_mantenimiento']) {
                $herramienta->update([
                    'requiere_mantenimiento' => true,
                    'dias_para_mantenimiento' => $herramienta->dias_para_mantenimiento ?: 30
                ]);
            }

            if ($validated['necesita_reemplazo']) {
                $herramienta->update(['estado' => Herramienta::ESTADO_MANTENIMIENTO]);
            }

            DB::commit();

            return redirect()->route('herramientas.estados.index')
                ->with('success', 'Estado de herramienta registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en EstadoHerramientaController@store: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar el estado: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalles de un estado
     */
    public function show(EstadoHerramienta $estado)
    {
        try {
            $estado->load(['herramienta', 'inspeccionadoPor']);

            return Inertia::render('Herramientas/Estados/Show', [
                'estado' => $estado
            ]);

        } catch (\Exception $e) {
            Log::error('Error en EstadoHerramientaController@show: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los detalles.');
        }
    }

    /**
     * Mostrar formulario para editar estado
     */
    public function edit(EstadoHerramienta $estado)
    {
        try {
            $estado->load(['herramienta']);

            return Inertia::render('Herramientas/Estados/Edit', [
                'estado' => $estado,
                'herramientas' => Herramienta::select('id', 'nombre', 'numero_serie')->orderBy('nombre')->get()
            ]);

        } catch (\Exception $e) {
            Log::error('Error en EstadoHerramientaController@edit: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Actualizar estado de herramienta
     */
    public function update(Request $request, EstadoHerramienta $estado)
    {
        try {
            $validated = $request->validate([
                'condicion_general' => 'required|in:excelente,buena,regular,mala,critica',
                'porcentaje_desgaste' => 'required|numeric|min:0|max:100',
                'necesita_mantenimiento' => 'boolean',
                'necesita_reemplazo' => 'boolean',
                'observaciones' => 'nullable|string|max:2000',
                'foto_estado' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'prioridad_mantenimiento' => 'required|in:baja,media,alta,urgente',
            ]);

            DB::beginTransaction();

            $herramienta = $estado->herramienta;

            // Procesar foto si se subió
            if ($request->hasFile('foto_estado')) {
                // Eliminar foto anterior si existe
                if ($estado->foto_estado) {
                    Storage::disk('public')->delete($estado->foto_estado);
                }
                $validated['foto_estado'] = $this->storeFoto($request->file('foto_estado'));
            }

            // Actualizar estado
            $estado->update($validated);

            // Actualizar campos de la herramienta
            $herramienta->update([
                'requiere_mantenimiento' => $validated['necesita_mantenimiento'] ?: false,
                'estado' => $validated['necesita_reemplazo'] ? Herramienta::ESTADO_MANTENIMIENTO : $herramienta->estado
            ]);

            DB::commit();

            return redirect()->route('herramientas.estados.index')
                ->with('success', 'Estado de herramienta actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en EstadoHerramientaController@update: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar estado de herramienta
     */
    public function destroy(EstadoHerramienta $estado)
    {
        try {
            DB::beginTransaction();

            $herramienta = $estado->herramienta;

            // Eliminar foto si existe
            if ($estado->foto_estado) {
                Storage::disk('public')->delete($estado->foto_estado);
            }

            $estado->delete();

            // Verificar si hay otros estados que requieran mantenimiento
            $necesitaMantenimiento = $herramienta->estados()
                ->where('necesita_mantenimiento', true)
                ->exists();

            $herramienta->update(['requiere_mantenimiento' => $necesitaMantenimiento]);

            DB::commit();

            return redirect()->route('herramientas.estados.index')
                ->with('success', 'Estado de herramienta eliminado correctamente.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en EstadoHerramientaController@destroy: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al eliminar el estado: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de desgaste de una herramienta
     */
    public function getEstadisticasDesgaste(Herramienta $herramienta)
    {
        try {
            $estados = $herramienta->estados()
                ->orderBy('fecha_inspeccion', 'desc')
                ->take(10)
                ->get();

            $estadisticas = [
                'promedio_desgaste' => $estados->avg('porcentaje_desgaste'),
                'desgaste_actual' => $estados->first()?->porcentaje_desgaste ?? 0,
                'tendencia' => $this->calcularTendenciaDesgaste($estados),
                'proximo_mantenimiento' => $herramienta->necesitaMantenimiento() ? 'Necesario' : 'No requerido',
                'dias_para_mantenimiento' => $herramienta->dias_para_proximo_mantenimiento,
                'vida_util_restante' => $herramienta->porcentaje_vida_util ? (100 - $herramienta->porcentaje_vida_util) : null,
            ];

            return response()->json($estadisticas);

        } catch (\Exception $e) {
            Log::error('Error en EstadoHerramientaController@getEstadisticasDesgaste: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de desgaste.'
            ], 500);
        }
    }

    /**
     * Generar reporte de herramientas que necesitan atención
     */
    public function reporteAtencion()
    {
        try {
            $herramientas = Herramienta::with(['ultimoEstado', 'tecnico'])
                ->where(function ($q) {
                    $q->where('requiere_mantenimiento', true)
                        ->orWhereHas('estados', function ($sq) {
                            $sq->where('necesita_reemplazo', true);
                        })
                        ->orWhereHas('estados', function ($sq) {
                            $sq->where('prioridad_mantenimiento', 'urgente');
                        });
                })
                ->get();

            return Inertia::render('Herramientas/Estados/ReporteAtencion', [
                'herramientas' => $herramientas
            ]);

        } catch (\Exception $e) {
            Log::error('Error en EstadoHerramientaController@reporteAtencion: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el reporte.');
        }
    }

    /**
     * Almacenar foto de estado
     */
    private function storeFoto($file)
    {
        return $file->store('estados-herramientas', 'public');
    }

    /**
     * Calcular tendencia de desgaste
     */
    private function calcularTendenciaDesgaste($estados)
    {
        if ($estados->count() < 2) return 'insuficiente_datos';

        $primeros = $estados->take(3)->avg('porcentaje_desgaste');
        $ultimos = $estados->skip($estados->count() - 3)->avg('porcentaje_desgaste');

        if ($ultimos > $primeros + 5) return 'aumentando';
        if ($primeros > $ultimos + 5) return 'disminuyendo';
        return 'estable';
    }
}
