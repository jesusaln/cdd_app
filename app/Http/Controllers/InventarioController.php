<?php
// app/Http/Controllers/InventarioController.php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventarioController extends Controller
{
    public function index()
    {
        $inventarios = Inventario::with('producto')->get();
        return Inertia::render('Inventario/Index', ['inventarios' => $inventarios]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        Inventario::create($request->all());

        return redirect()->route('inventario.index')->with('success', 'Inventario actualizado.');
    }

    public function update(Request $request, Inventario $inventario)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        $inventario->update($request->all());

        return redirect()->route('inventario.index')->with('success', 'Inventario actualizado.');
    }
}
