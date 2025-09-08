<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Cliente;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class ClienteController extends Controller
{
    private const ITEMS_PER_PAGE = 10;
    private const CACHE_TTL = 60;

    private const TIPOS_PERSONA = [
        'fisica' => 'Persona Física',
        'moral'  => 'Persona Moral',
    ];

    private const REGIMENES_FISCALES = [
        '601' => 'General de Ley Personas Morales',
        '603' => 'Personas Morales con Fines no Lucrativos',
        '605' => 'Sueldos y Salarios e Ingresos Asimilados a Salarios',
        '606' => 'Arrendamiento',
        '607' => 'Régimen de Enajenación o Adquisición de Bienes',
        '608' => 'Demás ingresos',
        '609' => 'Consolidación',
        '610' => 'Residentes en el Extranjero sin Establecimiento Permanente en México',
        '611' => 'Ingresos por Dividendos (socios y accionistas)',
        '612' => 'Personas Físicas con Actividades Empresariales y Profesionales',
        '614' => 'Ingresos por intereses',
        '615' => 'Régimen de los ingresos por obtención de premios',
        '616' => 'Sin obligaciones fiscales',
        '620' => 'Sociedades Cooperativas de Producción que optan por diferir sus ingresos',
        '621' => 'Incorporación Fiscal',
        '622' => 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
        '623' => 'Opcional para Grupos de Sociedades',
        '624' => 'Coordinados',
        '625' => 'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
        '626' => 'Régimen Simplificado de Confianza',
        '628' => 'Hidrocarburos',
        '629' => 'De los Regímenes Fiscales Preferentes y de las Empresas Multinacionales',
        '630' => 'Enajenación de acciones en bolsa de valores',
    ];

    private const REGIMENES_FISICA = ['605', '606', '607', '608', '610', '611', '612', '614', '615', '616', '621', '625', '626'];
    private const REGIMENES_MORAL  = ['601', '603', '609', '620', '622', '623', '624', '628', '629', '630'];

    private const USOS_CFDI = [
        'G01' => 'Adquisición de mercancías',
        'G02' => 'Devoluciones, descuentos o bonificaciones',
        'G03' => 'Gastos en general',
        'I01' => 'Construcciones',
        'I02' => 'Mobiliario y equipo de oficina por inversiones',
        'I03' => 'Equipo de transporte',
        'I04' => 'Equipo de cómputo y accesorios',
        'I05' => 'Dados, troqueles, moldes, matrices y herramental',
        'I06' => 'Comunicaciones telefónicas',
        'I07' => 'Comunicaciones satelitales',
        'I08' => 'Otra maquinaria y equipo',
        'D01' => 'Honorarios médicos, dentales y gastos hospitalarios',
        'D02' => 'Gastos médicos por incapacidad o discapacidad',
        'D03' => 'Gastos funerales',
        'D04' => 'Donativos',
        'D05' => 'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación)',
        'D06' => 'Aportaciones voluntarias al SAR',
        'D07' => 'Primas por seguros de gastos médicos',
        'D08' => 'Gastos de transportación escolar obligatoria',
        'D09' => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones',
        'D10' => 'Pagos por servicios educativos (colegiaturas)',
        'S01' => 'Sin efectos fiscales',
        'CP01' => 'Pagos',
        'CN01' => 'Nómina',
    ];

    private const ESTADOS_MEXICO = [
        'Aguascalientes',
        'Baja California',
        'Baja California Sur',
        'Campeche',
        'Chiapas',
        'Chihuahua',
        'Coahuila',
        'Colima',
        'Ciudad de México',
        'Durango',
        'Guanajuato',
        'Guerrero',
        'Hidalgo',
        'Jalisco',
        'México',
        'Michoacán',
        'Morelos',
        'Nayarit',
        'Nuevo León',
        'Oaxaca',
        'Puebla',
        'Querétaro',
        'Quintana Roo',
        'San Luis Potosí',
        'Sinaloa',
        'Sonora',
        'Tabasco',
        'Tamaulipas',
        'Tlaxcala',
        'Veracruz',
        'Yucatán',
        'Zacatecas'
    ];

    // ========================= Helpers de catálogos =========================
    private function getTiposPersona(): array
    {
        return Cache::remember('tipos_persona', self::CACHE_TTL, fn() => self::TIPOS_PERSONA);
    }
    private function getRegimenesFiscales(): array
    {
        return Cache::remember('regimenes_fiscales', self::CACHE_TTL, fn() => self::REGIMENES_FISCALES);
    }
    private function getUsosCFDI(): array
    {
        return Cache::remember('usos_cfdi', self::CACHE_TTL, fn() => self::USOS_CFDI);
    }
    private function getEstadosMexico(): array
    {
        return Cache::remember('estados_mexico', self::CACHE_TTL, fn() => array_combine(self::ESTADOS_MEXICO, self::ESTADOS_MEXICO));
    }

    private function formatForVueSelect(array $options, bool $includeEmpty = false, bool $showCode = false): array
    {
        $formatted = collect($options)->map(function ($label, $value) use ($showCode) {
            return [
                'value' => $value,
                'label' => $showCode ? "{$value} - {$label}" : $label
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
        return $reg[$codigo] ?? $codigo;
    }
    private function getUsoCFDINombre(string $codigo): string
    {
        $u = $this->getUsosCFDI();
        return $u[$codigo] ?? $codigo;
    }

    private function transformClientesPaginator($paginator)
    {
        $collection = $paginator->getCollection();

        $collection->transform(function ($cliente) {
            $cliente->tipo_persona_nombre   = $this->getTipoPersonaNombre($cliente->tipo_persona);
            $cliente->regimen_fiscal_nombre = $this->getRegimenFiscalNombre($cliente->regimen_fiscal);
            $cliente->uso_cfdi_nombre       = $this->getUsoCFDINombre($cliente->uso_cfdi);
            $cliente->estado_texto          = $cliente->activo ? 'Activo' : 'Inactivo';
            return $cliente;
        });

        $paginator->setCollection($collection);
        return $paginator;
    }

    private function buildSearchQuery(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nombre_razon_social', 'like', "%{$search}%")
                    ->orWhere('rfc', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo_persona')) {
            $query->where('tipo_persona', $request->get('tipo_persona'));
        }
        if ($request->filled('regimen_fiscal')) {
            $query->where('regimen_fiscal', $request->get('regimen_fiscal'));
        }
        if ($request->filled('uso_cfdi')) {
            $query->where('uso_cfdi', $request->get('uso_cfdi'));
        }

        // importante: permitir activo=0 (false)
        if ($request->has('activo') && $request->get('activo') !== '') {
            $query->where('activo', filter_var($request->get('activo'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->get('estado'));
        }

        return $query;
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
                $query = Cliente::where('rfc', $value);
                if ($clienteId) $query->where('id', '!=', $clienteId);

                if ($query->exists()) {
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
        return [
            'required',
            'string',
            function ($attribute, $value, $fail) {
                $tipo = request()->input('tipo_persona');
                if ($tipo === 'fisica' && !in_array($value, self::REGIMENES_FISICA)) {
                    $fail('El régimen fiscal no es válido para persona física.');
                }
                if ($tipo === 'moral' && !in_array($value, self::REGIMENES_MORAL)) {
                    $fail('El régimen fiscal no es válido para persona moral.');
                }
            },
        ];
    }

    private function getValidationRules(?int $clienteId = null): array
    {
        return [
            'nombre_razon_social' => 'required|string|max:255|regex:/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜ\s\.,&\-\']+$/',
            'tipo_persona'        => 'required|in:fisica,moral',
            'rfc'                 => $this->getRfcValidationRules($clienteId),
            'regimen_fiscal'      => $this->getRegimenFiscalValidationRules(),
            'uso_cfdi'            => 'required|string|in:' . implode(',', array_keys(self::USOS_CFDI)),
            'email'               => ['required', 'email:rfc,dns', 'max:255'], // SIN unicidad
            'telefono'            => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'calle'               => 'required|string|max:255',
            'numero_exterior'     => 'required|string|max:20',
            'numero_interior'     => 'nullable|string|max:20',
            'colonia'             => 'required|string|max:255',
            'codigo_postal'       => 'required|string|size:5|regex:/^[0-9]{5}$/',
            'municipio'           => 'required|string|max:255',
            'estado'              => 'required|string|max:255|in:' . implode(',', self::ESTADOS_MEXICO),
            'pais'                => 'required|string|max:255',
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
            'uso_cfdi.required'            => 'El uso de CFDI es obligatorio.',
            'uso_cfdi.in'                  => 'El uso de CFDI seleccionado no es válido.',
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
            'notas.max'                    => 'Las notas no pueden exceder 1000 caracteres.',
        ];
    }

    private function formatClienteForView(Cliente $c): Cliente
    {
        $c->tipo_persona_nombre   = $this->getTipoPersonaNombre($c->tipo_persona);
        $c->regimen_fiscal_nombre = $this->getRegimenFiscalNombre($c->regimen_fiscal);
        $c->uso_cfdi_nombre       = $this->getUsoCFDINombre($c->uso_cfdi);
        $c->estado_texto          = $c->activo ? 'Activo' : 'Inactivo';
        return $c;
    }

    private function getCatalogData(): array
    {
        return [
            'tiposPersona'     => $this->formatForVueSelect($this->getTiposPersona(), true),
            'regimenesFiscales' => $this->formatForVueSelect($this->getRegimenesFiscales(), true, true),
            'usosCFDI'         => $this->formatForVueSelect($this->getUsosCFDI(), true, true),
            'estados'          => $this->formatForVueSelect($this->getEstadosMexico(), true),
        ];
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

            $clientesCount  = Cliente::count();
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
        try {
            return Inertia::render('Clientes/Create', [
                'catalogs' => $this->getCatalogData(),
                'cliente'  => [
                    'id' => null,
                    // todos requieren datos fiscales -> no hay requiere_factura
                    'nombre_razon_social' => '',
                    'tipo_persona'        => 'fisica',
                    'rfc'                 => '',
                    'regimen_fiscal'      => '',
                    'uso_cfdi'            => '',
                    'email'               => '',
                    'telefono'            => '',
                    'calle'               => '',
                    'numero_exterior'     => '',
                    'numero_interior'     => '',
                    'colonia'             => '',
                    'codigo_postal'       => '',
                    'municipio'           => '',
                    'estado'              => '',
                    'pais'                => 'México',
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Error en ClienteController@create: ' . $e->getMessage());
            return redirect()->route('clientes.index')->with('error', 'Error al cargar el formulario de creación.');
        }
    }

    public function store(StoreClienteRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['rfc']                   = strtoupper(trim($data['rfc']));
            $data['email']                 = strtolower(trim($data['email']));
            $data['nombre_razon_social']   = trim($data['nombre_razon_social']);

            $cliente = Cliente::create($data);

            DB::commit();
            Log::info('Cliente creado', ['id' => $cliente->id]);

            return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente');
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Validación fallida al crear cliente', $e->errors());
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cliente: ' . $e->getMessage(), ['data' => $request->all(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Hubo un problema al crear el cliente.')->withInput();
        }
    }

    public function show(Cliente $cliente)
    {
        try {
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
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['rfc']                 = strtoupper(trim($data['rfc']));
            $data['email']               = strtolower(trim($data['email']));
            $data['nombre_razon_social'] = trim($data['nombre_razon_social']);

            $cliente->update($data);

            DB::commit();
            Log::info('Cliente actualizado', ['cliente_id' => $cliente->id]);

            return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Cliente no encontrado para actualización', ['id' => $cliente->id ?? 'N/A']);
            return redirect()->route('clientes.index')->with('error', 'Cliente no encontrado.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cliente: ' . $e->getMessage(), ['cliente_id' => $cliente->id ?? 'N/A', 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Hubo un problema al actualizar el cliente.')->withInput();
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

            // Email puede repetirse: devolvemos solo validación de formato
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

            $todos    = $this->getRegimenesFiscales();
            $validos  = $tipoPersona === 'fisica' ? self::REGIMENES_FISICA : self::REGIMENES_MORAL;
            $filtrados = array_intersect_key($todos, array_flip($validos));
            $opciones = $this->formatForVueSelect($filtrados, true, true);

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
            $limit = min($request->input('limit', 10), 50);

            if (strlen($query) < 2) {
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
                'id'          => $c->id,
                'nombre'      => $c->nombre_razon_social,
                'rfc'         => $c->rfc,
                'email'       => $c->email,
                'tipo_persona' => $this->getTipoPersonaNombre($c->tipo_persona),
                'label'       => "{$c->nombre_razon_social} ({$c->rfc})"
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
                    'Estado',
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
                        $cliente->codigo_postal,
                        $cliente->activo ? 'Sí' : 'No',
                        $cliente->notas,
                        $cliente->created_at->format('d/m/Y H:i:s')
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
                    'total'          => Cliente::count(),
                    'activos'        => Cliente::where('activo', true)->count(),
                    'inactivos'      => Cliente::where('activo', false)->count(),
                    'personas_fisicas' => Cliente::where('tipo_persona', 'fisica')->count(),
                    'personas_morales' => Cliente::where('tipo_persona', 'moral')->count(),
                    'nuevos_mes'     => Cliente::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                    'top_regimenes'  => Cliente::select('regimen_fiscal')->selectRaw('COUNT(*) as total')->groupBy('regimen_fiscal')
                        ->orderByDesc('total')->limit(5)->get()->map(fn($item) => [
                            'regimen' => $item->regimen_fiscal,
                            'nombre'  => $this->getRegimenFiscalNombre($item->regimen_fiscal),
                            'total'   => $item->total
                        ]),
                    'top_estados'    => Cliente::select('estado')->selectRaw('COUNT(*) as total')->groupBy('estado')
                        ->orderByDesc('total')->limit(5)->get()
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
            Cache::forget('regimenes_fiscales');
            Cache::forget('usos_cfdi');
            Cache::forget('estados_mexico');
            Cache::forget('clientes_stats');

            Log::info('Cache de clientes limpiada', ['usuario' => Auth::id()]);

            return response()->json(['success' => true, 'message' => 'Cache limpiada correctamente']);
        } catch (Exception $e) {
            Log::error('Error limpiando cache: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }
}
