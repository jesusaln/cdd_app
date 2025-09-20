<?php

namespace App\Http\Controllers;

use App\Models\Renta;
use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\ComponenteKit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;

class RentasController extends Controller
{
    /**
     * Muestra una lista de rentas.
     */
    public function index(Request $request)
    {
        try {
            // Construir query con filtros
            $query = Renta::with(['cliente:id,nombre,email', 'componentesKit:id,nombre,tipo']);

            // Filtro de búsqueda
            if ($search = trim($request->input('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero_contrato', 'like', "%{$search}%")
                        ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                            $clienteQuery->where('nombre', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('componentesKit', function ($componenteQuery) use ($search) {
                            $componenteQuery->where('nombre', 'like', "%{$search}%");
                        });
                });
            }

            // Filtro por estado
            if ($estado = $request->input('estado')) {
                $query->where('estado', $estado);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $validSort = ['numero_contrato', 'fecha_inicio', 'fecha_fin', 'created_at', 'estado'];

            if (!in_array($sortBy, $validSort)) $sortBy = 'created_at';
            if (!in_array($sortDirection, ['asc', 'desc'])) $sortDirection = 'desc';

            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $rentas = $query->paginate(10)->appends($request->query());

            // Estadísticas
            $rentasCount = Renta::count();
            $rentasActivas = Renta::where('estado', 'activo')->count();
            $rentasVencidas = Renta::whereIn('estado', ['vencido', 'moroso', 'proximo_vencimiento'])->count();

            return inertia('Rentas/Index', [
                'rentas' => $rentas,
                'stats' => [
                    'total' => $rentasCount,
                    'activas' => $rentasActivas,
                    'vencidas' => $rentasVencidas,
                ],
                'filters' => $request->only(['search', 'estado']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (Exception $e) {
            Log::error('Error en RentasController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la lista de rentas.');
        }
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

        return response()->json(compact('clientes'));
    }

    /**
     * Almacena una nueva renta.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|exists:clientes,id',
            'componentes' => 'required|array|min:1',
            'componentes.*.componente_id' => 'required|exists:componentes_kit,id',
            'componentes.*.precio_mensual' => 'required|numeric|min:0',
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

        // Asociar componentes del kit
        foreach ($request->componentes as $componenteData) {
            $renta->componentesKit()->attach($componenteData['componente_id'], [
                'precio_mensual' => $componenteData['precio_mensual']
            ]);

            // Actualizar estado del componente
            $componente = ComponenteKit::find($componenteData['componente_id']);
            $componente->update(['estado' => 'rentado']);
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

    /**
     * Exportar rentas a CSV.
     */
    public function export(Request $request)
    {
        try {
            $query = Renta::with(['cliente:id,nombre,email', 'componentesKit:id,nombre,tipo']);

            // Aplicar los mismos filtros que en index
            if ($search = trim($request->input('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero_contrato', 'like', "%{$search}%")
                        ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                            $clienteQuery->where('nombre', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('componentesKit', function ($componenteQuery) use ($search) {
                            $componenteQuery->where('nombre', 'like', "%{$search}%");
                        });
                });
            }

            if ($estado = $request->input('estado')) {
                $query->where('estado', $estado);
            }

            $rentas = $query->get();

            $filename = 'rentas_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($rentas) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

                fputcsv($file, [
                    'ID',
                    'Número de Contrato',
                    'Cliente',
                    'Email Cliente',
                    'Equipos',
                    'Fecha Inicio',
                    'Fecha Fin',
                    'Estado',
                    'Monto Mensual',
                    'Fecha Creación'
                ]);

                foreach ($rentas as $renta) {
                    $equiposNombres = $renta->equipos->pluck('nombre')->join(', ');

                    fputcsv($file, [
                        $renta->id,
                        $renta->numero_contrato,
                        $renta->cliente?->nombre ?? 'N/A',
                        $renta->cliente?->email ?? 'N/A',
                        $equiposNombres,
                        $renta->fecha_inicio?->format('d/m/Y') ?? 'N/A',
                        $renta->fecha_fin?->format('d/m/Y') ?? 'N/A',
                        $renta->estado,
                        $renta->monto_mensual,
                        $renta->created_at?->format('d/m/Y H:i:s')
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('Error en exportación de rentas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar las rentas.');
        }
    }
}
