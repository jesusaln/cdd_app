<?php
// app/Http/Controllers/PanelController.php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente; // Modelo Cliente
use App\Models\Producto; // Modelo Producto
use App\Models\Proveedor; // Modelo Proveedor
use App\Models\Cita; // Modelo Cita

class PanelController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Contadores de registros
        $clientesCount = Cliente::count(); // Total de clientes
        $productosCount = Producto::count(); // Total de productos
        $proveedoresCount = Proveedor::count(); // Total de proveedores
        $citasCount = Cita::count(); // Total de citas

        // Pasar los datos al frontend
        return Inertia::render('Panel', [
            'user' => $user ? [
                'id' => $user->id,
                'nombre' => $user->name,
                'rol' => $user->rol ?? $user->roles->pluck('name')->first() ?? 'Usuario',
            ] : null,
            'clientesCount' => $clientesCount,
            'productosCount' => $productosCount,
            'proveedoresCount' => $proveedoresCount,
            'citasCount' => $citasCount,
        ]);
    }
}
