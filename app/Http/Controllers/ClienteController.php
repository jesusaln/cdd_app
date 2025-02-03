<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ClienteController extends Controller
{
    public function index()
    {
        return Inertia::render('Clientes'); // Renderiza el componente Vue "Productos"
    }
}
