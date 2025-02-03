<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ProductoController extends Controller
{
    public function index()
    {
        return Inertia::render('Productos'); // Renderiza el componente Vue "Productos"
    }
}
