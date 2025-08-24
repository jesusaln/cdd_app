<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Equipo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function index(Request $request)
    {
        // Ejemplo de paginado simple para tu índice
        $equipos = Equipo::query()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Equipos/Index', [
            'equipos' => $equipos,
        ]);
    }

    public function create()
    {
        // 👈 AQUÍ ESTABA EL PROBLEMA: devolver Inertia, no view()
        return Inertia::render('Equipos/Create', [
            // Si necesitas catálogos iniciales, pásalos aquí
            'estados' => ['disponible', 'rentado', 'mantenimiento', 'reparacion', 'fuera_servicio'], // ver nota (punto 4)
            'condiciones' => ['nuevo', 'excelente', 'bueno', 'regular', 'malo'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:255', 'unique:equipos,codigo'],
            'nombre' => ['required', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'numero_serie' => ['nullable', 'string', 'max:255', 'unique:equipos,numero_serie'],
            'descripcion' => ['nullable', 'string'],
            'especificaciones' => ['nullable', 'array'],
            'precio_renta_mensual' => ['required', 'numeric', 'min:0'],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'fecha_adquisicion' => ['nullable', 'date'],
            'estado' => ['required', 'in:disponible,rentado,mantenimiento,reparacion,fuera_servicio'],
            'condicion' => ['required', 'in:nuevo,excelente,bueno,regular,malo'],
            'ubicacion_fisica' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
            'accesorios' => ['nullable', 'array'],
            'fecha_garantia' => ['nullable', 'date'],
            'proveedor' => ['nullable', 'string', 'max:255'],
        ]);

        // Si llegan arrays/objetos para JSON:
        $data['especificaciones'] = $data['especificaciones'] ?? null;
        $data['accesorios'] = $data['accesorios'] ?? null;

        $equipo = Equipo::create($data);

        return redirect()->route('equipos.index')->with('success', 'Equipo creado correctamente.');
    }
    // 🔹 EDIT
    public function edit(Equipo $equipo)
    {
        return Inertia::render('Equipos/Edit', [
            'equipo' => [
                'id' => $equipo->id,
                'codigo' => $equipo->codigo,
                'nombre' => $equipo->nombre,
                'marca' => $equipo->marca,
                'modelo' => $equipo->modelo,
                'numero_serie' => $equipo->numero_serie,
                'descripcion' => $equipo->descripcion,
                'especificaciones' => $equipo->especificaciones, // cast array
                'precio_renta_mensual' => $equipo->precio_renta_mensual,
                'precio_compra' => $equipo->precio_compra,
                'fecha_adquisicion' => optional($equipo->fecha_adquisicion)->format('Y-m-d'),
                'estado' => $equipo->estado,
                'condicion' => $equipo->condicion,
                'ubicacion_fisica' => $equipo->ubicacion_fisica,
                'notas' => $equipo->notas,
                'accesorios' => $equipo->accesorios, // cast array
                'fecha_garantia' => optional($equipo->fecha_garantia)->format('Y-m-d'),
                'proveedor' => $equipo->proveedor,
                'created_at' => $equipo->created_at,
                'updated_at' => $equipo->updated_at,
            ],
            'estados' => ['disponible', 'rentado', 'mantenimiento', 'reparacion', 'fuera_servicio'],
            'condiciones' => ['nuevo', 'excelente', 'bueno', 'regular', 'malo'],
        ]);
    }

    // 🔹 UPDATE
    public function update(Request $request, Equipo $equipo)
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:255', Rule::unique('equipos', 'codigo')->ignore($equipo->id)],
            'nombre' => ['required', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'numero_serie' => ['nullable', 'string', 'max:255', Rule::unique('equipos', 'numero_serie')->ignore($equipo->id)],
            'descripcion' => ['nullable', 'string'],
            'especificaciones' => ['nullable', 'array'],
            'precio_renta_mensual' => ['required', 'numeric', 'min:0'],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'fecha_adquisicion' => ['nullable', 'date'],
            'estado' => ['required', Rule::in(['disponible', 'rentado', 'mantenimiento', 'reparacion', 'fuera_servicio'])],
            'condicion' => ['required', Rule::in(['nuevo', 'excelente', 'bueno', 'regular', 'malo'])],
            'ubicacion_fisica' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
            'accesorios' => ['nullable', 'array'],
            'fecha_garantia' => ['nullable', 'date'],
            'proveedor' => ['nullable', 'string', 'max:255'],
        ]);

        $data['especificaciones'] = $data['especificaciones'] ?? null;
        $data['accesorios'] = $data['accesorios'] ?? null;

        $equipo->update($data);

        return redirect()->route('equipos.index')->with('success', 'Equipo actualizado correctamente.');
    }
}
