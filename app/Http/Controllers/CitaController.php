<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Tecnico;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class CitaController extends Controller
{
    /**
     * Mostrar todas las citas con paginación y filtros.
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

        // Datos adicionales para filtros
        $tecnicos = Tecnico::select('id', 'nombre')->get();
        $clientes = Cliente::select('id', 'nombre_razon_social')->get();
        $estados = [
            Cita::ESTADO_PENDIENTE => 'Pendiente',
            Cita::ESTADO_EN_PROCESO => 'En Proceso',
            Cita::ESTADO_COMPLETADO => 'Completado',
            Cita::ESTADO_CANCELADO => 'Cancelado',
        ];

        return Inertia::render('Citas/Index', [
            'citas' => $citas->items(), // Solo los items del array
            'pagination' => [
                'current_page' => $citas->currentPage(),
                'last_page' => $citas->lastPage(),
                'per_page' => $citas->perPage(),
                'total' => $citas->total(),
                'from' => $citas->firstItem(),
                'to' => $citas->lastItem(),
            ],
            'tecnicos' => $tecnicos,
            'clientes' => $clientes,
            'estados' => $estados,
            'filtros' => $request->only(['estado', 'tecnico_id', 'cliente_id', 'fecha_desde', 'fecha_hasta', 'busqueda'])
        ]);
    }

    /**
     * Mostrar formulario para crear una nueva cita.
     */
    public function create()
    {
        $tecnicos = Tecnico::all();
        $clientes = Cliente::select('id', 'nombre_razon_social', 'email', 'telefono')->get();
        return Inertia::render('Citas/Create', ['tecnicos' => $tecnicos, 'clientes' => $clientes]);
    }

    /**
     * Almacenar una nueva cita en la base de datos.
     */
    public function store(Request $request)
    {
        // Validar los datos recibidos con mejoras
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
            'prioridad' => 'nullable|string|in:baja,media,alta,urgente',
            'descripcion' => 'nullable|string|max:1000',
            'tipo_equipo' => 'required|string|max:255',
            'marca_equipo' => 'required|string|max:255',
            'modelo_equipo' => 'required|string|max:255',
            'problema_reportado' => 'nullable|string|max:1000',
            'estado' => 'required|string|in:pendiente,en_proceso,completado,cancelado',
            'evidencias' => 'nullable|string|max:2000',
            'foto_equipo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'foto_hoja_servicio' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'foto_identificacion' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'tecnico_id.required' => 'Debe seleccionar un técnico.',
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'fecha_hora.after' => 'La fecha debe ser posterior a la actual.',
            '*.max:5120' => 'La imagen no debe superar los 5MB.',
        ]);

        try {
            DB::beginTransaction();

            // Verificar disponibilidad del técnico
            $this->verificarDisponibilidadTecnico(
                $validated['tecnico_id'],
                $validated['fecha_hora']
            );

            // Guardar archivos y obtener sus rutas
            $filePaths = $this->saveFiles($request, ['foto_equipo', 'foto_hoja_servicio', 'foto_identificacion']);

            // Crear la cita con los datos validados y las rutas de los archivos
            $cita = Cita::create(array_merge($validated, $filePaths));

            DB::commit();

            return redirect()->route('citas.index')->with('success', 'Cita creada exitosamente.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cita: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear la cita. Por favor, intente nuevamente.');
        }
    }

    /**
     * Mostrar formulario para editar una cita existente.
     */
    public function edit(Cita $cita)
    {
        $tecnicos = Tecnico::all();
        $clientes = Cliente::all();
        return Inertia::render('Citas/Edit', ['cita' => $cita, 'tecnicos' => $tecnicos, 'clientes' => $clientes]);
    }

    /**
     * Actualizar una cita existente en la base de datos.
     */
    public function update(Request $request, Cita $cita)
    {
        // Validar los datos recibidos
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
            'prioridad' => 'nullable|string|in:baja,media,alta,urgente',
            'descripcion' => 'nullable|string|max:1000',
            'tipo_equipo' => 'sometimes|required|string|max:255',
            'marca_equipo' => 'sometimes|required|string|max:255',
            'modelo_equipo' => 'sometimes|required|string|max:255',
            'problema_reportado' => 'nullable|string|max:1000',
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

            return redirect()->route('citas.index')->with('success', 'Cita actualizada exitosamente.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cita: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar la cita. Por favor, intente nuevamente.');
        }
    }

    /**
     * Método mejorado para guardar archivos
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
                $filePaths[$field] = $existingFiles[$field] ?? null; // Conservar el archivo existente
            }
        }
        return $filePaths;
    }

    /**
     * Verificar disponibilidad del técnico
     */
    private function verificarDisponibilidadTecnico(int $tecnicoId, string $fechaHora, ?int $excludeId = null): void
    {
        $query = Cita::where('tecnico_id', $tecnicoId)
            ->where('fecha_hora', $fechaHora)
            ->where('estado', '!=', 'cancelado');

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
     * Eliminar una cita existente.
     */
    public function destroy(Cita $cita)
    {
        try {
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

            return redirect()->route('citas.index')->with('success', 'Cita eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar cita: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar la cita.');
        }
    }

    /**
     * Mostrar detalles de una cita.
     */
    public function show(Cita $cita)
    {
        $cita->load(['cliente', 'tecnico']);

        return Inertia::render('Citas/Show', [
            'cita' => $cita,
        ]);
    }

}
