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

/**
 * Controlador para la gestión de clientes
 *
 * Maneja todas las operaciones CRUD y funcionalidades adicionales
 * relacionadas con la gestión de clientes del sistema
 */
class ClienteController extends Controller
{
    /**
     * Número de registros por página para paginación
     */
    private const ITEMS_PER_PAGE = 10;

    /**
     * Tiempo de cache en minutos para catálogos
     */
    private const CACHE_TTL = 60;

    /**
     * Tipos de persona permitidos
     */
    private const TIPOS_PERSONA = [
        'fisica' => 'Persona Física',
        'moral' => 'Persona Moral',
    ];

    /**
     * Regímenes fiscales del SAT
     */
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

    /**
     * Regímenes fiscales válidos para personas físicas
     */
    private const REGIMENES_FISICA = ['605', '606', '607', '608', '610', '611', '612', '614', '615', '616', '621', '625', '626'];

    /**
     * Regímenes fiscales válidos para personas morales
     */
    private const REGIMENES_MORAL = ['601', '603', '609', '620', '622', '623', '624', '628', '629', '630'];

    /**
     * Usos de CFDI del SAT
     */
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

    /**
     * Estados de México
     */
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

    // ====================================================================
    // MÉTODOS PRIVADOS AUXILIARES
    // ====================================================================

    /**
     * Obtiene los tipos de persona desde cache o constante
     */
    private function getTiposPersona(): array
    {
        return Cache::remember('tipos_persona', self::CACHE_TTL, function () {
            return self::TIPOS_PERSONA;
        });
    }

    /**
     * Obtiene los regímenes fiscales desde cache o constante
     */
    private function getRegimenesFiscales(): array
    {
        return Cache::remember('regimenes_fiscales', self::CACHE_TTL, function () {
            return self::REGIMENES_FISCALES;
        });
    }

    /**
     * Obtiene los usos de CFDI desde cache o constante
     */
    private function getUsosCFDI(): array
    {
        return Cache::remember('usos_cfdi', self::CACHE_TTL, function () {
            return self::USOS_CFDI;
        });
    }

    /**
     * Obtiene los estados de México desde cache o constante
     */
    private function getEstadosMexico(): array
    {
        return Cache::remember('estados_mexico', self::CACHE_TTL, function () {
            return array_combine(self::ESTADOS_MEXICO, self::ESTADOS_MEXICO);
        });
    }

    /**
     * Formatea un array asociativo para uso en componentes Vue/Select
     */
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

    /**
     * Obtiene el nombre del tipo de persona
     */
    private function getTipoPersonaNombre(string $codigo): string
    {
        $tipos = $this->getTiposPersona();
        return $tipos[$codigo] ?? $codigo;
    }

    /**
     * Obtiene el nombre del régimen fiscal
     */
    private function getRegimenFiscalNombre(string $codigo): string
    {
        $regimenes = $this->getRegimenesFiscales();
        return $regimenes[$codigo] ?? $codigo;
    }

    /**
     * Obtiene el nombre del uso de CFDI
     */
    private function getUsoCFDINombre(string $codigo): string
    {
        $usos = $this->getUsosCFDI();
        return $usos[$codigo] ?? $codigo;
    }

    /**
     * Transforma la colección de clientes agregando nombres descriptivos
     */
    private function transformClientes($clientes)
    {
        if ($clientes->isEmpty()) {
            return $clientes;
        }

        $clientes->transform(function ($cliente) {
            $cliente->tipo_persona_nombre = $this->getTipoPersonaNombre($cliente->tipo_persona);
            $cliente->regimen_fiscal_nombre = $this->getRegimenFiscalNombre($cliente->regimen_fiscal);
            $cliente->uso_cfdi_nombre = $this->getUsoCFDINombre($cliente->uso_cfdi);
            $cliente->estado_texto = $cliente->activo ? 'Activo' : 'Inactivo';
            return $cliente;
        });

        return $clientes;
    }

