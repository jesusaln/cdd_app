<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Prestamo;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Jobs\SendWhatsAppTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PrestamoController extends Controller
{
    private const ITEMS_PER_PAGE = 10;

    // Columnas permitidas para ordenamiento
    private const ALLOWED_SORT_COLUMNS = [
        'id',
        'cliente_id',
        'monto_prestado',
        'monto_pagado',
        'monto_pendiente',
        'tasa_interes_mensual',
        'numero_pagos',
        'pagos_realizados',
        'pagos_pendientes',
        'estado',
        'fecha_inicio',
        'fecha_primer_pago',
        'created_at',
        'updated_at'
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Prestamo::query()->with(['cliente']);

            // Filtros
            if ($search = $request->input('search')) {
                $query->whereHas('cliente', function ($q) use ($search) {
                    $q->where('nombre_razon_social', 'like', "%{$search}%")
                      ->orWhere('rfc', 'like', "%{$search}%");
                });
            }

            if ($estado = $request->input('estado')) {
                $query->where('estado', $estado);
            }

            if ($cliente_id = $request->input('cliente_id')) {
                $query->where('cliente_id', $cliente_id);
            }

            // Ordenamiento seguro
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            // Validar columna permitida para ordenamiento
            if (!in_array($sortBy, self::ALLOWED_SORT_COLUMNS)) {
                $sortBy = 'created_at';
            }

            // Validar dirección de ordenamiento
            if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
                $sortDirection = 'desc';
            }

            $query->orderBy($sortBy, $sortDirection);

            // Paginación con soporte para per_page
            $perPage = $request->get('per_page', self::ITEMS_PER_PAGE);
            $perPage = max(1, min(100, (int)$perPage)); // Limitar entre 1 y 100

            $prestamos = $query->paginate($perPage)->appends($request->query());

            // Estadísticas
            $estadisticas = [
                'total' => Prestamo::count(),
                'activos' => Prestamo::where('estado', 'activo')->count(),
                'completados' => Prestamo::where('estado', 'completado')->count(),
                'cancelados' => Prestamo::where('estado', 'cancelado')->count(),
                'monto_total_prestado' => Prestamo::sum('monto_prestado'),
                'monto_total_pagado' => Prestamo::sum('monto_pagado'),
                'monto_total_pendiente' => Prestamo::sum('monto_pendiente'),
            ];

            return Inertia::render('Prestamos/Index', [
                'prestamos' => $prestamos,
                'estadisticas' => $estadisticas,
                'filters' => $request->only(['search', 'estado', 'cliente_id']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
                'pagination' => [
                    'current_page' => $prestamos->currentPage(),
                    'last_page' => $prestamos->lastPage(),
                    'per_page' => $prestamos->perPage(),
                    'from' => $prestamos->firstItem(),
                    'to' => $prestamos->lastItem(),
                    'total' => $prestamos->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error en PrestamoController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la lista de préstamos.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            // Obtener clientes activos para el componente de búsqueda
            $clientes = Cliente::where('activo', true)
                ->orderBy('nombre_razon_social')
                ->get([
                    'id',
                    'nombre_razon_social',
                    'rfc',
                    'email',
                    'telefono',
                    'estado',
                    'limite_credito',
                    'credito_disponible'
                ]);

            return Inertia::render('Prestamos/Create', [
                'clientes' => $clientes,
                'prestamo' => [
                    'cliente_id' => null,
                    'monto_prestado' => 0,
                    'tasa_interes_mensual' => 0,
                    'numero_pagos' => 12,
                    'frecuencia_pago' => 'mensual',
                    'fecha_inicio' => now()->format('Y-m-d'),
                    'descripcion' => null,
                    'notas' => null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error en PrestamoController@create: ' . $e->getMessage());
            return redirect()->route('prestamos.index')->with('error', 'Error al cargar el formulario de creación.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'monto_prestado' => 'required|numeric|min:0.01|max:999999999.99',
                'tasa_interes_mensual' => 'required|numeric|min:0|max:100',
                'numero_pagos' => 'required|integer|min:1|max:1200',
                'frecuencia_pago' => 'required|in:semanal,quincenal,mensual',
                'fecha_inicio' => 'required|date|after_or_equal:today',
                'fecha_primer_pago' => 'nullable|date|after:fecha_inicio',
                'descripcion' => 'nullable|string|max:1000',
                'notas' => 'nullable|string|max:2000',
            ]);

            // Crear instancia del préstamo
            $prestamo = new Prestamo($validated);

            // Calcular fechas y pagos
            $this->calcularFechasYPagos($prestamo);

            // Calcular montos financieros con fórmula corregida
            $calculos = $prestamo->calcularPagos();
            $prestamo->pago_periodico = $calculos['pago_periodico'];
            $prestamo->monto_interes_total = $calculos['interes_total'];
            $prestamo->monto_total_pagar = $calculos['total_pagar'];
            $prestamo->monto_pendiente = $calculos['total_pagar'];
            $prestamo->pagos_pendientes = $prestamo->numero_pagos;

            $prestamo->save();

            // Crear pagos programados automáticamente
            $prestamo->crearPagosProgramados();

            DB::commit();

            Log::info('Préstamo creado', ['id' => $prestamo->id, 'cliente_id' => $prestamo->cliente_id]);

            return redirect()->route('prestamos.index')->with('success', 'Préstamo creado correctamente');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear préstamo: ' . $e->getMessage(), ['data' => $request->all()]);
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Prestamo $prestamo)
    {
        try {
            $prestamo->load(['cliente']);

            return Inertia::render('Prestamos/Show', [
                'prestamo' => $prestamo,
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('prestamos.index')->with('error', 'Préstamo no encontrado.');
        } catch (\Exception $e) {
            Log::error('Error al mostrar préstamo: ' . $e->getMessage());
            return redirect()->route('prestamos.index')->with('error', 'Error al cargar el préstamo.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prestamo $prestamo)
    {
        try {
            $prestamo->load(['cliente']);

            // Obtener clientes activos para el componente de búsqueda
            $clientes = Cliente::where('activo', true)
                ->orderBy('nombre_razon_social')
                ->get([
                    'id',
                    'nombre_razon_social',
                    'rfc',
                    'email',
                    'telefono',
                    'estado',
                    'limite_credito',
                    'credito_disponible'
                ]);

            return Inertia::render('Prestamos/Edit', [
                'prestamo' => $prestamo,
                'clientes' => $clientes,
                'puede_editar' => $prestamo->puedeSerEditado(),
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('prestamos.index')->with('error', 'Préstamo no encontrado.');
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return redirect()->route('prestamos.index')->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prestamo $prestamo)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'monto_prestado' => 'required|numeric|min:0.01|max:999999999.99',
                'tasa_interes_mensual' => 'required|numeric|min:0|max:100',
                'numero_pagos' => 'required|integer|min:1|max:1200',
                'frecuencia_pago' => 'required|in:semanal,quincenal,mensual',
                'fecha_inicio' => 'required|date',
                'fecha_primer_pago' => 'nullable|date|after:fecha_inicio',
                'descripcion' => 'nullable|string|max:1000',
                'notas' => 'nullable|string|max:2000',
            ]);

            // Si el préstamo ya tiene pagos, no permitir cambios en montos o términos
            if ($prestamo->pagos_realizados > 0) {
                $camposProhibidos = ['monto_prestado', 'tasa_interes_mensual', 'numero_pagos', 'frecuencia_pago'];
                foreach ($camposProhibidos as $campo) {
                    if (isset($validated[$campo]) && $validated[$campo] != $prestamo->$campo) {
                        throw ValidationException::withMessages([
                            $campo => 'No se puede modificar este campo porque el préstamo ya tiene pagos registrados.'
                        ]);
                    }
                }
            }

            // Actualizar préstamo
            $prestamo->fill($validated);

            // Recalcular si es necesario
            if ($prestamo->pagos_realizados == 0) {
                $this->calcularFechasYPagos($prestamo);
                $calculos = $prestamo->calcularPagos();
                $prestamo->pago_periodico = $calculos['pago_periodico'];
                $prestamo->monto_interes_total = $calculos['interes_total'];
                $prestamo->monto_total_pagar = $calculos['total_pagar'];
                $prestamo->monto_pendiente = $calculos['total_pagar'];
                $prestamo->pagos_pendientes = $prestamo->numero_pagos;
            }

            $prestamo->save();

            DB::commit();

            Log::info('Préstamo actualizado', ['id' => $prestamo->id]);

            return redirect()->route('prestamos.index')->with('success', 'Préstamo actualizado correctamente');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar préstamo: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prestamo $prestamo)
    {
        try {
            if (!$prestamo->puedeSerEliminado()) {
                return redirect()->back()->with('error',
                    'No se puede eliminar el préstamo porque tiene pagos registrados o está activo.');
            }

            Log::info('Eliminando préstamo', [
                'id' => $prestamo->id,
                'cliente_id' => $prestamo->cliente_id
            ]);

            $prestamo->delete();

            return redirect()->route('prestamos.index')->with('success', 'Préstamo eliminado correctamente');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('prestamos.index')->with('error', 'Préstamo no encontrado.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar préstamo: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error interno al eliminar el préstamo.');
        }
    }

    /**
     * Calcular fechas de pagos y actualizar modelo
     */
    private function calcularFechasYPagos(Prestamo $prestamo): void
    {
        $fechaInicio = $prestamo->fecha_inicio;

        // Si no hay fecha de primer pago, calcular según frecuencia
        if (!$prestamo->fecha_primer_pago) {
            $diasSumar = match($prestamo->frecuencia_pago) {
                'semanal' => 7,
                'quincenal' => 15,
                'mensual' => 30,
                default => 30,
            };

            $prestamo->fecha_primer_pago = $fechaInicio->copy()->addDays($diasSumar);
        }
    }

    /**
      * API para calcular pagos en tiempo real
      */
    public function calcularPagos(Request $request)
    {
        try {
            Log::info('Solicitud de cálculo de pagos recibida', [
                'data' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'has_session' => $request->session()->isStarted(),
                'session_id' => $request->session()->getId(),
                'csrf_header' => $request->header('X-CSRF-TOKEN') ? 'present' : 'missing',
                'csrf_input' => $request->input('_token') ? 'present' : 'missing',
            ]);

            // Validación más permisiva para evitar problemas CSRF
            $validated = $request->validate([
                'monto_prestado' => 'required|numeric|min:0.01',
                'tasa_interes_mensual' => 'required|numeric|min:0|max:100',
                'numero_pagos' => 'required|integer|min:1|max:1200',
                'frecuencia_pago' => 'required|in:semanal,quincenal,mensual',
            ]);

            Log::info('Datos validados para cálculo', ['validated' => $validated]);

            $prestamo = new Prestamo($validated);
            $calculos = $prestamo->calcularPagos();

            Log::info('Cálculos realizados exitosamente', ['calculos' => $calculos]);

            return response()->json([
                'success' => true,
                'calculos' => $calculos,
                'debug' => [
                    'session_active' => true,
                    'csrf_bypass' => true
                ]
            ]);
        } catch (ValidationException $e) {
            Log::warning('Error de validación en cálculo de pagos', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error calculando pagos: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno al calcular pagos'
            ], 500);
        }
    }

    /**
     * Cambiar estado del préstamo
     */
    public function cambiarEstado(Request $request, Prestamo $prestamo)
    {
        try {
            $validated = $request->validate([
                'estado' => 'required|in:activo,completado,cancelado',
            ]);

            // Validaciones de seguridad
            if ($validated['estado'] === 'cancelado' && !$prestamo->puedeSerCancelado()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cancelar un préstamo que ya tiene pagos registrados.'
                ], 422);
            }

            if ($validated['estado'] === 'completado' && $prestamo->monto_pendiente > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede completar un préstamo con pagos pendientes.'
                ], 422);
            }

            $prestamo->estado = $validated['estado'];
            $prestamo->save();

            Log::info('Estado de préstamo cambiado', [
                'id' => $prestamo->id,
                'nuevo_estado' => $validated['estado']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'prestamo' => $prestamo,
            ]);
        } catch (\Exception $e) {
            Log::error('Error cambiando estado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado'
            ], 500);
        }
    }

    /**
     * Generar pagaré en PDF
     */
    public function generarPagare(Prestamo $prestamo)
    {
        try {
            $prestamo->load(['cliente']);

            // Obtener datos de la empresa
            $empresa = \App\Models\EmpresaConfiguracion::first();
            $empresaNombre = $empresa ? $empresa->razon_social : 'CLIMAS DEL DESIERTO';
            $empresaRfc = $empresa ? $empresa->rfc : 'XAXX010101000';
            $empresaDireccion = $empresa ? $empresa->direccion_completa : 'Hermosillo, Sonora, México';

            // Datos para el pagaré
            $datosPagare = [
                'prestamo' => $prestamo,
                'cliente' => $prestamo->cliente,
                'empresa' => [
                    'nombre' => $empresaNombre,
                    'rfc' => $empresaRfc,
                    'direccion' => $empresaDireccion,
                ],
                'fecha_actual' => now()->format('d/m/Y'),
                'monto_letras' => $this->numeroALetras($prestamo->monto_prestado),
                'tasa_mensual' => floatval($prestamo->tasa_interes_mensual),
                'pago_mensual_letras' => $this->numeroALetras($prestamo->pago_periodico),
            ];

            return Inertia::render('Prestamos/Pagare', $datosPagare);
        } catch (\Exception $e) {
            Log::error('Error generando pagaré: ' . $e->getMessage());
            return redirect()->route('prestamos.index')->with('error', 'Error al generar el pagaré.');
        }
    }

    /**
     * Convertir número a letras (función mejorada)
     */
    private function numeroALetras($numero)
    {
        $unidades = ['', 'un', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
        $decenas = ['', 'diez', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        $centenas = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];
        $especiales = [
            11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce', 15 => 'quince',
            16 => 'dieciséis', 17 => 'diecisiete', 18 => 'dieciocho', 19 => 'diecinueve',
            21 => 'veintiuno', 22 => 'veintidós', 23 => 'veintitrés', 24 => 'veinticuatro', 25 => 'veinticinco',
            26 => 'veintiséis', 27 => 'veintisiete', 28 => 'veintiocho', 29 => 'veintinueve'
        ];

        $entero = intval($numero);
        $decimales = intval(round(($numero - $entero) * 100));

        if ($entero == 0) return 'cero';

        $letras = '';

        // Miles (si es necesario)
        if ($entero >= 1000) {
            $miles = intval($entero / 1000);
            if ($miles == 1) {
                $letras = 'mil';
            } else {
                $letras = $this->numeroALetras($miles) . ' mil';
            }
            $entero %= 1000;
        }

        // Centenas
        if ($entero >= 100) {
            if ($entero == 100) {
                $letras .= ' cien';
            } else {
                $letras .= ' ' . $centenas[intval($entero / 100)];
            }
            $entero %= 100;
        }

        // Decenas y unidades
        if ($entero > 0) {
            if ($letras != '') $letras .= ' ';

            if (isset($especiales[$entero])) {
                $letras .= $especiales[$entero];
            } elseif ($entero >= 10) {
                if ($entero < 30) {
                    $letras .= $decenas[$entero - 10];
                } else {
                    $letras .= $decenas[intval($entero / 10)];
                    if ($entero % 10 > 0) {
                        $letras .= ' y ' . $unidades[$entero % 10];
                    }
                }
            } else {
                $letras .= $unidades[$entero];
            }
        }

        $resultado = trim($letras) . ' pesos';

        // Agregar centavos si los hay
        if ($decimales > 0) {
            $resultado .= ' con ' . $decimales . '/100';
        }

        return $resultado;
    }

    /**
     * Enviar recordatorio por WhatsApp
     */
    public function enviarRecordatorioWhatsApp(Request $request, Prestamo $prestamo)
    {
        try {
            // Verificar que el préstamo esté activo
            if ($prestamo->estado !== 'activo') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden enviar recordatorios para préstamos activos'
                ], 400);
            }

            // Verificar que el cliente tenga teléfono
            if (!$prestamo->cliente->telefono) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cliente no tiene número de teléfono registrado'
                ], 400);
            }

            // Obtener empresa para configuración de WhatsApp
            $empresa = Empresa::first();

            if (!$empresa || !$empresa->whatsapp_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp no está configurado para esta empresa'
                ], 400);
            }

            // Preparar parámetros para la plantilla
            // Nota: Ajustar según los parámetros que acepte la plantilla
            // Para plantilla "saludo", usar solo el nombre del cliente
            $templateParams = [
                $prestamo->cliente->nombre_razon_social,
            ];

            // Formatear número de teléfono para WhatsApp
            $telefonoOriginal = $prestamo->cliente->telefono;
            $telefonoFormateado = \App\Services\WhatsAppService::formatPhoneToE164($telefonoOriginal);

            // Despachar job para envío
            SendWhatsAppTemplate::dispatch(
                $empresa->id,
                $telefonoOriginal, // El servicio formateará automáticamente
                $empresa->whatsapp_template_payment_reminder,
                $empresa->whatsapp_default_language,
                $templateParams,
                [
                    'tipo' => 'recordatorio_prestamo',
                    'prestamo_id' => $prestamo->id,
                ]
            );

            Log::info('Recordatorio WhatsApp programado', [
                'prestamo_id' => $prestamo->id,
                'cliente_id' => $prestamo->cliente_id,
                'cliente_telefono' => $telefonoOriginal,
                'telefono_formateado' => $telefonoFormateado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Recordatorio enviado por WhatsApp a ' . $prestamo->cliente->nombre_razon_social
            ]);

        } catch (\Exception $e) {
            Log::error('Error al enviar recordatorio WhatsApp', [
                'prestamo_id' => $prestamo->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar recordatorio: ' . $e->getMessage()
            ], 500);
        }
    }
}
