<?php
// app/Http/Controllers/PanelController.php
namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Aquí pasamos los datos de 'user' y cualquier otra información que necesites
        return Inertia::render('Panel', [
            'user' => $user ? [
                'id' => $user->id,
                'nombre' => $user->name,
                // Agrega el rol del usuario (ajusta según tu implementación)
                'rol' => $user->rol ?? $user->roles->pluck('name')->first() ?? 'Usuario',
            ] : null,
            // otros datos...
        ]);
    }
}
