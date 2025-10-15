<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Cliente;
use App\Models\SatEstado;
use App\Models\SatRegimenFiscal;
use App\Models\SatUsoCfdi;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;
use Facturapi\Facturapi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientesExport;
use Illuminate\Support\Facades\Gate;



class ClienteController extends Controller
{
    private const ITEMS_PER_PAGE = 10;
    private const CACHE_TTL = 60;

    // Constantes para validaciones y valores por defecto
    private const DEFAULT_COUNTRY = 'MX';
    private const RFC_GENERIC_FOREIGN = 'XEXX010101000';
    private const MIN_SEARCH_LENGTH = 2;
    private const MAX_SEARCH_LIMIT = 50;
    private const VALID_SORT_FIELDS = ['nombre_razon_social', 'rfc', 'email', 'created_at', 'activo'];
    private const VALID_SORT_DIRECTIONS = ['asc', 'desc'];
    private const DEFAULT_SORT_BY = 'created_at';
    private const DEFAULT_SORT_DIRECTION = 'desc';

    // Estados de registros relacionados que bloquean eliminación
    private const BLOCKING_STATES = [
        'cotizaciones' => ['cancelada'],
        'ventas' => ['cancelada'],
        'pedidos' => ['cancelado'],
        'rentas' => ['finalizada']
    ];

    // ========================= Helpers de catálogos (DB + Cache) =========================
    private function getTiposPersona(): array
    {
        // Usamos valores fijos pero mínimos (no hardcodeamos regímenes/estados/usos)
        return Cache::remember('tipos_persona', self::CACHE_TTL, fn() => [
            'fisica' => 'Persona Física',
            'moral'  => 'Persona Moral',
        ]);
    }

    private function getRegimenesFiscales(): array
    {
        try {
            return Cache::remember('regimenes_fiscales_db', self::CACHE_TTL, function () {
                return SatRegimenFiscal::orderBy('clave')
                    ->get(['clave', 'descripcion', 'persona_fisica', 'persona_moral'])
                    ->keyBy('clave')
                    ->toArray();
            });
        } catch (\Exception $e) {
            Log::warning('Error obteniendo regímenes fiscales del cache, usando DB directa', ['error' => $e->getMessage()]);
            return SatRegimenFiscal::orderBy('clave')
                ->get(['clave', 'descripcion', 'persona_fisica', 'persona_moral'])
                ->keyBy('clave')
                ->toArray();
        }
    }

    private function getUsosCFDI(): array
    {
        try {
            return Cache::remember('usos_cfdi_db', self::CACHE_TTL, function () {
                return SatUsoCfdi::orderBy('clave')
                    ->get(['clave', 'descripcion', 'regimen_fiscal_receptor', 'activo'])
                    ->keyBy('clave')
                    ->toArray();
            });
        } catch (\Exception $e) {
            Log::warning('Error obteniendo usos CFDI del cache, usando DB directa', ['error' => $e->getMessage()]);
            return SatUsoCfdi::orderBy('clave')
                ->get(['clave', 'descripcion', 'regimen_fiscal_receptor', 'activo'])
                ->keyBy('clave')
                ->toArray();
        }
    }

    private function getEstadosMexico(): array
    {
        try {
            // Estados SAT (3 letras) -> nombre
            return Cache::remember('estados_mexico_db', self::CACHE_TTL, function () {
                return SatEstado::orderBy('nombre')
                    ->get(['clave', 'nombre'])
                    ->pluck('nombre', 'clave') // ['SON' => 'Sonora', ...]
                    ->toArray();
            });
        } catch (\Exception $e) {
            Log::warning('Error obteniendo estados del cache, usando DB directa', ['error' => $e->getMessage()]);
            return SatEstado::orderBy('nombre')
                ->get(['clave', 'nombre'])
                ->pluck('nombre', 'clave')
                ->toArray();
        }
    }

    private function estadosSatCsv(): string
    {
        return implode(',', array_map(function($estado) {
            return '"' . $estado . '"';
        }, array_keys($this->getEstadosMexico()))); // "AGU,BCN,..."
    }

    private function formatForVueSelect(array $options, bool $includeEmpty = false, bool $showCode = false): array
    {
        // $options puede venir como ['clave' => ['descripcion'=>..., ...]] o ['clave' => 'texto'].
        $formatted = collect($options)->map(function ($value, $key) use ($showCode) {
            if (is_array($value)) {
                $label = $value['descripcion'] ?? ($value['nombre'] ?? $key);
            } else {
                $label = $value;
            }
            return [
                'value' => $key,
                'label' => $showCode ? "{$key} - {$label}" : $label,
            ];
        })->values()->toArray();

        if ($includeEmpty) {
            array_unshift($formatted, ['value' => '', 'label' => 'Selecciona una opción']);
        }
        return $formatted;
    }

    private function getTipoPersonaNombre(?string $codigo): string
    {
        if (!$codigo) {
            return 'No aplica';
        }
        $tipos = $this->getTiposPersona();
        return $tipos[$codigo] ?? $codigo;
    }

    private function getRegimenFiscalNombre(?string $codigo): string
    {
        if (!$codigo) {
            return 'No aplica';
        }
        $reg = $this->getRegimenesFiscales();
        return isset($reg[$codigo]) ? ($reg[$codigo]['descripcion'] ?? $codigo) : $codigo;
    }

    private function getUsoCFDINombre(?string $codigo): string
    {
        if (!$codigo) {
            return 'No aplica';
        }
        $u = $this->getUsosCFDI();
        return isset($u[$codigo]) ? ($u[$codigo]['descripcion'] ?? $codigo) : $codigo;
    }

    private function getEstadoNombre(?string $clave): ?string
    {
        if (!$clave) return 'No especificado';

        // Si la clave tiene 3 caracteres, buscar en la tabla SAT
        if (strlen($clave) === 3) {
            $est = $this->getEstadosMexico();
            return $est[$clave] ?? $clave;
        }

        // Si es texto más largo, devolver tal cual (nuevo formato)
        return $clave;
    }

