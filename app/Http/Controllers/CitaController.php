<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Tecnico;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Services\InventarioService;
use App\Enums\EstadoVenta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Exception;

class CitaController extends Controller
{
    /**
     * Mostrar todas las citas con paginación y filtros.
     */
    public function index(Request $request)
    {
        try {
            $query = Cita::with('tecnico', 'cliente');

            // Filtros de búsqueda
            if ($s = trim((string) $request->input('search', ''))) {
                $query->where(function ($w) use ($s) {
                    $w->where('tipo_servicio', 'like', "%{$s}%")
                        ->orWhere('descripcion', 'like', "%{$s}%")
                        ->orWhere('problema_reportado', 'like', "%{$s}%")
                        ->orWhereHas('cliente', function($clienteQuery) use ($s) {
                            $clienteQuery->where('nombre_razon_social', 'like', "%{$s}%");
                        })
                        ->orWhereHas('tecnico', function($tecnicoQuery) use ($s) {
                            $tecnicoQuery->where('nombre', 'like', "%{$s}%");
                        });
                });
            }

            // Filtros adicionales
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('tecnico_id')) {
                $query->where('tecnico_id', $request->tecnico_id);
            }

            if ($request->filled('cliente_id')) {
                $query->where('cliente_id', $request->cliente_id);
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
            }


            // Ordenamiento dinámico
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            // Si no hay sort_by específico, mantener el orden por estado
            if ($sortBy === 'created_at') {
                $query->orderByRaw("
                    CASE
                        WHEN estado = 'en_proceso' THEN 1
                        WHEN estado = 'pendiente' THEN 2
                        WHEN estado = 'completado' THEN 3
                        WHEN estado = 'cancelado' THEN 4
                        ELSE 999
                    END ASC
                ")->orderBy('fecha_hora', 'asc');
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación - obtener per_page del request o usar default
            $perPage = $request->get('per_page', 10);
            $validPerPage = [10, 15, 25, 50]; // Solo estas opciones válidas
            if (!in_array((int)$perPage, $validPerPage)) {
                $perPage = 50;
            }

            // Paginar con el per_page dinámico
            $citas = $query->paginate((int)$perPage);

            // Estadísticas por estado de cita
            $citasCount = Cita::count();
            $citasPendientes = Cita::where('estado', Cita::ESTADO_PENDIENTE)->count();
            $citasEnProceso = Cita::where('estado', Cita::ESTADO_EN_PROCESO)->count();
            $citasCompletadas = Cita::where('estado', Cita::ESTADO_COMPLETADO)->count();
            $citasCanceladas = Cita::where('estado', Cita::ESTADO_CANCELADO)->count();

            // Datos adicionales para filtros
            $tecnicos = Tecnico::select('id', 'nombre')->get();
            $clientes = Cliente::select('id', 'nombre_razon_social')->get();
            $estados = [
                Cita::ESTADO_PENDIENTE => 'Pendiente',
                Cita::ESTADO_EN_PROCESO => 'En Proceso',
                Cita::ESTADO_COMPLETADO => 'Completado',
                Cita::ESTADO_CANCELADO => 'Cancelado',
            ];

            return Inertia::render('Citas/Index', [
                'citas' => $citas,
                'stats' => [
                    'total' => $citasCount,
                    'pendientes' => $citasPendientes,
                    'en_proceso' => $citasEnProceso,
                    'completadas' => $citasCompletadas,
                    'canceladas' => $citasCanceladas,
                ],
                'tecnicos' => $tecnicos,
                'clientes' => $clientes,
                'estados' => $estados,
                'filters' => $request->only(['search', 'estado', 'tecnico_id', 'cliente_id', 'fecha_desde', 'fecha_hasta']),
                'sorting' => [
                    'sort_by' => $sortBy,
                    'sort_direction' => $sortDirection
                ],
                'pagination' => [
                    'per_page' => (int)$perPage,
                    'current_page' => $citas->currentPage(),
                    'last_page' => $citas->lastPage(),
                    'total' => $citas->total(),
                    'from' => $citas->firstItem(),
                    'to' => $citas->lastItem(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Error en CitaController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la lista de citas.');
        }
    }

    /**
      * Mostrar formulario para crear una nueva cita.
      */
    public function create()
    {
        $tecnicos = Tecnico::all();
        $clientes = Cliente::select('id', 'nombre_razon_social', 'email', 'telefono')->get();
        $servicios = Servicio::with('categoria')->select('id', 'nombre', 'precio', 'descripcion', 'categoria_id')->get();
        // Productos básicos para buscador
        $productos = \App\Models\Producto::with(['categoria', 'marca'])
            ->select('id', 'nombre', 'descripcion', 'codigo', 'categoria_id', 'marca_id', 'precio_venta', 'stock', 'estado')
            ->active()
            ->get();

        return Inertia::render('Citas/Create', [
            'tecnicos' => $tecnicos,
            'clientes' => $clientes,
            'servicios' => $servicios,
            'productos' => $productos,
        ]);
    }

    /**
      * Almacenar una nueva cita en la base de datos.
      */
    public function store(Request $request)
    {
        // Validar los datos recibidos con mejoras
        $validated = $request->validate([
            'tecnico_id' => 'required|exists:tecnicos,id',
            'cliente_id' => 'required|exists:clientes,id',
            'tipo_servicio' => 'required|string|max:255',
            'fecha_hora' => [
                'required',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
                    $fecha = Carbon::parse($value);
                    if ($fecha->isSunday()) {
                        $fail('No se pueden programar citas los domingos.');
                    }
                    if ($fecha->hour < 8 || $fecha->hour > 18) {
                        $fail('Las citas deben programarse entre las 8:00 AM y 6:00 PM.');
                    }
                }
            ],
            'prioridad' => 'nullable|string|in:baja,media,alta,urgente',
            'descripcion' => 'nullable|string|max:1000',
            'problema_reportado' => 'nullable|string|max:1000',
            'estado' => 'required|string|in:pendiente,en_proceso,completado,cancelado',
            'evidencias' => 'nullable|string|max:2000',
            'foto_equipo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'foto_hoja_servicio' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'foto_identificacion' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'productos_utilizados' => 'nullable|array',
            'productos_vendidos' => 'nullable|array',
            'servicios_realizados' => 'nullable|array',
            'monto_productos_vendidos' => 'nullable|numeric|min:0',
            'requiere_venta' => 'nullable|boolean',
        ], [
            'tecnico_id.required' => 'Debe seleccionar un técnico.',
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'fecha_hora.after' => 'La fecha debe ser posterior a la actual.',
            '*.max:2048' => 'La imagen no debe superar los 2MB.',
        ]);

        try {
            DB::beginTransaction();

            // Verificar disponibilidad del técnico
            $this->verificarDisponibilidadTecnico(
                $validated['tecnico_id'],
                $validated['fecha_hora']
            );

            // Verificar límite de citas por día para el técnico
            $this->verificarLimiteCitasPorDia(
                $validated['tecnico_id'],
                $validated['fecha_hora']
            );

            // Verificar que el cliente no tenga múltiples citas activas
            $this->verificarCitasClienteActivas(
                $validated['cliente_id'],
                $validated['fecha_hora']
            );

            // Guardar archivos y obtener sus rutas
            $filePaths = $this->saveFiles($request, ['foto_equipo', 'foto_hoja_servicio', 'foto_identificacion']);

            // Crear la cita con los datos validados y las rutas de los archivos
            $cita = Cita::create(array_merge(collect($validated)->except([
                'productos_utilizados',
                'productos_vendidos',
                'servicios_realizados',
                'monto_productos_vendidos',
                'requiere_venta',
            ])->toArray(), $filePaths));

            // Adjuntar productos vendidos
            $productosVendidos = $request->input('productos_vendidos');
            if (is_string($productosVendidos)) {
                $decoded = json_decode($productosVendidos, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $productosVendidos = $decoded;
                }
            }
            if (is_array($productosVendidos)) {
                foreach ($productosVendidos as $item) {
                    $productoId = (int) ($item['id'] ?? 0);
                    $cantidad = max(1, (int) ($item['cantidad'] ?? 1));
                    $precio = (float) ($item['precio'] ?? 0);
                    if ($productoId > 0 && $cantidad > 0) {
                        $subtotal = $cantidad * $precio;
                        $cita->productosVendidos()->attach($productoId, [
                            'cantidad' => $cantidad,
                            'precio_venta' => $precio,
                            'subtotal' => $subtotal,
                        ]);
                    }
                }
            }

            // Adjuntar productos utilizados
            $productosUtilizados = $request->input('productos_utilizados');
            if (is_string($productosUtilizados)) {
                $decoded = json_decode($productosUtilizados, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $productosUtilizados = $decoded;
                }
            }
            if (is_array($productosUtilizados)) {
                foreach ($productosUtilizados as $item) {
                    $productoId = (int) ($item['id'] ?? 0);
                    $cantidad = max(1, (int) ($item['cantidad'] ?? 1));
                    $precioUnitario = (float) ($item['precio_unitario'] ?? 0);
                    $tipoUso = $item['tipo_uso'] ?? 'repuesto';
                    $notas = $item['notas'] ?? null;

                    if ($productoId > 0 && $cantidad > 0) {
                        $cita->productosUtilizados()->attach($productoId, [
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precioUnitario,
                            'tipo_uso' => $tipoUso,
                            'notas' => $notas,
                        ]);
                    }
                }
            }

            // Adjuntar servicios realizados
            $serviciosRealizados = $request->input('servicios_realizados');
            if (is_string($serviciosRealizados)) {
                $decoded = json_decode($serviciosRealizados, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $serviciosRealizados = $decoded;
                }
            }
            if (is_array($serviciosRealizados)) {
                foreach ($serviciosRealizados as $item) {
                    $servicioId = (int) ($item['id'] ?? 0);
                    $cantidad = max(1, (int) ($item['cantidad'] ?? 1));
                    $precio = (float) ($item['precio'] ?? 0);
                    if ($servicioId > 0 && $cantidad > 0) {
                        $subtotal = $cantidad * $precio;
                        $cita->serviciosRealizados()->attach($servicioId, [
                            'cantidad' => $cantidad,
                            'precio' => $precio,
                            'subtotal' => $subtotal,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('citas.index')->with('success', 'Cita creada exitosamente.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cita: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear la cita. Por favor, intente nuevamente.');
        }
    }

    /**
      * Mostrar formulario para editar una cita existente.
      */
    public function edit(Cita $cita)
    {
        $tecnicos = Tecnico::all();
        $clientes = Cliente::all();
        $servicios = Servicio::with('categoria')->select('id', 'nombre', 'precio', 'descripcion', 'categoria_id')->get();

        return Inertia::render('Citas/Edit', [
            'cita' => $cita,
            'tecnicos' => $tecnicos,
            'clientes' => $clientes,
            'servicios' => $servicios
        ]);
    }

    /**
     * Actualizar una cita existente en la base de datos.
     */
    public function update(Request $request, Cita $cita)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'tecnico_id' => 'sometimes|required|exists:tecnicos,id',
            'cliente_id' => 'sometimes|required|exists:clientes,id',
            'tipo_servicio' => 'sometimes|required|string|max:255',
            'fecha_hora' => [
                'sometimes',
                'required',
                'date',
                function ($attribute, $value, $fail) use ($cita) {
                    $fecha = Carbon::parse($value);
                    if ($fecha->isPast() && $cita->estado === Cita::ESTADO_PENDIENTE) {
                        $fail('No se puede programar una cita pendiente en el pasado.');
                    }
                    if ($fecha->isSunday()) {
                        $fail('No se pueden programar citas los domingos.');
                    }
                    if ($fecha->hour < 8 || $fecha->hour > 18) {
                        $fail('Las citas deben programarse entre las 8:00 AM y 6:00 PM.');
                    }
                }
            ],
            'prioridad' => 'nullable|string|in:baja,media,alta,urgente',
            'descripcion' => 'nullable|string|max:1000',
            'problema_reportado' => 'nullable|string|max:1000',
            'estado' => 'sometimes|required|string|in:pendiente,en_proceso,completado,cancelado',
            'evidencias' => 'nullable|string|max:2000',
            'foto_equipo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'foto_hoja_servicio' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'foto_identificacion' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'productos_utilizados' => 'nullable|array',
            'productos_vendidos' => 'nullable|array',
            'servicios_realizados' => 'nullable|array',
            'monto_productos_vendidos' => 'nullable|numeric|min:0',
            'requiere_venta' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();
            $estadoAnterior = $cita->estado;

            // Verificar disponibilidad del técnico si cambió
            if (
                isset($validated['tecnico_id']) &&
                ($validated['tecnico_id'] != $cita->tecnico_id ||
                    isset($validated['fecha_hora']) && $validated['fecha_hora'] != $cita->fecha_hora)
            ) {
                $this->verificarDisponibilidadTecnico(
                    $validated['tecnico_id'],
                    $validated['fecha_hora'] ?? $cita->fecha_hora,
                    $cita->id
                );

                // Verificar límite de citas por día si cambió la fecha
                if (isset($validated['fecha_hora'])) {
                    $this->verificarLimiteCitasPorDia(
                        $validated['tecnico_id'],
                        $validated['fecha_hora']
                    );
                }
            }

            // Verificar citas activas del cliente si cambió la fecha
            if (isset($validated['cliente_id']) && $validated['cliente_id'] != $cita->cliente_id) {
                $this->verificarCitasClienteActivas(
                    $validated['cliente_id'],
                    $validated['fecha_hora'] ?? $cita->fecha_hora
                );
            }

            // Guardar archivos y obtener sus rutas (conservando los archivos existentes si no se suben nuevos)
            $filePaths = $this->saveFiles($request, ['foto_equipo', 'foto_hoja_servicio', 'foto_identificacion'], [
                'foto_equipo' => $cita->foto_equipo,
                'foto_hoja_servicio' => $cita->foto_hoja_servicio,
                'foto_identificacion' => $cita->foto_identificacion,
            ]);

            // Actualizar la cita con los datos validados y las rutas de los archivos
            $cita->update(array_merge(collect($validated)->except([
                'productos_utilizados',
                'productos_vendidos',
                'servicios_realizados',
                'monto_productos_vendidos',
                'requiere_venta',
            ])->toArray(), $filePaths));

            // Limpiar relaciones existentes si se proporcionaron nuevos datos
            if ($request->has('productos_utilizados')) {
                $cita->productosUtilizados()->detach();
            }
            if ($request->has('productos_vendidos')) {
                $cita->productosVendidos()->detach();
            }
            if ($request->has('servicios_realizados')) {
                $cita->serviciosRealizados()->detach();
            }

            // Adjuntar productos utilizados
            $productosUtilizados = $request->input('productos_utilizados');
            if (is_string($productosUtilizados)) {
                $decoded = json_decode($productosUtilizados, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $productosUtilizados = $decoded;
                }
            }
            if (is_array($productosUtilizados)) {
                foreach ($productosUtilizados as $item) {
                    $productoId = (int) ($item['id'] ?? 0);
                    $cantidad = max(1, (int) ($item['cantidad'] ?? 1));
                    $precioUnitario = (float) ($item['precio_unitario'] ?? 0);
                    $tipoUso = $item['tipo_uso'] ?? 'repuesto';
                    $notas = $item['notas'] ?? null;

                    if ($productoId > 0 && $cantidad > 0) {
                        $cita->productosUtilizados()->attach($productoId, [
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precioUnitario,
                            'tipo_uso' => $tipoUso,
                            'notas' => $notas,
                        ]);
                    }
                }
            }

            // Adjuntar productos vendidos
            $productosVendidos = $request->input('productos_vendidos');
            if (is_string($productosVendidos)) {
                $decoded = json_decode($productosVendidos, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $productosVendidos = $decoded;
                }
            }
            if (is_array($productosVendidos)) {
                foreach ($productosVendidos as $item) {
                    $productoId = (int) ($item['id'] ?? 0);
                    $cantidad = max(1, (int) ($item['cantidad'] ?? 1));
                    $precio = (float) ($item['precio'] ?? 0);
                    if ($productoId > 0 && $cantidad > 0) {
                        $subtotal = $cantidad * $precio;
                        $cita->productosVendidos()->attach($productoId, [
                            'cantidad' => $cantidad,
                            'precio_venta' => $precio,
                            'subtotal' => $subtotal,
                        ]);
                    }
                }
            }

            // Adjuntar servicios realizados
            $serviciosRealizados = $request->input('servicios_realizados');
            if (is_string($serviciosRealizados)) {
                $decoded = json_decode($serviciosRealizados, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $serviciosRealizados = $decoded;
                }
            }
            if (is_array($serviciosRealizados)) {
                foreach ($serviciosRealizados as $item) {
                    $servicioId = (int) ($item['id'] ?? 0);
                    $cantidad = max(1, (int) ($item['cantidad'] ?? 1));
                    $precio = (float) ($item['precio'] ?? 0);
                    if ($servicioId > 0 && $cantidad > 0) {
                        $subtotal = $cantidad * $precio;
                        $cita->serviciosRealizados()->attach($servicioId, [
                            'cantidad' => $cantidad,
                            'precio' => $precio,
                            'subtotal' => $subtotal,
                        ]);
                    }
                }
            }

            // Si la cita se marcó como completada, convertir a venta con verificación de inventario
            if (($validated['estado'] ?? $cita->estado) === Cita::ESTADO_COMPLETADO && $estadoAnterior !== Cita::ESTADO_COMPLETADO) {
                $this->convertirCitaAVenta($cita, $request->user());
            }

            DB::commit();

            return redirect()->route('citas.index')->with('success', 'Cita actualizada exitosamente.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cita: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar la cita. Por favor, intente nuevamente.');
        }
    }

    /**
     * Convierte una cita completada en una venta y descuenta inventario para productos
     */
    private function convertirCitaAVenta(Cita $cita, $user = null): void
    {
        // Cargar relaciones necesarias
        $cita->load(['cliente', 'tecnico', 'productosVendidos', 'productosUtilizados', 'serviciosRealizados']);

        // Recolectar ítems
        $items = [];
        foreach ($cita->productosVendidos as $producto) {
            $items[] = [
                'modelo' => $producto,
                'tipo' => 'producto',
                'cantidad' => (int) ($producto->pivot->cantidad ?? 1),
                'precio' => (float) ($producto->pivot->precio_venta ?? $producto->precio_venta ?? 0),
            ];
        }
        foreach ($cita->serviciosRealizados as $servicio) {
            $items[] = [
                'modelo' => $servicio,
                'tipo' => 'servicio',
                'cantidad' => (int) ($servicio->pivot->cantidad ?? 1),
                'precio' => (float) ($servicio->pivot->precio ?? $servicio->precio ?? 0),
            ];
        }

        if (empty($items)) {
            return; // No hay nada que vender, salir silenciosamente
        }

        // Validar stock disponible para productos
        foreach ($items as $it) {
            if ($it['tipo'] === 'producto') {
                $producto = $it['modelo'];
                $cantidad = $it['cantidad'];
                if ($producto->stock_disponible < $cantidad) {
                    throw ValidationException::withMessages([
                        'productos' => "Stock insuficiente para '{$producto->nombre}'. Disponible: {$producto->stock_disponible}, Solicitado: {$cantidad}",
                    ]);
                }
            }
        }

        // Crear venta
        $venta = Venta::create([
            'cliente_id' => $cita->cliente_id,
            'numero_venta' => $this->generarNumeroVenta(),
            'fecha' => now()->toDateString(),
            'estado' => EstadoVenta::Pendiente,
            'subtotal' => 0,
            'descuento_general' => 0,
            'iva' => 0,
            'total' => 0,
            'vendedor_type' => $cita->tecnico ? get_class($cita->tecnico) : null,
            'vendedor_id' => $cita->tecnico_id,
        ]);

        $subtotal = 0;
        foreach ($items as $it) {
            $modelo = $it['modelo'];
            $cantidad = $it['cantidad'];
            $precio = $it['precio'];
            $descuento = 0;
            $lineaSubtotal = $cantidad * $precio;
            $subtotal += $lineaSubtotal;

            VentaItem::create([
                'venta_id' => $venta->id,
                'ventable_id' => $modelo->id,
                'ventable_type' => get_class($modelo),
                'cantidad' => $cantidad,
                'precio' => $precio,
                'descuento' => $descuento,
                'subtotal' => $lineaSubtotal,
            ]);
        }

        // Actualizar totales simples (sin IVA configurable aquí)
        $venta->update([
            'subtotal' => $subtotal,
            'iva' => 0,
            'total' => $subtotal,
        ]);

        // Descontar inventario para productos
        $inventarioService = app(InventarioService::class);
        foreach ($items as $it) {
            if ($it['tipo'] === 'producto') {
                $producto = $it['modelo'];
                $cantidad = $it['cantidad'];
                $inventarioService->salida($producto, $cantidad, [
                    'motivo' => 'Venta generada desde cita completada',
                    'referencia' => $venta,
                    'detalles' => [
                        'cita_id' => $cita->id,
                    ],
                    'user_id' => $user?->id,
                ]);
            }
        }

        // Marcar en los pivotes de productos vendidos el id de venta
        foreach ($cita->productosVendidos as $producto) {
            $cita->productosVendidos()->updateExistingPivot($producto->id, [
                'venta_id' => $venta->id,
            ]);
        }
    }

    /**
     * Genera un número de venta único.
     */
    private function generarNumeroVenta(): string
    {
        $ultimo = Venta::orderBy('id', 'desc')->first();
        $numero = $ultimo ? $ultimo->id + 1 : 1;
        return 'VEN-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Método mejorado para guardar archivos
     */
    private function saveFiles(Request $request, array $fileFields, $existingFiles = [])
    {
        $filePaths = [];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                try {
                    $file = $request->file($field);

                    // Generar nombre único para evitar conflictos
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $filename = $originalName . '_' . now()->format('YmdHis') . '_' . substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 6) . '.' . $extension;

                    $path = $file->storeAs('citas', $filename, 'public');
                    $filePaths[$field] = $path;

                    // Eliminar el archivo anterior si existe
                    if (!empty($existingFiles[$field])) {
                        Storage::disk('public')->delete($existingFiles[$field]);
                    }
                } catch (\Exception $e) {
                    Log::error("Error al guardar el archivo {$field}: " . $e->getMessage());
                    $filePaths[$field] = $existingFiles[$field] ?? null;
                }
            } else {
                $filePaths[$field] = $existingFiles[$field] ?? null; // Conservar el archivo existente
            }
        }
        return $filePaths;
    }

    /**
     * Verificar disponibilidad del técnico
     */
    private function verificarDisponibilidadTecnico(int $tecnicoId, string $fechaHora, ?int $excludeId = null): void
    {
        $query = Cita::where('tecnico_id', $tecnicoId)
            ->where('fecha_hora', $fechaHora)
            ->where('estado', '!=', 'cancelado');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'El técnico ya tiene una cita programada en esta fecha y hora.'
            ]);
        }
    }

    /**
      * Eliminar una cita existente.
      */
    public function destroy(Cita $cita)
    {
        try {
            DB::beginTransaction();

            // Verificar si se puede eliminar la cita
            $this->verificarPuedeEliminar($cita);

            // Eliminar archivos asociados
            $archivos = [
                $cita->foto_equipo,
                $cita->foto_hoja_servicio,
                $cita->foto_identificacion
            ];

            foreach ($archivos as $archivo) {
                if ($archivo && Storage::disk('public')->exists($archivo)) {
                    Storage::disk('public')->delete($archivo);
                }
            }

            $cita->delete();

            DB::commit();

            return redirect()->route('citas.index')->with('success', 'Cita eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar cita: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar la cita.');
        }
    }

    /**
     * Mostrar detalles de una cita.
     */
    public function show(Cita $cita)
    {
        $cita->load(['cliente', 'tecnico', 'productosUtilizados', 'productosVendidos', 'serviciosRealizados']);

        return Inertia::render('Citas/Show', [
            'cita' => $cita,
        ]);
    }


    public function export(Request $request)
    {
        try {
            $query = Cita::with('tecnico', 'cliente');

            // Aplicar los mismos filtros que en index
            if ($s = trim((string) $request->input('search', ''))) {
                $query->where(function ($w) use ($s) {
                    $w->where('tipo_servicio', 'like', "%{$s}%")
                        ->orWhere('descripcion', 'like', "%{$s}%")
                        ->orWhere('problema_reportado', 'like', "%{$s}%")
                        ->orWhereHas('cliente', function($clienteQuery) use ($s) {
                            $clienteQuery->where('nombre_razon_social', 'like', "%{$s}%");
                        })
                        ->orWhereHas('tecnico', function($tecnicoQuery) use ($s) {
                            $tecnicoQuery->where('nombre', 'like', "%{$s}%");
                        });
                });
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('tecnico_id')) {
                $query->where('tecnico_id', $request->tecnico_id);
            }

            if ($request->filled('cliente_id')) {
                $query->where('cliente_id', $request->cliente_id);
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
            }


            $citas = $query->get();

            $filename = 'citas_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($citas) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($file, [
                    'ID',
                    'Cliente',
                    'Técnico',
                    'Tipo Servicio',
                    'Fecha y Hora',
                    'Estado',
                    'Prioridad',
                    'Tipo Equipo',
                    'Marca',
                    'Modelo',
                    'Fecha Creación'
                ]);

                foreach ($citas as $cita) {
                    fputcsv($file, [
                        $cita->id,
                        $cita->cliente?->nombre_razon_social ?? 'N/A',
                        $cita->tecnico?->nombre ?? 'N/A',
                        $cita->tipo_servicio,
                        $cita->fecha_hora?->format('d/m/Y H:i:s'),
                        $cita->estado,
                        $cita->prioridad ?? 'N/A',
                        'N/A',
                        'N/A',
                        'N/A',
                        $cita->created_at?->format('d/m/Y H:i:s')
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('Error en exportación de citas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar las citas.');
        }
    }

    /**
     * Verificar límite de citas por día para un técnico
     */
    private function verificarLimiteCitasPorDia(int $tecnicoId, string $fechaHora): void
    {
        $fecha = Carbon::parse($fechaHora)->toDateString();
        $inicioDia = Carbon::parse($fecha)->startOfDay();
        $finDia = Carbon::parse($fecha)->endOfDay();

        $citasEnDia = Cita::where('tecnico_id', $tecnicoId)
            ->whereBetween('fecha_hora', [$inicioDia, $finDia])
            ->where('estado', '!=', 'cancelado')
            ->count();

        // Límite de 8 citas por día
        if ($citasEnDia >= 8) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'El técnico ya tiene el máximo de 8 citas programadas para este día.'
            ]);
        }
    }

