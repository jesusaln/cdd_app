<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use App\Models\Carro;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class MantenimientoController extends Controller
{
    // Mostrar lista de mantenimientos con alertas básicas
    public function index()
    {
        // Obtener mantenimientos con relación al carro
        $mantenimientos = Mantenimiento::with('carro')
            ->orderBy('fecha', 'desc')
            ->get();

        // Obtener mantenimientos próximos a vencer (30 días)
        $proximosAVencer = $this->getMantenimientosProximosAVencer(30);

        // Obtener carros para el filtro
        $carros = Carro::orderBy('marca', 'asc')->orderBy('modelo', 'asc')->get();

        return Inertia::render('Mantenimientos/Index', [
            'mantenimientos' => $mantenimientos,
            'proximosAVencer' => $proximosAVencer,
            'carros' => $carros,
        ]);
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
        ], [
            'carro_id.required' => 'Debes seleccionar un vehículo.',
            'tipo.required' => 'El tipo de servicio es obligatorio.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'proximo_mantenimiento.after' => 'El próximo mantenimiento debe ser posterior a la fecha actual.',
            'kilometraje_actual.required' => 'El kilometraje es obligatorio.',
            'kilometraje_actual.min' => 'El kilometraje no puede ser negativo.',
            'costo.max' => 'El costo no puede exceder $999,999.99',
            'notas.max' => 'Las notas no pueden exceder 1000 caracteres.',
        ]);

        DB::beginTransaction();

        try {
            // Obtener el carro asociado
            $carro = Carro::findOrFail($validated['carro_id']);

            // Validar que el kilometraje sea coherente
            if ($validated['kilometraje_actual'] < $carro->kilometraje) {
                return back()->withErrors([
                    'kilometraje_actual' => "El kilometraje debe ser mayor o igual al actual del carro ({$carro->kilometraje} km)."
                ])->withInput();
            }

            // Actualizar el kilometraje del carro
            $carro->update(['kilometraje' => $validated['kilometraje_actual']]);

            // Crear el registro de mantenimiento
            Mantenimiento::create([
                'carro_id' => $validated['carro_id'],
                'tipo' => $validated['tipo'],
                'fecha' => $validated['fecha'],
                'proximo_mantenimiento' => $validated['proximo_mantenimiento'],
                'notas' => $validated['notas'],
                'kilometraje_actual' => $validated['kilometraje_actual'],
                'costo' => $validated['costo'] ?? 0,
                'descripcion' => $validated['descripcion'],
                'estado' => Mantenimiento::ESTADO_COMPLETADO,
            ]);

            DB::commit();

            return redirect()->route('mantenimientos.index')
                ->with('success', 'Mantenimiento registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'general' => 'Error al procesar el mantenimiento. Intenta nuevamente.'
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
                        'dias_restantes' => Carbon::parse($ultimoMantenimiento->proximo_mantenimiento)->diffInDays(Carbon::now())
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
                        'dias_vencido' => Carbon::now()->diffInDays(Carbon::parse($ultimoMantenimiento->proximo_mantenimiento))
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
}
