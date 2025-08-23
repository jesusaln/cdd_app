<?php

namespace App\Http\Controllers;

use App\Models\Renta;
use App\Models\Cliente;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RentasController extends Controller
{
    /**
     * Muestra una lista de rentas.
     */
    public function index()
    {
        $rentas = Renta::with(['cliente:id,nombre,email'])
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10);

        return inertia('Rentas/Index', [
            'rentas' => $rentas // Inertia maneja automáticamente la paginación
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva renta.
     */
    public function create()
    {
        return inertia('Rentas/Create');
    }

    /**
     * Retorna datos necesarios para el formulario de creación.
     */
    public function createData()
    {
        $clientes = Cliente::select('id', 'nombre', 'email')->get();
        $equipos = Equipo::where('estado', 'disponible')
            ->select('id', 'codigo', 'nombre', 'marca', 'modelo', 'numero_serie', 'precio_renta_mensual')
            ->get();

        return response()->json(compact('clientes', 'equipos'));
    }

    /**
     * Almacena una nueva renta.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|exists:clientes,id',
            'equipos' => 'required|array|min:1',
            'equipos.*.equipo_id' => 'required|exists:equipos,id',
            'fecha_inicio' => 'required|date',
            'duracion_meses' => 'required|integer|in:6,12,18,24',
            'tiene_prorroga' => 'boolean',
            'meses_prorroga' => 'nullable|integer|in:3,6,12',
            'precio_mensual' => 'required|numeric|min:0',
            'anticipo' => 'nullable|numeric|min:0',
            'forma_pago' => 'required|string|in:transferencia,efectivo,tarjeta,cheque',
            'observaciones' => 'nullable|string',
            'terminos_aceptados' => 'accepted'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $duracion = $request->duracion_meses;
        $prorroga = $request->tiene_prorroga ? ($request->meses_prorroga ?? 0) : 0;
        $mesesTotales = $duracion + $prorroga;

        $fechaInicio = now()->parse($request->fecha_inicio);
        $fechaFin = $fechaInicio->copy()->addMonths($mesesTotales);

        // Generar número de contrato único
        $numeroContrato = $this->generarNumeroContrato();

        // Crear la renta
        $renta = Renta::create([
            'numero_contrato' => $numeroContrato,
            'cliente_id' => $request->cliente_id,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'fecha_firma' => now(),
            'monto_mensual' => $request->precio_mensual,
            'deposito_garantia' => $request->anticipo,
            'forma_pago' => $request->forma_pago,
            'observaciones' => $request->observaciones,
            'renovacion_automatica' => $request->tiene_prorroga,
            'meses_duracion' => $duracion,
            'estado' => 'activo',
            'dia_pago' => 1, // Puedes hacerlo configurable
        ]);

        // Asociar equipos
        foreach ($request->equipos as $equipoData) {
            $renta->equipos()->attach($equipoData['equipo_id'], [
                'precio_mensual' => $equipoData['precio_mensual']
            ]);

            // Actualizar estado del equipo
            $equipo = Equipo::find($equipoData['equipo_id']);
            $equipo->update(['estado' => 'rentado']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Renta creada exitosamente',
            'renta' => $renta->load('cliente', 'equipos')
        ], 201);
    }

    /**
     * Genera un número de contrato único (ej: R-2024-001).
     */
    private function generarNumeroContrato()
    {
        $anio = now()->year;
        $ultimo = Renta::whereYear('created_at', $anio)->latest()->first();
        $numero = $ultimo ? intval(substr($ultimo->numero_contrato, -3)) + 1 : 1;
        return 'R-' . $anio . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Muestra los detalles de una renta.
     */
    public function show(Renta $renta)
    {
        $renta->load('cliente', 'equipos', 'pagos');
        return inertia('Rentas/Show', compact('renta'));
    }

    /**
     * Muestra el formulario para editar una renta.
     */
    public function edit(Renta $renta)
    {
        $renta->load('cliente', 'equipos');
        return inertia('Rentas/Edit', compact('renta'));
    }

    /**
     * Actualiza una renta existente.
     */
    public function update(Request $request, Renta $renta)
    {
        // Validación similar a store
        $request->validate([
            'observaciones' => 'nullable|string',
            'estado' => 'sometimes|string|in:activo,suspendido,cancelado,finalizado'
        ]);

        $renta->update($request->only('observaciones', 'estado'));

        return back()->with('success', 'Renta actualizada correctamente.');
    }

    /**
     * Elimina una renta (soft delete).
     */
    public function destroy(Renta $renta)
    {
        $renta->delete();
        return redirect()->route('rentas.index')->with('success', 'Renta eliminada correctamente.');
    }

    /**
     * Renovar una renta.
     */
    public function renovar(Request $request, Renta $renta)
    {
        $request->validate(['meses_renovacion' => 'required|integer|min:1']);

        if (!in_array($renta->estado, ['activo', 'proximo_vencimiento', 'vencido'])) {
            return response()->json([
                'success' => false,
                'error' => 'No se puede renovar una renta en estado: ' . $renta->estado
            ], 400);
        }

        $nuevaFechaFin = now()->parse($renta->fecha_fin)->addMonths($request->meses_renovacion);

        $renta->update([
            'fecha_fin' => $nuevaFechaFin,
            'meses_duracion' => $renta->meses_duracion + $request->meses_renovacion,
            'estado' => 'activo',
            'historial_cambios' => $renta->historial_cambios ? array_merge($renta->historial_cambios, [
                ['accion' => 'renovacion', 'fecha' => now()->toISOString(), 'meses' => $request->meses_renovacion]
            ]) : [
                ['accion' => 'renovacion', 'fecha' => now()->toISOString(), 'meses' => $request->meses_renovacion]
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Renta renovada exitosamente',
            'renta' => $renta->fresh(['cliente', 'equipos'])
        ]);
    }

    /**
     * Suspender una renta.
     */
    public function suspender(Renta $renta)
    {
        if ($renta->estado !== 'activo') {
            return response()->json(['success' => false, 'error' => 'Solo se pueden suspender rentas activas'], 400);
        }

        $renta->update(['estado' => 'suspendido']);

        return response()->json([
            'success' => true,
            'message' => 'Renta suspendida correctamente'
        ]);
    }

    /**
     * Reactivar una renta suspendida.
     */
    public function reactivar(Renta $renta)
    {
        if ($renta->estado !== 'suspendido') {
            return response()->json(['success' => false, 'error' => 'Solo se pueden reactivar rentas suspendidas'], 400);
        }

        $renta->update(['estado' => 'activo']);

        return response()->json([
            'success' => true,
            'message' => 'Renta reactivada correctamente'
        ]);
    }
}
