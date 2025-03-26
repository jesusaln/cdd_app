<?php

namespace App\Http\Controllers\Api;

use App\Models\Tecnico;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TecnicoController extends Controller
{
    public function index()
    {
        try {
            $tecnicos = Tecnico::all();
            return response()->json($tecnicos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los tecnicos'], 500);
        }
    }
}
