<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmpresasController extends Controller
{
    // Mostrar el formulario y los datos de la empresa
    public function index()
    {
        $empresa = Empresa::first(); // Obtiene la primera empresa registrada
        return Inertia::render('Empresas/Index', ['empresa' => $empresa]);
    }

    // Guardar o actualizar los datos de la empresa
    public function store(Request $request)
    {
        $request->validate([
            'nombre_razon_social' => 'required|string|max:255',
            'tipo_persona' => 'required|string|max:255',
            'rfc' => 'required|string|max:13',
            'regimen_fiscal' => 'required|string|max:255',
            'uso_cfdi' => 'required|string|max:255',
            'email' => 'required|email|unique:empresas,email,' . ($request->id ?? ''),
            'calle' => 'required|string|max:255',
            'numero_exterior' => 'required|string|max:255',
            'colonia' => 'required|string|max:255',
            'codigo_postal' => 'required|string|max:5',
            'municipio' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
        ]);

        // Si no existe una empresa, crea una nueva; si existe, actualiza la existente
        $empresa = Empresa::firstOrNew(['id' => $request->id]);
        $empresa->fill($request->all());
        $empresa->save();

        return redirect()->route('empresas.index')->with('success', 'Empresa guardada exitosamente.');
    }
}
