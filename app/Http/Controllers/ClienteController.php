<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Cliente;
use App\Http\Requests\StoreClienteRequest;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    // --- Métodos privados existentes (no necesitan cambios aquí) ---
    private function getTiposPersona()
    {
        return [
            'fisica' => 'Persona Física',
            'moral' => 'Persona Moral',
        ];
    }

    private function getRegimenesFiscales()
    {
        return [
            '601' => 'General de Ley Personas Morales',
            '603' => 'Personas Morales con Fines no Lucrativos',
            '605' => 'Sueldos y Salarios e Ingresos Asimilados a Salarios',
            '606' => 'Arrendamiento',
            '607' => 'Régimen de Enajenación o Adquisición de Bienes',
            '608' => 'Demás ingresos',
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
        ];
    }

    private function getUsosCFDI()
    {
        return [
            'G01' => 'Adquisición de mercancías',
            'G02' => 'Devoluciones, descuentos o bonificaciones',
            'G03' => 'Gastos en general',
            'I01' => 'Construcciones',
            'I02' => 'Mobilario y equipo de oficina por inversiones',
            'I03' => 'Equipo de transporte',
            'I04' => 'Equipo de computo y accesorios',
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
    }

    // --- NUEVO método auxiliar para formatear los datos para Inertia/Vue ---
    private function formatForVueSelect(array $options, bool $includeEmpty = false): array
    {
        $formatted = collect($options)->map(function ($label, $value) {
            return ['value' => $value, 'label' => $label];
        })->values()->toArray();

        if ($includeEmpty) {
            array_unshift($formatted, ['value' => '', 'label' => 'Selecciona una opción']);
        }

        return $formatted;
    }

    private function getTipoPersonaNombre($codigo)
    {
        $tipos = $this->getTiposPersona();
        return $tipos[$codigo] ?? $codigo;
    }

    private function getRegimenFiscalNombre($codigo)
    {
        $regimenes = $this->getRegimenesFiscales();
        return $regimenes[$codigo] ?? $codigo;
    }

    private function getUsoCFDINombre($codigo)
    {
        $usos = $this->getUsosCFDI();
        return $usos[$codigo] ?? $codigo;
    }

    private function transformClientes($clientes)
    {
        if ($clientes->isEmpty()) {
            return $clientes;
        }

        $clientes->transform(function ($cliente) {
            $cliente->tipo_persona_nombre = $this->getTipoPersonaNombre($cliente->tipo_persona);
            $cliente->regimen_fiscal_nombre = $this->getRegimenFiscalNombre($cliente->regimen_fiscal);
            $cliente->uso_cfdi_nombre = $this->getUsoCFDINombre($cliente->uso_cfdi);
            return $cliente;
        });

        return $clientes;
    }

    // --- Métodos públicos modificados para usar el nuevo formato ---

    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre_razon_social', 'like', "%{$search}%")
                    ->orWhere('rfc', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('uso_cfdi')) {
            $query->where('uso_cfdi', $request->get('uso_cfdi'));
        }

        $clientes = $query->paginate(10);
        $clientesCount = Cliente::count();

        $this->transformClientes($clientes);

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'clientesCount' => $clientesCount,
            // <--- MODIFICADO AQUÍ --- >
            'tiposPersona' => $this->formatForVueSelect($this->getTiposPersona()),
            'regimenesFiscales' => $this->formatForVueSelect($this->getRegimenesFiscales(), true),
            'usosCFDI' => $this->formatForVueSelect($this->getUsosCFDI(), true),
            // <--- FIN DE MODIFICACIÓN --- >
            'filters' => $request->only(['search', 'tipo_persona', 'regimen_fiscal', 'uso_cfdi']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Clientes/Create', [
            // <--- MODIFICADO AQUÍ --- >
            'tiposPersona' => $this->formatForVueSelect($this->getTiposPersona()),
            'regimenesFiscales' => $this->formatForVueSelect($this->getRegimenesFiscales(), true),
            'usosCFDI' => $this->formatForVueSelect($this->getUsosCFDI(), true),
            // <--- FIN DE MODIFICACIÓN --- >
        ]);
    }

    public function store(StoreClienteRequest $request)
    {
        try {
            Log::info('Datos recibidos en store:', $request->validated());

            $cliente = Cliente::create($request->validated());

            Log::info('Cliente creado:', $cliente->toArray());

            return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al crear el cliente: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Hubo un problema al crear el cliente: ' . $e->getMessage())->withInput();
        }
    }


    public function show(Cliente $cliente)
    {
        $cliente->tipo_persona_nombre = $this->getTipoPersonaNombre($cliente->tipo_persona);
        $cliente->regimen_fiscal_nombre = $this->getRegimenFiscalNombre($cliente->regimen_fiscal);
        $cliente->uso_cfdi_nombre = $this->getUsoCFDINombre($cliente->uso_cfdi);

        return Inertia::render('Clientes/Show', [
            'cliente' => $cliente,
        ]);
    }

    public function edit(Cliente $cliente)
    {
        Log::info('Datos del cliente enviados a edit:', $cliente->toArray());
        $cliente->tipo_persona_nombre = $this->getTipoPersonaNombre($cliente->tipo_persona);
        $cliente->regimen_fiscal_nombre = $this->getRegimenFiscalNombre($cliente->regimen_fiscal);
        $cliente->uso_cfdi_nombre = $this->getUsoCFDINombre($cliente->uso_cfdi);

        return Inertia::render('Clientes/Edit', [
            'cliente' => $cliente,
            // <--- MODIFICADO AQUÍ --- >
            'tiposPersona' => $this->formatForVueSelect($this->getTiposPersona()),
            'regimenesFiscales' => $this->formatForVueSelect($this->getRegimenesFiscales(), true),
            'usosCFDI' => $this->formatForVueSelect($this->getUsosCFDI(), true),
            // <--- FIN DE MODIFICACIÓN --- >
            'errors' => session('errors') ? session('errors')->getBag('default')->getMessages() : [],
        ]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        try {
            Log::info('Datos recibidos en update:', $request->all());

            // Definir las reglas de validación para la actualización
            $regimenesFisicas = ['605', '606', '607', '608', '610', '611', '612', '614', '615', '616', '621', '625', '626'];
            $regimenesMorales = ['601', '603', '609', '620', '622', '623', '624', '628'];

            $rules = [
                'nombre_razon_social' => 'required|string|max:255',
                'tipo_persona' => 'required|in:fisica,moral',
                'rfc' => [
                    'required',
                    'string',
                    'max:13',
                    'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/',
                    function ($attribute, $value, $fail) use ($cliente) {
                        if ($value !== 'XAXX010101000' && Cliente::where('rfc', $value)->where('id', '!=', $cliente->id)->exists()) {
                            $fail('El RFC ya está registrado.');
                        }
                    },
                ],
                'regimen_fiscal' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($regimenesFisicas, $regimenesMorales, $request) {
                        if ($request->tipo_persona === 'fisica' && !in_array($value, $regimenesFisicas)) {
                            $fail('El régimen fiscal no es válido para persona física.');
                        }
                        if ($request->tipo_persona === 'moral' && !in_array($value, $regimenesMorales)) {
                            $fail('El régimen fiscal no es válido para persona moral.');
                        }
                    },
                ],
                'uso_cfdi' => 'required|string|in:G01,G02,G03,I01,I02,I03,I04,I05,I06,I07,I08,D01,D02,D03,D04,D05,D06,D07,D08,D09,D10,S01,CP01,CN01',
                'email' => 'required|email|max:255', // Eliminada la validación de unicidad
                'telefono' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
                'calle' => 'required|string|max:255',
                'numero_exterior' => 'required|string|max:20',
                'numero_interior' => 'nullable|string|max:20',
                'colonia' => 'required|string|max:255',
                'codigo_postal' => 'required|string|size:5|regex:/^[0-9]{5}$/',
                'municipio' => 'required|string|max:255',
                'estado' => 'required|string|max:255',
                'pais' => 'required|string|max:255',
                'notas' => 'nullable|string|max:500',
                'activo' => 'boolean',
                'acepta_marketing' => 'boolean',
            ];

            // Ajustar la longitud del RFC según el tipo de persona
            if ($request->tipo_persona === 'fisica') {
                $rules['rfc'][] = 'size:13';
            } elseif ($request->tipo_persona === 'moral') {
                $rules['rfc'][] = 'size:12';
            }

            // Validar los datos
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $this->messages());

            if ($validator->fails()) {
                Log::warning('Errores de validación en update:', $validator->errors()->toArray());
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Actualizar el cliente
            $cliente->update($validator->validated());

            Log::info('Cliente actualizado:', $cliente->toArray());

            return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar el cliente: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Hubo un problema al actualizar el cliente: ' . $e->getMessage())->withInput();
        }
    }

    // Método auxiliar para los mensajes de error personalizados
    private function messages()
    {
        return [
            'nombre_razon_social.required' => 'El nombre o razón social es obligatorio.',
            'tipo_persona.required' => 'El tipo de persona es obligatorio.',
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.regex' => 'El RFC no tiene un formato válido.',
            'rfc.unique' => 'El RFC ya está registrado.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'telefono.regex' => 'El teléfono solo debe contener números, espacios, paréntesis, guiones y el signo +.',
            'codigo_postal.required' => 'El código postal es obligatorio.',
            'codigo_postal.size' => 'El código postal debe tener 5 dígitos.',
            'calle.required' => 'La calle es obligatoria.',
            'numero_exterior.required' => 'El número exterior es obligatorio.',
            'colonia.required' => 'La colonia es obligatoria.',
            'municipio.required' => 'El municipio es obligatorio.',
            'estado.required' => 'El estado es obligatorio.',
            'pais.required' => 'El país es obligatorio.',
            'regimen_fiscal.required' => 'El régimen fiscal es obligatorio.',
            'uso_cfdi.required' => 'El uso CFDI es obligatorio.',
        ];
    }


    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente');
    }


    /**
     * Valida si un RFC ya existe en la base de datos
     */
    public function validarRfc(Request $request)
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

            // Verificar si el RFC ya existe, excluyendo el cliente actual si es edición
            $query = \App\Models\Cliente::where('rfc', $rfc);

            if ($clienteId) {
                $query->where('id', '!=', $clienteId);
            }

            $existe = $query->exists();

            return response()->json([
                'success' => true,
                'exists' => $existe,
                'message' => $existe ? 'RFC ya registrado' : 'RFC disponible'
            ]);
        } catch (\Exception $e) {
            Log::error('Error validando RFC: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function toggle(Cliente $cliente)
    {
        try {
            $cliente->update(['activo' => !$cliente->activo]);
            Log::info('Estado del cliente actualizado:', $cliente->toArray());

            return Inertia::render('Clientes/Edit', [
                'cliente' => $cliente,
                // <--- MODIFICADO AQUÍ --- >
                'tiposPersona' => $this->formatForVueSelect($this->getTiposPersona()),
                'regimenesFiscales' => $this->formatForVueSelect($this->getRegimenesFiscales(), true),
                'usosCFDI' => $this->formatForVueSelect($this->getUsosCFDI(), true),
                // <--- FIN DE MODIFICACIÓN --- >
                'errors' => [],
                'success' => 'Estado del cliente actualizado correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cambiar el estado del cliente: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Hubo un problema al cambiar el estado del cliente: ' . $e->getMessage());
        }
    }



    public function checkRfc(Request $request)
    {
        $rfc = $request->query('rfc');
        $clienteId = $request->query('cliente_id');

        $query = Cliente::where('rfc', $rfc);

        if ($clienteId) {
            $query->where('id', '!=', $clienteId);
        }

        $exists = $query->exists();
        return response()->json(['exists' => $exists]);
    }

    public function getTiposPersonaOptions()
    {
        $tipos = $this->getTiposPersona();
        $options = [];

        foreach ($tipos as $codigo => $nombre) {
            $options[] = [
                'value' => $codigo,
                'label' => $nombre,
            ];
        }

        return response()->json($options);
    }

    public function getRegimenesFiscalesOptions()
    {
        $regimenes = $this->getRegimenesFiscales();
        $options = [];

        foreach ($regimenes as $codigo => $nombre) {
            $options[] = [
                'value' => $codigo,
                'label' => $codigo . ' - ' . $nombre,
            ];
        }

        return response()->json($options);
    }

    public function getUsosCFDIOptions()
    {
        $usos = $this->getUsosCFDI();
        $options = [];

        foreach ($usos as $codigo => $nombre) {
            $options[] = [
                'value' => $codigo,
                'label' => $codigo . ' - ' . $nombre,
            ];
        }

        return response()->json($options);
    }
}
