<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use App\Models\SatRegimenFiscal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UpdateClienteRequest extends FormRequest
{
    /**
     * The cliente instance.
     */
    protected Cliente $cliente;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->cliente = $this->route('cliente');
        return $this->cliente !== null;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cliente_id' => $this->cliente?->id,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $clienteId = $this->cliente?->id;
        $estadosCsv = $this->getEstadosCsv();

        return [
            'nombre_razon_social' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜ\s\.,&\-\']+$/',
            ],
            'tipo_persona' => ['sometimes', 'required', 'in:fisica,moral'],
            'rfc' => array_merge($this->getRfcValidationRules($clienteId), [
                'sometimes',
                'required',
                'string',
                'max:13',
                Rule::unique('clientes', 'rfc')->ignore($clienteId),
                'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/',
            ]),
            'regimen_fiscal' => array_merge($this->getRegimenFiscalValidationRules(), [
                'sometimes',
                'required',
                'string',
                'size:3',
                'exists:sat_regimenes_fiscales,clave',
            ]),
            'uso_cfdi' => [
                'sometimes',
                'required',
                'string',
                'size:3',
                'exists:sat_usos_cfdi,clave',
            ],
            'email' => [
                'sometimes',
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('clientes', 'email')->ignore($clienteId),
            ],
            'telefono' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],
            'calle' => ['sometimes', 'required', 'string', 'max:255'],
            'numero_exterior' => ['sometimes', 'required', 'string', 'max:20'],
            'numero_interior' => ['sometimes', 'nullable', 'string', 'max:20'],
            'colonia' => ['sometimes', 'required', 'string', 'max:255'],
            'codigo_postal' => [
                'sometimes',
                'required',
                'string',
                'size:5',
                'regex:/^[0-9]{5}$/',
            ],
            'municipio' => ['sometimes', 'required', 'string', 'max:255'],
            'estado' => [
                'sometimes',
                'required',
                'string',
                'size:3',
                "in:{$estadosCsv}",
            ],
            'pais' => ['sometimes', 'required', 'string', 'in:MX'],
            'notas' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'activo' => ['sometimes', 'boolean'],
            // Campos opcionales de identificación
            'tipo_identificacion' => ['sometimes', 'nullable', 'string', 'max:20'],
            'identificacion' => ['sometimes', 'nullable', 'string', 'max:50'],
            'curp' => [
                'sometimes',
                'nullable',
                'string',
                'size:18',
                'regex:/^[A-Z][AEIOU][A-Z]{2}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/',
            ],
            // Campos Facturapi (opcionales en update)
            'facturapi_customer_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cfdi_default_use' => ['sometimes', 'nullable', 'string', 'size:3', 'exists:sat_usos_cfdi,clave'],
            'payment_form_default' => ['sometimes', 'nullable', 'string', 'size:2'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nombre_razon_social.required' => 'El nombre o razón social es obligatorio.',
            'nombre_razon_social.regex' => 'El nombre/razón social contiene caracteres no válidos.',
            'tipo_persona.required' => 'El tipo de persona es obligatorio.',
            'tipo_persona.in' => 'El tipo de persona debe ser física o moral.',
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.regex' => 'El RFC no tiene un formato válido.',
            'rfc.unique' => 'El RFC ya está registrado en otro cliente.',
            'rfc.rfc_unique' => 'El RFC ya está registrado.',
            'regimen_fiscal.required' => 'El régimen fiscal es obligatorio.',
            'regimen_fiscal.exists' => 'El régimen fiscal no existe en el catálogo.',
            'regimen_fiscal.regimen_valid' => 'El régimen fiscal no es válido para el tipo de persona seleccionado.',
            'uso_cfdi.required' => 'El uso de CFDI es obligatorio.',
            'uso_cfdi.exists' => 'El uso de CFDI seleccionado no es válido.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'El email ya está registrado en otro cliente.',
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
            'pais.in' => 'El país debe ser MX.',
            'notas.max' => 'Las notas no pueden exceder 1000 caracteres.',
            'curp.regex' => 'El CURP no tiene un formato válido.',
        ];
    }

    /**
     * Get RFC validation rules (including unique check, excluding self).
     */
    private function getRfcValidationRules(?int $clienteId = null): array
    {
        return [
            function ($attribute, $value, $fail) use ($clienteId) {
                $value = strtoupper(trim($value));
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

    /**
     * Get Regimen Fiscal validation rules (compatibility with tipo_persona).
     */
    private function getRegimenFiscalValidationRules(): array
    {
        return [
            function ($attribute, $value, $fail) {
                $tipo = $this->input('tipo_persona');
                if (!$tipo) return; // No validar si no se proporciona
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

    /**
     * Get CSV of valid estados (from SAT).
     */
    private function getEstadosCsv(): string
    {
        // Asumir config('sat.estados') o similar; fallback a estados comunes si no existe
        $estados = collect([
            'AGU', 'BCN', 'BCS', 'CAM', 'CHP', 'CHH', 'COA', 'COL', 'DUR', 'GUA',
            'GRO', 'HID', 'JAL', 'MEX', 'MIC', 'MOR', 'NAY', 'NLE', 'OAX', 'PUE',
            'QUE', 'ROO', 'SLP', 'SIN', 'SON', 'TAB', 'TAM', 'TLA', 'VER', 'YUC', 'ZAC'
        ]);
        return $estados->implode(',');
    }
}
