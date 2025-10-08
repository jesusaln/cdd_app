<?php

namespace App\Http\Controllers;

use App\Models\Herramienta;
use App\Models\Tecnico;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GestionHerramientasController extends Controller
{
    public function index()
    {
        $tecnicos = Tecnico::with(['herramientas' => function ($query) {
            $query->select('id', 'nombre', 'numero_serie', 'estado', 'foto', 'categoria_id', 'tecnico_id', 'fecha_ultimo_mantenimiento', 'requiere_mantenimiento')
                  ->with('categoriaHerramienta:id,nombre');
        }])
        ->select('id', 'nombre', 'telefono', 'email')
        ->orderBy('nombre')
        ->get()
        ->map(function ($tecnico) {
            return [
                'id' => $tecnico->id,
                'nombre' => $tecnico->nombre,
                'telefono' => $tecnico->telefono,
                'email' => $tecnico->email,
                'herramientas' => $tecnico->herramientas->map(function ($herramienta) {
                    return [
                        'id' => $herramienta->id,
                        'nombre' => $herramienta->nombre,
                        'numero_serie' => $herramienta->numero_serie,
                        'estado' => $herramienta->estado,
                        'foto' => $herramienta->foto,
                        'categoria_herramienta' => $herramienta->categoriaHerramienta,
                        'fecha_ultimo_mantenimiento' => $herramienta->fecha_ultimo_mantenimiento,
                        'requiere_mantenimiento' => $herramienta->requiere_mantenimiento,
                        'necesita_mantenimiento' => $herramienta->necesitaMantenimiento(),
                    ];
                }),
            ];
        });

        return Inertia::render('Herramientas/Gestion/Index', [
            'tecnicos' => $tecnicos,
        ]);
    }

    public function create()
    {
        return Inertia::render('Herramientas/Gestion/Create', [
            'tecnicos' => Tecnico::select('id','nombre','telefono')->orderBy('nombre')->get(),
            'herramientas' => Herramienta::where(function($q) {
                    $q->whereNull('tecnico_id')
                      ->orWhere('estado', Herramienta::ESTADO_DISPONIBLE);
                })
                ->with(['categoriaHerramienta:id,nombre'])
                ->select('id', 'nombre', 'numero_serie', 'estado', 'foto', 'categoria_id')
                ->orderBy('nombre')
                ->get()
                ->map(function ($herramienta) {
                    return [
                        'id' => $herramienta->id,
                        'nombre' => $herramienta->nombre,
                        'numero_serie' => $herramienta->numero_serie,
                        'estado' => $herramienta->estado,
                        'foto' => $herramienta->foto,
                        'categoria_herramienta' => $herramienta->categoriaHerramienta,
                    ];
                }),
        ]);
    }

    public function edit(Tecnico $tecnico)
    {
        $asignadas = Herramienta::where('tecnico_id', $tecnico->id)
            ->with(['categoriaHerramienta:id,nombre'])
            ->select('id', 'nombre', 'numero_serie', 'estado', 'foto', 'categoria_id', 'fecha_ultimo_mantenimiento', 'requiere_mantenimiento')
            ->orderBy('nombre')
            ->get()
            ->map(function ($herramienta) {
                return [
                    'id' => $herramienta->id,
                    'nombre' => $herramienta->nombre,
                    'numero_serie' => $herramienta->numero_serie,
                    'estado' => $herramienta->estado,
                    'foto' => $herramienta->foto,
                    'categoria_herramienta' => $herramienta->categoriaHerramienta,
                    'fecha_ultimo_mantenimiento' => $herramienta->fecha_ultimo_mantenimiento,
                    'requiere_mantenimiento' => $herramienta->requiere_mantenimiento,
                    'necesita_mantenimiento' => $herramienta->necesitaMantenimiento(),
                ];
            });

        $disponibles = Herramienta::where(function($q) use ($tecnico) {
                $q->whereNull('tecnico_id')
                  ->orWhere('estado', Herramienta::ESTADO_DISPONIBLE)
                  ->orWhere('tecnico_id', $tecnico->id);
            })
            ->whereNotIn('id', $asignadas->pluck('id'))
            ->with(['categoriaHerramienta:id,nombre'])
            ->select('id', 'nombre', 'numero_serie', 'estado', 'foto', 'categoria_id')
            ->orderBy('nombre')
            ->get()
            ->map(function ($herramienta) {
                return [
                    'id' => $herramienta->id,
                    'nombre' => $herramienta->nombre,
                    'numero_serie' => $herramienta->numero_serie,
                    'estado' => $herramienta->estado,
                    'foto' => $herramienta->foto,
                    'categoria_herramienta' => $herramienta->categoriaHerramienta,
                ];
            });

        return Inertia::render('Herramientas/Gestion/Edit', [
            'tecnico' => [
                'id' => $tecnico->id,
                'nombre' => $tecnico->nombre,
                'telefono' => $tecnico->telefono,
                'email' => $tecnico->email,
            ],
            'asignadas' => $asignadas,
            'disponibles' => $disponibles,
        ]);
    }

    public function asignar(Request $request)
    {
        $data = $request->validate([
            'tecnico_id' => 'required|exists:tecnicos,id',
            'herramientas' => 'array',
            'herramientas.*' => 'integer|exists:herramientas,id',
        ]);

        $ids = $data['herramientas'] ?? [];
        if (!empty($ids)) {
            Herramienta::whereIn('id', $ids)->update([
                'tecnico_id' => $data['tecnico_id'],
                'estado' => Herramienta::ESTADO_ASIGNADA,
            ]);
        }

        return redirect()->route('herramientas.gestion.index')->with('success', 'Herramientas asignadas');
    }

    public function update(Request $request, Tecnico $tecnico)
    {
        $data = $request->validate([
            'asignadas' => 'array',
            'asignadas.*' => 'integer|exists:herramientas,id',
        ]);

        $deseadas = collect($data['asignadas'] ?? []);
        $actuales = Herramienta::where('tecnico_id', $tecnico->id)->pluck('id');

        $aRemover = $actuales->diff($deseadas);
        $aAgregar = $deseadas->diff($actuales);

        if ($aRemover->isNotEmpty()) {
            Herramienta::whereIn('id', $aRemover)->update([
                'tecnico_id' => null,
                'estado' => Herramienta::ESTADO_DISPONIBLE,
            ]);
        }
        if ($aAgregar->isNotEmpty()) {
            Herramienta::whereIn('id', $aAgregar)->update([
                'tecnico_id' => $tecnico->id,
                'estado' => Herramienta::ESTADO_ASIGNADA,
            ]);
        }

        return redirect()->route('herramientas.gestion.edit', $tecnico->id)->with('success', 'Asignaciones actualizadas');
    }
}

