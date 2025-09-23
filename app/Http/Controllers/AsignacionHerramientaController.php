<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Herramienta;
use App\Models\Tecnico;
use App\Models\AsignacionHerramienta;
use App\Models\HistorialHerramienta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AsignacionHerramientaController extends Controller
{
    /**
     * Mostrar lista de asignaciones de herramientas
     */
    public function index(Request $request)
    {
        try {
            // Cargar asignaciones sin relaciones problemáticas primero
            $query = AsignacionHerramienta::orderBy('fecha_asignacion', 'desc');

            // Filtros
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('herramienta', function ($hq) use ($search) {
                        $hq->where('nombre', 'like', "%{$search}%")
                            ->orWhere('numero_serie', 'like', "%{$search}%");
                    })
                    ->orWhereHas('tecnico', function ($tq) use ($search) {
                        $tq->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                    });
                });
            }

            if ($request->input('tipo')) {
                $query->where('tipo_asignacion', $request->input('tipo'));
            }

            if ($request->input('estado') !== null) {
                $query->where('activo', $request->input('estado'));
            }

            $asignaciones = $query->paginate(15)->appends($request->query());

            // Cargar datos básicos sin relaciones problemáticas
            $herramientas = Herramienta::select('id', 'nombre', 'numero_serie')->get();
            $tecnicos = Tecnico::select('id', 'nombre', 'apellido')->get();

            // Estadísticas básicas
            $stats = [
                'total' => AsignacionHerramienta::count(),
                'entregas' => AsignacionHerramienta::where('tipo_asignacion', 'entrega')->count(),
                'recepciones' => AsignacionHerramienta::where('tipo_asignacion', 'recepcion')->count(),
                'activas' => AsignacionHerramienta::where('activo', true)->count(),
            ];

            return Inertia::render('Herramientas/Asignaciones/Index', [
                'asignaciones' => $asignaciones,
                'herramientas' => $herramientas,
                'tecnicos' => $tecnicos,
                'stats' => $stats,
                'filters' => $request->only(['search', 'tipo', 'estado'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error en AsignacionHerramientaController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las asignaciones.');
        }
    }

    /**
     * Mostrar formulario para crear nueva asignación
     */
    public function create(Request $request)
    {
        try {
            $herramientas = Herramienta::select('id', 'nombre', 'numero_serie', 'estado', 'categoria')
                ->where('estado', Herramienta::ESTADO_DISPONIBLE)
                ->orderBy('nombre')
                ->get();

            $tecnicos = Tecnico::select('id', 'nombre', 'apellido', 'activo')
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            return Inertia::render('Herramientas/Asignaciones/Create', [
                'herramientas' => $herramientas,
                'tecnicos' => $tecnicos,
                'tipo' => $request->input('tipo', 'entrega'),
                'herramienta_id' => $request->input('herramienta_id')
            ]);

        } catch (\Exception $e) {
            Log::error('Error en AsignacionHerramientaController@create: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Crear nueva asignación de herramienta
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'herramienta_id' => 'required|exists:herramientas,id',
                'tecnico_id' => 'required|exists:tecnicos,id',
                'tipo_asignacion' => 'required|in:entrega,recepcion',
                'observaciones_entrega' => 'nullable|string|max:1000',
                'observaciones_recepcion' => 'nullable|string|max:1000',
                'estado_herramienta_entrega' => 'nullable|string|max:255',
                'estado_herramienta_recepcion' => 'nullable|string|max:255',
                'foto_estado_entrega' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'foto_estado_recepcion' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            DB::beginTransaction();

            $herramienta = Herramienta::findOrFail($validated['herramienta_id']);
            $tecnico = Tecnico::findOrFail($validated['tecnico_id']);

            // Validar estado de la herramienta
            if ($validated['tipo_asignacion'] === 'entrega' && !$herramienta->estaDisponible()) {
                throw new \Exception('La herramienta no está disponible para entrega.');
            }

            if ($validated['tipo_asignacion'] === 'recepcion' && !$herramienta->estaAsignada()) {
                throw new \Exception('La herramienta no está asignada actualmente.');
            }

            // Procesar fotos si se subieron
            if ($request->hasFile('foto_estado_entrega')) {
                $validated['foto_estado_entrega'] = $this->storeFoto($request->file('foto_estado_entrega'));
            }

            if ($request->hasFile('foto_estado_recepcion')) {
                $validated['foto_estado_recepcion'] = $this->storeFoto($request->file('foto_estado_recepcion'));
            }

            // Crear asignación
            $asignacion = AsignacionHerramienta::create([
                ...$validated,
                'fecha_asignacion' => now(),
                'usuario_entrega_id' => auth()->id(),
                'usuario_recepcion_id' => $validated['tipo_asignacion'] === 'recepcion' ? auth()->id() : null,
                'activo' => $validated['tipo_asignacion'] === 'entrega'
            ]);

            // Actualizar estado de la herramienta
            if ($validated['tipo_asignacion'] === 'entrega') {
                $herramienta->update(['estado' => Herramienta::ESTADO_ASIGNADA]);

                // Crear registro en historial
                HistorialHerramienta::create([
                    'herramienta_id' => $herramienta->id,
                    'tecnico_id' => $tecnico->id,
                    'fecha_asignacion' => now(),
                    'asignado_por' => auth()->id(),
                    'observaciones_asignacion' => $validated['observaciones_entrega'],
                    'estado_herramienta_asignacion' => $validated['estado_herramienta_entrega']
                ]);
            } else {
                // Recepcion - completar registro en historial
                $historial = $herramienta->historial()->whereNull('fecha_devolucion')->first();
                if ($historial) {
                    $historial->update([
                        'fecha_devolucion' => now(),
                        'recibido_por' => auth()->id(),
                        'observaciones_devolucion' => $validated['observaciones_recepcion'],
                        'estado_herramienta_devolucion' => $validated['estado_herramienta_recepcion'],
                        'motivo_devolucion' => $this->determinarMotivoDevolucion($validated),
                        'duracion_dias' => $historial->fecha_asignacion->diffInDays(now())
                    ]);
                }

                $herramienta->update(['estado' => Herramienta::ESTADO_DISPONIBLE]);
                $asignacion->update(['activo' => false]);
            }

            DB::commit();

            $message = $validated['tipo_asignacion'] === 'entrega'
                ? 'Herramienta entregada correctamente.'
                : 'Herramienta recibida correctamente.';

            return redirect()->route('herramientas.asignaciones.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en AsignacionHerramientaController@store: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al procesar la asignación: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalles de una asignación
     */
    public function show(AsignacionHerramienta $asignacion)
    {
        try {
            $asignacion->load(['herramienta', 'tecnico', 'usuarioEntrega', 'usuarioRecepcion']);

            return Inertia::render('Herramientas/Asignaciones/Show', [
                'asignacion' => $asignacion
            ]);

        } catch (\Exception $e) {
            Log::error('Error en AsignacionHerramientaController@show: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los detalles.');
        }
    }

    /**
     * Agregar firma a una asignación
     */
    public function agregarFirma(Request $request, AsignacionHerramienta $asignacion)
    {
        try {
            $validated = $request->validate([
                'tipo_firma' => 'required|in:entrega,recepcion',
                'firma' => 'required|string',
                'observaciones' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            if ($validated['tipo_firma'] === 'entrega') {
                $asignacion->update([
                    'firma_entrega' => $validated['firma'],
                    'observaciones_entrega' => $validated['observaciones'] ?: $asignacion->observaciones_entrega
                ]);
            } else {
                $asignacion->update([
                    'firma_recepcion' => $validated['firma'],
                    'observaciones_recepcion' => $validated['observaciones'] ?: $asignacion->observaciones_recepcion
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Firma agregada correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en AsignacionHerramientaController@agregarFirma: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al agregar la firma.'
            ], 500);
        }
    }

    /**
     * Obtener asignaciones activas de una herramienta
     */
    public function getAsignacionesActivas(Herramienta $herramienta)
    {
        try {
            $asignaciones = $herramienta->asignaciones()
                ->where('activo', true)
                ->with(['tecnico', 'usuarioEntrega'])
                ->orderBy('fecha_asignacion', 'desc')
                ->get();

            return response()->json($asignaciones);

        } catch (\Exception $e) {
            Log::error('Error en AsignacionHerramientaController@getAsignacionesActivas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignaciones activas.'
            ], 500);
        }
    }

    /**
     * Almacenar foto de estado
     */
    private function storeFoto($file)
    {
        return $file->store('asignaciones-herramientas', 'public');
    }

    /**
     * Determinar motivo de devolución basado en observaciones
     */
    private function determinarMotivoDevolucion($validated)
    {
        $observaciones = strtolower($validated['observaciones_recepcion'] ?? '');

        if (str_contains($observaciones, 'desgaste') || str_contains($observaciones, 'desgastada')) {
            return HistorialHerramienta::MOTIVO_DEVOLUCION_DESGASTE;
        }

        if (str_contains($observaciones, 'daño') || str_contains($observaciones, 'dañada') || str_contains($observaciones, 'roto')) {
            return HistorialHerramienta::MOTIVO_DEVOLUCION_DANIO;
        }

        if (str_contains($observaciones, 'perdida') || str_contains($observaciones, 'perdido')) {
            return HistorialHerramienta::MOTIVO_DEVOLUCION_PERDIDA;
        }

        return HistorialHerramienta::MOTIVO_DEVOLUCION_NORMAL;
    }
}
