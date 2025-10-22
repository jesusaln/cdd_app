<?php

namespace App\Http\Controllers\Api;

use App\Models\Cita;
use App\Models\Tecnico;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class CitaController extends Controller
{
    /**
     * Obtener todas las citas en formato JSON con paginación y filtros.
     */
    public function index(Request $request)
    {
        $query = Cita::with('tecnico', 'cliente');

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tecnico_id')) {
            $query->where('tecnico_id', $request->tecnico_id);
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
        }

        if ($request->filled('busqueda')) {
            $search = $request->busqueda;
            $query->where(function($q) use ($search) {
                $q->where('tipo_servicio', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhere('problema_reportado', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function($clienteQuery) use ($search) {
                      $clienteQuery->where('nombre_razon_social', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tecnico', function($tecnicoQuery) use ($search) {
                      $tecnicoQuery->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        $citas = $query->orderBy('fecha_hora', 'desc')->paginate(15);

        return response()->json([
            'citas' => $citas,
            'meta' => [
                'total' => $citas->total(),
                'per_page' => $citas->perPage(),
                'current_page' => $citas->currentPage(),
                'last_page' => $citas->lastPage(),
            ]
        ]);
    }

    /**
     * Almacenar una nueva cita en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tecnico_id' => 'required|exists:tecnicos,id',
            'cliente_id' => 'required|exists:clientes,id',
            'tipo_servicio' => 'required|string|max:255',
            'fecha_hora' => [
                'required',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
                    $fecha = Carbon::parse($value);
                    if ($fecha->isSunday()) {
                        $fail('No se pueden programar citas los domingos.');
                    }
                    if ($fecha->hour < 8 || $fecha->hour > 18) {
                        $fail('Las citas deben programarse entre las 8:00 AM y 6:00 PM.');
                    }
                }
            ],
            'descripcion' => 'nullable|string|max:1000',
            'problema_reportado' => 'nullable|string|max:1000',
            'prioridad' => 'nullable|string|in:baja,media,alta,urgente',
            'estado' => 'required|string|in:pendiente,en_proceso,completado,cancelado',
            'evidencias' => 'nullable|string|max:2000',
            'foto_equipo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'foto_hoja_servicio' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'foto_identificacion' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        try {
            DB::beginTransaction();

            // Verificar disponibilidad del técnico
            $this->verificarDisponibilidadTecnico(
                $validated['tecnico_id'],
                $validated['fecha_hora']
            );

            // Verificar límite de citas por día para el técnico
            $this->verificarLimiteCitasPorDia(
                $validated['tecnico_id'],
                $validated['fecha_hora']
            );

            // Verificar que el cliente no tenga múltiples citas activas
            $this->verificarCitasClienteActivas(
                $validated['cliente_id'],
                $validated['fecha_hora']
            );

            // Guardar archivos y obtener sus rutas
            $filePaths = $this->saveFiles($request, ['foto_equipo', 'foto_hoja_servicio', 'foto_identificacion']);

            // Crear la cita con los datos validados y las rutas de los archivos
            $cita = Cita::create(array_merge($validated, $filePaths));

            DB::commit();

            // Cargar las relaciones
            $cita->load('cliente', 'tecnico');

            return response()->json([
                'message' => 'Cita creada exitosamente.',
                'cita' => $cita
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cita API: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear la cita. Por favor, intente nuevamente.'
            ], 500);
        }
    }

    /**
     * Obtener detalles de una cita.
     */
    public function show($id)
    {
        try {
            $cita = Cita::with(['cliente', 'tecnico'])->findOrFail($id);
            return response()->json($cita);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener cita API: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener la cita'], 500);
        }
    }

    /**
     * Actualizar una cita existente.
     */
    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);

        $validated = $request->validate([
            'tecnico_id' => 'sometimes|required|exists:tecnicos,id',
            'cliente_id' => 'sometimes|required|exists:clientes,id',
            'tipo_servicio' => 'sometimes|required|string|max:255',
            'fecha_hora' => [
                'sometimes',
                'required',
                'date',
                function ($attribute, $value, $fail) use ($cita) {
                    $fecha = Carbon::parse($value);
                    if ($fecha->isPast() && $cita->estado === Cita::ESTADO_PENDIENTE) {
                        $fail('No se puede programar una cita pendiente en el pasado.');
                    }
                    if ($fecha->isSunday()) {
                        $fail('No se pueden programar citas los domingos.');
                    }
                    if ($fecha->hour < 8 || $fecha->hour > 18) {
                        $fail('Las citas deben programarse entre las 8:00 AM y 6:00 PM.');
                    }
                }
            ],
            'descripcion' => 'nullable|string|max:1000',
            'problema_reportado' => 'nullable|string|max:1000',
            'prioridad' => 'nullable|string|in:baja,media,alta,urgente',
            'estado' => 'sometimes|required|string|in:pendiente,en_proceso,completado,cancelado',
            'evidencias' => 'nullable|string|max:2000',
            'foto_equipo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'foto_hoja_servicio' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'foto_identificacion' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        try {
            DB::beginTransaction();

            // Verificar disponibilidad del técnico si cambió
            if (
                isset($validated['tecnico_id']) &&
                ($validated['tecnico_id'] != $cita->tecnico_id ||
                    isset($validated['fecha_hora']) && $validated['fecha_hora'] != $cita->fecha_hora)
            ) {
                $this->verificarDisponibilidadTecnico(
                    $validated['tecnico_id'],
                    $validated['fecha_hora'] ?? $cita->fecha_hora,
                    $cita->id
                );
            }

            // Guardar archivos y obtener sus rutas (conservando los archivos existentes si no se suben nuevos)
            $filePaths = $this->saveFiles($request, ['foto_equipo', 'foto_hoja_servicio', 'foto_identificacion'], [
                'foto_equipo' => $cita->foto_equipo,
                'foto_hoja_servicio' => $cita->foto_hoja_servicio,
                'foto_identificacion' => $cita->foto_identificacion,
            ]);

            // Actualizar la cita con los datos validados y las rutas de los archivos
            $cita->update(array_merge($validated, $filePaths));

            DB::commit();

            // Cargar las relaciones
            $cita->load('cliente', 'tecnico');

            return response()->json([
                'message' => 'Cita actualizada exitosamente.',
                'cita' => $cita
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cita API: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar la cita. Por favor, intente nuevamente.'
            ], 500);
        }
    }

    /**
     * Eliminar una cita.
     */
    public function destroy($id)
    {
        try {
            $cita = Cita::findOrFail($id);

            DB::beginTransaction();

            // Eliminar archivos asociados
            $archivos = [
                $cita->foto_equipo,
                $cita->foto_hoja_servicio,
                $cita->foto_identificacion
            ];

            foreach ($archivos as $archivo) {
                if ($archivo && Storage::disk('public')->exists($archivo)) {
                    Storage::disk('public')->delete($archivo);
                }
            }

            $cita->delete();

            DB::commit();

            return response()->json(['message' => 'Cita eliminada exitosamente.']);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar cita API: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al eliminar la cita.'
            ], 500);
        }
    }

    /**
     * Verificar disponibilidad del técnico
     */
    private function verificarDisponibilidadTecnico(int $tecnicoId, string $fechaHora, ?int $excludeId = null): void
    {
        $query = Cita::where('tecnico_id', $tecnicoId)
            ->where('fecha_hora', $fechaHora)
            ->where('estado', '!=', Cita::ESTADO_CANCELADO);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'El técnico ya tiene una cita programada en esta fecha y hora.'
            ]);
        }
    }

    /**
     * Método para guardar archivos
     */
    private function saveFiles(Request $request, array $fileFields, $existingFiles = [])
    {
        $filePaths = [];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                try {
                    $file = $request->file($field);

                    // Generar nombre único para evitar conflictos
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $filename = $originalName . '_' . now()->format('YmdHis') . '_' . substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 6) . '.' . $extension;

                    $path = $file->storeAs('citas', $filename, 'public');
                    $filePaths[$field] = $path;

                    // Eliminar el archivo anterior si existe
                    if (!empty($existingFiles[$field])) {
                        Storage::disk('public')->delete($existingFiles[$field]);
                    }
                } catch (\Exception $e) {
                    Log::error("Error al guardar el archivo {$field}: " . $e->getMessage());
                    $filePaths[$field] = $existingFiles[$field] ?? null;
                }
            } else {
                $filePaths[$field] = $existingFiles[$field] ?? null;
            }
        }
        return $filePaths;
    }

    /**
     * Verificar límite de citas por día para un técnico
     */
    private function verificarLimiteCitasPorDia(int $tecnicoId, string $fechaHora): void
    {
        $fecha = Carbon::parse($fechaHora)->toDateString();
        $inicioDia = Carbon::parse($fecha)->startOfDay();
        $finDia = Carbon::parse($fecha)->endOfDay();

        $citasEnDia = Cita::where('tecnico_id', $tecnicoId)
            ->whereBetween('fecha_hora', [$inicioDia, $finDia])
            ->where('estado', '!=', Cita::ESTADO_CANCELADO)
            ->count();

        // Límite de 8 citas por día
        if ($citasEnDia >= 8) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'El técnico ya tiene el máximo de 8 citas programadas para este día.'
            ]);
        }
    }

    /**
     * Verificar que el cliente no tenga múltiples citas activas
     */
    private function verificarCitasClienteActivas(int $clienteId, string $fechaHora): void
    {
        $fecha = Carbon::parse($fechaHora);

        // Verificar si el cliente tiene más de 2 citas activas en los próximos 7 días
        $citasActivas = Cita::where('cliente_id', $clienteId)
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->where('fecha_hora', '>=', now())
            ->where('fecha_hora', '<=', now()->addDays(7))
            ->count();

        if ($citasActivas >= 2) {
            throw ValidationException::withMessages([
                'cliente_id' => 'El cliente ya tiene múltiples citas activas. Complete las citas existentes antes de programar nuevas.'
            ]);
        }

        // Verificar si hay conflicto de horario el mismo día
        $citasMismoDia = Cita::where('cliente_id', $clienteId)
            ->whereDate('fecha_hora', $fecha->toDateString())
            ->where('estado', '!=', Cita::ESTADO_CANCELADO)
            ->where('fecha_hora', '!=', $fechaHora)
            ->count();

        if ($citasMismoDia > 0) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'El cliente ya tiene una cita programada para este día.'
            ]);
        }
    }
}
