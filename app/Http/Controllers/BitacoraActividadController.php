<?php

namespace App\Http\Controllers;

use App\Models\BitacoraActividad;
use App\Models\Cliente;
use App\Models\User;
use App\Http\Requests\StoreBitacoraRequest;
use App\Http\Requests\UpdateBitacoraRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class BitacoraActividadController extends Controller
{
    const TIPOS = ['soporte', 'mantenimiento', 'instalacion', 'cotizacion', 'visita', 'administrativo', 'otro'];
    // ✅ AHORA EN ESPAÑOL — para que coincida con Requests, Modelo y BD
    const ESTADOS = ['pendiente', 'en_proceso', 'completado', 'cancelado'];

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'usuario', 'cliente', 'desde', 'hasta', 'tipo', 'estado']);

        Log::info('Filtros recibidos:', $filters);

        $estadoSeleccionado = $filters['estado'] ?? '';
        $estadosAMostrar = $this->determinarEstadosAMostrar($estadoSeleccionado);

        Log::info('Estados a mostrar:', $estadosAMostrar);

        $aplicarFiltroHoy = $this->debeAplicarFiltroHoy($filters);
        Log::info('¿Aplicar filtro hoy?', ['valor' => $aplicarFiltroHoy]);

        [$desde, $hasta] = $this->validarFechas($filters['desde'] ?? null, $filters['hasta'] ?? null);

        $query = BitacoraActividad::with(['usuario:id,name', 'cliente:id,nombre_razon_social'])
            ->whereIn('estado', $estadosAMostrar);

        if ($aplicarFiltroHoy) {
            $query->soloHoyOMantenerEnProceso();
        }

        $query->rangoFechas($desde, $hasta)
            ->deUsuario($filters['usuario'] ?? null)
            ->deCliente($filters['cliente'] ?? null)
            ->buscar($filters['q'] ?? null)
            ->when($filters['tipo'] ?? null, fn($q, $v) => $q->where('tipo', $v));

        Log::info('SQL generado:', [$query->toSql(), $query->getBindings()]);

        $actividades = $query->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        Log::info('Total de actividades encontradas:', ['total' => $actividades->total()]);

        return Inertia::render('Bitacora/Index', [
            'actividades' => $actividades,
            'filters'     => $filters,
            'usuarios'    => $this->getUsuarios(),
            'clientes'    => $this->getClientes(),
            'tipos'       => self::TIPOS,
            'estados'     => self::ESTADOS,
        ]);
    }

    /**
     * Determina qué estados mostrar según la selección del usuario
     */
    private function determinarEstadosAMostrar(string $estadoSeleccionado): array
    {
        switch ($estadoSeleccionado) {
            case 'todos':
                return self::ESTADOS;

            case 'completado':
                return ['completado'];

            case 'cancelado':
                return ['cancelado'];

            case 'pendiente':
                return ['pendiente'];

            case 'en_proceso':
                return ['en_proceso'];

            default:
                // Vista por defecto: solo pendientes y en proceso
                return ['pendiente', 'en_proceso'];
        }
    }

    /**
     * Determina si debe aplicar el filtro de "solo hoy + mantener en proceso"
     */
    private function debeAplicarFiltroHoy(array $filters): bool
    {
        $sinFiltrosFecha = empty($filters['desde']) && empty($filters['hasta']);
        $sinEstadoEspecifico = empty($filters['estado']);

        return $sinFiltrosFecha && $sinEstadoEspecifico;
    }

    /**
     * Valida y normaliza fechas de entrada
     */
    private function validarFechas(?string $desde, ?string $hasta): array
    {
        try {
            $desde = $desde ? Carbon::parse($desde)->toDateString() : null;
            $hasta = $hasta ? Carbon::parse($hasta)->toDateString() : null;
            if ($desde && $hasta && $desde > $hasta) {
                [$desde, $hasta] = [$hasta, $desde];
            }
        } catch (\Exception $e) {
            $desde = $hasta = null;
        }
        return [$desde, $hasta];
    }

    public function create()
    {
        return Inertia::render('Bitacora/Create', [
            'usuarios' => $this->getUsuarios(),
            'clientes' => $this->getClientes(),
            'tipos'    => self::TIPOS,
            'estados'  => self::ESTADOS,
        ]);
    }

    public function store(StoreBitacoraRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->setFechaHora($data);
        $data['adjuntos'] = $data['adjuntos'] ?? [];

        BitacoraActividad::create($data);

        return redirect()
            ->route('bitacora.index')
            ->with('success', 'Actividad registrada correctamente.');
    }

    public function show(BitacoraActividad $bitacora)
    {
        $bitacora->load(['usuario:id,name', 'cliente:id,nombre_razon_social']);
        return Inertia::render('Bitacora/Show', [
            'actividad' => $bitacora,
        ]);
    }

    public function edit(BitacoraActividad $bitacora)
    {
        return Inertia::render('Bitacora/Edit', [
            'actividad' => $bitacora->only([
                'id',
                'titulo',
                'user_id',
                'cliente_id',
                'tipo',
                'estado',
                'inicio_at',
                'fin_at',
                'fecha',
                'hora',
                'ubicacion',
                'descripcion',
                'prioridad',
                'adjuntos',
                'es_facturable',
                'costo_mxn'
            ]),
            'usuarios' => $this->getUsuarios(),
            'clientes' => $this->getClientes(),
            'tipos'    => self::TIPOS,
            'estados'  => self::ESTADOS,
        ]);
    }

    public function update(UpdateBitacoraRequest $request, BitacoraActividad $bitacora): RedirectResponse
    {
        $data = $request->validated();
        $this->setFechaHora($data);
        $data['adjuntos'] = $data['adjuntos'] ?? $bitacora->adjuntos ?? [];

        $bitacora->update($data);

        return redirect()
            ->route('bitacora.index')
            ->with('success', 'Actividad actualizada correctamente.');
    }

    public function destroy(BitacoraActividad $bitacora): RedirectResponse
    {
        $bitacora->delete();

        return redirect()
            ->back()
            ->with('success', 'Actividad eliminada.');
    }

    protected function getUsuarios()
    {
        return User::select('id', 'name')->orderBy('name')->get();
    }

    protected function getClientes()
    {
        return Cliente::select('id', 'nombre_razon_social')->orderBy('nombre_razon_social')->limit(500)->get();
    }

    private function setFechaHora(array &$data)
    {
        if (!empty($data['inicio_at'])) {
            $inicio = Carbon::parse($data['inicio_at']);
            $data['fecha'] = $inicio->toDateString();
            $data['hora'] = $inicio->format('H:i');
        }
    }
}
