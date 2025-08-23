<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class EquipoController extends Controller
{
    /**
     * Muestra una lista de equipos.
     */
    public function index()
    {
        $equipos = Equipo::withTrashed() // Incluye eliminados si usas SoftDeletes
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Equipos/Index', [
            'equipos' => $equipos, // Inertia manda datos a Vue
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo equipo.
     */
    public function create()
    {
        return view('equipos.create');
    }

    /**
     * Almacena un nuevo equipo en la base de datos.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:50|unique:equipos,codigo',
            'nombre' => 'required|string|max:255',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100|unique:equipos,numero_serie',
            'descripcion' => 'nullable|string',
            'especificaciones' => 'nullable|array',
            'precio_renta_mensual' => 'required|numeric|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
            'fecha_adquisicion' => 'nullable|date',
            'estado' => 'required|in:disponible,rentado,mantenimiento,reparacion,dado_baja',
            'condicion' => 'required|in:nuevo,excelente,bueno,regular,malo',
            'ubicacion_fisica' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'accesorios' => 'nullable|array',
            'fecha_garantia' => 'nullable|date',
            'proveedor' => 'nullable|string|max:150',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $equipo = new Equipo($request->all());

        // Guardar JSON como array (Laravel lo maneja automáticamente)
        $equipo->especificaciones = $request->especificaciones;
        $equipo->accesorios = $request->accesorios;

        $equipo->save();

        return redirect()->route('equipos.index')->with('success', 'Equipo creado exitosamente.');
    }

    /**
     * Muestra un equipo específico.
     */
    public function show(Equipo $equipo)
    {
        return view('equipos.show', compact('equipo'));
    }

    /**
     * Muestra el formulario para editar un equipo.
     */
    public function edit(Equipo $equipo)
    {
        return view('equipos.edit', compact('equipo'));
    }

    /**
     * Actualiza un equipo en la base de datos.
     */
    public function update(Request $request, Equipo $equipo)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:50|unique:equipos,codigo,' . $equipo->id,
            'nombre' => 'required|string|max:255',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100|unique:equipos,numero_serie,' . $equipo->id,
            'descripcion' => 'nullable|string',
            'especificaciones' => 'nullable|array',
            'precio_renta_mensual' => 'required|numeric|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
            'fecha_adquisicion' => 'nullable|date',
            'estado' => 'required|in:disponible,rentado,mantenimiento,reparacion,dado_baja',
            'condicion' => 'required|in:nuevo,excelente,bueno,regular,malo',
            'ubicacion_fisica' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'accesorios' => 'nullable|array',
            'fecha_garantia' => 'nullable|date',
            'proveedor' => 'nullable|string|max:150',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $equipo->fill($request->all());

        // Actualizar campos JSON
        $equipo->especificaciones = $request->especificaciones ?? $equipo->especificaciones;
        $equipo->accesorios = $request->accesorios ?? $equipo->accesorios;

        $equipo->save();

        return redirect()->route('equipos.index')->with('success', 'Equipo actualizado exitosamente.');
    }

    /**
     * Elimina (soft delete) un equipo.
     */
    public function destroy(Equipo $equipo)
    {
        $equipo->delete();

        return redirect()->route('equipos.index')->with('success', 'Equipo eliminado correctamente.');
    }

    /**
     * Restaura un equipo eliminado.
     */
    public function restore($id)
    {
        $equipo = Equipo::withTrashed()->findOrFail($id);
        $equipo->restore();

        return redirect()->route('equipos.index')->with('success', 'Equipo restaurado correctamente.');
    }
}