    /**
     * Verificar que el cliente no tenga múltiples citas activas
     */
    private function verificarCitasClienteActivas(int $clienteId, string $fechaHora): void
    {
        $fecha = Carbon::parse($fechaHora);

        // Verificar si el cliente tiene más de 2 citas activas en los próximos 7 días
        $citasActivas = Cita::where('cliente_id', $clienteId)
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->where('fecha_hora', '>=', now())
            ->where('fecha_hora', '<=', now()->addDays(7))
            ->count();

        if ($citasActivas >= 2) {
            throw ValidationException::withMessages([
                'cliente_id' => 'El cliente ya tiene múltiples citas activas. Complete las citas existentes antes de programar nuevas.'
            ]);
        }

        // Verificar si hay conflicto de horario el mismo día
        $citasMismoDia = Cita::where('cliente_id', $clienteId)
            ->whereDate('fecha_hora', $fecha->toDateString())
            ->where('estado', '!=', 'cancelado')
            ->where('fecha_hora', '!=', $fechaHora)
            ->count();

        if ($citasMismoDia > 0) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'El cliente ya tiene una cita programada para este día.'
            ]);
        }
    }

    /**
     * Verificar si se puede eliminar la cita (sin relaciones críticas)
     */
    private function verificarPuedeEliminar(Cita $cita): void
    {
        // No permitir eliminar citas completadas con menos de 30 días de antigüedad
        if ($cita->estado === Cita::ESTADO_COMPLETADO) {
            $diasDesdeCreacion = now()->diffInDays($cita->created_at);
            if ($diasDesdeCreacion < 30) {
                throw ValidationException::withMessages([
                    'cita' => 'No se pueden eliminar citas completadas con menos de 30 días de antigüedad por políticas de auditoría.'
                ]);
            }
        }

        // Verificar si la cita está en proceso (solo permitir cancelación)
        if ($cita->estado === Cita::ESTADO_EN_PROCESO) {
            throw ValidationException::withMessages([
                'cita' => 'No se puede eliminar una cita en proceso. Solo se puede cancelar.'
            ]);
        }
    }
}
