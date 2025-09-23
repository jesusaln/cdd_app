<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use App\Models\Carro;
use App\Services\AlertaMantenimientoService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class MantenimientoController extends Controller
{
    // Mostrar lista de mantenimientos con paginación y filtros
    public function index(Request $request)
    {
        try {
            $query = Mantenimiento::with('carro');

            // Filtros
            if ($search = trim($request->input('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('tipo', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%")
                      ->orWhereHas('carro', function ($carroQuery) use ($search) {
                          $carroQuery->where('marca', 'like', "%{$search}%")
                                    ->orWhere('modelo', 'like', "%{$search}%")
                                    ->orWhere('placa', 'like', "%{$search}%");
                      });
                });
            }

            if ($estado = $request->input('estado')) {
                $query->where('estado', $estado);
            }

            if ($tipo = $request->input('tipo')) {
                $query->where('tipo', $tipo);
            }

            if ($carroId = $request->input('carro_id')) {
                $query->where('carro_id', $carroId);
            }

            // Ordenamiento
            $sortBy = $request->input('sort_by', 'fecha');
            $sortDirection = $request->input('sort_direction', 'asc'); // Por defecto orden ascendente para mejor flujo cronológico

            $validSortFields = ['fecha', 'tipo', 'costo', 'estado', 'created_at'];
            if (!in_array($sortBy, $validSortFields)) {
                $sortBy = 'fecha';
            }

            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $perPage = min((int) $request->input('per_page', 10), 50);
            $mantenimientos = $query->paginate($perPage)->appends($request->query());

            // Calcular días restantes para cada mantenimiento
            foreach ($mantenimientos->items() as $mantenimiento) {
                if ($mantenimiento->proximo_mantenimiento) {
                    $fechaProximo = Carbon::parse($mantenimiento->proximo_mantenimiento);
                    $fechaHoy = Carbon::now();
                    $diasRestantes = round($fechaHoy->diffInDays($fechaProximo, false)); // Redondear a número entero

                    $mantenimiento->dias_restantes = $diasRestantes;
                } else {
                    $mantenimiento->dias_restantes = null;
                }
            }

            // Estadísticas
            $stats = [
                'total' => Mantenimiento::count(),
                'completados' => Mantenimiento::where('estado', Mantenimiento::ESTADO_COMPLETADO)->count(),
                'pendientes' => Mantenimiento::where('estado', Mantenimiento::ESTADO_PENDIENTE)->count(),
                'en_proceso' => Mantenimiento::where('estado', Mantenimiento::ESTADO_EN_PROCESO)->count(),
                'costo_total_mes' => Mantenimiento::whereMonth('fecha', now()->month)->sum('costo'),
                'proximos_vencer' => $this->getMantenimientosProximosAVencer(30)->count(),
            ];

            // Obtener carros para el filtro
            $carros = Carro::orderBy('marca', 'asc')->orderBy('modelo', 'asc')->get();

            return Inertia::render('Mantenimientos/Index', [
                'mantenimientos' => $mantenimientos,
                'stats' => $stats,
                'filters' => $request->only(['search', 'estado', 'tipo', 'carro_id']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
                'carros' => $carros,
                'tiposMantenimiento' => $this->getTiposMantenimiento(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en MantenimientoController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los mantenimientos.');
        }
    }

    // Dashboard con métricas mejoradas pero simplificadas
    public function dashboard()
    {
        $mantenimientos = Mantenimiento::with('carro')->get();

        $metricas = [
            'total_mantenimientos' => $mantenimientos->count(),
            'mantenimientos_mes_actual' => $mantenimientos->filter(function ($m) {
                return Carbon::parse($m->fecha)->isCurrentMonth();
            })->count(),
            'costo_total_mes' => $mantenimientos->filter(function ($m) {
                return Carbon::parse($m->fecha)->isCurrentMonth();
            })->sum('costo'),
            'proximos_vencer' => $this->getMantenimientosProximosAVencer(30)->count(),
            'vencidos' => $this->getMantenimientosVencidos()->count(),
        ];

        // Análisis por tipo de mantenimiento (simplificado)
        $mantenimientosPorTipo = $mantenimientos->groupBy('tipo')
            ->map(function ($items) {
                return [
                    'cantidad' => $items->count(),
                    'costo_promedio' => round($items->avg('costo') ?? 0, 2),
                ];
            });

        return Inertia::render('Mantenimientos/Dashboard', [
            'metricas' => $metricas,
            'mantenimientosPorTipo' => $mantenimientosPorTipo,
        ]);
    }

    // Mostrar formulario para crear un nuevo mantenimiento
    public function create()
    {
        $carros = Carro::orderBy('marca')->orderBy('modelo')->get();
        $tiposMantenimiento = $this->getTiposMantenimiento();

        return Inertia::render('Mantenimientos/Create', [
            'carros' => $carros,
            'tiposMantenimiento' => $tiposMantenimiento
        ]);
    }

    // Crear un nuevo mantenimiento (mejorado pero simplificado)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'carro_id' => 'required|exists:carros,id',
            'tipo' => 'required|string|max:255',
            'fecha' => 'required|date|before_or_equal:today',
            'proximo_mantenimiento' => 'required|date|after:fecha',
            'notas' => 'nullable|string|max:1000',
            'kilometraje_actual' => 'required|integer|min:0',
            'costo' => 'nullable|numeric|min:0|max:999999.99',
            'descripcion' => 'nullable|string|max:1000',
            'prioridad' => 'required|in:baja,media,alta,critica',
            'dias_anticipacion_alerta' => 'required|integer|min:1|max:365',
            'requiere_aprobacion' => 'boolean',
            'observaciones_alerta' => 'nullable|string|max:500',
        ], [
            'carro_id.required' => 'Debes seleccionar un vehículo.',
            'tipo.required' => 'El tipo de servicio es obligatorio.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'proximo_mantenimiento.after' => 'El próximo mantenimiento debe ser posterior a la fecha actual.',
            'kilometraje_actual.required' => 'El kilometraje es obligatorio.',
            'kilometraje_actual.min' => 'El kilometraje no puede ser negativo.',
            'costo.max' => 'El costo no puede exceder $999,999.99',
            'notas.max' => 'Las notas no pueden exceder 1000 caracteres.',
            'prioridad.required' => 'La prioridad es obligatoria.',
            'prioridad.in' => 'La prioridad debe ser: baja, media, alta o crítica.',
            'dias_anticipacion_alerta.required' => 'Los días de anticipación son obligatorios.',
            'dias_anticipacion_alerta.min' => 'Debe haber al menos 1 día de anticipación.',
            'dias_anticipacion_alerta.max' => 'No puede haber más de 365 días de anticipación.',
            'observaciones_alerta.max' => 'Las observaciones no pueden exceder 500 caracteres.',
        ]);

        DB::beginTransaction();

        try {
            Log::info('Iniciando creación de mantenimiento', [
                'validated_data' => $validated,
                'carro_id' => $validated['carro_id']
            ]);

            // Con Opción B: Permitir crear mantenimientos completados incluso si existe uno pendiente
            // Los completados son registros históricos, los pendientes son únicos por tipo

            // Verificar si ya existe un mantenimiento para este vehículo y tipo en la misma fecha
            $existeMantenimiento = Mantenimiento::where('carro_id', $validated['carro_id'])
                ->where('tipo', $validated['tipo'])
                ->where('fecha', $validated['fecha'])
                ->exists();

            if ($existeMantenimiento) {
                return back()->withErrors([
                    'fecha' => 'Ya existe un mantenimiento registrado para este vehículo y tipo en la fecha seleccionada.'
                ])->withInput();
            }

            // Obtener el carro asociado
            $carro = Carro::findOrFail($validated['carro_id']);
            Log::info('Carro encontrado', ['carro_id' => $carro->id, 'kilometraje_actual' => $carro->kilometraje]);

            // Validar que el kilometraje sea coherente
            if ($validated['kilometraje_actual'] < $carro->kilometraje) {
                Log::warning('Kilometraje inconsistente', [
                    'kilometraje_enviado' => $validated['kilometraje_actual'],
                    'kilometraje_carro' => $carro->kilometraje
                ]);
                return back()->withErrors([
                    'kilometraje_actual' => "El kilometraje debe ser mayor o igual al actual del carro ({$carro->kilometraje} km)."
                ])->withInput();
            }

            // Log antes de actualizar el carro
            Log::info('Actualizando kilometraje del carro', [
                'carro_id' => $carro->id,
                'kilometraje_anterior' => $carro->kilometraje,
                'kilometraje_nuevo' => $validated['kilometraje_actual']
            ]);

            // Actualizar el kilometraje del carro
            $carro->update(['kilometraje' => $validated['kilometraje_actual']]);

            // Validar que la fecha del próximo mantenimiento sea posterior a la fecha del servicio
            $fechaServicio = Carbon::parse($validated['fecha']);
            $proximoMantenimiento = Carbon::parse($validated['proximo_mantenimiento']);

            if ($proximoMantenimiento->lessThanOrEqualTo($fechaServicio)) {
                return back()->withErrors([
                    'proximo_mantenimiento' => 'La fecha del próximo mantenimiento debe ser posterior a la fecha del servicio actual.'
                ])->withInput();
            }

            $intervaloDias = $fechaServicio->diffInDays($proximoMantenimiento);

            // 1. Crear el servicio que YA SE HIZO (completado)
            $mantenimientoCompletado = [
                'carro_id' => $validated['carro_id'],
                'tipo' => $validated['tipo'],
                'fecha' => $validated['fecha'],
                'proximo_mantenimiento' => $validated['proximo_mantenimiento'],
                'notas' => $validated['notas'] ?? '',
                'kilometraje_actual' => $validated['kilometraje_actual'],
                'costo' => $validated['costo'] ?? 0,
                'descripcion' => $validated['descripcion'] ?? '',
                'estado' => Mantenimiento::ESTADO_COMPLETADO, // Este ya se completó
                'prioridad' => $validated['prioridad'],
                'dias_anticipacion_alerta' => $validated['dias_anticipacion_alerta'],
                'requiere_aprobacion' => filter_var($validated['requiere_aprobacion'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'observaciones_alerta' => $validated['observaciones_alerta'] ?? null,
            ];

            Log::info('Creando mantenimiento completado', ['mantenimiento_data' => $mantenimientoCompletado]);
            $mantenimiento = Mantenimiento::create($mantenimientoCompletado);

            // NO crear automáticamente el siguiente mantenimiento pendiente
            // El siguiente mantenimiento se creará solo cuando se complete este desde el modal
            Log::info('No se crea mantenimiento pendiente automáticamente - se creará desde el modal al completar');
            $proximoMantenimiento = null;

            Log::info('Mantenimiento creado exitosamente', [
                'completado_id' => $mantenimiento->id,
                'pendiente_id' => $proximoMantenimiento ? $proximoMantenimiento->id : null
            ]);

            DB::commit();

            return redirect()->route('mantenimientos.index')
                ->with('success', 'Mantenimiento registrado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación en MantenimientoController@store: ' . $e->getMessage());
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Error de base de datos en MantenimientoController@store: ' . $e->getMessage());
            Log::error('SQL Error Code: ' . $e->getCode());
            Log::error('SQL Error: ' . $e->getSql());

            $errorMessage = 'Error en la base de datos al crear el mantenimiento.';
            if ($e->getCode() == 23000) {
                $errorMessage = 'Error de integridad de datos. Verifica que el vehículo existe y los datos sean válidos.';
            }

            return back()->withErrors([
                'general' => $errorMessage . ' Detalles: ' . $e->getMessage()
            ])->withInput();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error inesperado en MantenimientoController@store: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Request data: ' . json_encode($request->all()));

            return back()->withErrors([
                'general' => 'Error inesperado al procesar el mantenimiento: ' . $e->getMessage()
            ])->withInput();
        }
    }

    // Mostrar formulario para editar un mantenimiento
    public function edit(Mantenimiento $mantenimiento)
    {
        $mantenimiento->load('carro');
        $carros = Carro::orderBy('marca')->orderBy('modelo')->get();
        $tiposMantenimiento = $this->getTiposMantenimiento();

        return Inertia::render('Mantenimientos/Edit', [
            'mantenimiento' => $mantenimiento,
            'carros' => $carros,
            'tiposMantenimiento' => $tiposMantenimiento
        ]);
    }

    // Actualizar un mantenimiento existente
    public function update(Request $request, Mantenimiento $mantenimiento)
    {
        $validated = $request->validate([
            'carro_id' => 'required|exists:carros,id',
            'tipo' => 'required|string|max:255',
            'fecha' => 'required|date|before_or_equal:today',
            'proximo_mantenimiento' => 'required|date|after:fecha',
            'notas' => 'nullable|string|max:1000',
            'kilometraje_actual' => 'required|integer|min:0',
            'costo' => 'nullable|numeric|min:0|max:999999.99',
            'descripcion' => 'nullable|string|max:1000',
            'prioridad' => 'required|in:baja,media,alta,critica',
            'dias_anticipacion_alerta' => 'required|integer|min:1|max:365',
            'requiere_aprobacion' => 'boolean',
            'observaciones_alerta' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $carro = Carro::findOrFail($validated['carro_id']);

            // Validación de kilometraje solo si es el mantenimiento más reciente
            $ultimoMantenimiento = Mantenimiento::where('carro_id', $validated['carro_id'])
                ->orderBy('fecha', 'desc')
                ->first();

            if ($ultimoMantenimiento && $ultimoMantenimiento->id === $mantenimiento->id) {
                if (
                    $validated['kilometraje_actual'] < $carro->kilometraje &&
                    Carbon::parse($validated['fecha'])->isAfter(Carbon::now()->subDays(30))
                ) {
                    return back()->withErrors([
                        'kilometraje_actual' => "El kilometraje debe ser coherente con el actual del carro ({$carro->kilometraje} km)."
                    ])->withInput();
                }
            }

            // Actualizar el mantenimiento
            $mantenimiento->update([
                'carro_id' => $validated['carro_id'],
                'tipo' => $validated['tipo'],
                'fecha' => $validated['fecha'],
                'proximo_mantenimiento' => $validated['proximo_mantenimiento'],
                'notas' => $validated['notas'],
                'kilometraje_actual' => $validated['kilometraje_actual'],
                'costo' => $validated['costo'] ?? 0,
                'descripcion' => $validated['descripcion'],
                'prioridad' => $validated['prioridad'],
                'dias_anticipacion_alerta' => $validated['dias_anticipacion_alerta'],
                'requiere_aprobacion' => $validated['requiere_aprobacion'] ?? false,
                'observaciones_alerta' => $validated['observaciones_alerta'] ?? null,
            ]);

            // Si es el mantenimiento más reciente, actualizar kilometraje del carro
            if ($ultimoMantenimiento && $ultimoMantenimiento->id === $mantenimiento->id) {
                $carro->update(['kilometraje' => $validated['kilometraje_actual']]);
            }

            DB::commit();

            return redirect()->route('mantenimientos.index')
                ->with('success', 'Mantenimiento actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'general' => 'Error al actualizar el mantenimiento. Intenta nuevamente.'
            ])->withInput();
        }
    }

    // Eliminar un mantenimiento
    public function destroy(Mantenimiento $mantenimiento)
    {
        Log::info('Intentando eliminar mantenimiento ID: ' . $mantenimiento->id);

        DB::beginTransaction();

        try {
            $carroId = $mantenimiento->carro_id;
            $mantenimientoId = $mantenimiento->id;

            // Eliminar el mantenimiento
            $deleted = $mantenimiento->delete();

            if (!$deleted) {
                Log::error('No se pudo eliminar el mantenimiento ID: ' . $mantenimientoId);
                DB::rollBack();
                return redirect()->route('mantenimientos.index')
                    ->with('error', 'No se pudo eliminar el mantenimiento.');
            }

            Log::info('Mantenimiento eliminado exitosamente: ' . $mantenimientoId);

            // Recalcular el kilometraje del carro
            $this->actualizarKilometrajeCarroTrasEliminacion($carroId);

            DB::commit();

            return redirect()->route('mantenimientos.index')
                ->with('success', 'Mantenimiento eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al eliminar mantenimiento: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->route('mantenimientos.index')
                ->with('error', 'Error al eliminar el mantenimiento.');
        }
    }

    /**
     * Actualiza el kilometraje del carro después de eliminar un mantenimiento
     */
    private function actualizarKilometrajeCarroTrasEliminacion($carroId)
    {
        // Buscar el último mantenimiento con kilometraje válido
        $ultimoMantenimiento = Mantenimiento::where('carro_id', $carroId)
            ->whereNotNull('kilometraje_actual')
            ->where('kilometraje_actual', '>', 0) // También filtrar valores válidos
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc') // En caso de empate en fecha
            ->first();

        $carro = Carro::find($carroId);

        if ($ultimoMantenimiento) {
            // Actualizar con el kilometraje del último mantenimiento válido
            $carro->update(['kilometraje' => $ultimoMantenimiento->kilometraje_actual]);
            Log::info("Kilometraje del carro ID: {$carroId} actualizado a: {$ultimoMantenimiento->kilometraje_actual}");
        } else {
            // No hay mantenimientos válidos, decidir qué hacer:

            // Opción 1: Mantener el kilometraje actual (no hacer nada)
            Log::info("No hay mantenimientos válidos para el carro ID: {$carroId}, manteniendo kilometraje actual: {$carro->kilometraje}");

            // Opción 2: Resetear a 0 (descomenta si lo prefieres)
            // $carro->update(['kilometraje' => 0]);
            // \Log::info("No hay mantenimientos válidos para el carro ID: {$carroId}, kilometraje reseteado a 0");

            // Opción 3: Usar el kilometraje inicial del carro si tienes ese campo
            // if ($carro->kilometraje_inicial) {
            //     $carro->update(['kilometraje' => $carro->kilometraje_inicial]);
            // }
        }
    }

    // API: Obtener mantenimientos próximos a vencer
    public function getProximosAVencer()
    {
        $proximosAVencer = $this->getMantenimientosProximosAVencer(30);

        return response()->json([
            'proximos_a_vencer' => $proximosAVencer,
            'total' => $proximosAVencer->count()
        ]);
    }

    // API: Obtener estadísticas de alertas para dashboard
    public function getEstadisticasAlertas()
    {
        $alertaService = new AlertaMantenimientoService();
        $estadisticas = $alertaService->generarReporteAlertas();
        $criticos = $alertaService->getMantenimientosCriticos();

        return response()->json([
            'estadisticas' => $estadisticas,
            'criticos' => $criticos,
            'timestamp' => now()->toISOString()
        ]);
    }

    // API: Marcar alerta como enviada manualmente
    public function marcarAlertaEnviada(Mantenimiento $mantenimiento)
    {
        $mantenimiento->marcarAlertaEnviada('manual');

        return response()->json([
            'success' => true,
            'message' => 'Alerta marcada como enviada',
            'mantenimiento' => $mantenimiento->load('carro')
        ]);
    }

    // Reprogramar mantenimiento (Opción B: actualizar registro existente)
    public function completar(Request $request, Mantenimiento $mantenimiento)
    {
        $request->validate([
            'costo' => 'nullable|numeric|min:0|max:999999.99',
            'proxima_fecha' => 'required|date|after:today',
        ], [
            'costo.min' => 'El costo no puede ser negativo.',
            'costo.max' => 'El costo no puede exceder $999,999.99',
            'proxima_fecha.required' => 'La fecha del próximo mantenimiento es obligatoria.',
            'proxima_fecha.after' => 'La fecha del próximo mantenimiento debe ser posterior a hoy.',
        ]);

        DB::beginTransaction();

        try {
            Log::info('Reprogramando mantenimiento desde modal (Opción B)', [
                'mantenimiento_id' => $mantenimiento->id,
                'tipo' => $mantenimiento->tipo,
                'carro_id' => $mantenimiento->carro_id,
                'costo' => $request->input('costo'),
                'proxima_fecha' => $request->input('proxima_fecha'),
                'fecha_actual_mantenimiento' => $mantenimiento->fecha
            ]);

            // Calcular intervalo entre servicios para mantener consistencia
            $fechaUltimoServicio = Carbon::parse($mantenimiento->fecha);
            $proximaFechaServicio = Carbon::parse($request->input('proxima_fecha'));
            $intervaloDias = $fechaUltimoServicio->diffInDays($proximaFechaServicio);

            // Calcular nueva fecha de próximo mantenimiento
            $nuevaFechaProximo = $proximaFechaServicio->copy()->addDays($intervaloDias);

            // Opción B: Actualizar el registro existente con nueva información
            $mantenimiento->update([
                // Mantener fecha del último servicio realizado (histórico)
                'fecha' => $mantenimiento->fecha,
                // Actualizar fecha del próximo mantenimiento programado
                'proximo_mantenimiento' => $nuevaFechaProximo->format('Y-m-d'),
                // Estado permanece como completado (servicio ya realizado)
                'estado' => Mantenimiento::ESTADO_COMPLETADO,
                // Actualizar costo del último servicio
                'costo' => $request->input('costo', $mantenimiento->costo),
                // Mantener kilometraje del último servicio
                'kilometraje_actual' => $mantenimiento->kilometraje_actual,
                // Actualizar notas con información del reprogramado
                'notas' => 'Último servicio: ' . $mantenimiento->fecha . ' - Próximo programado: ' . $nuevaFechaProximo->format('d/m/Y'),
                // Mantener otros campos sin cambios
                'descripcion' => $mantenimiento->descripcion,
                'prioridad' => $mantenimiento->prioridad,
                'dias_anticipacion_alerta' => $mantenimiento->dias_anticipacion_alerta,
                'requiere_aprobacion' => $mantenimiento->requiere_aprobacion,
                'observaciones_alerta' => $mantenimiento->observaciones_alerta,
            ]);

            // Actualizar kilometraje del carro si este es el mantenimiento más reciente
            $carro = $mantenimiento->carro;
            if ($carro && $mantenimiento->kilometraje_actual) {
                $carro->update(['kilometraje' => $mantenimiento->kilometraje_actual]);
                Log::info('Kilometraje del carro actualizado tras reprogramación', [
                    'carro_id' => $carro->id,
                    'kilometraje_actualizado' => $mantenimiento->kilometraje_actual
                ]);
            }

            Log::info('Mantenimiento reprogramado exitosamente (Opción B)', [
                'mantenimiento_id' => $mantenimiento->id,
                'fecha_ultimo_servicio' => $mantenimiento->fecha,
                'proxima_fecha_programada' => $nuevaFechaProximo->format('Y-m-d'),
                'intervalo_dias' => $intervaloDias
            ]);

            DB::commit();

            return redirect()->route('mantenimientos.index')
                ->with('success', 'Mantenimiento reprogramado exitosamente. El siguiente servicio está programado para el ' . $nuevaFechaProximo->format('d/m/Y') . '.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al reprogramar mantenimiento desde modal: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Error al reprogramar el mantenimiento.');
        }
    }

    // Marcar mantenimiento como realizado hoy
    public function marcarRealizadoHoy(Mantenimiento $mantenimiento)
    {
        DB::beginTransaction();

        try {
            Log::info('Marcando mantenimiento como realizado hoy', [
                'mantenimiento_id' => $mantenimiento->id,
                'tipo' => $mantenimiento->tipo,
                'carro_id' => $mantenimiento->carro_id,
                'fecha_actual' => $mantenimiento->fecha,
                'proxima_fecha_actual' => $mantenimiento->proximo_mantenimiento
            ]);

            // Calcular el intervalo entre servicios
            $fechaUltimoServicio = Carbon::parse($mantenimiento->fecha);
            $fechaProximaActual = Carbon::parse($mantenimiento->proximo_mantenimiento);
            $intervaloDias = $fechaUltimoServicio->diffInDays($fechaProximaActual);

            // Nueva fecha del último servicio: hoy
            $fechaHoy = Carbon::now();
            // Nueva fecha de próximo mantenimiento: hoy + intervalo
            $nuevaFechaProximo = $fechaHoy->copy()->addDays($intervaloDias);

            // Actualizar el mantenimiento
            $mantenimiento->update([
                'fecha' => $fechaHoy->format('Y-m-d'),
                'proximo_mantenimiento' => $nuevaFechaProximo->format('Y-m-d'),
                'estado' => Mantenimiento::ESTADO_COMPLETADO,
                'notas' => 'Servicio realizado hoy (' . $fechaHoy->format('d/m/Y') . ') - Próximo programado: ' . $nuevaFechaProximo->format('d/m/Y'),
                // Mantener otros campos sin cambios
                'kilometraje_actual' => $mantenimiento->kilometraje_actual,
                'costo' => $mantenimiento->costo,
                'descripcion' => $mantenimiento->descripcion,
                'prioridad' => $mantenimiento->prioridad,
                'dias_anticipacion_alerta' => $mantenimiento->dias_anticipacion_alerta,
                'requiere_aprobacion' => $mantenimiento->requiere_aprobacion,
                'observaciones_alerta' => $mantenimiento->observaciones_alerta,
            ]);

            // Actualizar kilometraje del carro si este es el mantenimiento más reciente
            $carro = $mantenimiento->carro;
            if ($carro && $mantenimiento->kilometraje_actual) {
                $carro->update(['kilometraje' => $mantenimiento->kilometraje_actual]);
                Log::info('Kilometraje del carro actualizado tras marcar como realizado hoy', [
                    'carro_id' => $carro->id,
                    'kilometraje_actualizado' => $mantenimiento->kilometraje_actual
                ]);
            }

            Log::info('Mantenimiento marcado como realizado hoy exitosamente', [
                'mantenimiento_id' => $mantenimiento->id,
                'nueva_fecha_ultimo_servicio' => $fechaHoy->format('Y-m-d'),
                'nueva_fecha_proximo' => $nuevaFechaProximo->format('Y-m-d'),
                'intervalo_dias' => $intervaloDias
            ]);

            DB::commit();

            return redirect()->route('mantenimientos.index')
                ->with('success', 'Mantenimiento marcado como realizado hoy. Próximo servicio programado para el ' . $nuevaFechaProximo->format('d/m/Y') . '.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al marcar mantenimiento como realizado hoy: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Error al marcar el mantenimiento como realizado hoy.');
        }
    }

    // Generar mantenimientos recurrentes (método legacy - ya no se usa)
    public function generarRecurrentes(Request $request)
    {
        // Este método ya no es necesario ya que la lógica está integrada
        // en el proceso de crear y completar mantenimientos
        return redirect()->route('mantenimientos.index')
            ->with('info', 'La generación de mantenimientos recurrentes ahora es automática al crear y completar servicios.');
    }

    // =================== MÉTODOS PRIVADOS ===================

    private function getMantenimientosProximosAVencer($dias = 30)
    {
        $fechaLimite = Carbon::now()->addDays($dias)->format('Y-m-d');
        $fechaHoy = Carbon::now()->format('Y-m-d');

        return Carro::with(['mantenimientos' => function ($query) {
            $query->orderBy('fecha', 'desc')->limit(1);
        }])
            ->get()
            ->map(function ($carro) use ($fechaLimite, $fechaHoy) {
                $ultimoMantenimiento = $carro->mantenimientos->first();

                if (
                    $ultimoMantenimiento &&
                    $ultimoMantenimiento->proximo_mantenimiento &&
                    $ultimoMantenimiento->proximo_mantenimiento <= $fechaLimite &&
                    $ultimoMantenimiento->proximo_mantenimiento >= $fechaHoy
                ) {

                    return [
                        'carro' => $carro,
                        'ultimo_mantenimiento' => $ultimoMantenimiento,
                        'dias_restantes' => round(Carbon::parse($ultimoMantenimiento->proximo_mantenimiento)->diffInDays(Carbon::now()))
                    ];
                }
                return null;
            })
            ->filter()
            ->values();
    }

    private function getMantenimientosVencidos()
    {
        $fechaHoy = Carbon::now()->format('Y-m-d');

        return Carro::with(['mantenimientos' => function ($query) {
            $query->orderBy('fecha', 'desc')->limit(1);
        }])
            ->get()
            ->map(function ($carro) use ($fechaHoy) {
                $ultimoMantenimiento = $carro->mantenimientos->first();

                if (
                    $ultimoMantenimiento &&
                    $ultimoMantenimiento->proximo_mantenimiento &&
                    $ultimoMantenimiento->proximo_mantenimiento < $fechaHoy
                ) {

                    return [
                        'carro' => $carro,
                        'ultimo_mantenimiento' => $ultimoMantenimiento,
                        'dias_vencido' => round(Carbon::now()->diffInDays(Carbon::parse($ultimoMantenimiento->proximo_mantenimiento)))
                    ];
                }
                return null;
            })
            ->filter()
            ->values();
    }

    private function getTiposMantenimiento()
    {
        return [
            'Cambio de aceite',
            'Revisión periódica',
            'Servicio de frenos',
            'Servicio de llantas',
            'Servicio de batería',
            'Servicio de motor',
            'Revisión de luces',
            'Alineación y balanceo',
            'Cambio de filtros',
            'Revisión de transmisión',
            'Otro servicio'
        ];
    }

    // Exportar mantenimientos a CSV
    public function export(Request $request)
    {
        try {
            $query = Mantenimiento::with('carro');

            // Aplicar los mismos filtros que en index
            if ($search = trim($request->input('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('tipo', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%")
                      ->orWhereHas('carro', function ($carroQuery) use ($search) {
                          $carroQuery->where('marca', 'like', "%{$search}%")
                                    ->orWhere('modelo', 'like', "%{$search}%")
                                    ->orWhere('placa', 'like', "%{$search}%");
                      });
                });
            }

            if ($estado = $request->input('estado')) {
                $query->where('estado', $estado);
            }

            if ($tipo = $request->input('tipo')) {
                $query->where('tipo', $tipo);
            }

            if ($carroId = $request->input('carro_id')) {
                $query->where('carro_id', $carroId);
            }

            $mantenimientos = $query->get();

            $filename = 'mantenimientos_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($mantenimientos) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

                fputcsv($file, [
                    'ID',
                    'Vehículo',
                    'Tipo',
                    'Fecha',
                    'Próximo Mantenimiento',
                    'Kilometraje',
                    'Costo',
                    'Estado',
                    'Descripción',
                    'Fecha Creación'
                ]);

                foreach ($mantenimientos as $mantenimiento) {
                    fputcsv($file, [
                        $mantenimiento->id,
                        $mantenimiento->carro ? $mantenimiento->carro->marca . ' ' . $mantenimiento->carro->modelo : 'N/A',
                        $mantenimiento->tipo,
                        $mantenimiento->fecha?->format('d/m/Y'),
                        $mantenimiento->proximo_mantenimiento?->format('d/m/Y'),
                        $mantenimiento->kilometraje_actual,
                        $mantenimiento->costo,
                        $mantenimiento->estado,
                        $mantenimiento->descripcion,
                        $mantenimiento->created_at?->format('d/m/Y H:i:s')
                    ]);
                }
                fclose($file);
            };

            Log::info('Exportación de mantenimientos', ['total' => $mantenimientos->count()]);

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error en exportación de mantenimientos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los mantenimientos.');
        }
    }
}
