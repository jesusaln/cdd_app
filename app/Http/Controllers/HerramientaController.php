<?php

namespace App\Http\Controllers;

use App\Models\Herramienta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HerramientaController extends Controller
{
    public function index(Request $request)
    {
        $search = (string) $request->query('search', '');

        $query = Herramienta::query()
            ->select('id', 'nombre', 'numero_serie', 'estado', 'foto', 'created_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('numero_serie', 'like', "%{$search}%");
            });
        }

        $herramientas = $query->orderByDesc('created_at')->paginate(12)->withQueryString();

        return Inertia::render('Herramientas/Index', [
            'herramientas' => $herramientas,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Herramientas/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'numero_serie' => 'nullable|string|max:255',
            'estado' => 'nullable|string|in:disponible,asignada,mantenimiento,baja,perdida',
            'descripcion' => 'nullable|string',
            'foto' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('herramientas', 'public');
        }

        // Estado por defecto
        if (empty($data['estado'])) {
            $data['estado'] = Herramienta::ESTADO_DISPONIBLE;
        }

        Herramienta::create($data);

        return redirect()->route('herramientas.index')->with('success', 'Herramienta creada correctamente');
    }

    public function edit(Herramienta $herramienta)
    {
        return Inertia::render('Herramientas/Edit', [
            'herramienta' => $herramienta->only(['id','nombre','numero_serie','estado','descripcion','foto'])
        ]);
    }

    public function show(Herramienta $herramienta)
    {
        return Inertia::render('Herramientas/Show', [
            'herramienta' => [
                'id' => $herramienta->id,
                'nombre' => $herramienta->nombre,
                'numero_serie' => $herramienta->numero_serie,
                'estado' => $herramienta->estado,
                'descripcion' => $herramienta->descripcion,
                'foto' => $herramienta->foto,
                'created_at' => $herramienta->created_at,
            ],
        ]);
    }

    public function update(Request $request, Herramienta $herramienta)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'numero_serie' => 'nullable|string|max:255',
            'estado' => 'nullable|string|in:disponible,asignada,mantenimiento,baja,perdida',
            'descripcion' => 'nullable|string',
            'foto' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('foto')) {
            if ($herramienta->foto) {
                Storage::disk('public')->delete($herramienta->foto);
            }
            $data['foto'] = $request->file('foto')->store('herramientas', 'public');
        }

        $herramienta->update($data);

        return redirect()->route('herramientas.index')->with('success', 'Herramienta actualizada correctamente');
    }

    public function destroy(Herramienta $herramienta)
    {
        if ($herramienta->foto) {
            Storage::disk('public')->delete($herramienta->foto);
        }
        $herramienta->delete();
        return redirect()->route('herramientas.index')->with('success', 'Herramienta eliminada correctamente');
    }
}
