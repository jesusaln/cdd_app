<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Herramienta;
use App\Models\Tecnico;
use App\Models\CategoriaHerramienta;
use App\Models\AsignacionMasiva;
use App\Models\ResponsabilidadHerramienta;
use App\Models\HistorialHerramienta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HerramientaController extends Controller
{
    /**
     * Muestra una lista de herramientas con paginación y relaciones precargadas.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Construir query con filtros
        $query = Herramienta::with([
            'tecnico',
            'categoriaHerramienta',
            'detallesAsignacionesMasivas.asignacionMasiva',
            'estados' => function($q) { $q->latest('fecha_inspeccion'); }
        ]);

        // Filtro por búsqueda
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('numero_serie', 'like', "%{$search}%")
                    ->orWhereHas('tecnico', function ($tq) use ($search) {
                        $tq->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                    });
            });
        }

        // Filtro por estado (asignada/sin_asignar)
        if ($request->has('filtro_estado')) {
            $estado = $request->input('filtro_estado');
            if ($estado === 'asignada') {
                $query->whereNotNull('tecnico_id');
            } elseif ($estado === 'sin_asignar') {
                $query->whereNull('tecnico_id');
            }
        }

        // Paginación del lado del servidor
        $perPage = min((int) $request->input('per_page', 10), 50);
        $herramientas = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->query());

        // Estadísticas mejoradas
        $totalHerramientas = Herramienta::count();
        $herramientasAsignadas = Herramienta::whereNotNull('tecnico_id')->count();
        $herramientasSinAsignar = $totalHerramientas - $herramientasAsignadas;
        $herramientasEnAsignacionMasiva = Herramienta::whereHas('detallesAsignacionesMasivas', function($q) {
            $q->where('estado_individual', 'asignada')
              ->whereHas('asignacionMasiva', function($aq) {
                  $aq->where('estado', 'activa');
              });
        })->count();

        $tecnicos = Tecnico::select('id', 'nombre', 'apellido')->get();
        $categorias = CategoriaHerramienta::activas()->select('id', 'nombre', 'slug')->get();

        return Inertia::render('Herramientas/Index', [
            'herramientas' => $herramientas,
            'tecnicos' => $tecnicos,
            'categorias' => $categorias,
            'stats' => [
                'total' => $totalHerramientas,
                'asignadas' => $herramientasAsignadas,
                'sin_asignar' => $herramientasSinAsignar,
                'en_asignacion_masiva' => $herramientasEnAsignacionMasiva,
            ],
            'filters' => $request->only(['search', 'filtro_estado']),
            'sorting' => ['sort_by' => 'created_at', 'sort_direction' => 'desc'],
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva herramienta.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Herramientas/Create', [
            'tecnicos' => Tecnico::select('id', 'nombre', 'apellido')->orderBy('nombre')->get(),
            'categorias' => CategoriaHerramienta::activas()->select('id', 'nombre', 'slug')->get(),
        ]);
    }

    /**
     * Almacena una nueva herramienta en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'numero_serie' => 'required|string|max:255|unique:herramientas,numero_serie',
                'categoria_id' => 'required|exists:categoria_herramientas,id',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'tecnico_id' => 'nullable|exists:tecnicos,id',
                'vida_util_meses' => 'nullable|integer|min:1|max:120',
                'costo_reemplazo' => 'nullable|numeric|min:0',
                'requiere_mantenimiento' => 'nullable|boolean',
                'dias_para_mantenimiento' => 'nullable|integer|min:1|max:365',
                'descripcion' => 'nullable|string|max:1000',
            ]);

            if ($request->hasFile('foto')) {
                $validated['foto'] = $this->storeFoto($request->file('foto'));
            }

            $herramienta = Herramienta::create($validated);

            return redirect()->route('herramientas.index')
                ->with('success', 'Herramienta "' . $herramienta->nombre . '" creada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear herramienta: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la herramienta: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para editar una herramienta existente.
     *
     * @param  \App\Models\Herramienta  $herramienta
     * @return \Inertia\Response
     */
    public function edit(Herramienta $herramienta)
    {
        return Inertia::render('Herramientas/Edit', [
            'herramienta' => $this->addFotoUrl($herramienta->load('tecnico', 'categoriaHerramienta')),
            'tecnicos' => Tecnico::select('id', 'nombre', 'apellido')->orderBy('nombre')->get(),
            'categorias' => CategoriaHerramienta::activas()->select('id', 'nombre', 'slug')->get(),
        ]);
    }

    /**
     * Actualiza una herramienta existente en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Herramienta  $herramienta
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Herramienta $herramienta)
    {
        try {
            Log::info('Solicitud recibida en update:', [
                'input' => $request->all(),
                'files' => $request->files->all(),
                'hasFile_foto' => $request->hasFile('foto'),
                'file_details' => $request->hasFile('foto') ? [
                    'name' => $request->file('foto')->getClientOriginalName(),
                    'size' => $request->file('foto')->getSize(),
                    'mime' => $request->file('foto')->getMimeType(),
                    'valid' => $request->file('foto')->isValid()
                ] : 'No file uploaded',
                'remove_foto' => $request->input('remove_foto'),
                'current_foto' => $herramienta->foto
            ]);

            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|max:255',
                'numero_serie' => 'sometimes|required|string|max:255|unique:herramientas,numero_serie,' . $herramienta->id,
                'categoria_id' => 'sometimes|required|exists:categoria_herramientas,id',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'tecnico_id' => 'nullable|exists:tecnicos,id',
                'vida_util_meses' => 'nullable|integer|min:1|max:120',
                'costo_reemplazo' => 'nullable|numeric|min:0',
                'requiere_mantenimiento' => 'nullable|boolean',
                'dias_para_mantenimiento' => 'nullable|integer|min:1|max:365',
                'descripcion' => 'nullable|string|max:1000',
                'remove_foto' => 'sometimes|boolean',
            ]);

            if ($request->hasFile('foto')) {
                if ($herramienta->foto) {
                    $deleted = Storage::disk('public')->delete($herramienta->foto);
                    Log::info('Foto anterior eliminada:', ['path' => $herramienta->foto, 'deleted' => $deleted]);
                }
                $validated['foto'] = $this->storeFoto($request->file('foto'));
                Log::info('Nueva foto guardada:', ['path' => $validated['foto']]);
            } elseif ($request->input('remove_foto') === '1') {
                if ($herramienta->foto) {
                    $deleted = Storage::disk('public')->delete($herramienta->foto);
                    Log::info('Foto eliminada por remove_foto:', ['path' => $herramienta->foto, 'deleted' => $deleted]);
                }
                $validated['foto'] = null;
            } else {
                Log::info('Ningún cambio en la foto');
                unset($validated['foto']);
            }

            $updated = $herramienta->update($validated);
            Log::info('Resultado de la actualización:', ['updated' => $updated, 'validated_data' => $validated]);

            $herramienta->refresh();
            Log::info('Herramienta después de actualizar:', $herramienta->toArray());

            return redirect()->route('herramientas.index')
                ->with('success', 'Herramienta "' . $herramienta->nombre . '" actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar herramienta:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la herramienta: ' . $e->getMessage());
        }
    }


    /**
     * Elimina una herramienta de la base de datos.
     *
     * @param  \App\Models\Herramienta  $herramienta
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Herramienta $herramienta)
    {
        try {
            $nombre = $herramienta->nombre;

            if ($herramienta->foto) {
                Storage::disk('public')->delete($herramienta->foto);
            }

            $herramienta->delete();

            return redirect()->route('herramientas.index')
                ->with('success', 'Herramienta "' . $nombre . '" eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar herramienta: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al eliminar la herramienta: ' . $e->getMessage());
        }
    }

    /**
     * Muestra los detalles de una herramienta específica en formato JSON.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $herramienta = Herramienta::with('tecnico', 'categoriaHerramienta')->findOrFail($id);
            return response()->json($this->addFotoUrl($herramienta));
        } catch (\Exception $e) {
            Log::error('Error al mostrar herramienta: ' . $e->getMessage());
            return response()->json(['error' => 'Herramienta no encontrada'], 404);
        }
    }

    /**
     * Agrega la URL completa de la foto al modelo de herramienta.
     *
     * @param  \App\Models\Herramienta  $herramienta
     * @return \App\Models\Herramienta
     */
    private function addFotoUrl($herramienta)
    {
        $herramienta->foto_url = $herramienta->foto ? asset('storage/' . $herramienta->foto) : null;
        return $herramienta;
    }

    /**
     * Asigna una herramienta a un técnico.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Herramienta  $herramienta
     * @return \Illuminate\Http\RedirectResponse
     */
    public function asignar(Request $request, Herramienta $herramienta)
    {
        try {
            $validated = $request->validate([
                'tecnico_id' => 'required|exists:tecnicos,id',
                'observaciones' => 'nullable|string|max:500',
            ]);

            // Verificar que la herramienta no esté ya asignada
            if ($herramienta->tecnico_id) {
                return redirect()->back()
                    ->with('error', 'La herramienta ya está asignada a otro técnico.');
            }

            // Verificar si la herramienta está en una asignación masiva
            if ($herramienta->estaEnAsignacionMasiva()) {
                return redirect()->back()
                    ->with('error', 'La herramienta está en una asignación masiva activa. Use el sistema de asignaciones masivas para gestionarla.');
            }

            DB::beginTransaction();

            $herramienta->update([
                'tecnico_id' => $validated['tecnico_id'],
                'estado' => 'asignada',
                'fecha_asignacion' => now(),
            ]);

            // Crear registro en historial
            HistorialHerramienta::create([
                'herramienta_id' => $herramienta->id,
                'tecnico_id' => $validated['tecnico_id'],
                'fecha_asignacion' => now(),
                'asignado_por' => Auth::id(),
                'observaciones_asignacion' => $validated['observaciones'] ?? 'Asignación individual',
                'tipo_asignacion' => 'individual'
            ]);

            // Actualizar responsabilidades del técnico
            ResponsabilidadHerramienta::actualizarParaTecnico($validated['tecnico_id']);

            DB::commit();

            return redirect()->route('herramientas.index')
                ->with('success', 'Herramienta "' . $herramienta->nombre . '" asignada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al asignar herramienta: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al asignar la herramienta: ' . $e->getMessage());
        }
    }

    /**
     * Recibe una herramienta de un técnico (la desasigna).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Herramienta  $herramienta
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recibir(Request $request, Herramienta $herramienta)
    {
        try {
            $validated = $request->validate([
                'observaciones' => 'nullable|string|max:500',
            ]);

            // Verificar que la herramienta esté asignada
            if (!$herramienta->tecnico_id) {
                return redirect()->back()
                    ->with('error', 'La herramienta no está asignada a ningún técnico.');
            }

            // Verificar si la herramienta está en una asignación masiva
            if ($herramienta->estaEnAsignacionMasiva()) {
                return redirect()->back()
                    ->with('error', 'La herramienta está en una asignación masiva activa. Use el sistema de asignaciones masivas para gestionarla.');
            }

            DB::beginTransaction();

            $tecnicoAnterior = $herramienta->tecnico_id;

            $herramienta->update([
                'tecnico_id' => null,
                'estado' => 'disponible',
                'fecha_recepcion' => now(),
            ]);

            // Completar registro en historial
            $historial = HistorialHerramienta::where('herramienta_id', $herramienta->id)
                                           ->where('tipo_asignacion', 'individual')
                                           ->whereNull('fecha_devolucion')
                                           ->latest('fecha_asignacion')
                                           ->first();

            if ($historial) {
                $historial->update([
                    'fecha_devolucion' => now(),
                    'recibido_por' => Auth::id(),
                    'observaciones_devolucion' => $validated['observaciones'] ?? 'Recepción individual',
                    'motivo_devolucion' => HistorialHerramienta::MOTIVO_DEVOLUCION_NORMAL,
                    'duracion_dias' => $historial->fecha_asignacion->diffInDays(now())
                ]);
            }

            // Actualizar responsabilidades del técnico
            if ($tecnicoAnterior) {
                ResponsabilidadHerramienta::actualizarParaTecnico($tecnicoAnterior);
            }

            DB::commit();

            return redirect()->route('herramientas.index')
                ->with('success', 'Herramienta "' . $herramienta->nombre . '" recibida correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al recibir herramienta: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al recibir la herramienta: ' . $e->getMessage());
        }
    }

    /**
     * Almacena una foto en el sistema de archivos y devuelve su ruta.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string
     * @throws \Exception
     */
    private function storeFoto($file)
    {
        $path = $file->store('herramientas', 'public');
        Log::info('Ruta generada por storeFoto: ' . $path);
        return $path;
    }
}
