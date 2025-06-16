<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Events\ClientCreated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    /**
     * Regímenes fiscales disponibles
     */
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

    /**
     * Usos CFDI disponibles
     */
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

    /**
     * Obtiene el nombre del régimen fiscal por código
     */
    private function getRegimenFiscalNombre($codigo)
    {
        $regimenes = $this->getRegimenesFiscales();
        return $regimenes[$codigo] ?? $codigo;
    }

    /**
     * Obtiene el nombre del uso CFDI por código
     */
    private function getUsoCFDINombre($codigo)
    {
        $usos = $this->getUsosCFDI();
        return $usos[$codigo] ?? $codigo;
    }

    /**
     * Transforma los clientes agregando el nombre del régimen fiscal y uso CFDI
     */
    private function transformClientes($clientes)
    {
        if ($clientes->isEmpty()) {
            return $clientes;
        }

        $clientes->transform(function ($cliente) {
            $cliente->regimen_fiscal_nombre = $this->getRegimenFiscalNombre($cliente->regimen_fiscal);
            $cliente->uso_cfdi_nombre = $this->getUsoCFDINombre($cliente->uso_cfdi);
            return $cliente;
        });

        return $clientes;
    }

    public function index(Request $request)
    {
        $query = Cliente::query();

        // Filtro de búsqueda opcional
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre_razon_social', 'like', "%{$search}%")
                    ->orWhere('rfc', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por uso CFDI opcional
        if ($request->filled('uso_cfdi')) {
            $query->where('uso_cfdi', $request->get('uso_cfdi'));
        }

        $clientes = $query->paginate(10);
        $clientesCount = Cliente::count();

        // Transformar clientes para incluir nombre del régimen fiscal y uso CFDI
        $clientes->through(function ($cliente) {
            $cliente->regimen_fiscal_nombre = $this->getRegimenFiscalNombre($cliente->regimen_fiscal);
            $cliente->uso_cfdi_nombre = $this->getUsoCFDINombre($cliente->uso_cfdi);
            return $cliente;
        });

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'clientesCount' => $clientesCount,
            'regimenesFiscales' => $this->getRegimenesFiscales(),
            'usosCFDI' => $this->getUsosCFDI(),
            'filters' => $request->only(['search', 'regimen_fiscal', 'uso_cfdi']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Clientes/Create', [
            'regimenesFiscales' => $this->getRegimenesFiscales(),
            'usosCFDI' => $this->getUsosCFDI(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Datos recibidos:', $request->all());

            $validator = Validator::make($request->all(), [
                'nombre_razon_social' => 'required|string|max:255',
                'rfc' => [
                    'nullable',
                    'string',
                    'max:13',
                    'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/',
                    function ($attribute, $value, $fail) {
                        if (!empty($value) && $value !== 'XAXX010101000' && Cliente::where('rfc', $value)->exists()) {
                            $fail('El RFC ya está registrado.');
                        }
                    },
                ],
                'regimen_fiscal' => 'nullable|string|in:' . implode(',', array_keys($this->getRegimenesFiscales())),
                'uso_cfdi' => 'nullable|string|in:' . implode(',', array_keys($this->getUsosCFDI())),
                'email' => 'nullable|email|max:255|unique:clientes,email',
                'telefono' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
                'calle' => 'nullable|string|max:255',
                'numero_exterior' => 'nullable|string|max:20',
                'numero_interior' => 'nullable|string|max:20',
                'colonia' => 'nullable|string|max:255',
                'codigo_postal' => 'nullable|string|max:5|regex:/^[0-9]{5}$/',
                'municipio' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:255',
                'pais' => 'nullable|string|max:255',
            ], [
                'nombre_razon_social.required' => 'El nombre o razón social es obligatorio.',
                'rfc.regex' => 'El RFC no tiene un formato válido.',
                'email.email' => 'El email debe tener un formato válido.',
                'email.unique' => 'El email ya está registrado.',
                'telefono.regex' => 'El teléfono solo debe contener números, espacios, paréntesis, guiones y el signo +.',
                'codigo_postal.regex' => 'El código postal debe tener 5 dígitos.',
                'regimen_fiscal.in' => 'El régimen fiscal seleccionado no es válido.',
                'uso_cfdi.in' => 'El uso CFDI seleccionado no es válido.',
                'uso_cfdi.in' => 'El uso CFDI seleccionado no es válido.',
            ]);

            if ($validator->fails()) {
                Log::error('Errores de validación:', $validator->errors()->toArray());
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $cliente = Cliente::create($request->all());
            Log::info('Cliente creado:', $cliente->toArray());

            // event(new ClientCreated($cliente)); // Descomenta si necesitas el evento

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente creado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al crear el cliente: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Hubo un problema al crear el cliente: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Cliente $cliente)
    {
        $cliente->regimen_fiscal_nombre = $this->getRegimenFiscalNombre($cliente->regimen_fiscal);
        $cliente->uso_cfdi_nombre = $this->getUsoCFDINombre($cliente->uso_cfdi);

        return Inertia::render('Clientes/Show', [
            'cliente' => $cliente,
        ]);
    }

    public function edit(Cliente $cliente)
    {
        $cliente->regimen_fiscal_nombre = $this->getRegimenFiscalNombre($cliente->regimen_fiscal);
        $cliente->uso_cfdi_nombre = $this->getUsoCFDINombre($cliente->uso_cfdi);

        return Inertia::render('Clientes/Edit', [
            'cliente' => $cliente,
            'regimenesFiscales' => $this->getRegimenesFiscales(),
            'usosCFDI' => $this->getUsosCFDI(),
        ]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre_razon_social' => 'required|string|max:255',
                'rfc' => [
                    'nullable',
                    'string',
                    'max:13',
                    'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/',
                    'unique:clientes,rfc,' . $cliente->id,
                ],
                'regimen_fiscal' => 'nullable|string|in:' . implode(',', array_keys($this->getRegimenesFiscales())),
                'uso_cfdi' => 'nullable|string|in:' . implode(',', array_keys($this->getUsosCFDI())),
                'email' => 'nullable|email|max:255|unique:clientes,email,' . $cliente->id,
                'telefono' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
                'calle' => 'nullable|string|max:255',
                'numero_exterior' => 'nullable|string|max:20',
                'numero_interior' => 'nullable|string|max:20',
                'colonia' => 'nullable|string|max:255',
                'codigo_postal' => 'nullable|string|max:5|regex:/^[0-9]{5}$/',
                'municipio' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:255',
                'pais' => 'nullable|string|max:255',
            ], [
                'nombre_razon_social.required' => 'El nombre o razón social es obligatorio.',
                'rfc.regex' => 'El RFC no tiene un formato válido.',
                'rfc.unique' => 'El RFC ya está registrado.',
                'email.email' => 'El email debe tener un formato válido.',
                'email.unique' => 'El email ya está registrado.',
                'telefono.regex' => 'El teléfono solo debe contener números, espacios, paréntesis, guiones y el signo +.',
                'codigo_postal.regex' => 'El código postal debe tener 5 dígitos.',
                'regimen_fiscal.in' => 'El régimen fiscal seleccionado no es válido.',
            ]);

            if ($validator->fails()) {
                Log::error('Errores de validación:', $validator->errors()->toArray());
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $cliente->update($request->all());

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente actualizado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar el cliente: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Hubo un problema al actualizar el cliente: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            $cliente->delete();

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente eliminado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar el cliente: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Hubo un problema al eliminar el cliente: ' . $e->getMessage());
        }
    }

    public function checkEmail(Request $request)
    {
        $exists = Cliente::where('email', $request->query('email'))->exists();
        return response()->json(['exists' => $exists]);
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

    /**
     * Obtiene los regímenes fiscales para el frontend
     */
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

    /**
     * Obtiene los usos CFDI para el frontend
     */
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