    /**
     * Construye el query de búsqueda y filtros
     */
    private function buildSearchQuery(Request $request)
    {
        $query = Cliente::query();

        // Búsqueda general
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nombre_razon_social', 'like', "%{$search}%")
                    ->orWhere('rfc', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        // Filtro por tipo de persona
        if ($request->filled('tipo_persona')) {
            $query->where('tipo_persona', $request->get('tipo_persona'));
        }

        // Filtro por régimen fiscal
        if ($request->filled('regimen_fiscal')) {
            $query->where('regimen_fiscal', $request->get('regimen_fiscal'));
        }

        // Filtro por uso de CFDI
        if ($request->filled('uso_cfdi')) {
            $query->where('uso_cfdi', $request->get('uso_cfdi'));
        }

        // Filtro por estado (activo/inactivo)
        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        // Filtro por estado geográfico
        if ($request->filled('estado')) {
            $query->where('estado', $request->get('estado'));
        }

        return $query;
    }

    /**
     * Obtiene las reglas de validación para RFC
     */
    private function getRfcValidationRules(?int $clienteId = null): array
    {
        return [
            'required',
            'string',
            'max:13',
            'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/',
            function ($attribute, $value, $fail) use ($clienteId) {
                $query = Cliente::where('rfc', $value);
                if ($clienteId) {
                    $query->where('id', '!=', $clienteId);
                }

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

    // 2. MÉTODO PARA CREAR/OBTENER CLIENTE GENÉRICO AUTOMÁTICAMENTE
    /**
     * Obtiene o crea el cliente genérico si no existe
     */
    public function getOrCreateClienteGenerico(): Cliente
    {
        $clienteGenerico = Cliente::where('rfc', 'XAXX010101000')->first();

        if (!$clienteGenerico) {
            $clienteGenerico = Cliente::create([
                'nombre_razon_social' => 'PUBLICO EN GENERAL',
                'tipo_persona' => 'fisica',
                'rfc' => 'XAXX010101000',
                'regimen_fiscal' => '616', // Sin obligaciones fiscales
                'uso_cfdi' => 'S01', // Sin efectos fiscales
                'email' => 'publico@general.com',
                'telefono' => null,
                'calle' => 'N/A',
                'numero_exterior' => 'S/N',
                'numero_interior' => null,
                'colonia' => 'N/A',
                'codigo_postal' => '00000',
                'municipio' => 'N/A',
                'estado' => 'Ciudad de México',
                'pais' => 'México',
                'notas' => 'Cliente genérico para ventas al público en general',
                'activo' => true,
                'acepta_marketing' => false,
            ]);

            Log::info('Cliente genérico creado automáticamente', ['id' => $clienteGenerico->id]);
        }

        return $clienteGenerico;
    }

    /**
     * Obtiene las reglas de validación para régimen fiscal
     */
    private function getRegimenFiscalValidationRules(): array
    {
        return [
            'required',
            'string',
            function ($attribute, $value, $fail) {
                $request = request();
                $tipoPersona = $request->input('tipo_persona');

                if ($tipoPersona === 'fisica' && !in_array($value, self::REGIMENES_FISICA)) {
                    $fail('El régimen fiscal no es válido para persona física.');
                }

                if ($tipoPersona === 'moral' && !in_array($value, self::REGIMENES_MORAL)) {
                    $fail('El régimen fiscal no es válido para persona moral.');
                }
            },
        ];
    }

    /**
     * Obtiene las reglas de validación completas
     */
    private function getValidationRules(?int $clienteId = null): array
    {
        return [
            'nombre_razon_social' => 'required|string|max:255|regex:/^[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s\.,&\-\']+$/',
            'tipo_persona' => 'required|in:fisica,moral',
            'rfc' => $this->getRfcValidationRules($clienteId),
            'regimen_fiscal' => $this->getRegimenFiscalValidationRules(),
            'uso_cfdi' => 'required|string|in:' . implode(',', array_keys(self::USOS_CFDI)),
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                function ($attribute, $value, $fail) use ($clienteId) {
                    $query = Cliente::where('email', $value);
                    if ($clienteId) {
                        $query->where('id', '!=', $clienteId);
                    }
                    if ($query->exists()) {
                        $fail('El email ya está registrado.');
                    }
                }
            ],
            'telefono' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'calle' => 'required|string|max:255',
            'numero_exterior' => 'required|string|max:20',
            'numero_interior' => 'nullable|string|max:20',
            'colonia' => 'required|string|max:255',
            'codigo_postal' => 'required|string|size:5|regex:/^[0-9]{5}$/',
            'municipio' => 'required|string|max:255',
            'estado' => 'required|string|max:255|in:' . implode(',', self::ESTADOS_MEXICO),
            'pais' => 'required|string|max:255',
            'notas' => 'nullable|string|max:1000',
            'activo' => 'boolean',
            'acepta_marketing' => 'boolean',
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    private function getValidationMessages(): array
    {
        return [
            'nombre_razon_social.required' => 'El nombre o razón social es obligatorio.',
            'nombre_razon_social.regex' => 'El nombre o razón social contiene caracteres no válidos.',
            'tipo_persona.required' => 'El tipo de persona es obligatorio.',
            'tipo_persona.in' => 'El tipo de persona debe ser física o moral.',
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.regex' => 'El RFC no tiene un formato válido.',
            'rfc.size' => 'El RFC debe tener la longitud correcta.',
            'regimen_fiscal.required' => 'El régimen fiscal es obligatorio.',
            'uso_cfdi.required' => 'El uso de CFDI es obligatorio.',
            'uso_cfdi.in' => 'El uso de CFDI seleccionado no es válido.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'El email ya está registrado.',
            'telefono.regex' => 'El teléfono solo debe contener números, espacios, paréntesis, guiones y el signo +.',
            'calle.required' => 'La calle es obligatoria.',
            'numero_exterior.required' => 'El número exterior es obligatorio.',
            'colonia.required' => 'La colonia es obligatoria.',
            'codigo_postal.required' => 'El código postal es obligatorio.',
            'codigo_postal.size' => 'El código postal debe tener 5 dígitos.',
            'codigo_postal.regex' => 'El código postal debe contener solo números.',
            'municipio.required' => 'El municipio es obligatorio.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'pais.required' => 'El país es obligatorio.',
            'notas.max' => 'Las notas no pueden exceder 1000 caracteres.',
        ];
    }

    /**
     * Formatea un cliente individual agregando información descriptiva
     */
    private function formatClienteForView(Cliente $cliente): Cliente
    {
        $cliente->tipo_persona_nombre = $this->getTipoPersonaNombre($cliente->tipo_persona);
        $cliente->regimen_fiscal_nombre = $this->getRegimenFiscalNombre($cliente->regimen_fiscal);
        $cliente->uso_cfdi_nombre = $this->getUsoCFDINombre($cliente->uso_cfdi);
        $cliente->estado_texto = $cliente->activo ? 'Activo' : 'Inactivo';

        return $cliente;
    }

    /**
     * Obtiene los datos de catálogos para formularios
     */
    private function getCatalogData(): array
    {
        return [
            'tiposPersona' => $this->formatForVueSelect($this->getTiposPersona()),
            'regimenesFiscales' => $this->formatForVueSelect($this->getRegimenesFiscales(), true, true),
            'usosCFDI' => $this->formatForVueSelect($this->getUsosCFDI(), true, true),
            'estados' => $this->formatForVueSelect($this->getEstadosMexico(), true),
        ];
    }

    // ====================================================================
    // MÉTODOS PÚBLICOS - CRUD
    // ====================================================================

    /**
     * Muestra la lista de clientes con filtros y paginación
     */
    public function index(Request $request)
    {
        try {
            $query = $this->buildSearchQuery($request);

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            $validSortFields = ['nombre_razon_social', 'rfc', 'email', 'created_at', 'activo'];
            if (!in_array($sortBy, $validSortFields)) {
                $sortBy = 'created_at';
            }

            $query->orderBy($sortBy, $sortDirection);

            $clientes = $query->paginate(self::ITEMS_PER_PAGE);
            $clientesCount = Cliente::count();
            $clientesActivos = Cliente::where('activo', true)->count();

            $this->transformClientes($clientes);

            return Inertia::render('Clientes/Index', [
                'clientes' => $clientes,
                'stats' => [
                    'total' => $clientesCount,
                    'activos' => $clientesActivos,
                    'inactivos' => $clientesCount - $clientesActivos,
                ],
                'catalogs' => $this->getCatalogData(),
                'filters' => $request->only([
                    'search',
                    'tipo_persona',
                    'regimen_fiscal',
                    'uso_cfdi',
                    'activo',
                    'estado'
                ]),
                'sorting' => [
                    'sort_by' => $sortBy,
                    'sort_direction' => $sortDirection,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Error en ClienteController@index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Error al cargar la lista de clientes.');
        }
    }

    /**
     * Muestra el formulario de creación de cliente
     */
    public function create()
    {
        try {
            return Inertia::render('Clientes/Create', [
                'catalogs' => $this->getCatalogData(),
            ]);
        } catch (Exception $e) {
            Log::error('Error en ClienteController@create: ' . $e->getMessage());
            return redirect()->route('clientes.index')->with('error', 'Error al cargar el formulario de creación.');
        }
    }

    /**
     * Almacena un nuevo cliente
     */
    public function store(StoreClienteRequest $request)
    {
        try {
            DB::beginTransaction();

            Log::info('Creando nuevo cliente', $request->validated());

            // Normalizar datos
            $data = $request->validated();
            $data['rfc'] = strtoupper(trim($data['rfc']));
            $data['email'] = strtolower(trim($data['email']));
            $data['nombre_razon_social'] = trim($data['nombre_razon_social']);

            $cliente = Cliente::create($data);

            DB::commit();

            Log::info('Cliente creado exitosamente', ['id' => $cliente->id]);

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente creado correctamente');
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Error de validación al crear cliente', $e->errors());
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cliente: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Hubo un problema al crear el cliente. Por favor, inténtelo de nuevo.')
                ->withInput();
        }
    }

    /**
     * Muestra un cliente específico
     */
    public function show(Cliente $cliente)
    {
        try {
            $cliente = $this->formatClienteForView($cliente);

            return Inertia::render('Clientes/Show', [
                'cliente' => $cliente,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::warning('Cliente no encontrado', ['id' => request()->route('cliente')]);
            return redirect()->route('clientes.index')->with('error', 'Cliente no encontrado.');
        } catch (Exception $e) {
            Log::error('Error al mostrar cliente: ' . $e->getMessage());
            return redirect()->route('clientes.index')->with('error', 'Error al cargar el cliente.');
        }
    }

    /**
     * Muestra el formulario de edición de cliente
     */
    public function edit(Cliente $cliente)
    {
        try {
            $cliente = $this->formatClienteForView($cliente);

            Log::info('Cargando formulario de edición', ['cliente_id' => $cliente->id]);

            return Inertia::render('Clientes/Edit', [
                'cliente' => $cliente,
                'catalogs' => $this->getCatalogData(),
                'errors' => session('errors') ? session('errors')->getBag('default')->getMessages() : [],
            ]);
        } catch (ModelNotFoundException $e) {
            Log::warning('Cliente no encontrado para edición', ['id' => request()->route('cliente')]);
            return redirect()->route('clientes.index')->with('error', 'Cliente no encontrado.');
        } catch (Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return redirect()->route('clientes.index')->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Actualiza un cliente existente
     */
    public function update(Request $request, Cliente $cliente)
    {
        try {
            DB::beginTransaction();

            Log::info('Actualizando cliente', [
                'cliente_id' => $cliente->id,
                'data' => $request->all()
            ]);

            // Validar datos
            $validator = Validator::make(
                $request->all(),
                $this->getValidationRules($cliente->id),
                $this->getValidationMessages()
            );

            if ($validator->fails()) {
                Log::warning('Errores de validación en actualización', $validator->errors()->toArray());
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Normalizar datos
            $data = $validator->validated();
            $data['rfc'] = strtoupper(trim($data['rfc']));
            $data['email'] = strtolower(trim($data['email']));
            $data['nombre_razon_social'] = trim($data['nombre_razon_social']);

            // Validar longitud del RFC según tipo de persona
            if ($data['tipo_persona'] === 'fisica' && strlen($data['rfc']) !== 13) {
                return redirect()->back()
                    ->withErrors(['rfc' => 'El RFC de persona física debe tener 13 caracteres.'])
                    ->withInput();
            } elseif ($data['tipo_persona'] === 'moral' && strlen($data['rfc']) !== 12) {
                return redirect()->back()
                    ->withErrors(['rfc' => 'El RFC de persona moral debe tener 12 caracteres.'])
                    ->withInput();
            }

            $cliente->update($data);

            DB::commit();

            Log::info('Cliente actualizado exitosamente', ['cliente_id' => $cliente->id]);

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente actualizado correctamente');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Cliente no encontrado para actualización', ['id' => $cliente->id ?? 'N/A']);
            return redirect()->route('clientes.index')->with('error', 'Cliente no encontrado.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cliente: ' . $e->getMessage(), [
                'cliente_id' => $cliente->id ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Hubo un problema al actualizar el cliente. Por favor, inténtelo de nuevo.')
                ->withInput();
        }
    }

    /**
     * Elimina un cliente (soft delete si está configurado)
     */
    public function destroy(Cliente $cliente)
    {
        try {
            DB::beginTransaction();

            Log::info('Eliminando cliente', ['cliente_id' => $cliente->id]);

            // Verificar si el cliente tiene relaciones que impidan su eliminación
            // Aquí puedes agregar validaciones según tu modelo de negocio
            // Por ejemplo: if ($cliente->facturas()->exists()) { ... }

            $cliente->delete();

            DB::commit();

            Log::info('Cliente eliminado exitosamente', ['cliente_id' => $cliente->id]);

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente eliminado correctamente');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Cliente no encontrado para eliminación', ['id' => request()->route('cliente')]);
            return redirect()->route('clientes.index')->with('error', 'Cliente no encontrado.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar cliente: ' . $e->getMessage(), [
                'cliente_id' => $cliente->id ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'No se pudo eliminar el cliente. Verifique que no tenga registros relacionados.');
        }
    }

    // ====================================================================
    // MÉTODOS PÚBLICOS - FUNCIONALIDADES ADICIONALES
    // ====================================================================

    /**
     * Cambia el estado activo/inactivo de un cliente
     */
    public function toggle(Cliente $cliente)
    {
        try {
            DB::beginTransaction();

            $estadoAnterior = $cliente->activo;
            $cliente->update(['activo' => !$cliente->activo]);

            DB::commit();

            Log::info('Estado del cliente actualizado', [
                'cliente_id' => $cliente->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $cliente->activo
            ]);

            $mensaje = $cliente->activo
                ? 'Cliente activado correctamente'
                : 'Cliente desactivado correctamente';

            return redirect()->back()->with('success', $mensaje);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Cliente no encontrado para cambio de estado', ['id' => request()->route('cliente')]);
            return redirect()->route('clientes.index')->with('error', 'Cliente no encontrado.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado del cliente: ' . $e->getMessage(), [
                'cliente_id' => $cliente->id ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Hubo un problema al cambiar el estado del cliente.');
        }
    }

    // ====================================================================
    // MÉTODOS PÚBLICOS - API/AJAX
    // ====================================================================

    /**
     * Valida si un RFC ya existe en la base de datos (AJAX)
     */
    public function validarRfc(Request $request): JsonResponse
    {
        try {
            $rfc = strtoupper(trim($request->input('rfc', '')));
            $clienteId = $request->input('cliente_id');

            if (empty($rfc)) {
                return response()->json([
                    'success' => false,
                    'exists' => false,
                    'message' => 'RFC requerido'
                ], 422);
            }

            // Validar formato del RFC
            if (!preg_match('/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $rfc)) {
                return response()->json([
                    'success' => false,
                    'exists' => false,
                    'message' => 'Formato de RFC inválido'
                ], 422);
            }

            // RFC genérico no se considera duplicado
            if ($rfc === 'XAXX010101000') {
                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'message' => 'RFC genérico válido'
                ]);
            }

            // Verificar si el RFC ya existe
            $query = Cliente::where('rfc', $rfc);
            if ($clienteId) {
                $query->where('id', '!=', $clienteId);
            }

            $existe = $query->exists();
            $cliente = $existe ? $query->first() : null;

            return response()->json([
                'success' => true,
                'exists' => $existe,
                'message' => $existe ? 'RFC ya registrado' : 'RFC disponible',
                'cliente' => $existe ? [
                    'id' => $cliente->id,
                    'nombre' => $cliente->nombre_razon_social
                ] : null
            ]);
        } catch (Exception $e) {
            Log::error('Error validando RFC: ' . $e->getMessage(), [
                'rfc' => $request->input('rfc'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Valida si un email ya existe en la base de datos (AJAX)
     */
    public function validarEmail(Request $request): JsonResponse
    {
        try {
            $email = strtolower(trim($request->input('email', '')));
            $clienteId = $request->input('cliente_id');

            if (empty($email)) {
                return response()->json([
                    'success' => false,
                    'exists' => false,
                    'message' => 'Email requerido'
                ], 422);
            }

            // Validar formato del email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'exists' => false,
                    'message' => 'Formato de email inválido'
                ], 422);
            }

            // Verificar si el email ya existe
            $query = Cliente::where('email', $email);
            if ($clienteId) {
                $query->where('id', '!=', $clienteId);
            }

            $existe = $query->exists();
            $cliente = $existe ? $query->first() : null;

            return response()->json([
                'success' => true,
                'exists' => $existe,
                'message' => $existe ? 'Email ya registrado' : 'Email disponible',
                'cliente' => $existe ? [
                    'id' => $cliente->id,
                    'nombre' => $cliente->nombre_razon_social
                ] : null
            ]);
        } catch (Exception $e) {
            Log::error('Error validando email: ' . $e->getMessage(), [
                'email' => $request->input('email'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtiene regímenes fiscales válidos según el tipo de persona (AJAX)
     */
    public function getRegimenesByTipoPersona(Request $request): JsonResponse
    {
        try {
            $tipoPersona = $request->input('tipo_persona');

            if (!in_array($tipoPersona, ['fisica', 'moral'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de persona inválido'
                ], 422);
            }

            $todosLosRegimenes = $this->getRegimenesFiscales();
            $regimenesValidos = $tipoPersona === 'fisica' ? self::REGIMENES_FISICA : self::REGIMENES_MORAL;

            $regimenesFiltrados = array_intersect_key($todosLosRegimenes, array_flip($regimenesValidos));
            $opciones = $this->formatForVueSelect($regimenesFiltrados, true, true);

            return response()->json([
                'success' => true,
                'regimenes' => $opciones
            ]);
        } catch (Exception $e) {
            Log::error('Error obteniendo regímenes fiscales: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Búsqueda de clientes para autocompletado (AJAX)
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = trim($request->input('q', ''));
            $limit = min($request->input('limit', 10), 50); // Máximo 50 resultados

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mínimo 2 caracteres para búsqueda'
                ], 422);
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

            $resultados = $clientes->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'nombre' => $cliente->nombre_razon_social,
                    'rfc' => $cliente->rfc,
                    'email' => $cliente->email,
                    'tipo_persona' => $this->getTipoPersonaNombre($cliente->tipo_persona),
                    'label' => "{$cliente->nombre_razon_social} ({$cliente->rfc})"
                ];
            });

            return response()->json([
                'success' => true,
                'clientes' => $resultados
            ]);
        } catch (Exception $e) {
            Log::error('Error en búsqueda de clientes: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Exporta clientes a CSV
     */
    public function export(Request $request)
    {
        try {
            $query = $this->buildSearchQuery($request);
            $clientes = $query->get();

            $filename = 'clientes_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($clientes) {
                $file = fopen('php://output', 'w');

                // BOM para UTF-8
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Encabezados
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
                    'Acepta Marketing',
                    'Notas',
                    'Fecha Creación'
                ]);

                // Datos
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
                        $cliente->acepta_marketing ? 'Sí' : 'No',
                        $cliente->notas,
                        $cliente->created_at->format('d/m/Y H:i:s')
                    ]);
                }

                fclose($file);
            };

            Log::info('Exportación de clientes realizada', [
                'total_registros' => $clientes->count(),
                'usuario' => Auth::id()  // ✅ Corrección
            ]);

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('Error en exportación de clientes: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Error al exportar los clientes.');
        }
    }

    // ====================================================================
    // MÉTODOS PÚBLICOS - CATÁLOGOS (LEGACY - MANTENER PARA COMPATIBILIDAD)
    // ====================================================================

    /**
     * @deprecated Usar getCatalogData() en su lugar
     */
    public function checkRfc(Request $request): JsonResponse
    {
        return $this->validarRfc($request);
    }

    /**
     * @deprecated Usar getCatalogData() en su lugar
     */
    public function getTiposPersonaOptions(): JsonResponse
    {
        try {
            $opciones = $this->formatForVueSelect($this->getTiposPersona());
            return response()->json($opciones);
        } catch (Exception $e) {
            Log::error('Error obteniendo tipos de persona: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * @deprecated Usar getCatalogData() en su lugar
     */
    public function getRegimenesFiscalesOptions(): JsonResponse
    {
        try {
            $opciones = $this->formatForVueSelect($this->getRegimenesFiscales(), false, true);
            return response()->json($opciones);
        } catch (Exception $e) {
            Log::error('Error obteniendo regímenes fiscales: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * @deprecated Usar getCatalogData() en su lugar
     */
    public function getUsosCFDIOptions(): JsonResponse
    {
        try {
            $opciones = $this->formatForVueSelect($this->getUsosCFDI(), false, true);
            return response()->json($opciones);
        } catch (Exception $e) {
            Log::error('Error obteniendo usos de CFDI: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    // ====================================================================
    // MÉTODOS PÚBLICOS - ESTADÍSTICAS Y REPORTES
    // ====================================================================

    /**
     * Obtiene estadísticas generales de clientes
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = Cache::remember('clientes_stats', 5, function () {
                return [
                    'total' => Cliente::count(),
                    'activos' => Cliente::where('activo', true)->count(),
                    'inactivos' => Cliente::where('activo', false)->count(),
                    'personas_fisicas' => Cliente::where('tipo_persona', 'fisica')->count(),
                    'personas_morales' => Cliente::where('tipo_persona', 'moral')->count(),
                    'con_marketing' => Cliente::where('acepta_marketing', true)->count(),
                    'nuevos_mes' => Cliente::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->count(),
                    'top_regimenes' => Cliente::select('regimen_fiscal')
                        ->selectRaw('COUNT(*) as total')
                        ->groupBy('regimen_fiscal')
                        ->orderByDesc('total')
                        ->limit(5)
                        ->get()
                        ->map(function ($item) {
                            return [
                                'regimen' => $item->regimen_fiscal,
                                'nombre' => $this->getRegimenFiscalNombre($item->regimen_fiscal),
                                'total' => $item->total
                            ];
                        }),
                    'top_estados' => Cliente::select('estado')
                        ->selectRaw('COUNT(*) as total')
                        ->groupBy('estado')
                        ->orderByDesc('total')
                        ->limit(5)
                        ->get()
                ];
            });

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            Log::error('Error obteniendo estadísticas de clientes: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtiene clientes con cumpleaños próximos (si tienes fecha de nacimiento)
     */
    public function cumpleanos(): JsonResponse
    {
        try {
            // Nota: Este método requiere que tengas un campo 'fecha_nacimiento' en tu modelo
            // Si no lo tienes, puedes remover este método o adaptarlo según tus necesidades

            return response()->json([
                'success' => true,
                'message' => 'Funcionalidad de cumpleaños no implementada',
                'clientes' => []
            ]);
        } catch (Exception $e) {
            Log::error('Error obteniendo cumpleaños: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    // ====================================================================
    // MÉTODOS PÚBLICOS - UTILIDADES
    // ====================================================================

    /**
     * Limpia la cache de catálogos
     */
    public function clearCache(): JsonResponse
    {
        try {
            Cache::forget('tipos_persona');
            Cache::forget('regimenes_fiscales');
            Cache::forget('usos_cfdi');
            Cache::forget('estados_mexico');
            Cache::forget('clientes_stats');

            Log::info('Cache de clientes limpiada', ['usuario' => Auth::id()]);

            return response()->json([
                'success' => true,
                'message' => 'Cache limpiada correctamente'
            ]);
        } catch (Exception $e) {
            Log::error('Error limpiando cache: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Valida masivamente RFCs desde un archivo
     */
    public function validateBulkRfc(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'rfcs' => 'required|array|max:100',
                'rfcs.*' => 'required|string|max:13'
            ]);

            $rfcs = array_map(function ($rfc) {
                return strtoupper(trim($rfc));
            }, $request->input('rfcs'));

            $resultados = [];
            $rfcsExistentes = Cliente::whereIn('rfc', $rfcs)->pluck('nombre_razon_social', 'rfc')->toArray();

            foreach ($rfcs as $rfc) {
                $valido = preg_match('/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $rfc);
                $existe = isset($rfcsExistentes[$rfc]);

                $resultados[] = [
                    'rfc' => $rfc,
                    'valido' => $valido,
                    'existe' => $existe,
                    'cliente' => $existe ? $rfcsExistentes[$rfc] : null,
                    'mensaje' => $this->getRfcValidationMessage($rfc, $valido, $existe)
                ];
            }

            return response()->json([
                'success' => true,
                'resultados' => $resultados
            ]);
        } catch (Exception $e) {
            Log::error('Error en validación masiva de RFCs: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtiene mensaje de validación para RFC
     */
    private function getRfcValidationMessage(string $rfc, bool $valido, bool $existe): string
    {
        if (!$valido) {
            return 'RFC con formato inválido';
        }

        if ($rfc === 'XAXX010101000') {
            return 'RFC genérico válido';
        }

        if ($existe) {
            return 'RFC ya registrado';
        }

        return 'RFC válido y disponible';
    }
}
