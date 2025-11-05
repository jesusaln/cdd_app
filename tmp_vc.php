<?php

namespace App\Http\Controllers;

use App\Models\Vacacion;
use App\Models\User;
use App\Models\RegistroVacaciones;
use App\Models\AjusteVacaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Inertia\Inertia;
use Exception;

class VacacionController extends BaseController
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'verified']);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Vacacion::class);

        try {
            Log::info('VacacionController@index: Iniciando carga de vacaciones', [
                'user_id' => Auth::id(),
                'user_roles' => Auth::user()->roles->pluck('name')->toArray(),
                'filters' => $request->all()
            ]);

            $query = Vacacion::with(['empleado', 'aprobador']);

            // Filtros
            if ($empleado = $request->input('empleado')) {
                $query->where('user_id', $empleado);
                Log::info('VacacionController@index: Aplicando filtro empleado', ['empleado_id' => $empleado]);
            }

            if ($estado = $request->input('estado')) {
                $query->where('estado', $estado);
                Log::info('VacacionController@index: Aplicando filtro estado', ['estado' => $estado]);
            }

            if ($fechaDesde = $request->input('fecha_desde')) {
                $query->where('fecha_inicio', '>=', $fechaDesde);
                Log::info('VacacionController@index: Aplicando filtro fecha_desde', ['fecha_desde' => $fechaDesde]);
            }

            if ($fechaHasta = $request->input('fecha_hasta')) {
                $query->where('fecha_fin', '<=', $fechaHasta);
                Log::info('VacacionController@index: Aplicando filtro fecha_hasta', ['fecha_hasta' => $fechaHasta]);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $validSort = ['fecha_inicio', 'fecha_fin', 'dias_solicitados', 'estado', 'created_at'];

            if (!in_array($sortBy, $validSort)) $sortBy = 'created_at';
            if (!in_array($sortDirection, ['asc', 'desc'])) $sortDirection = 'desc';

            $query->orderBy($sortBy, $sortDirection);

            $vacaciones = $query->paginate(15)->appends($request->query());

            Log::info('VacacionController@index: Vacaciones obtenidas', [
                'total_vacaciones' => $vacaciones->total(),
                'vacaciones_count' => $vacaciones->count(),
                'vacaciones_data' => $vacaciones->items()
            ]);

            // Estadísticas
            $stats = [
                'total' => Vacacion::count(),
                'pendientes' => Vacacion::where('estado', 'pendiente')->count(),
                'aprobadas' => Vacacion::where('estado', 'aprobada')->count(),
                'rechazadas' => Vacacion::where('estado', 'rechazada')->count(),
            ];

            Log::info('VacacionController@index: Estadísticas calculadas', $stats);

            // Lista de empleados para filtro
            $empleados = User::empleadosActivos()
                ->orderBy('name')
                ->get(['id', 'name']);

            Log::info('VacacionController@index: Empleados obtenidos', ['empleados_count' => $empleados->count()]);

            return Inertia::render('Vacaciones/Index', [
                'vacaciones' => $vacaciones,
                'stats' => $stats,
                'empleados' => $empleados,
                'filters' => $request->only(['empleado', 'estado', 'fecha_desde', 'fecha_hasta']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (Exception $e) {
            Log::error('Error en VacacionController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las vacaciones.');
        }
    }

    public function create(Request $request)
    {
        $this->authorize('create', Vacacion::class);

        $empleados = User::empleadosActivos()
            ->orderBy('name')
            ->get(['id', 'name']);

        $empleadoSeleccionado = null;

        // Si se especifica un empleado en la URL, preseleccionarlo
        if ($request->has('empleado_id')) {
            $empleadoSeleccionado = User::empleadosActivos()
                ->find($request->empleado_id);
        } else {
            // Si no hay empleado específico, usar el usuario autenticado si es empleado
            $authUser = Auth::user();
            if ($authUser && $authUser->es_empleado) {
                $empleadoSeleccionado = $authUser;
            }
        }

        $registroVacaciones = null;
        if ($empleadoSeleccionado) {
            $registroVacaciones = RegistroVacaciones::actualizarRegistroAnual($empleadoSeleccionado->id);
        }

        return Inertia::render('Vacaciones/Create', [
            'empleados' => $empleados,
            'empleadoSeleccionado' => $empleadoSeleccionado,
            'registroVacaciones' => $registroVacaciones,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Vacacion::class);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'motivo' => 'nullable|string|max:500',
        ]);

        // Verificar que el usuario sea un empleado
        $empleado = User::findOrFail($validated['user_id']);
        if (!$empleado->es_empleado) {
            return redirect()->back()->with('error', 'El usuario seleccionado no es un empleado.');
        }

        // Los empleados pueden crear vacaciones para sí mismos
        // Los administradores pueden crear vacaciones para cualquiera
        // Esta funcionalidad está controlada por las políticas de autorización

        // Calcular días solicitados
        $fechaInicio = \Carbon\Carbon::parse($validated['fecha_inicio']);
        $fechaFin = \Carbon\Carbon::parse($validated['fecha_fin']);
        $diasSolicitados = $fechaInicio->diffInDays($fechaFin) + 1;

        // Verificar que no haya vacaciones en esas fechas
        $conflicto = Vacacion::where('user_id', $validated['user_id'])
            ->where('estado', 'aprobada')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                      ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                      ->orWhere(function ($q) use ($fechaInicio, $fechaFin) {
                          $q->where('fecha_inicio', '<=', $fechaInicio)
                            ->where('fecha_fin', '>=', $fechaFin);
                      });
            })
            ->exists();

        if ($conflicto) {
            return redirect()->back()->with('error', 'Ya existen vacaciones aprobadas en esas fechas.');
        }

        // Verificar que el empleado tenga suficientes días disponibles
        $registroActual = RegistroVacaciones::actualizarRegistroAnual($validated['user_id']);
        if (!$registroActual || !$registroActual->tieneDiasDisponibles($diasSolicitados)) {
            $diasDisponibles = $registroActual ? $registroActual->dias_disponibles : 0;
            return redirect()->back()->with('error', "El empleado no tiene suficientes días de vacaciones disponibles. Días disponibles: {$diasDisponibles}, Días solicitados: {$diasSolicitados}");
        }

        Vacacion::create([
            'user_id' => $validated['user_id'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'dias_solicitados' => $diasSolicitados,
            'dias_pendientes' => $diasSolicitados,
            'motivo' => $validated['motivo'],
        ]);

        return redirect()->route('vacaciones.index')->with('success', 'Solicitud de vacaciones creada exitosamente.');
    }

    public function show($id)
    {
        $vacacion = Vacacion::with([
            'empleado' => function ($query) {
                $query->select(['id', 'name', 'puesto', 'departamento', 'fecha_contratacion']);
            },
            'aprobador' => function ($query) {
                $query->select(['id', 'name']);
            }
        ])->findOrFail($id);

        $this->authorize('view', $vacacion);

        $registroVacaciones = null;
        if ($vacacion->empleado) {
            $registroVacaciones = RegistroVacaciones::actualizarRegistroAnual($vacacion->empleado->id);
        }

        $anioAjuste = $vacacion->fecha_inicio ? \Carbon\Carbon::parse($vacacion->fecha_inicio)->year : now()->year;
        $ajustes = AjusteVacaciones::with(['creador:id,name'])
            ->where('user_id', $vacacion->user_id)
            ->where('anio', $anioAjuste)
            ->orderBy('created_at', 'desc')
            ->get(['id','user_id','anio','dias','motivo','creado_por','created_at']);

        return Inertia::render('Vacaciones/Show', [
            'vacacion' => $vacacion,
            'registroVacaciones' => $registroVacaciones,
            'ajustesVacaciones' => $ajustes,
        ]);
    }

    public function aprobar(Request $request, Vacacion $vacacion)
    {
        $this->authorize('update', $vacacion);

        // Verificación adicional: solo administradores pueden aprobar
        // Esta verificación está controlada por las políticas de autorización

        if ($vacacion->estado !== 'pendiente') {
            return redirect()->back()->with('error', 'Solo se pueden aprobar vacaciones pendientes.');
        }

        $vacacion->aprobar(Auth::id(), $request->input('observaciones'));

        return redirect()->back()->with('success', 'Vacaciones aprobadas exitosamente.');
    }

    public function rechazar(Request $request, Vacacion $vacacion)
    {
        $this->authorize('update', $vacacion);

        // Verificación adicional: solo administradores pueden rechazar
        // Esta verificación está controlada por las políticas de autorización

        if ($vacacion->estado !== 'pendiente') {
            return redirect()->back()->with('error', 'Solo se pueden rechazar vacaciones pendientes.');
        }

        $vacacion->rechazar(Auth::id(), $request->input('observaciones'));

        return redirect()->back()->with('success', 'Vacaciones rechazadas.');
    }

    public function destroy(Vacacion $vacacion)
    {
        $this->authorize('delete', $vacacion);

        // Verificación adicional: solo administradores pueden eliminar
        // Esta verificación está controlada por las políticas de autorización

        if (!in_array($vacacion->estado, ['pendiente', 'rechazada'])) {
            return redirect()->back()->with('error', 'Solo se pueden eliminar vacaciones pendientes o rechazadas.');
        }

        try {
            $vacacion->delete();
            return redirect()->route('vacaciones.index')->with('success', 'Solicitud de vacaciones eliminada.');
        } catch (Exception $e) {
            Log::error('Error eliminando vacaciones: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar las vacaciones.');
        }
    }

    public function misVacaciones(Request $request)
    {
        $user = Auth::user();

        // Verificar que el usuario sea un empleado
        if (!$user->es_empleado) {
            return redirect()->back()->with('error', 'Solo los empleados pueden acceder a esta sección.');
        }

        $query = Vacacion::with(['aprobador'])->where('user_id', $user->id);

        // Filtros
        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        $vacaciones = $query->orderBy('created_at', 'desc')->paginate(10);

        return Inertia::render('Vacaciones/MisVacaciones', [
            'vacaciones' => $vacaciones,
            'filters' => $request->only(['estado']),
        ]);
    }
}

