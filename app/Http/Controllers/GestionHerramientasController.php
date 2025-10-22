<?php

namespace App\Http\Controllers;

use App\Models\Herramienta;
use App\Models\Tecnico;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GestionHerramientasController extends Controller
{
    public function index()
    {
        $tecnicos = Tecnico::with(['herramientas' => function ($query) {
            $query->select('id', 'nombre', 'numero_serie', 'estado', 'foto', 'categoria_id', 'tecnico_id', 'fecha_ultimo_mantenimiento', 'requiere_mantenimiento')
                  ->with('categoriaHerramienta:id,nombre');
        }])
        ->select('id', 'nombre', 'telefono', 'email')
        ->orderBy('nombre')
        ->get()
        ->map(function ($tecnico) {
            return [
                'id' => $tecnico->id,
                'nombre' => $tecnico->nombre,
                'telefono' => $tecnico->telefono,
                'email' => $tecnico->email,
                'herramientas' => $tecnico->herramientas->map(function ($herramienta) {
                    return [
                        'id' => $herramienta->id,
                        'nombre' => $herramienta->nombre,
                        'numero_serie' => $herramienta->numero_serie,
                        'estado' => $herramienta->estado,
                        'foto' => $herramienta->foto,
                        'categoria_herramienta' => $herramienta->categoriaHerramienta,
                        'fecha_ultimo_mantenimiento' => $herramienta->fecha_ultimo_mantenimiento,
                        'requiere_mantenimiento' => $herramienta->requiere_mantenimiento,
                        'necesita_mantenimiento' => $herramienta->necesitaMantenimiento(),
                    ];
                }),
            ];
        });

        return Inertia::render('Herramientas/Gestion/Index', [
            'tecnicos' => $tecnicos,
        ]);
    }

    public function create()
    {
        return Inertia::render('Herramientas/Gestion/Create', [
            'tecnicos' => Tecnico::select('id','nombre','telefono')->orderBy('nombre')->get(),
            'herramientas' => Herramienta::where(function($q) {
                    $q->whereNull('tecnico_id')
                      ->orWhere('estado', Herramienta::ESTADO_DISPONIBLE);
                })
                ->with(['categoriaHerramienta:id,nombre'])
                ->select('id', 'nombre', 'numero_serie', 'estado', 'foto', 'categoria_id')
                ->orderBy('nombre')
                ->get()
                ->map(function ($herramienta) {
                    return [
                        'id' => $herramienta->id,
                        'nombre' => $herramienta->nombre,
                        'numero_serie' => $herramienta->numero_serie,
                        'estado' => $herramienta->estado,
                        'foto' => $herramienta->foto,
                        'categoria_herramienta' => $herramienta->categoriaHerramienta,
                    ];
                }),
        ]);
    }

    public function edit(Tecnico $tecnico)
    {
        $asignadas = Herramienta::where('tecnico_id', $tecnico->id)
            ->with(['categoriaHerramienta:id,nombre'])
            ->select('id', 'nombre', 'numero_serie', 'estado', 'foto', 'categoria_id', 'fecha_ultimo_mantenimiento', 'requiere_mantenimiento')
            ->orderBy('nombre')
            ->get()
            ->map(function ($herramienta) {
                return [
                    'id' => $herramienta->id,
                    'nombre' => $herramienta->nombre,
                    'numero_serie' => $herramienta->numero_serie,
                    'estado' => $herramienta->estado,
                    'foto' => $herramienta->foto,
                    'categoria_herramienta' => $herramienta->categoriaHerramienta,
                    'fecha_ultimo_mantenimiento' => $herramienta->fecha_ultimo_mantenimiento,
                    'requiere_mantenimiento' => $herramienta->requiere_mantenimiento,
                    'necesita_mantenimiento' => $herramienta->necesitaMantenimiento(),
                ];
            });

        $disponibles = Herramienta::where(function($q) use ($tecnico) {
                $q->whereNull('tecnico_id')
                  ->orWhere('estado', Herramienta::ESTADO_DISPONIBLE)
                  ->orWhere('tecnico_id', $tecnico->id);
            })
            ->whereNotIn('id', $asignadas->pluck('id'))
            ->with(['categoriaHerramienta:id,nombre'])
            ->select('id', 'nombre', 'numero_serie', 'estado', 'foto', 'categoria_id')
            ->orderBy('nombre')
            ->get()
            ->map(function ($herramienta) {
                return [
                    'id' => $herramienta->id,
                    'nombre' => $herramienta->nombre,
                    'numero_serie' => $herramienta->numero_serie,
                    'estado' => $herramienta->estado,
                    'foto' => $herramienta->foto,
                    'categoria_herramienta' => $herramienta->categoriaHerramienta,
                ];
            });

        return Inertia::render('Herramientas/Gestion/Edit', [
            'tecnico' => [
                'id' => $tecnico->id,
                'nombre' => $tecnico->nombre,
                'telefono' => $tecnico->telefono,
                'email' => $tecnico->email,
            ],
            'asignadas' => $asignadas,
            'disponibles' => $disponibles,
            'tecnicos' => Tecnico::select('id','nombre','telefono')->orderBy('nombre')->get(),
        ]);
    }

    public function asignar(Request $request)
    {
        $data = $request->validate([
            'tecnico_id' => 'required|exists:tecnicos,id',
            'herramientas' => 'array',
            'herramientas.*' => 'integer|exists:herramientas,id',
        ]);

        $ids = $data['herramientas'] ?? [];
        if (!empty($ids)) {
            Herramienta::whereIn('id', $ids)->update([
                'tecnico_id' => $data['tecnico_id'],
                'estado' => Herramienta::ESTADO_ASIGNADA,
            ]);
        }

        return redirect()->route('herramientas.gestion.index')->with('success', 'Herramientas asignadas');
    }

    public function update(Request $request, Tecnico $tecnico)
    {
        $data = $request->validate([
            'asignadas' => 'array',
            'asignadas.*' => 'integer|exists:herramientas,id',
        ]);

        $deseadas = collect($data['asignadas'] ?? []);
        $actuales = Herramienta::where('tecnico_id', $tecnico->id)->pluck('id');

        $aRemover = $actuales->diff($deseadas);
        $aAgregar = $deseadas->diff($actuales);

        if ($aRemover->isNotEmpty()) {
            Herramienta::whereIn('id', $aRemover)->update([
                'tecnico_id' => null,
                'estado' => Herramienta::ESTADO_DISPONIBLE,
            ]);
        }
        if ($aAgregar->isNotEmpty()) {
            Herramienta::whereIn('id', $aAgregar)->update([
                'tecnico_id' => $tecnico->id,
                'estado' => Herramienta::ESTADO_ASIGNADA,
            ]);
        }

        return redirect()->route('herramientas.gestion.edit', $tecnico->id)->with('success', 'Asignaciones actualizadas');
    }

    public function reasignar(Request $request)
    {
        $data = $request->validate([
            'herramienta_id' => 'required|exists:herramientas,id',
            'tecnico_anterior_id' => 'required|exists:tecnicos,id',
            'tecnico_nuevo_id' => 'required|exists:tecnicos,id',
            'observaciones' => 'nullable|string',
        ]);

        $herramienta = Herramienta::findOrFail($data['herramienta_id']);

        // Verificar que la herramienta pertenece al técnico anterior
        if ($herramienta->tecnico_id !== $data['tecnico_anterior_id']) {
            return redirect()->back()->with('error', 'La herramienta no pertenece al técnico especificado');
        }

        // Actualizar la herramienta
        $herramienta->update([
            'tecnico_id' => $data['tecnico_nuevo_id'],
            'estado' => Herramienta::ESTADO_ASIGNADA,
            'fecha_asignacion' => now(),
        ]);

        // Crear registro en historial si existe el modelo
        if (class_exists('\App\Models\HistorialHerramienta')) {
            \App\Models\HistorialHerramienta::create([
                'herramienta_id' => $herramienta->id,
                'tecnico_id' => $data['tecnico_nuevo_id'],
                'fecha_asignacion' => now(),
                'asignado_por' => 1, // Usuario por defecto para evitar errores
                'observaciones_asignacion' => "Reasignación de herramienta de técnico {$data['tecnico_anterior_id']} a técnico {$data['tecnico_nuevo_id']}" .
                               ($data['observaciones'] ? ": {$data['observaciones']}" : ''),
                'estado_herramienta_asignacion' => $herramienta->estado,
            ]);
        }

        return redirect()->back()->with('success', 'Herramienta reasignada correctamente');
    }

    public function exportarPorTecnico($tecnicoId)
    {
        try {
            // Buscar el técnico por ID
            $tecnico = Tecnico::findOrFail($tecnicoId);

            // Obtener herramientas asignadas al técnico con información completa
            $herramientas = Herramienta::where('tecnico_id', $tecnico->id)
                ->where('estado', Herramienta::ESTADO_ASIGNADA)
                ->with(['categoriaHerramienta'])
                ->get()
                ->map(function ($herramienta) {
                    return [
                        'id' => $herramienta->id,
                        'nombre' => $herramienta->nombre,
                        'numero_serie' => $herramienta->numero_serie,
                        'categoria' => $herramienta->categoria_label,
                        'estado' => $herramienta->estado_label,
                        'costo_reemplazo' => $herramienta->costo_reemplazo,
                        'fecha_ultimo_mantenimiento' => $herramienta->fecha_ultimo_mantenimiento?->format('d/m/Y'),
                        'dias_para_mantenimiento' => $herramienta->dias_para_mantenimiento,
                        'vida_util_meses' => $herramienta->vida_util_meses,
                        'descripcion' => $herramienta->descripcion,
                        'fecha_asignacion' => $herramienta->fecha_asignacion?->format('d/m/Y H:i:s'),
                        'ultima_inspeccion' => null,
                        'condicion_ultima_inspeccion' => 'Sin inspección',
                        'necesita_mantenimiento' => $herramienta->necesitaMantenimiento(),
                        'dias_desde_ultimo_mantenimiento' => $herramienta->dias_desde_ultimo_mantenimiento,
                        'porcentaje_vida_util' => $herramienta->porcentaje_vida_util,
                    ];
                });

            // Información del técnico
            $tecnicoInfo = [
                'id' => $tecnico->id,
                'nombre_completo' => $tecnico->nombre . ' ' . $tecnico->apellido,
                'email' => $tecnico->email,
                'telefono' => $tecnico->telefono,
                'fecha_exportacion' => now()->format('d/m/Y H:i:s'),
            ];

            // Estadísticas
            $estadisticas = [
                'total_herramientas' => $herramientas->count(),
                'valor_total' => $herramientas->sum('costo_reemplazo'),
                'herramientas_mantenimiento' => $herramientas->where('necesita_mantenimiento', true)->count(),
                'herramientas_por_vencer' => $herramientas->where('porcentaje_vida_util', '>', 80)->count(),
            ];

            return Inertia::render('Herramientas/Gestion/Exportar', [
                'tecnico' => $tecnicoInfo,
                'herramientas' => $herramientas,
                'estadisticas' => $estadisticas,
            ]);

        } catch (Exception $e) {
            Log::error('Error en exportación de herramientas por técnico: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el reporte de herramientas.');
        }
    }

    public function descargarReporteTecnico($tecnicoId)
    {
        try {
            // Buscar el técnico por ID
            $tecnico = Tecnico::findOrFail($tecnicoId);

            // Obtener herramientas asignadas al técnico
            $herramientas = Herramienta::where('tecnico_id', $tecnico->id)
                ->where('estado', Herramienta::ESTADO_ASIGNADA)
                ->with(['categoriaHerramienta'])
                ->get();

            $filename = 'herramientas_tecnico_' . $tecnico->nombre . '_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($tecnico, $herramientas) {
                $file = fopen('php://output', 'w');

                // Encabezado del reporte
                fputcsv($file, ['REPORTE DE HERRAMIENTAS ASIGNADAS']);
                fputcsv($file, ['Técnico:', $tecnico->nombre . ' ' . $tecnico->apellido]);
                fputcsv($file, ['Fecha de Exportación:', now()->format('d/m/Y H:i:s')]);
                fputcsv($file, ['']);

                // Encabezados de la tabla
                fputcsv($file, [
                    'ID',
                    'Nombre',
                    'Número de Serie',
                    'Categoría',
                    'Estado',
                    'Costo de Reemplazo',
                    'Fecha Último Mantenimiento',
                    'Días para Mantenimiento',
                    'Vida Útil (meses)',
                    'Descripción',
                    'Fecha de Asignación',
                    'Necesita Mantenimiento'
                ]);

                foreach ($herramientas as $herramienta) {
                    fputcsv($file, [
                        $herramienta->id,
                        $herramienta->nombre,
                        $herramienta->numero_serie,
                        $herramienta->categoria_label,
                        $herramienta->estado_label,
                        $herramienta->costo_reemplazo,
                        $herramienta->fecha_ultimo_mantenimiento?->format('d/m/Y'),
                        $herramienta->dias_para_mantenimiento,
                        $herramienta->vida_util_meses,
                        $herramienta->descripcion,
                        $herramienta->fecha_asignacion?->format('d/m/Y H:i:s'),
                        $herramienta->necesitaMantenimiento() ? 'Sí' : 'No'
                    ]);
                }

                // Estadísticas finales
                fputcsv($file, ['']);
                fputcsv($file, ['ESTADÍSTICAS']);
                fputcsv($file, ['Total de Herramientas:', $herramientas->count()]);
                fputcsv($file, ['Valor Total:', '$' . number_format($herramientas->sum('costo_reemplazo'), 2)]);
                fputcsv($file, ['Herramientas que necesitan mantenimiento:', $herramientas->where('requiere_mantenimiento', true)->count()]);

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (Exception $e) {
            Log::error('Error en descarga de reporte de herramientas por técnico: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al descargar el reporte de herramientas.');
        }
    }
}
