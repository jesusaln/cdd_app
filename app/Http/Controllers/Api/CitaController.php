<?php

namespace App\Http\Controllers\Api;

use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class CitaController extends Controller
{
    /**
     * Obtener todas las citas en formato JSON.
     */
    public function index()
    {
        $citas = Cita::with('tecnico', 'cliente')->get();
        return response()->json(['citas' => $citas]);
    }

    /**
     * Almacenar una nueva cita en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tecnico_id' => 'required|exists:tecnicos,id',
            'cliente_id' => 'required|exists:clientes,id',
            'tipo_servicio' => 'required|string|max:255',
            'fecha_hora' => 'required|date',
            'descripcion' => 'nullable|string',
            'tipo_equipo' => 'required|string|max:255',
            'marca_equipo' => 'required|string|max:255',
            'modelo_equipo' => 'required|string|max:255',
            'problema_reportado' => 'nullable|string',
            'estado' => 'required|string|in:pendiente,en_proceso,completado,cancelado',
        ]);

        // Crear la cita
        $cita = Cita::create($validated);

        // Cargar las relaciones de cliente y técnico
        $cita->load('cliente', 'tecnico');

        // Devolver la cita con las relaciones incluidas
        return response()->json(['message' => 'Cita creada exitosamente.', 'cita' => $cita]);
    }

    /**
     * Obtener detalles de una cita.
     */
    public function show($id)
    {
        $cita = Cita::with(['cliente', 'tecnico'])->findOrFail($id);

        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrado'], 404);
        }

        return response()->json($cita);
    }

    /**
     * Actualizar una cita existente.
     */
    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);

        $validated = $request->validate([
            'tecnico_id' => 'sometimes|required|exists:tecnicos,id',
            'cliente_id' => 'sometimes|required|exists:clientes,id',
            'tipo_servicio' => 'sometimes|required|string|max:255',
            'fecha_hora' => 'sometimes|required|date',
            'descripcion' => 'nullable|string',
            'tipo_equipo' => 'sometimes|required|string|max:255',
            'marca_equipo' => 'sometimes|required|string|max:255',
            'modelo_equipo' => 'sometimes|required|string|max:255',
            'problema_reportado' => 'nullable|string',
            'estado' => 'sometimes|required|string|in:pendiente,en_proceso,completado,cancelado',
        ]);

        $cita->update($validated);
        return response()->json(['message' => 'Cita actualizada exitosamente.', 'cita' => $cita]);
    }

    /**
     * Eliminar una cita.
     */
    public function destroy($id)
    {
        $cita = Cita::findOrFail($id);
        $cita->delete();

        return response()->json(['message' => 'Cita eliminada exitosamente.']);
    }
}
