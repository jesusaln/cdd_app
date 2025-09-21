<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportesDashboardController extends Controller
{
    /**
     * Mostrar el dashboard de reportes organizado por categorías
     */
    public function index()
    {
        return Inertia::render('Reportes/Dashboard');
    }
}
