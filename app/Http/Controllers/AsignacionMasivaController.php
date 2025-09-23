<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Herramienta;
use App\Models\Tecnico;
use App\Models\AsignacionMasiva;
use App\Models\DetalleAsignacionMasiva;
use App\Models\HistorialHerramienta;
use App\Models\ResponsabilidadHerramienta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AsignacionMasivaController extends Controller
{
    /**
     * Mostrar lista de asignaciones masivas
     */
    public function index(Request $request)
    {
        try {
            $query = AsignacionMasiva::with(['tecnico', 'asignadoPor'])
                                   ->orderBy('fecha_asignacion', 'desc');

            // Filtros
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('codigo_asignacion', 'like', "%{$search}%")
                      ->orWhere('proyecto_trabajo', 'like', "%{$search}%")
                      ->orWhereHas('tecnico', function ($tq) use ($search) {
                          $tq->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->input('estado')) {
                $query->where('estado', $request->input('estado'));
            }

            if ($request->input('tecnico_id')) {
                $query->where('tecnico_id', $request->input('tecnico_id'));
            }

            $asignaciones = $query->paginate(15)->appends($request->query());

            // Estadísticas
            $stats = [
                'total' => AsignacionMasiva::count(),
                'activas' => AsignacionMasiva::where('estado', AsignacionMasiva::ESTADO_ACTIVA)->count(),
                'pendientes' => AsignacionMasiva::where('estado', AsignacionMasiva::ESTADO_PENDIENTE)->count(),
                'completadas' => AsignacionMasiva::where('estado', AsignacionMasiva::ESTADO_COMPLETADA)->count(),
                'total_herramientas_asignadas' => AsignacionMasiva::where('estado', AsignacionMasiva::ESTADO_ACTIVA)->sum('total_herramientas'),
            ];

            $tecnicos = Tecnico::select('id', 'nombre', 'apellido')->where('activo', true)->get();

            return Inertia::render('Herramientas/AsignacionesMasivas/Index', [
                'asignaciones' => $asignaciones,
                'tecnicos' => $tecnicos,
                'stats' => $stats,
                'filters' => $request->only(['search', 'estado', 'tecnico_id'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error en AsignacionMasivaController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las asignaciones masivas.');
        }
    }

    /**
     * Mostrar formulario para crear nueva asignación masiva
     */
    public function create()
    {
        try {
            $herramientasDisponibles = Herramienta::with('categoriaHerramienta')
                                                 ->where('estado', Herramienta::ESTADO_DISPONIBLE)
                                                 ->orderBy('nombre')
                                                 ->get();

            $tecnicos = Tecnico::select('id', 'nombre', 'apellido', 'activo')
                              ->where('activo', true)
                              ->orderBy('nombre')
                              ->get();

            return Inertia::render('Herramientas/AsignacionesMasivas/Create', [
                'herramientas' => $herramientasDisponibles,
                'tecnicos' => $tecnicos
            ]);

        } catch (\Exception $e) {
            Log::error('Error en AsignacionMasivaController@create: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Crear nueva asignación masiva
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tecnico_id' => 'required|exists:tecnicos,id',
                'herramientas_ids' => 'required|array|min:1',
                'herramientas_ids.*' => 'exists:herramientas,id',
                'proyecto_trabajo' => 'nullable|string|max:255',
                'fecha_devolucion_programada' => 'nullable|date|after:today',
                'observaciones_asignacion' => 'nullable|string|max:1000',
                'requiere_autorizacion' => 'boolean'
            ]);

            DB::beginTransaction();

            // Verificar que todas las herramientas estén disponibles
            $herramientas = Herramienta::whereIn('id', $validated['herramientas_ids'])->get();
            $herramientasNoDisponibles = $herramientas->where('estado', '!=', Herramienta::ESTADO_DISPONIBLE);

            if ($herramientasNoDisponibles->count() > 0) {
                throw new \Exception('Algunas herramientas seleccionadas no están disponibles.');
            }

            // Crear asignación masiva
            $asignacionMasiva = AsignacionMasiva::create([
                'codigo_asignacion' => AsignacionMasiva::generarCodigoAsignacion(),
                'tecnico_id' => $validated['tecnico_id'],
                'asignado_por' => Auth::id(),
                'fecha_asignacion' => now(),
                'fecha_devolucion_programada' => $validated['fecha_devolucion_programada'] ?? null,
                'estado' => $validated['requiere_autorizacion'] ? AsignacionMasiva::ESTADO_PENDIENTE : AsignacionMasiva::ESTADO_ACTIVA,
                'observaciones_asignacion' => $validated['observaciones_asignacion'],
                'herramientas_ids' => $validated['herramientas_ids'],
                'total_herramientas' => count($validated['herramientas_ids']),
                'proyecto_trabajo' => $validated['proyecto_trabajo']
            ]);

            // Crear detalles de asignación
            foreach ($herramientas as $herramienta) {
                DetalleAsignacionMasiva::create([
                    'asignacion_masiva_id' => $asignacionMasiva->id,
                    'herramienta_id' => $herramienta->id,
                    'estado_individual' => DetalleAsignacionMasiva::ESTADO_ASIGNADA,
                    'fecha_asignacion_individual' => now(),
                    'observaciones_asignacion' => $validated['observaciones_asignacion']
                ]);

                // Crear registro en historial
                HistorialHerramienta::create([
                    'herramienta_id' => $herramienta->id,
                    'tecnico_id' => $validated['tecnico_id'],
                    'asignacion_masiva_id' => $asignacionMasiva->id,
                    'codigo_asignacion' => $asignacionMasiva->codigo_asignacion,
                    'proyecto_trabajo' => $validated['proyecto_trabajo'],
                    'tipo_asignacion' => 'masiva',
                    'fecha_asignacion' => now(),
                    'asignado_por' => Auth::id(),
                    'observaciones_asignacion' => $validated['observaciones_asignacion']
                ]);
            }

            // Si no requiere autorización, activar inmediatamente
            if (!$validated['requiere_autorizacion']) {
                $asignacionMasiva->activar();
            }

            // Actualizar responsabilidades del técnico
            ResponsabilidadHerramienta::actualizarParaTecnico($validated['tecnico_id']);

            DB::commit();

            $message = $validated['requiere_autorizacion']
                ? 'Asignación masiva creada y pendiente de autorización.'
                : 'Asignación masiva creada y activada correctamente.';

            return redirect()->route('herramientas.asignaciones-masivas.index')
                           ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en AsignacionMasivaController@store: ' . $e->getMessage());

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al crear la asignación masiva: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalles de una asignación masiva
     */
    public function show(AsignacionMasiva $asignacionMasiva)
    {
        try {
            $asignacionMasiva->load([
                'tecnico',
                'asignadoPor',
                'recibidoPor',
                'detalles.herramienta.categoriaHerramienta',
                'historialHerramientas'
            ]);

            return Inertia::render('Herramientas/AsignacionesMasivas/Show', [
                'asignacion' => $asignacionMasiva
            ]);

        } catch (\Exception $e) {
            Log::error('Error en AsignacionMasivaController@show: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los detalles.');
        }
    }

    /**
     * Autorizar asignación masiva pendiente
     */
    public function autorizar(AsignacionMasiva $asignacionMasiva)
    {
        try {
            if (!$asignacionMasiva->estaPendiente()) {
                throw new \Exception('La asignación no está pendiente de autorización.');
            }

            DB::beginTransaction();

            $asignacionMasiva->activar();

            DB::commit();

            return redirect()->back()->with('success', 'Asignación masiva autorizada y activada correctamente.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en AsignacionMasivaController@autorizar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al autorizar la asignación: ' . $e->getMessage());
        }
    }

    /**
     * Completar asignación masiva (devolver todas las herramientas)
     */
    public function completar(Request $request, AsignacionMasiva $asignacionMasiva)
    {
        try {
            $validated = $request->validate([
                'observaciones_devolucion' => 'nullable|string|max:1000'
            ]);

            if (!$asignacionMasiva->estaActiva()) {
                throw new \Exception('La asignación no está activa.');
            }

            DB::beginTransaction();

            // Completar todos los detalles pendientes
            $detallesPendientes = $asignacionMasiva->detalles()
                                                 ->where('estado_individual', DetalleAsignacionMasiva::ESTADO_ASIGNADA)
                                                 ->get();

            foreach ($detallesPendientes as $detalle) {
                $detalle->marcarComoDevuelta($validated['observaciones_devolucion']);

                // Completar historial
                $historial = HistorialHerramienta::where('herramienta_id', $detalle->herramienta_id)
                                                ->where('asignacion_masiva_id', $asignacionMasiva->id)
                                                ->whereNull('fecha_devolucion')
                                                ->first();

                if ($historial) {
                    $historial->update([
                        'fecha_devolucion' => now(),
                        'recibido_por' => Auth::id(),
                        'observaciones_devolucion' => $validated['observaciones_devolucion'],
                        'motivo_devolucion' => HistorialHerramienta::MOTIVO_DEVOLUCION_NORMAL,
                        'duracion_dias' => $historial->fecha_asignacion->diffInDays(now())
                    ]);
                }
            }

            $asignacionMasiva->completar($validated['observaciones_devolucion']);

            // Actualizar responsabilidades del técnico
            ResponsabilidadHerramienta::actualizarParaTecnico($asignacionMasiva->tecnico_id);

            DB::commit();

            return redirect()->back()->with('success', 'Asignación masiva completada correctamente.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en AsignacionMasivaController@completar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al completar la asignación: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar asignación masiva
     */
    public function cancelar(Request $request, AsignacionMasiva $asignacionMasiva)
    {
        try {
            $validated = $request->validate([
                'motivo_cancelacion' => 'required|string|max:500'
            ]);

            if ($asignacionMasiva->estaCompletada() || $asignacionMasiva->estaCancelada()) {
                throw new \Exception('La asignación ya está completada o cancelada.');
            }

            DB::beginTransaction();

            $asignacionMasiva->cancelar($validated['motivo_cancelacion']);

            // Actualizar responsabilidades del técnico
            ResponsabilidadHerramienta::actualizarParaTecnico($asignacionMasiva->tecnico_id);

            DB::commit();

            return redirect()->back()->with('success', 'Asignación masiva cancelada correctamente.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en AsignacionMasivaController@cancelar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cancelar la asignación: ' . $e->getMessage());
        }
    }

    /**
     * Devolver herramienta individual de una asignación masiva
     */
    public function devolverHerramienta(Request $request, AsignacionMasiva $asignacionMasiva, $herramientaId)
    {
        try {
            $validated = $request->validate([
                'observaciones' => 'nullable|string|max:500',
                'estado_herramienta' => 'nullable|string|max:255',
                'foto_estado' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'tipo_devolucion' => 'required|in:normal,perdida,dañada'
            ]);

            $detalle = $asignacionMasiva->detalles()
                                      ->where('herramienta_id', $herramientaId)
                                      ->where('estado_individual', DetalleAsignacionMasiva::ESTADO_ASIGNADA)
                                      ->firstOrFail();

            DB::beginTransaction();

            $foto = null;
            if ($request->hasFile('foto_estado')) {
                $foto = $request->file('foto_estado')->store('devoluciones-herramientas', 'public');
            }

            // Marcar según el tipo de devolución
            switch ($validated['tipo_devolucion']) {
                case 'perdida':
                    $detalle->marcarComoPerdida($validated['observaciones']);
                    break;
                case 'dañada':
                    $detalle->marcarComoDañada($validated['observaciones'], $foto);
                    break;
                default:
                    $detalle->marcarComoDevuelta($validated['observaciones'], $validated['estado_herramienta'], $foto);
            }

            // Actualizar historial
            $historial = HistorialHerramienta::where('herramienta_id', $herramientaId)
                                            ->where('asignacion_masiva_id', $asignacionMasiva->id)
                                            ->whereNull('fecha_devolucion')
                                            ->first();

            if ($historial) {
                $motivo = match($validated['tipo_devolucion']) {
                    'perdida' => HistorialHerramienta::MOTIVO_DEVOLUCION_PERDIDA,
                    'dañada' => HistorialHerramienta::MOTIVO_DEVOLUCION_DANIO,
                    default => HistorialHerramienta::MOTIVO_DEVOLUCION_NORMAL
                };

                $historial->update([
                    'fecha_devolucion' => now(),
                    'recibido_por' => Auth::id(),
                    'observaciones_devolucion' => $validated['observaciones'],
                    'estado_herramienta_devolucion' => $validated['estado_herramienta'],
                    'motivo_devolucion' => $motivo,
                    'duracion_dias' => $historial->fecha_asignacion->diffInDays(now())
                ]);
            }

            // Verificar si la asignación masiva está completa
            $herramientasPendientes = $asignacionMasiva->detalles()
                                                     ->where('estado_individual', DetalleAsignacionMasiva::ESTADO_ASIGNADA)
                                                     ->count();

            if ($herramientasPendientes == 0) {
                $asignacionMasiva->completar('Todas las herramientas han sido devueltas');
            }

            // Actualizar responsabilidades del técnico
            ResponsabilidadHerramienta::actualizarParaTecnico($asignacionMasiva->tecnico_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Herramienta devuelta correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en AsignacionMasivaController@devolverHerramienta: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al devolver la herramienta: ' . $e->getMessage()
            ], 500);
        }
    }
}
