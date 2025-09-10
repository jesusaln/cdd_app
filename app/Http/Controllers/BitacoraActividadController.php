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

class BitacoraActividadController extends Controller
{
    const TIPOS = ['soporte', 'mantenimiento', 'instalacion', 'cotizacion', 'visita', 'administrativo', 'otro'];
    const ESTADOS = ['pendiente', 'en_proceso', 'completado', 'cancelado'];

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'usuario', 'cliente', 'desde', 'hasta', 'tipo', 'estado']);

        $actividades = BitacoraActividad::with(['usuario:id,name', 'cliente:id,nombre_razon_social'])
            ->rangoFechas($filters['desde'] ?? null, $filters['hasta'] ?? null)
            ->deUsuario($filters['usuario'] ?? null)
            ->deCliente($filters['cliente'] ?? null)
            ->buscar($filters['q'] ?? null)
            ->when($filters['tipo'] ?? null, fn($q, $v) => $q->where('tipo', $v))
            ->when($filters['estado'] ?? null, fn($q, $v) => $q->where('estado', $v))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Bitacora/Index', [
            'actividades' => $actividades,
            'filters'     => $filters,
            'usuarios'    => $this->getUsuarios(),
            'clientes'    => $this->getClientes(),
            'tipos'       => self::TIPOS,
            'estados'     => self::ESTADOS,
        ]);
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
