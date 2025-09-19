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



class ClienteController extends Controller
{
    private const ITEMS_PER_PAGE = 10;
    private const CACHE_TTL = 60;

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
                    ->get(['clave', 'descripcion', 'persona_fisica', 'persona_moral'])
                    ->keyBy('clave')
                    ->toArray();
            });
        } catch (\Exception $e) {
            Log::warning('Error obteniendo usos CFDI del cache, usando DB directa', ['error' => $e->getMessage()]);
            return SatUsoCfdi::orderBy('clave')
                ->get(['clave', 'descripcion', 'persona_fisica', 'persona_moral'])
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
        return implode(',', array_keys($this->getEstadosMexico())); // "AGU,BCN,..."
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

    private function getTipoPersonaNombre(string $codigo): string
    {
        $tipos = $this->getTiposPersona();
        return $tipos[$codigo] ?? $codigo;
    }

    private function getRegimenFiscalNombre(string $codigo): string
    {
        $reg = $this->getRegimenesFiscales();
        return isset($reg[$codigo]) ? ($reg[$codigo]['descripcion'] ?? $codigo) : $codigo;
    }

    private function getUsoCFDINombre(string $codigo): string
    {
        $u = $this->getUsosCFDI();
        return isset($u[$codigo]) ? ($u[$codigo]['descripcion'] ?? $codigo) : $codigo;
    }

    private function getEstadoNombre(?string $clave): ?string
    {
        if (!$clave) return null;
        $est = $this->getEstadosMexico();
        return $est[$clave] ?? $clave;
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
        $q = \App\Models\Cliente::query()->with(['estadoSat', 'regimen', 'uso']);

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

        // CLAVE: solo filtrar si 'activo' viene exactamente como '0' o '1'
        if ($request->query->has('activo')) {
            $val = (string) $request->query('activo');

            if (in_array($val, ['0', '1'], true)) {
                $q->where('activo', (int) $val); // 0 o 1
            }
            // Si NO es '0' ni '1', NO filtramos.
        }


        return $q->orderByDesc('created_at');
    }


    private function transformClientesPaginator($paginator)
    {
        $collection = $paginator->getCollection();

        $collection->transform(function ($cliente) {
            $cliente->tipo_persona_nombre   = $this->getTipoPersonaNombre($cliente->tipo_persona);
            $cliente->regimen_fiscal_nombre = $cliente->regimen?->descripcion ?? $this->getRegimenFiscalNombre($cliente->regimen_fiscal);
            $cliente->uso_cfdi_nombre       = $cliente->uso?->descripcion ?? $this->getUsoCFDINombre($cliente->uso_cfdi);
            $cliente->estado_nombre         = $cliente->estadoSat?->nombre ?? $this->getEstadoNombre($cliente->estado);
            $cliente->estado_texto          = $cliente->activo ? 'Activo' : 'Inactivo';
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
                    if ($value === 'XAXX010101000') {
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
                    $fail('El régimen fiscal no es válido para Persona Física.');
                }
                if ($tipo === 'moral' && !$rf->persona_moral) {
                    $fail('El régimen fiscal no es válido para Persona Moral.');
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
            'email'               => ['required', 'email:rfc,dns', 'max:255'],
            'telefono'            => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'calle'               => 'required|string|max:255',
            'numero_exterior'     => 'required|string|max:20',
            'numero_interior'     => 'nullable|string|max:20',
            'colonia'             => 'required|string|max:255',
            'codigo_postal'       => 'required|string|size:5|regex:/^[0-9]{5}$/',
            'municipio'           => 'required|string|max:255',
            'estado'              => "required|string|size:3|in:$estadosCsv", // clave SAT (3 letras)
            'pais'                => 'required|string|in:MX',
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
            'telefono.regex'               => 'El teléfono solo debe contener números, espacios, paréntesis, guiones y el signo +.',
            'calle.required'               => 'La calle es obligatoria.',
            'numero_exterior.required'     => 'El número exterior es obligatorio.',
            'colonia.required'             => 'La colonia es obligatoria.',
            'codigo_postal.required'       => 'El código postal es obligatorio.',
            'codigo_postal.size'           => 'El código postal debe tener 5 dígitos.',
            'codigo_postal.regex'          => 'El código postal debe contener solo números.',
            'municipio.required'           => 'El municipio es obligatorio.',
            'estado.required'              => 'El estado es obligatorio.',
            'estado.in'                    => 'El estado seleccionado no es válido.',
            'pais.required'                => 'El país es obligatorio.',
            'pais.in'                      => 'El país debe ser MX.',
            'notas.max'                    => 'Las notas no pueden exceder 1000 caracteres.',
        ];
    }

    private function formatClienteForView(Cliente $c): Cliente
    {
        $c->tipo_persona_nombre   = $this->getTipoPersonaNombre($c->tipo_persona);
        $c->regimen_fiscal_nombre = $c->regimen?->descripcion ?? $this->getRegimenFiscalNombre($c->regimen_fiscal);
        $c->uso_cfdi_nombre       = $c->uso?->descripcion ?? $this->getUsoCFDINombre($c->uso_cfdi);
        $c->estado_nombre         = $c->estadoSat?->nombre ?? $this->getEstadoNombre($c->estado);
        $c->estado_texto          = $c->activo ? 'Activo' : 'Inactivo';
        return $c;
    }

    // ============================= CRUD =============================
    public function index(Request $request)
    {
        try {
            $query = $this->buildSearchQuery($request);

            $sortBy        = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $validSort     = ['nombre_razon_social', 'rfc', 'email', 'created_at', 'activo'];

            if (!in_array($sortBy, $validSort)) $sortBy = 'created_at';
            if (!in_array($sortDirection, ['asc', 'desc'])) $sortDirection = 'desc';

            $query->orderBy($sortBy, $sortDirection);

            $clientes = $query->paginate(self::ITEMS_PER_PAGE)->appends($request->query());
            $clientes = $this->transformClientesPaginator($clientes);

            $clientesCount   = Cliente::count();
            $clientesActivos = Cliente::where('activo', true)->count();

            return Inertia::render('Clientes/Index', [
                'titulo'   => 'Clientes',
                'clientes' => $clientes,
                'stats'    => [
                    'total'     => $clientesCount,
                    'activos'   => $clientesActivos,
                    'inactivos' => $clientesCount - $clientesActivos,
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
                    ->get(['clave', 'descripcion', 'persona_fisica', 'persona_moral'])
                    ->map(function ($uso) {
                        return [
                            'value' => $uso->clave,
                            'text' => $uso->clave . ' — ' . $uso->descripcion
                        ];
                    })
                    ->toArray(),
            ],
            'cliente' => [ // valores por defecto
                'tipo_persona' => '',
                'pais' => 'MX',
            ],
        ]);
    }

    public function store(StoreClienteRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Normalización de datos
            $data['rfc']                 = strtoupper(trim($data['rfc']));
            $data['email']               = strtolower(trim($data['email']));
            $data['nombre_razon_social'] = trim($data['nombre_razon_social']);
            $data['pais']                = 'MX'; // Forzado

            $cliente = Cliente::create($data);

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

            // Integrar con Facturapi si no tiene ID
            if (empty($cliente->facturapi_customer_id)) {
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
            $data['pais']                = 'MX';

            // Integrar o actualizar en Facturapi si es necesario
            if (empty($cliente->facturapi_customer_id) || $this->shouldUpdateFacturapi($cliente, $data)) {
                $this->createOrUpdateFacturapiCustomer($cliente, $data);
            }

            $cliente->update($data);

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
            DB::beginTransaction();
            $cliente->delete();
            DB::commit();
            Log::info('Cliente eliminado', ['cliente_id' => $cliente->id]);
            return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Cliente no encontrado para eliminación', ['id' => request()->route('cliente')]);
            return redirect()->route('clientes.index')->with('error', 'Cliente no encontrado.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar cliente: ' . $e->getMessage(), ['cliente_id' => $cliente->id ?? 'N/A', 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'No se pudo eliminar el cliente. Verifique relaciones.');
        }
    }

    // ======================== Funcionalidades extra ========================
    public function toggle(Cliente $cliente)
    {
        try {
            DB::beginTransaction();
            $cliente->update(['activo' => !$cliente->activo]);
            DB::commit();
            return redirect()->back()->with('success', $cliente->activo ? 'Cliente activado correctamente' : 'Cliente desactivado correctamente');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->route('clientes.index')->with('error', 'Cliente no encontrado.');
        } catch (Exception $e) {
            DB::rollBack();
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
            if ($rfc === 'XAXX010101000') {
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
                $opciones[] = ['value' => $r->clave, 'label' => "{$r->clave} - {$r->descripcion}"];
            }

            return response()->json(['success' => true, 'regimenes' => $opciones]);
        } catch (Exception $e) {
            Log::error('Error obteniendo regímenes fiscales: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = trim($request->input('q', ''));
            $limit = min((int) $request->input('limit', 10), 50);

            if (mb_strlen($query) < 2) {
                return response()->json(['success' => false, 'message' => 'Mínimo 2 caracteres para búsqueda'], 422);
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
     * Create or update customer in Facturapi.
     */
    private function createOrUpdateFacturapiCustomer(Cliente $cliente, array $data = null): void
    {
        try {
            $facturapi = new Facturapi(config('facturapi.api_key'));

            $useData = $data !== null;

            $nombre = $useData ? ($data['nombre_razon_social'] ?? $cliente->nombre_razon_social) : $cliente->nombre_razon_social;
            $email = $useData ? ($data['email'] ?? $cliente->email) : $cliente->email;
            $rfc = $useData ? ($data['rfc'] ?? $cliente->rfc) : $cliente->rfc;
            $codigo_postal = $useData ? ($data['codigo_postal'] ?? $cliente->codigo_postal) : $cliente->codigo_postal;
            $municipio = $useData ? ($data['municipio'] ?? $cliente->municipio) : $cliente->municipio;
            $estado = $useData ? ($data['estado'] ?? $cliente->estado) : $cliente->estado;
            $pais = 'MX';
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

            $customerData = [
                'name' => $nombre,
                'email' => $email,
                'rfc' => $rfc,
                'address1' => $address1,
                'postal_code' => $codigo_postal,
                'city' => $municipio,
                'state' => $estado,
                'country' => $pais,
                'cfdi_use' => $uso_cfdi,
            ];

            if ($cliente->facturapi_customer_id) {
                // Update existing
                $customer = $facturapi->customers->update($cliente->facturapi_customer_id, $customerData);
                Log::info('Facturapi customer updated', ['customer_id' => $cliente->facturapi_customer_id]);
            } else {
                // Create new
                $customer = $facturapi->customers->create($customerData);
                $cliente->update(['facturapi_customer_id' => $customer->id]);
                Log::info('Facturapi customer created', ['customer_id' => $customer->id]);
            }
        } catch (\Exception $e) {
            Log::error('Error integrating with Facturapi', [
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // No throw, allow save without Facturapi if API down
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
}
