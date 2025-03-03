<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Herramienta;
use App\Models\Tecnico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class HerramientaController extends Controller
{
    /**
     * Muestra una lista de todas las herramientas con paginación y relaciones precargadas.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $herramientas = Herramienta::with('tecnico')->get()->map(function ($herramienta) {
            if ($herramienta->foto) {
                $herramienta->foto = asset('storage/' . $herramienta->foto);
            }
            return $herramienta;
        });

        $tecnicos = Tecnico::select('id', 'nombre', 'apellido')->get();

        return Inertia::render('Herramientas/Index', [
            'herramientas' => $herramientas,
            'tecnicos' => $tecnicos,
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
                'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'tecnico_id' => 'nullable|exists:tecnicos,id',
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
            'herramienta' => $this->addFotoUrl($herramienta->load('tecnico')),
            'tecnicos' => Tecnico::select('id', 'nombre', 'apellido')->orderBy('nombre')->get(),
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
                'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'tecnico_id' => 'nullable|exists:tecnicos,id',
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
            $herramienta = Herramienta::with('tecnico')->findOrFail($id);
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
