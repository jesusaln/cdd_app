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
use Exception;

class CitaController extends Controller
{
    /**
     * Mostrar todas las citas con paginación y filtros.
     */
    public function index(Request $request)
    {
        try {
            $query = Cita::with('tecnico', 'cliente');

            // Filtros de búsqueda
            if ($s = trim((string) $request->input('search', ''))) {
                $query->where(function ($w) use ($s) {
                    $w->where('tipo_servicio', 'like', "%{$s}%")
                        ->orWhere('descripcion', 'like', "%{$s}%")
                        ->orWhere('problema_reportado', 'like', "%{$s}%")
                        ->orWhereHas('cliente', function($clienteQuery) use ($s) {
                            $clienteQuery->where('nombre_razon_social', 'like', "%{$s}%");
                        })
                        ->orWhereHas('tecnico', function($tecnicoQuery) use ($s) {
                            $tecnicoQuery->where('nombre', 'like', "%{$s}%");
                        });
                });
            }

            // Filtros adicionales
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


            // Ordenamiento por estado primero, luego por fecha
            $query->orderByRaw("
                CASE
                    WHEN estado = 'en_proceso' THEN 1
                    WHEN estado = 'pendiente' THEN 2
                    WHEN estado = 'completado' THEN 3
                    WHEN estado = 'cancelado' THEN 4
                    ELSE 999
                END ASC
            ")->orderBy('fecha_hora', 'asc');

            // Paginación
            $citas = $query->paginate(10)->appends($request->query());

            // Estadísticas por estado de cita
            $citasCount = Cita::count();
            $citasPendientes = Cita::where('estado', Cita::ESTADO_PENDIENTE)->count();
            $citasEnProceso = Cita::where('estado', Cita::ESTADO_EN_PROCESO)->count();
            $citasCompletadas = Cita::where('estado', Cita::ESTADO_COMPLETADO)->count();
            $citasCanceladas = Cita::where('estado', Cita::ESTADO_CANCELADO)->count();

            // Datos adicionales para filtros
            $tecnicos = Tecnico::select('id', 'nombre')->get();
            $clientes = Cliente::select('id', 'nombre_razon_social')->get();
            $estados = [
                Cita::ESTADO_PENDIENTE => 'Pendiente',
                Cita::ESTADO_EN_PROCESO => 'En Proceso',
                Cita::ESTADO_COMPLETADO => 'Completado',
                Cita::ESTADO_CANCELADO => 'Cancelado',
            ];

            // Citas finalizadas (completadas y canceladas) para la segunda tabla
            $queryFinalizadas = Cita::with('tecnico', 'cliente')
                ->whereIn('estado', ['completado', 'cancelado'])
                ->orderByRaw("
                    CASE
                        WHEN estado = 'completado' THEN 1
                        WHEN estado = 'cancelado' THEN 2
                        ELSE 999
                    END ASC
                ")
                ->orderBy('fecha_hora', 'desc');

            $citasFinalizadas = $queryFinalizadas->paginate(
                $request->get('per_page_finalizadas', 10),
                ['*'],
                'page_finalizadas',
                $request->get('page_finalizadas', 1)
            )->appends($request->query());

            return Inertia::render('Citas/Index', [
                'citas' => $citas,
                'citasFinalizadas' => $citasFinalizadas,
                'stats' => [
                    'total' => $citasCount,
                    'pendientes' => $citasPendientes,
                    'en_proceso' => $citasEnProceso,
                    'completadas' => $citasCompletadas,
                    'canceladas' => $citasCanceladas,
                ],
                'tecnicos' => $tecnicos,
                'clientes' => $clientes,
                'estados' => $estados,
                'filters' => $request->only(['search', 'estado', 'tecnico_id', 'cliente_id', 'fecha_desde', 'fecha_hasta']),
                'sorting' => ['sort_by' => 'estado', 'sort_direction' => 'asc'],
            ]);
        } catch (Exception $e) {
            Log::error('Error en CitaController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la lista de citas.');
        }
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


    public function export(Request $request)
    {
        try {
            $query = Cita::with('tecnico', 'cliente');

            // Aplicar los mismos filtros que en index
            if ($s = trim((string) $request->input('search', ''))) {
                $query->where(function ($w) use ($s) {
                    $w->where('tipo_servicio', 'like', "%{$s}%")
                        ->orWhere('descripcion', 'like', "%{$s}%")
                        ->orWhere('problema_reportado', 'like', "%{$s}%")
                        ->orWhereHas('cliente', function($clienteQuery) use ($s) {
                            $clienteQuery->where('nombre_razon_social', 'like', "%{$s}%");
                        })
                        ->orWhereHas('tecnico', function($tecnicoQuery) use ($s) {
                            $tecnicoQuery->where('nombre', 'like', "%{$s}%");
                        });
                });
            }

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


            $citas = $query->get();

            $filename = 'citas_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($citas) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($file, [
                    'ID',
                    'Cliente',
                    'Técnico',
                    'Tipo Servicio',
                    'Fecha y Hora',
                    'Estado',
                    'Prioridad',
                    'Tipo Equipo',
                    'Marca',
                    'Modelo',
                    'Fecha Creación'
                ]);

                foreach ($citas as $cita) {
                    fputcsv($file, [
                        $cita->id,
                        $cita->cliente?->nombre_razon_social ?? 'N/A',
                        $cita->tecnico?->nombre ?? 'N/A',
                        $cita->tipo_servicio,
                        $cita->fecha_hora?->format('d/m/Y H:i:s'),
                        $cita->estado,
                        $cita->prioridad ?? 'N/A',
                        $cita->tipo_equipo,
                        $cita->marca_equipo,
                        $cita->modelo_equipo,
                        $cita->created_at?->format('d/m/Y H:i:s')
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('Error en exportación de citas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar las citas.');
        }
    }
}