    private function getCatalogData(): array
    {
        return [
            'tiposPersona'      => $this->formatForVueSelect($this->getTiposPersona(), true),
            'regimenesFiscales' => $this->formatForVueSelect($this->getRegimenesFiscales(), true, false), // ← false aquí
            'usosCFDI'          => $this->formatForVueSelect($this->getUsosCFDI(), true, false),          // ← false aquí
            'estados'           => $this->formatForVueSelect($this->getEstadosMexico(), true),
        ];
    }

    private function hasFulltextIndex(): bool
    {
        $driver = Schema::getConnection()->getDriverName();
        return $driver === 'mysql';
    }

    // ========================= Query base =========================
    // En tu ClienteController (o trait/repo)
    private function buildSearchQuery(Request $request)
    {
        // Optimizar carga de relaciones - solo cargar las necesarias
        $withRelations = [];
        if ($request->input('include_relations', false)) {
            $withRelations = ['estadoSat', 'regimen', 'uso'];
        }

        $q = \App\Models\Cliente::query()->with($withRelations);

        if ($s = trim((string) $request->input('search', ''))) {
            // Use FULLTEXT search if available and query is long enough
            if (strlen($s) >= 3 && $this->hasFulltextIndex()) {
                $q->whereRaw("MATCH(nombre_razon_social, email, rfc) AGAINST(? IN NATURAL LANGUAGE MODE)", [$s]);
            } else {
                // Fallback to LIKE search
                $q->where(function ($w) use ($s) {
                    $w->where('nombre_razon_social', 'like', "%{$s}%")
                        ->orWhere('rfc', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                });
            }
        }

        if ($tp = $request->input('tipo_persona')) {
            $q->where('tipo_persona', $tp);
        }
        if ($rf = $request->input('regimen_fiscal')) {
            $q->where('regimen_fiscal', $rf);
        }
        if ($uso = $request->input('uso_cfdi')) {
            $q->where('uso_cfdi', $uso);
        }
        if ($edo = $request->input('estado')) {
            $q->where('estado', $edo);
        }

        // Filtrar por estado activo/inactivo
        if ($request->query->has('activo')) {
            $val = (string) $request->query('activo');

            if ($val === '1') {
                // Activos: true o NULL (considerar NULL como true por defecto)
                $q->where(function ($query) {
                    $query->where('activo', true)->orWhereNull('activo');
                });
            } elseif ($val === '0') {
                // Inactivos: solo false
                $q->where('activo', false);
            }
            // Si NO es '0' ni '1', NO filtramos (mostrar todos)
        }
        // Por defecto, mostrar todos los clientes (activos e inactivos)


        return $q->orderByDesc('created_at');
    }


    private function transformClientesPaginator($paginator)
    {
        $collection = $paginator->getCollection();

        $collection->transform(function ($cliente) {
            // Solo calcular nombres si las relaciones están cargadas
            $cliente->tipo_persona_nombre   = $this->getTipoPersonaNombre($cliente->tipo_persona);

            if ($cliente->relationLoaded('regimen')) {
                $cliente->regimen_fiscal_nombre = $cliente->regimen?->descripcion ?? $this->getRegimenFiscalNombre($cliente->regimen_fiscal);
            } else {
                $cliente->regimen_fiscal_nombre = $this->getRegimenFiscalNombre($cliente->regimen_fiscal);
            }

            if ($cliente->relationLoaded('uso')) {
                $cliente->uso_cfdi_nombre = $cliente->uso?->descripcion ?? $this->getUsoCFDINombre($cliente->uso_cfdi);
            } else {
                $cliente->uso_cfdi_nombre = $this->getUsoCFDINombre($cliente->uso_cfdi);
            }

            if ($cliente->relationLoaded('estadoSat')) {
                $cliente->estado_nombre = $cliente->estadoSat?->nombre ?? $this->getEstadoNombre($cliente->estado);
            } else {
                $cliente->estado_nombre = $this->getEstadoNombre($cliente->estado);
            }

            $cliente->estado_texto = $cliente->activo ? 'Activo' : 'Inactivo';

            // Agregar indicador si requiere factura
            $cliente->requiere_factura_texto = $cliente->requiere_factura ? 'Sí requiere factura' : 'No requiere factura';

            // Agregar conteo de préstamos
            $cliente->prestamos_count = $cliente->prestamos()->count();

            return $cliente;
        });

        $paginator->setCollection($collection);
        return $paginator;
    }

    // ========================= Validaciones =========================
    private function getRfcValidationRules(?int $clienteId = null): array
    {
        return [
            'required',
            'string',
            'max:13',
            'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/',
            function ($attribute, $value, $fail) use ($clienteId) {
                $value = strtoupper(trim($value));

                // Use cache for faster lookup
                $existingRfc = Cache::remember("rfc_exists_{$value}", 30, function () use ($value) {
                    return Cliente::where('rfc', $value)->value('id');
                });

                $isDuplicate = $existingRfc && (!$clienteId || $existingRfc != $clienteId);

                if ($isDuplicate) {
                    if ($value === self::RFC_GENERIC_FOREIGN) {
                        $fail('Ya existe el cliente genérico. No se pueden crear múltiples clientes con RFC genérico.');
                    } else {
                        $fail('El RFC ya está registrado.');
                    }
                }
            },
        ];
    }

    private function getRegimenFiscalValidationRules(): array
    {
        // validamos en DB + compatibilidad con tipo_persona
        return [
            'required',
            'string',
            'exists:sat_regimenes_fiscales,clave',
            function ($attribute, $value, $fail) {
                $tipo = request()->input('tipo_persona');
                $rf = SatRegimenFiscal::find($value);
                if (!$rf) return; // exists ya fallará

                if ($tipo === 'fisica' && !$rf->persona_fisica) {
                    $fail("El régimen fiscal '{$rf->clave} - {$rf->descripcion}' no es válido para Persona Física. Selecciona un régimen fiscal para personas físicas.");
                }
                if ($tipo === 'moral' && !$rf->persona_moral) {
                    $fail("El régimen fiscal '{$rf->clave} - {$rf->descripcion}' no es válido para Persona Moral. Selecciona un régimen fiscal para personas morales.");
                }
            },
        ];
    }

    private function getValidationRules(?int $clienteId = null): array
    {
        $estadosCsv = $this->estadosSatCsv();

        return [
            'nombre_razon_social' => 'required|string|max:255|regex:/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜ\s\.,&\-\']+$/',
            'tipo_persona'        => 'required|in:fisica,moral',
            'rfc'                 => $this->getRfcValidationRules($clienteId),
            'regimen_fiscal'      => $this->getRegimenFiscalValidationRules(),
            'uso_cfdi'            => 'required|string|exists:sat_usos_cfdi,clave',
            'email'               => ['required', 'email', 'max:255'],
            'telefono'            => 'nullable|string|size:10|regex:/^[0-9]{10}$/',
            'calle'               => 'required|string|max:255',
            'numero_exterior'     => 'required|string|max:20',
            'numero_interior'     => 'nullable|string|max:20',
            'colonia'             => 'required|string|max:255',
            'codigo_postal'       => 'required|string|size:5|regex:/^[0-9]{5}$/',
            'municipio'           => 'required|string|max:255',
            'estado'              => "required|string|min:2|max:255", // nombre completo del estado (sin validaciones estrictas)
            'pais'                => 'required|string|in:' . self::DEFAULT_COUNTRY,
            'notas'               => 'nullable|string|max:1000',
            'activo'              => 'boolean',
        ];
    }

    private function getValidationMessages(): array
    {
        return [
            'nombre_razon_social.required' => 'El nombre o razón social es obligatorio.',
            'nombre_razon_social.regex'    => 'El nombre/razón social contiene caracteres no válidos.',
            'tipo_persona.required'        => 'El tipo de persona es obligatorio.',
            'tipo_persona.in'              => 'El tipo de persona debe ser física o moral.',
            'rfc.required'                 => 'El RFC es obligatorio.',
            'rfc.regex'                    => 'El RFC no tiene un formato válido.',
            'regimen_fiscal.required'      => 'El régimen fiscal es obligatorio.',
            'regimen_fiscal.exists'        => 'El régimen fiscal no existe en el catálogo.',
            'uso_cfdi.required'            => 'El uso de CFDI es obligatorio.',
            'uso_cfdi.exists'              => 'El uso de CFDI seleccionado no es válido.',
            'email.required'               => 'El email es obligatorio.',
            'email.email'                  => 'El email debe tener un formato válido.',
            'telefono.size'                => 'El teléfono debe tener exactamente 10 dígitos.',
            'telefono.regex'               => 'El teléfono debe contener solo números (10 dígitos).',
            'calle.required'               => 'La calle es obligatoria.',
            'numero_exterior.required'     => 'El número exterior es obligatorio.',
            'colonia.required'             => 'La colonia es obligatoria.',
            'codigo_postal.required'       => 'El código postal es obligatorio.',
            'codigo_postal.size'           => 'El código postal debe tener 5 dígitos.',
            'codigo_postal.regex'          => 'El código postal debe contener solo números.',
            'municipio.required'           => 'El municipio es obligatorio.',
            'estado.required'              => 'El estado es obligatorio.',
            'estado.min'                   => 'El estado es obligatorio y debe tener al menos 2 caracteres.',
            'pais.required'                => 'El país es obligatorio.',
            'pais.in'                      => 'El país debe ser ' . self::DEFAULT_COUNTRY . '.',
            'notas.max'                    => 'Las notas no pueden exceder 1000 caracteres.',
        ];
    }

    private function formatClienteForView(Cliente $c): Cliente
    {
        $c->tipo_persona_nombre   = $this->getTipoPersonaNombre($c->tipo_persona);
        $c->regimen_fiscal_nombre = $c->regimen?->descripcion ?? $this->getRegimenFiscalNombre($c->regimen_fiscal);
        $c->uso_cfdi_nombre       = $c->uso?->descripcion ?? $this->getUsoCFDINombre($c->uso_cfdi);

        // Para el estado, usar el valor tal cual si no se encuentra en la tabla SAT
        // Esto permite que funcione tanto con códigos como con nombres completos
        $c->estado_nombre = $c->estadoSat?->nombre ?? $c->estado;
        $c->estado_texto  = $c->activo ? 'Activo' : 'Inactivo';

        return $c;
    }

    /**
     * Crear respuesta de error para operaciones de cliente
     */
    private function handleClienteError(string $message, array $context = []): \Illuminate\Http\RedirectResponse
    {
        Log::error($message, $context);
        return redirect()->route('clientes.index')->with('error', $message);
    }

    /**
     * Crear respuesta de éxito para operaciones de cliente
     */
    private function handleClienteSuccess(string $message): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('clientes.index')->with('success', $message);
    }

