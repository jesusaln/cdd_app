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
        return Inertia::render('Herramientas/Gestion/Index', [
            'tecnicos' => Tecnico::select('id','nombre','telefono')->orderBy('nombre')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Herramientas/Gestion/Create', [
            'tecnicos' => Tecnico::select('id','nombre')->orderBy('nombre')->get(),
            'herramientas' => Herramienta::select('id','nombre','numero_serie')
                ->whereNull('tecnico_id')
                ->orWhere('estado', Herramienta::ESTADO_DISPONIBLE)
                ->orderBy('nombre')->get(),
        ]);
    }

    public function edit(Tecnico $tecnico)
    {
        $asignadas = Herramienta::select('id','nombre','numero_serie')
            ->where('tecnico_id', $tecnico->id)
            ->orderBy('nombre')->get();
        $disponibles = Herramienta::select('id','nombre','numero_serie')
            ->where(function($q){
                $q->whereNull('tecnico_id')->orWhere('estado', Herramienta::ESTADO_DISPONIBLE);
            })
            ->whereNotIn('id', $asignadas->pluck('id'))
            ->orderBy('nombre')->get();

        return Inertia::render('Herramientas/Gestion/Edit', [
            'tecnico' => $tecnico->only(['id','nombre','telefono']) + ['id' => $tecnico->id],
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

