<?php

namespace App\Http\Controllers;

use App\Models\ComponenteKit;
use Illuminate\Http\Request;

class ComponenteKitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ComponenteKit::query();

        // Filtro por tipo si se especifica
        if ($tipo = $request->input('tipo')) {
            $query->where('tipo', $tipo);
        }

        // Solo componentes disponibles
        $query->where('estado', 'disponible');

        // Ordenar por tipo y nombre
        $query->orderBy('tipo')->orderBy('nombre');

        $componentes = $query->paginate(50);

        return response()->json([
            'data' => $componentes->items(),
            'current_page' => $componentes->currentPage(),
            'last_page' => $componentes->lastPage(),
            'per_page' => $componentes->perPage(),
            'total' => $componentes->total()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