    /**
     * Preparar datos de cliente para Facturapi
     */
    private function prepareFacturapiData(Cliente $cliente, array $data = null): array
    {
        $useData = $data !== null;

        $nombre = $useData ? ($data['nombre_razon_social'] ?? $cliente->nombre_razon_social) : $cliente->nombre_razon_social;
        $email = $useData ? ($data['email'] ?? $cliente->email) : $cliente->email;
        $rfc = $useData ? ($data['rfc'] ?? $cliente->rfc) : $cliente->rfc;
        $codigo_postal = $useData ? ($data['codigo_postal'] ?? $cliente->codigo_postal) : $cliente->codigo_postal;
        $municipio = $useData ? ($data['municipio'] ?? $cliente->municipio) : $cliente->municipio;
        $estado = $useData ? ($data['estado'] ?? $cliente->estado) : $cliente->estado;
        $uso_cfdi = $useData ? ($data['uso_cfdi'] ?? $cliente->uso_cfdi) : $cliente->uso_cfdi;

        $calle = $useData ? ($data['calle'] ?? $cliente->calle) : $cliente->calle;
        $numero_exterior = $useData ? ($data['numero_exterior'] ?? $cliente->numero_exterior) : $cliente->numero_exterior;
        $numero_interior = $useData ? ($data['numero_interior'] ?? $cliente->numero_interior) : $cliente->numero_interior;
        $colonia = $useData ? ($data['colonia'] ?? $cliente->colonia) : $cliente->colonia;

        $address1 = trim(implode(' ', array_filter([
            $calle,
            $numero_exterior,
            $numero_interior ? "Int. {$numero_interior}" : null,
            $colonia,
            $municipio
        ])));

        return [
            'name' => $nombre,
            'email' => $email,
            'rfc' => $rfc,
            'address1' => $address1,
            'postal_code' => $codigo_postal,
            'city' => $municipio,
            'state' => $estado,
            'country' => 'MX',
            'cfdi_use' => $uso_cfdi,
        ];
    }

