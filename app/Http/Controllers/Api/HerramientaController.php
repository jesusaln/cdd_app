<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Herramienta;
use App\Models\AsignacionHerramienta;
use App\Models\EstadoHerramienta;
use App\Models\HistorialHerramienta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HerramientaController extends Controller
{
    /**
     * Listar todas las herramientas
     */
    public function index(Request $request)
    {
        try {
            $query = Herramienta::query();

            // Filtros
            if ($request->input('estado')) {
                $query->where('estado', $request->input('estado'));
            }

            if ($request->input('categoria')) {
                $query->where('categoria', $request->input('categoria'));
            }

            if ($request->input('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('numero_serie', 'like', "%{$search}%");
                });
            }

            // Cargar relaciones con manejo de errores
            $herramientas = $query->paginate(15);

            // Cargar relaciones adicionales de forma segura
            foreach ($herramientas as $herramienta) {
                try {
                    $herramienta->load(['tecnico', 'ultimoEstado']);
                } catch (\Exception $e) {
                    Log::warning('Error cargando relaciones para herramienta ' . $herramienta->id . ': ' . $e->getMessage());
                }
            }

            return response()->json($herramientas);

        } catch (\Exception $e) {
            Log::error('Error en HerramientaController@index: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener herramientas'], 500);
        }
    }

    /**
     * Mostrar una herramienta específica
     */
    public function show(Herramienta $herramienta)
    {
        try {
            // Cargar relaciones de forma segura
            try {
                $herramienta->load(['tecnico', 'ultimoEstado']);
            } catch (\Exception $e) {
                Log::warning('Error cargando relaciones para herramienta ' . $herramienta->id . ': ' . $e->getMessage());
            }

            return response()->json($herramienta);

        } catch (\Exception $e) {
            Log::error('Error en HerramientaController@show: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener herramienta'], 500);
        }
    }

    /**
     * Crear nueva herramienta
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'numero_serie' => 'required|string|max:255|unique:herramientas',
                'categoria' => 'nullable|string|max:255',
                'descripcion' => 'nullable|string|max:1000',
                'vida_util_meses' => 'nullable|integer|min:1',
                'costo_reemplazo' => 'nullable|numeric|min:0',
                'dias_para_mantenimiento' => 'nullable|integer|min:1',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            DB::beginTransaction();

            if ($request->hasFile('foto')) {
                $validated['foto'] = $request->file('foto')->store('herramientas', 'public');
            }

            $herramienta = Herramienta::create($validated);

            DB::commit();

            return response()->json($herramienta, 201);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en HerramientaController@store: ' . $e->getMessage());
            return response()->json(['error' => 'Error al crear herramienta'], 500);
        }
    }

    /**
     * Actualizar herramienta
     */
    public function update(Request $request, Herramienta $herramienta)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|max:255',
                'numero_serie' => 'sometimes|required|string|max:255|unique:herramientas,numero_serie,' . $herramienta->id,
                'categoria' => 'nullable|string|max:255',
                'descripcion' => 'nullable|string|max:1000',
                'vida_util_meses' => 'nullable|integer|min:1',
                'costo_reemplazo' => 'nullable|numeric|min:0',
                'dias_para_mantenimiento' => 'nullable|integer|min:1',
                'estado' => 'sometimes|required|in:disponible,asignada,mantenimiento,baja,perdida',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            DB::beginTransaction();

            if ($request->hasFile('foto')) {
                // Eliminar foto anterior si existe
                if ($herramienta->foto) {
                    Storage::disk('public')->delete($herramienta->foto);
                }
                $validated['foto'] = $request->file('foto')->store('herramientas', 'public');
            }

            $herramienta->update($validated);

            DB::commit();

            return response()->json($herramienta);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en HerramientaController@update: ' . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar herramienta'], 500);
        }
    }

    /**
     * Eliminar herramienta
     */
    public function destroy(Herramienta $herramienta)
    {
        try {
            DB::beginTransaction();

            // Eliminar foto si existe
            if ($herramienta->foto) {
                Storage::disk('public')->delete($herramienta->foto);
            }

            $herramienta->delete();

            DB::commit();

            return response()->json(['message' => 'Herramienta eliminada correctamente']);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en HerramientaController@destroy: ' . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar herramienta'], 500);
        }
    }

    /**
     * Obtener asignaciones de una herramienta
     */
    public function asignaciones(Herramienta $herramienta)
    {
        try {
            $asignaciones = $herramienta->asignaciones()
                ->with(['tecnico', 'usuarioEntrega', 'usuarioRecepcion'])
                ->orderBy('fecha_asignacion', 'desc')
                ->get();

            return response()->json($asignaciones);

        } catch (\Exception $e) {
            Log::error('Error en HerramientaController@asignaciones: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener asignaciones'], 500);
        }
    }

    /**
     * Obtener historial de una herramienta
     */
    public function historial(Herramienta $herramienta)
    {
        try {
            $historial = $herramienta->historial()
                ->with(['tecnico', 'asignadoPor', 'recibidoPor'])
                ->orderBy('fecha_asignacion', 'desc')
                ->get();

            return response()->json($historial);

        } catch (\Exception $e) {
            Log::error('Error en HerramientaController@historial: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener historial'], 500);
        }
    }

    /**
     * Obtener estados de una herramienta
     */
    public function estados(Herramienta $herramienta)
    {
        try {
            $estados = $herramienta->estados()
                ->with(['inspeccionadoPor'])
                ->orderBy('fecha_inspeccion', 'desc')
                ->get();

            return response()->json($estados);

        } catch (\Exception $e) {
            Log::error('Error en HerramientaController@estados: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener estados'], 500);
        }
    }

    /**
     * Obtener estadísticas de una herramienta
     */
    public function estadisticas(Herramienta $herramienta)
    {
        try {
            $estadisticas = $herramienta->estadisticas;

            return response()->json($estadisticas);

        } catch (\Exception $e) {
            Log::error('Error en HerramientaController@estadisticas: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener estadísticas'], 500);
        }
    }

    /**
     * Asignar herramienta a técnico
     */
    public function asignar(Request $request, Herramienta $herramienta)
    {
        try {
            $validated = $request->validate([
                'tecnico_id' => 'required|exists:tecnicos,id',
                'observaciones' => 'nullable|string|max:1000',
                'estado_herramienta' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();

            // Verificar que la herramienta esté disponible
            if (!$herramienta->estaDisponible()) {
                return response()->json(['error' => 'La herramienta no está disponible'], 400);
            }

            // Crear asignación
            $asignacion = AsignacionHerramienta::create([
                'herramienta_id' => $herramienta->id,
                'tecnico_id' => $validated['tecnico_id'],
                'tipo_asignacion' => 'entrega',
                'fecha_asignacion' => now(),
                'observaciones_entrega' => $validated['observaciones'],
                'estado_herramienta_entrega' => $validated['estado_herramienta'],
                'usuario_entrega_id' => auth()->id(),
                'activo' => true
            ]);

            // Actualizar estado de la herramienta
            $herramienta->update(['estado' => Herramienta::ESTADO_ASIGNADA]);

            // Crear registro en historial
            HistorialHerramienta::create([
                'herramienta_id' => $herramienta->id,
                'tecnico_id' => $validated['tecnico_id'],
                'fecha_asignacion' => now(),
                'asignado_por' => auth()->id(),
                'observaciones_asignacion' => $validated['observaciones'],
                'estado_herramienta_asignacion' => $validated['estado_herramienta']
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Herramienta asignada correctamente',
                'asignacion' => $asignacion->load(['tecnico', 'usuarioEntrega'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en HerramientaController@asignar: ' . $e->getMessage());
            return response()->json(['error' => 'Error al asignar herramienta'], 500);
        }
    }

    /**
     * Recibir herramienta de técnico
     */
    public function recibir(Request $request, Herramienta $herramienta)
    {
        try {
            $validated = $request->validate([
                'observaciones' => 'nullable|string|max:1000',
                'estado_herramienta' => 'nullable|string|max:255',
                'motivo_devolucion' => 'nullable|in:normal,desgaste,perdida,danio,reemplazo',
            ]);

            DB::beginTransaction();

            // Verificar que la herramienta esté asignada
            if (!$herramienta->estaAsignada()) {
                return response()->json(['error' => 'La herramienta no está asignada'], 400);
            }

            // Obtener asignación activa
            $asignacionActiva = $herramienta->asignacionActiva;
            if (!$asignacionActiva) {
                return response()->json(['error' => 'No se encontró asignación activa'], 400);
            }

            // Actualizar asignación
            $asignacionActiva->update([
                'tipo_asignacion' => 'recepcion',
                'observaciones_recepcion' => $validated['observaciones'],
                'estado_herramienta_recepcion' => $validated['estado_herramienta'],
                'usuario_recepcion_id' => auth()->id(),
                'activo' => false
            ]);

            // Actualizar estado de la herramienta
            $herramienta->update(['estado' => Herramienta::ESTADO_DISPONIBLE]);

            // Completar registro en historial
            $historial = $herramienta->historial()->whereNull('fecha_devolucion')->first();
            if ($historial) {
                $historial->update([
                    'fecha_devolucion' => now(),
                    'recibido_por' => auth()->id(),
                    'observaciones_devolucion' => $validated['observaciones'],
                    'estado_herramienta_devolucion' => $validated['estado_herramienta'],
                    'motivo_devolucion' => $validated['motivo_devolucion'] ?: 'normal',
                    'duracion_dias' => $historial->fecha_asignacion->diffInDays(now())
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Herramienta recibida correctamente',
                'asignacion' => $asignacionActiva->load(['tecnico', 'usuarioRecepcion'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en HerramientaController@recibir: ' . $e->getMessage());
            return response()->json(['error' => 'Error al recibir herramienta'], 500);
        }
    }

    /**
     * Inspeccionar herramienta
     */
    public function inspeccionar(Request $request, Herramienta $herramienta)
    {
        try {
            $validated = $request->validate([
                'condicion_general' => 'required|in:excelente,buena,regular,mala,critica',
                'porcentaje_desgaste' => 'required|numeric|min:0|max:100',
                'necesita_mantenimiento' => 'boolean',
                'necesita_reemplazo' => 'boolean',
                'observaciones' => 'nullable|string|max:2000',
                'prioridad_mantenimiento' => 'required|in:baja,media,alta,urgente',
            ]);

            DB::beginTransaction();

            // Crear estado
            $estado = EstadoHerramienta::create([
                'herramienta_id' => $herramienta->id,
                'condicion_general' => $validated['condicion_general'],
                'porcentaje_desgaste' => $validated['porcentaje_desgaste'],
                'necesita_mantenimiento' => $validated['necesita_mantenimiento'] ?: false,
                'necesita_reemplazo' => $validated['necesita_reemplazo'] ?: false,
                'observaciones' => $validated['observaciones'],
                'fecha_inspeccion' => now(),
                'inspeccionado_por' => auth()->id(),
                'prioridad_mantenimiento' => $validated['prioridad_mantenimiento'],
            ]);

            // Actualizar campos de la herramienta
            $herramienta->update([
                'requiere_mantenimiento' => $validated['necesita_mantenimiento'] ?: false,
                'estado' => $validated['necesita_reemplazo'] ? Herramienta::ESTADO_MANTENIMIENTO : $herramienta->estado
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Inspección registrada correctamente',
                'estado' => $estado->load(['inspeccionadoPor'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en HerramientaController@inspeccionar: ' . $e->getMessage());
            return response()->json(['error' => 'Error al registrar inspección'], 500);
        }
    }

    /**
     * Obtener alertas de mantenimiento
     */
    public function alertasMantenimiento()
    {
        try {
            $herramientas = Herramienta::with(['tecnico', 'ultimoEstado'])
                ->where('requiere_mantenimiento', true)
                ->orWhereHas('estados', function ($query) {
                    $query->where('necesita_mantenimiento', true);
                })
                ->get();

            return response()->json($herramientas);

        } catch (\Exception $e) {
            Log::error('Error en HerramientaController@alertasMantenimiento: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener alertas de mantenimiento'], 500);
        }
    }

    /**
     * Obtener alertas de desgaste
     */
    public function alertasDesgaste()
    {
        try {
            $herramientas = Herramienta::with(['tecnico', 'ultimoEstado'])
                ->whereHas('estados', function ($query) {
                    $query->where('porcentaje_desgaste', '>=', 70);
                })
                ->get();

            return response()->json($herramientas);

        } catch (\Exception $e) {
            Log::error('Error en HerramientaController@alertasDesgaste: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener alertas de desgaste'], 500);
        }
    }

    /**
     * Obtener alertas de vencimiento
     */
    public function alertasVencimiento()
    {
        try {
            $herramientas = Herramienta::with(['tecnico', 'ultimoEstado'])
                ->where('vida_util_meses', '>', 0)
                ->whereRaw('TIMESTAMPDIFF(MONTH, fecha_ultimo_mantenimiento, NOW()) >= vida_util_meses * 0.8')
                ->get();

            return response()->json($herramientas);

        } catch (\Exception $e) {
            Log::error('Error en HerramientaController@alertasVencimiento: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener alertas de vencimiento'], 500);
        }
    }

    /**
     * Marcar alerta como leída
     */
    public function marcarAlertaLeida(Request $request, $tipo, Herramienta $herramienta)
    {
        try {
            // Aquí podrías implementar un sistema de alertas leídas
            // Por ahora solo retornamos éxito
            return response()->json(['message' => 'Alerta marcada como leída']);

        } catch (\Exception $e) {
            Log::error('Error en HerramientaController@marcarAlertaLeida: ' . $e->getMessage());
            return response()->json(['error' => 'Error al marcar alerta como leída'], 500);
        }
    }
}