    // ============================= CRUD =============================
    public function index(Request $request)
    {
        try {
            $query = $this->buildSearchQuery($request);

            $sortBy        = $request->get('sort_by', self::DEFAULT_SORT_BY);
            $sortDirection = $request->get('sort_direction', self::DEFAULT_SORT_DIRECTION);

            if (!in_array($sortBy, self::VALID_SORT_FIELDS)) $sortBy = self::DEFAULT_SORT_BY;
            if (!in_array($sortDirection, self::VALID_SORT_DIRECTIONS)) $sortDirection = self::DEFAULT_SORT_DIRECTION;

            $query->orderBy($sortBy, $sortDirection);

            $clientes = $query->paginate(self::ITEMS_PER_PAGE)->appends($request->query());
            $clientes = $this->transformClientesPaginator($clientes);

            $clientesCount   = Cliente::count();
            $clientesActivos = Cliente::where(function ($q) {
                $q->where('activo', true)->orWhereNull('activo');
            })->count();

            return Inertia::render('Clientes/Index', [
                'clientes' => $clientes,
                'estadisticas'    => [
                    'total'     => $clientesCount,
                    'activos'   => $clientesActivos,
                    'inactivos' => $clientesCount - $clientesActivos,
                    'personas_fisicas' => Cliente::where('tipo_persona', 'fisica')->count(),
                    'personas_morales' => Cliente::where('tipo_persona', 'moral')->count(),
                    'nuevos_mes' => Cliente::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                ],
                'pagination' => [
                    'current_page' => $clientes->currentPage(),
                    'last_page' => $clientes->lastPage(),
                    'per_page' => $clientes->perPage(),
                    'from' => $clientes->firstItem(),
                    'to' => $clientes->lastItem(),
                    'total' => $clientes->total(),
                ],
                'catalogs' => $this->getCatalogData(),
                'filters'  => $request->only(['search', 'tipo_persona', 'regimen_fiscal', 'uso_cfdi', 'activo', 'estado']),
                'sorting'  => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (Exception $e) {
            Log::error('Error en ClienteController@index: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Error al cargar la lista de clientes.');
        }
    }

    public function create()
    {
        return Inertia::render('Clientes/Create', [
            'catalogs' => [
                'tiposPersona' => [
                    ['value' => 'fisica', 'text' => 'Persona Física'],
                    ['value' => 'moral', 'text' => 'Persona Moral'],
                ],
                'estados' => SatEstado::orderBy('nombre')
                    ->get(['clave', 'nombre'])
                    ->map(function ($estado) {
                        return [
                            'value' => $estado->clave,
                            'text' => $estado->clave . ' — ' . $estado->nombre
                        ];
                    })
                    ->toArray(),
                'regimenesFiscales' => SatRegimenFiscal::orderBy('clave')
                    ->get(['clave', 'descripcion', 'persona_fisica', 'persona_moral'])
                    ->toArray(),
                'usosCFDI' => SatUsoCfdi::orderBy('clave')
                    ->get(['clave', 'descripcion', 'regimen_fiscal_receptor', 'activo'])
                    ->map(function ($uso) {
                        return [
                            'value' => $uso->clave,
                            'text' => $uso->clave . ' — ' . $uso->descripcion
                        ];
                    })
                    ->toArray(),
            ],
            'cliente' => [ // valores por defecto
                'tipo_persona' => 'fisica',
                'pais' => 'MX',
                'estado' => 'SON', // Sonora por defecto
                'uso_cfdi' => 'G03', // G03 - Gastos en general
            ],
        ]);
    }

    public function store(StoreClienteRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Normalización de datos básicos (siempre presentes)
            $data['nombre_razon_social'] = trim($data['nombre_razon_social']);
            $data['pais']                = self::DEFAULT_COUNTRY; // Forzado

            // Solo normalizar campos fiscales si están presentes y no son null
            if (isset($data['rfc']) && !is_null($data['rfc'])) {
                $data['rfc'] = strtoupper(trim($data['rfc']));
            }

            if (isset($data['email']) && !is_null($data['email'])) {
                $data['email'] = strtolower(trim($data['email']));
            }

            // Establecer domicilio_fiscal_cp igual al codigo_postal para CFDI 4.0
            $data['domicilio_fiscal_cp'] = $data['codigo_postal'];

            $cliente = Cliente::create($data);

            // Solo validar CFDI si el cliente requiere factura
            if ($data['requiere_factura'] ?? false) {
                $erroresCfdi = $cliente->validarParaCfdi();
                if (!empty($erroresCfdi)) {
                    DB::rollBack();
                    throw ValidationException::withMessages([
                        'cfdi' => 'El cliente no cumple con los requisitos para facturación CFDI: ' . implode(', ', $erroresCfdi)
                    ]);
                }
            }

            // Crear notificaciones directamente (sistema simplificado)
            try {
                \App\Models\UserNotification::createClientNotification($cliente);
                Log::info('Notificaciones creadas para nuevo cliente', ['cliente_id' => $cliente->id]);
            } catch (\Exception $e) {
                Log::error('Error creando notificaciones para cliente', [
                    'cliente_id' => $cliente->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // No fallar la creación del cliente por error en notificaciones
            }

            // Solo integrar con Facturapi si requiere factura
            if (($data['requiere_factura'] ?? false) && empty($cliente->facturapi_customer_id)) {
                $this->createOrUpdateFacturapiCustomer($cliente);
            }

            DB::commit();

            Log::info('Cliente creado', ['id' => $cliente->id]);

            // Redirige a la lista con mensaje de éxito
            return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear cliente: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            // En lugar de redirect()->back()->with('error'), lanza el error
            // Inertia lo manejará y lo mostrará en form.errors
            throw $e;
        }
    }

    public function show(Cliente $cliente)
    {
        try {
            // Optimizar carga de relaciones - solo cargar las necesarias para mostrar
            $cliente->load(['regimen', 'uso', 'estadoSat']);
            $cliente = $this->formatClienteForView($cliente);
            return Inertia::render('Clientes/Show', ['cliente' => $cliente]);
        } catch (ModelNotFoundException $e) {
            Log::warning('Cliente no encontrado', ['id' => request()->route('cliente')]);
            return redirect()->route('clientes.index')->with('error', 'Cliente no encontrado.');
        } catch (Exception $e) {
            Log::error('Error al mostrar cliente: ' . $e->getMessage());
            return redirect()->route('clientes.index')->with('error', 'Error al cargar el cliente.');
        }
    }

    public function edit(Cliente $cliente)
    {
        try {
            // Optimizar carga de relaciones - solo cargar las necesarias para editar
            $cliente->load(['regimen', 'uso', 'estadoSat']);
            $cliente = $this->formatClienteForView($cliente);

            return Inertia::render('Clientes/Edit', [
                'cliente'  => $cliente,
                'catalogs' => $this->getCatalogData(),
                'errors'   => session('errors') ? session('errors')->getBag('default')->getMessages() : [],
            ]);
        } catch (ModelNotFoundException $e) {
            Log::warning('Cliente no encontrado para edición', ['id' => request()->route('cliente')]);
            return redirect()->route('clientes.index')->with('error', 'Cliente no encontrado.');
        } catch (Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return redirect()->route('clientes.index')->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Normalización
            $data['rfc']                 = strtoupper(trim($data['rfc']));
            $data['email']               = strtolower(trim($data['email']));
            $data['nombre_razon_social'] = trim($data['nombre_razon_social']);
            $data['pais']                = self::DEFAULT_COUNTRY;

            // Establecer domicilio_fiscal_cp igual al codigo_postal para CFDI 4.0
            $data['domicilio_fiscal_cp'] = $data['codigo_postal'];

            // Actualizar datos primero
            $cliente->update($data);

            // Validar que el cliente actualizado tenga datos completos para CFDI
            // Pero NO bloquear la actualización si hay errores de CFDI
            $erroresCfdi = $cliente->fresh()->validarParaCfdi();
            if (!empty($erroresCfdi)) {
                Log::warning('Cliente actualizado pero no cumple requisitos CFDI', [
                    'cliente_id' => $cliente->id,
                    'errores_cfdi' => $erroresCfdi,
                ]);
                // No lanzar excepción, solo loguear advertencia
            }

            // Integrar o actualizar en Facturapi si es necesario
            if (empty($cliente->facturapi_customer_id) || $this->shouldUpdateFacturapi($cliente, $data)) {
                $this->createOrUpdateFacturapiCustomer($cliente, $data);
            }

            DB::commit();

            Log::info('Cliente actualizado', ['cliente_id' => $cliente->id]);

            // ✅ Redirige con mensaje flash (asegúrate de leerlo en el frontend)
            return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Cliente no encontrado para actualización', ['id' => $cliente->id ?? 'N/A']);

            // ✅ En lugar de redirect()->back(), lanza error 404 o redirige
            return redirect()->route('clientes.index')->with('error', 'Cliente no encontrado.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cliente: ' . $e->getMessage(), [
                'cliente_id' => $cliente->id ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);

            // ✅ ¡NO uses redirect()->back()! Lanza la excepción.
            // Inertia la capturará y la mostrará en form.errors
            throw $e;
        }
    }

    public function destroy(Cliente $cliente)
    {
        try {
            Log::info('Iniciando eliminación de cliente', [
                'cliente_id' => $cliente->id,
                'cliente_nombre' => $cliente->nombre_razon_social,
                'usuario_id' => Auth::id()
            ]);

            // Verificar permisos de eliminación
            Gate::authorize('delete', $cliente);

            // Verificar relaciones antes de eliminar
            $relaciones = $this->verificarRelacionesCliente($cliente);

            if (!empty($relaciones)) {
                // Crear mensaje simple y claro
                $mensajeSimple = $this->generarMensajeSimple($cliente, $relaciones);

                Log::warning('Cliente no eliminado por relaciones existentes', [
                    'cliente_id' => $cliente->id,
                    'cliente_nombre' => $cliente->nombre_razon_social,
                    'estado_activo' => $cliente->activo,
                    'relaciones' => $relaciones
                ]);

                return redirect()->back()->with('error', $mensajeSimple);
            }

            DB::beginTransaction();
            $cliente->delete(); // Ahora usa soft delete
            DB::commit();

            Log::info('Cliente eliminado correctamente', [
                'cliente_id' => $cliente->id,
                'cliente_nombre' => $cliente->nombre_razon_social
            ]);

            return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente');

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Cliente no encontrado para eliminación', [
                'id' => request()->route('cliente'),
                'error' => $e->getMessage()
            ]);
            return redirect()->route('clientes.index')->with('error', 'Cliente no encontrado.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar cliente: ' . $e->getMessage(), [
                'cliente_id' => $cliente->id ?? 'N/A',
                'cliente_nombre' => $cliente->nombre_razon_social ?? 'N/A',
                'usuario_id' => Auth::id() ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error interno al eliminar el cliente. Detalles: ' . $e->getMessage());
        }
    }

    // ======================== Funcionalidades extra ========================
    public function toggle(Cliente $cliente)
    {
        try {
            DB::beginTransaction();
            $cliente->update(['activo' => !$cliente->activo]);
            DB::commit();

            Log::info('Estado de cliente cambiado', [
                'cliente_id' => $cliente->id,
                'cliente_nombre' => $cliente->nombre_razon_social,
                'nuevo_estado' => $cliente->activo ? 'activo' : 'inactivo',
                'usuario_id' => Auth::id()
            ]);

            // Retornar respuesta JSON para peticiones AJAX
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $cliente->activo ? 'Cliente activado correctamente' : 'Cliente desactivado correctamente',
                    'cliente' => [
                        'id' => $cliente->id,
                        'activo' => $cliente->activo,
                        'estado_texto' => $cliente->activo ? 'Activo' : 'Inactivo'
                    ]
                ]);
            }

            return redirect()->back()->with('success', $cliente->activo ? 'Cliente activado correctamente' : 'Cliente desactivado correctamente');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Cliente no encontrado para cambio de estado', ['id' => $cliente->id ?? 'N/A']);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado.'
                ], 404);
            }

            return redirect()->route('clientes.index')->with('error', 'Cliente no encontrado.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado del cliente: ' . $e->getMessage(), [
                'cliente_id' => $cliente->id ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hubo un problema al cambiar el estado del cliente.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Hubo un problema al cambiar el estado del cliente.');
        }
    }

    // ============================= API/AJAX =============================
    public function validarRfc(Request $request): JsonResponse
    {
        try {
            $rfc = strtoupper(trim($request->input('rfc', '')));
            $clienteId = $request->input('cliente_id');

            if (empty($rfc)) {
                return response()->json(['success' => false, 'exists' => false, 'message' => 'RFC requerido'], 422);
            }
            if (!preg_match('/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $rfc)) {
                return response()->json(['success' => false, 'exists' => false, 'message' => 'Formato de RFC inválido'], 422);
            }
            if ($rfc === self::RFC_GENERIC_FOREIGN) {
                return response()->json(['success' => true, 'exists' => false, 'message' => 'RFC genérico válido']);
            }

            $query = Cliente::where('rfc', $rfc);
            if ($clienteId) $query->where('id', '!=', $clienteId);

            $existe  = $query->exists();
            $cliente = $existe ? $query->first() : null;

            return response()->json([
                'success' => true,
                'exists'  => $existe,
                'message' => $existe ? 'RFC ya registrado' : 'RFC disponible',
                'cliente' => $existe ? ['id' => $cliente->id, 'nombre' => $cliente->nombre_razon_social] : null
            ]);
        } catch (Exception $e) {
            Log::error('Error validando RFC: ' . $e->getMessage(), ['rfc' => $request->input('rfc')]);
            return response()->json(['success' => false, 'exists' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    public function validarEmail(Request $request): JsonResponse
    {
        try {
            $email = strtolower(trim($request->input('email', '')));

            if (empty($email)) {
                return response()->json(['success' => false, 'message' => 'Email requerido'], 422);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['success' => false, 'message' => 'Formato de email inválido'], 422);
            }

            return response()->json(['success' => true, 'message' => 'Email con formato válido']);
        } catch (Exception $e) {
            Log::error('Error validando email: ' . $e->getMessage(), ['email' => $request->input('email')]);
            return response()->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    public function getRegimenesByTipoPersona(Request $request): JsonResponse
    {
        try {
            $tipoPersona = $request->input('tipo_persona');
            if (!in_array($tipoPersona, ['fisica', 'moral'])) {
                return response()->json(['success' => false, 'message' => 'Tipo de persona inválido'], 422);
            }

            $validos = SatRegimenFiscal::query()
                ->when($tipoPersona === 'fisica', fn($q) => $q->where('persona_fisica', true))
                ->when($tipoPersona === 'moral',  fn($q) => $q->where('persona_moral', true))
                ->orderBy('clave')
                ->get(['clave', 'descripcion']);

            $opciones = [['value' => '', 'label' => 'Selecciona una opción']];
            foreach ($validos as $r) {
                $opciones[] = [
                    'value' => $r->clave,
                    'label' => "{$r->clave} - {$r->descripcion}",
                    'tipo_persona' => $tipoPersona
                ];
            }

            return response()->json([
                'success' => true,
                'regimenes' => $opciones,
                'tipo_persona' => $tipoPersona,
                'total' => $validos->count()
            ]);
        } catch (Exception $e) {
            Log::error('Error obteniendo regímenes fiscales: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = trim($request->input('q', ''));
            $limit = min((int) $request->input('limit', 10), self::MAX_SEARCH_LIMIT);

            if (mb_strlen($query) < self::MIN_SEARCH_LENGTH) {
                return response()->json(['success' => false, 'message' => 'Mínimo ' . self::MIN_SEARCH_LENGTH . ' caracteres para búsqueda'], 422);
            }

            $clientes = Cliente::where('activo', true)
                ->where(function ($q) use ($query) {
                    $q->where('nombre_razon_social', 'like', "%{$query}%")
                        ->orWhere('rfc', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                })
                ->select('id', 'nombre_razon_social', 'rfc', 'email', 'tipo_persona')
                ->limit($limit)
                ->get();

            $resultados = $clientes->map(fn($c) => [
                'id'           => $c->id,
                'nombre'       => $c->nombre_razon_social,
                'rfc'          => $c->rfc,
                'email'        => $c->email,
                'tipo_persona' => $this->getTipoPersonaNombre($c->tipo_persona),
                'label'        => "{$c->nombre_razon_social} ({$c->rfc})"
            ]);

            return response()->json(['success' => true, 'clientes' => $resultados]);
        } catch (Exception $e) {
            Log::error('Error en búsqueda de clientes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            // Verificar permisos de exportación
            Gate::authorize('export', Cliente::class);

            $query    = $this->buildSearchQuery($request);
            $clientes = $query->get();

            $filename = 'clientes_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($clientes) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($file, [
                    'ID',
                    'Nombre/Razón Social',
                    'Tipo Persona',
                    'RFC',
                    'Régimen Fiscal',
                    'Uso CFDI',
                    'Email',
                    'Teléfono',
                    'Dirección Completa',
                    'Estado (clave)',
                    'Estado (nombre)',
                    'Código Postal',
                    'Activo',
                    'Notas',
                    'Fecha Creación'
                ]);

                foreach ($clientes as $cliente) {
                    $direccion = trim(implode(' ', [
                        $cliente->calle,
                        $cliente->numero_exterior,
                        $cliente->numero_interior ? "Int. {$cliente->numero_interior}" : '',
                        $cliente->colonia,
                        $cliente->municipio
                    ]));

                    fputcsv($file, [
                        $cliente->id,
                        $cliente->nombre_razon_social,
                        $this->getTipoPersonaNombre($cliente->tipo_persona),
                        $cliente->rfc,
                        $this->getRegimenFiscalNombre($cliente->regimen_fiscal),
                        $this->getUsoCFDINombre($cliente->uso_cfdi),
                        $cliente->email,
                        $cliente->telefono,
                        $direccion,
                        $cliente->estado,
                        $this->getEstadoNombre($cliente->estado),
                        $cliente->codigo_postal,
                        $cliente->activo ? 'Sí' : 'No',
                        $cliente->notas,
                        $cliente->created_at?->format('d/m/Y H:i:s')
                    ]);
                }
                fclose($file);
            };

            Log::info('Exportación de clientes', ['total' => $clientes->count(), 'usuario' => Auth::id()]);

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('Error en exportación de clientes: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los clientes.');
        }
    }

    // ============================= Stats / Utils =============================
    public function stats(): JsonResponse
    {
        try {
            // Verificar permisos de estadísticas
            Gate::authorize('stats', Cliente::class);

            $stats = Cache::remember('clientes_stats', 5, function () {
                return [
                    'total'            => Cliente::count(),
                    'activos'          => Cliente::where('activo', true)->count(),
                    'inactivos'        => Cliente::where('activo', false)->count(),
                    'personas_fisicas' => Cliente::where('tipo_persona', 'fisica')->count(),
                    'personas_morales' => Cliente::where('tipo_persona', 'moral')->count(),
                    'nuevos_mes'       => Cliente::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                    'top_regimenes'    => Cliente::select('regimen_fiscal')->selectRaw('COUNT(*) as total')->groupBy('regimen_fiscal')
                        ->orderByDesc('total')->limit(5)->get()->map(fn($item) => [
                            'regimen' => $item->regimen_fiscal,
                            'nombre'  => $this->getRegimenFiscalNombre($item->regimen_fiscal),
                            'total'   => $item->total
                        ]),
                    'top_estados'      => Cliente::select('estado')->selectRaw('COUNT(*) as total')->groupBy('estado')
                        ->orderByDesc('total')->limit(5)->get()->map(fn($item) => [
                            'estado' => $item->estado,
                            'nombre' => $this->getEstadoNombre($item->estado),
                            'total'  => $item->total,
                        ]),
                ];
            });

            return response()->json(['success' => true, 'stats' => $stats]);
        } catch (Exception $e) {
            Log::error('Error obteniendo estadísticas de clientes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    public function clearCache(): JsonResponse
    {
        try {
            Cache::forget('tipos_persona');
            Cache::forget('regimenes_fiscales_db');
            Cache::forget('usos_cfdi_db');
            Cache::forget('estados_mexico_db');
            Cache::forget('clientes_stats');

            Log::info('Cache de clientes limpiada', ['usuario' => Auth::id()]);

            return response()->json(['success' => true, 'message' => 'Cache limpiada correctamente']);
        } catch (Exception $e) {
            Log::error('Error limpiando cache: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Verificar si un cliente puede ser eliminado (sin documentos relacionados)
     */
    public function canDelete(Cliente $cliente): JsonResponse
    {
        try {
            $relaciones = $this->verificarRelacionesCliente($cliente);

            $puedeEliminar = empty($relaciones);

            return response()->json([
                'success' => true,
                'can_delete' => $puedeEliminar,
                'relaciones' => $relaciones,
                'message' => $puedeEliminar
                    ? 'El cliente puede ser eliminado'
                    : 'El cliente tiene documentos relacionados: ' . implode(', ', $relaciones)
            ]);
        } catch (Exception $e) {
            Log::error('Error verificando si cliente puede ser eliminado: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Verificar si un cliente tiene préstamos
     */
    public function hasPrestamos(Cliente $cliente): JsonResponse
    {
        try {
            $prestamosCount = $cliente->prestamos()->count();
            $tienePrestamos = $prestamosCount > 0;

            return response()->json([
                'success' => true,
                'has_prestamos' => $tienePrestamos,
                'prestamos_count' => $prestamosCount,
                'message' => $tienePrestamos
                    ? "El cliente tiene {$prestamosCount} préstamo(s)"
                    : 'El cliente no tiene préstamos'
            ]);
        } catch (Exception $e) {
            Log::error('Error verificando préstamos del cliente: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Create or update customer in Facturapi.
     */
    private function createOrUpdateFacturapiCustomer(Cliente $cliente, array $data = null): void
    {
        try {
            // Verificar que la configuración de Facturapi esté disponible
            $apiKey = config('facturapi.api_key');
            if (empty($apiKey)) {
                Log::warning('Facturapi API key not configured', ['cliente_id' => $cliente->id]);
                return;
            }

            $facturapi = new Facturapi($apiKey);
            $customerData = $this->prepareFacturapiData($cliente, $data);

            if ($cliente->facturapi_customer_id) {
                $this->updateFacturapiCustomer($facturapi, $cliente, $customerData);
            } else {
                $this->createFacturapiCustomer($facturapi, $cliente, $customerData);
            }
        } catch (\Exception $e) {
            Log::error('Error integrating with Facturapi', [
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // No lanzar excepción, permitir que el cliente se guarde sin sincronización
        }
    }

    /**
     * Update existing customer in Facturapi
     */
    private function updateFacturapiCustomer(Facturapi $facturapi, Cliente $cliente, array $customerData): void
    {
        try {
            $customer = $facturapi->customers->update($cliente->facturapi_customer_id, $customerData);
            Log::info('Facturapi customer updated successfully', [
                'cliente_id' => $cliente->id,
                'customer_id' => $cliente->facturapi_customer_id
            ]);
        } catch (\Exception $updateError) {
            Log::error('Error updating Facturapi customer', [
                'cliente_id' => $cliente->id,
                'customer_id' => $cliente->facturapi_customer_id,
                'error' => $updateError->getMessage(),
                'trace' => $updateError->getTraceAsString()
            ]);
            // No lanzar excepción, permitir que el cliente se guarde sin sincronización
        }
    }

    /**
     * Create new customer in Facturapi
     */
    private function createFacturapiCustomer(Facturapi $facturapi, Cliente $cliente, array $customerData): void
    {
        try {
            $customer = $facturapi->customers->create($customerData);
            $cliente->update(['facturapi_customer_id' => $customer->id]);
            Log::info('Facturapi customer created successfully', [
                'cliente_id' => $cliente->id,
                'customer_id' => $customer->id
            ]);
        } catch (\Exception $createError) {
            Log::error('Error creating Facturapi customer', [
                'cliente_id' => $cliente->id,
                'error' => $createError->getMessage(),
                'trace' => $createError->getTraceAsString()
            ]);
            // No lanzar excepción, permitir que el cliente se guarde sin sincronización
        }
    }

    /**
     * Determine if Facturapi customer should be updated.
     */
    private function shouldUpdateFacturapi(Cliente $cliente, array $data): bool
    {
        $fieldsToWatch = ['nombre_razon_social', 'email', 'rfc', 'calle', 'numero_exterior', 'numero_interior', 'colonia', 'codigo_postal', 'municipio', 'estado', 'pais', 'uso_cfdi'];
        foreach ($fieldsToWatch as $field) {
            if (isset($data[$field]) && $data[$field] !== $cliente->{$field}) {
                return true;
            }
        }
        return false;
    }

    /**
      * Verificar si el cliente tiene relaciones que impidan su eliminación
      * Las cotizaciones bloquean la eliminación SIEMPRE (activos e inactivos)
      * Otros documentos solo bloquean si el cliente está activo
      */
    private function verificarRelacionesCliente(Cliente $cliente): array
    {
        $relaciones = [];

        // ✅ COTIZACIONES: Bloquean SIEMPRE (incluso para clientes inactivos)
        $cotizacionesCount = $cliente->cotizaciones()->count();
        if ($cotizacionesCount > 0) {
            $relaciones[] = "tiene {$cotizacionesCount} cotización(es)";
        }

        // ✅ Si el cliente está inactivo, verificar cotizaciones, ventas y pedidos
        if (!$cliente->activo) {
            // Verificar ventas (todas)
            $ventasCount = $cliente->ventas()->count();
            if ($ventasCount > 0) {
                $relaciones[] = "tiene {$ventasCount} venta(s)";
            }

            // Verificar pedidos activos (solo los que NO están cancelados)
            $pedidosActivos = $cliente->pedidos()->where('estado', '!=', 'cancelado')->count();
            if ($pedidosActivos > 0) {
                $relaciones[] = "tiene {$pedidosActivos} pedido(s) activo(s)";
            }

            if (!empty($relaciones)) {
                Log::info('Cliente inactivo con relaciones - bloqueando eliminación', [
                    'cliente_id' => $cliente->id,
                    'cliente_nombre' => $cliente->nombre_razon_social,
                    'estado_activo' => $cliente->activo,
                    'relaciones' => $relaciones
                ]);
            }
            return $relaciones;
        }

        // ✅ Para clientes activos: verificar todas las relaciones

        // Verificar ventas (todas, ya que no tienen soft deletes)
        $ventasCount = $cliente->ventas()->count();
        if ($ventasCount > 0) {
            $relaciones[] = "tiene {$ventasCount} venta(s)";
        }

        // Verificar pedidos activos (solo los que NO están cancelados)
        $pedidosActivos = $cliente->pedidos()->where('estado', '!=', 'cancelado')->count();
        if ($pedidosActivos > 0) {
            $relaciones[] = "tiene {$pedidosActivos} pedido(s) activo(s)";
        }

        // Verificar facturas (todas)
        $facturasCount = $cliente->facturas()->count();
        if ($facturasCount > 0) {
            $relaciones[] = "tiene {$facturasCount} factura(s) emitida(s)";
        }

        // Verificar rentas activas (solo las que NO están finalizadas)
        $rentasActivas = $cliente->rentas()->where('estado', '!=', 'finalizada')->count();
        if ($rentasActivas > 0) {
            $relaciones[] = "tiene {$rentasActivas} renta(s) activa(s)";
        }

        // Log detallado para debugging
        if (!empty($relaciones)) {
            Log::info('Cliente tiene relaciones que bloquean eliminación', [
                'cliente_id' => $cliente->id,
                'cliente_nombre' => $cliente->nombre_razon_social,
                'estado_activo' => $cliente->activo,
                'relaciones' => $relaciones,
                'conteos' => [
                    'cotizaciones' => $cotizacionesCount,
                    'ventas' => $ventasCount,
                    'pedidos_activos' => $pedidosActivos,
                    'facturas' => $facturasCount,
                    'rentas_activas' => $rentasActivas,
                ]
            ]);
        }

        return $relaciones;
    }

    /**
     * Generar mensaje simple y claro para el usuario
     */
    private function generarMensajeSimple(Cliente $cliente, array $relaciones): string
    {
        $count = count($relaciones);

        if ($count === 1) {
            $mensaje = 'No se puede eliminar el cliente "' . $cliente->nombre_razon_social . '" porque ' . $relaciones[0] . '.';
        } elseif ($count <= 3) {
            $mensaje = 'No se puede eliminar el cliente "' . $cliente->nombre_razon_social . '" porque ' . implode(' y ', $relaciones) . '.';
        } else {
            // Si hay muchas relaciones, resumir
            $mensaje = 'No se puede eliminar el cliente "' . $cliente->nombre_razon_social . '" porque tiene múltiples registros relacionados (' . $count . ' tipos de relación).';
        }

        $mensaje .= "\n\nPara eliminar este cliente, cancele o elimine los registros relacionados.";

        return $mensaje;
    }
 }
