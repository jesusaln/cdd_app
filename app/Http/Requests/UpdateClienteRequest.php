<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use App\Models\SatRegimenFiscal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Obtén el modelo/ID desde el parámetro de ruta {cliente}
        $routeCliente = $this->route('cliente');
        $clienteId = is_object($routeCliente) ? ($routeCliente->id ?? null)
            : (is_numeric($routeCliente) ? (int) $routeCliente : null);

        // Normaliza y adjunta el id al payload para usarlo en las reglas
        $toUpper = fn($v) => is_string($v) ? mb_strtoupper(trim($v), 'UTF-8') : $v;

        $this->merge([
            'cliente_id'         => $clienteId,
            'nombre_razon_social' => $toUpper($this->input('nombre_razon_social')),
            'tipo_persona'       => $this->input('tipo_persona') ? strtolower(trim($this->input('tipo_persona'))) : null,
            'rfc'                => $toUpper($this->input('rfc')),
            'regimen_fiscal'     => $toUpper($this->input('regimen_fiscal')),
            'uso_cfdi'           => $toUpper($this->input('uso_cfdi')),
            'calle'              => $toUpper($this->input('calle')),
            'numero_exterior'    => $toUpper($this->input('numero_exterior')),
            'numero_interior'    => $toUpper($this->input('numero_interior')),
            'colonia'            => $toUpper($this->input('colonia')),
            'codigo_postal'      => $this->input('codigo_postal') ? preg_replace('/\D/', '', $this->input('codigo_postal')) : null,
            'municipio'          => $toUpper($this->input('municipio')),
            'estado'             => $toUpper($this->input('estado')),
            'pais'               => $toUpper($this->input('pais')),
        ]);
    }

    public function rules(): array
    {
        $clienteId = $this->input('cliente_id');

        return [
            'nombre_razon_social' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜ\s\.,&\-\']+$/',
            ],

            'tipo_persona' => ['required', Rule::in(['fisica', 'moral'])],

            'rfc' => array_merge($this->getRfcValidationRules($clienteId), [
                'required',
                'string',
                'max:13',
                'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/',
            ]),

            'regimen_fiscal' => array_merge($this->getRegimenFiscalValidationRules(), [
                'required',
                'string',
                'size:3',
                Rule::exists('sat_regimenes_fiscales', 'clave'),
            ]),

            'uso_cfdi' => [
                'required',
                'string',
                'size:3',
                Rule::exists('sat_usos_cfdi', 'clave'),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clientes', 'email')->ignore($clienteId),
            ],

            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],

            'calle'            => ['required', 'string', 'max:255'],
            'numero_exterior'  => ['required', 'string', 'max:20'],
            'numero_interior'  => ['nullable', 'string', 'max:20'],
            'colonia'          => ['required', 'string', 'max:255'],
            'codigo_postal'    => ['required', 'string', 'size:5', 'regex:/^[0-9]{5}$/'],
            'municipio'        => ['required', 'string', 'max:255'],

            'estado' => [
                'required',
                'string',
                'size:3',
                tap(Rule::exists('sat_estados', 'clave'), function ($rule) {
                    if (Schema::hasColumn('sat_estados', 'activo')) {
                        $rule->where(fn($q) => $q->where('activo', 1));
                    }
                }),
            ],

            'pais'  => ['required', 'string', Rule::in(['MX'])],
            'notas' => ['nullable', 'string', 'max:1000'],
            'activo' => ['boolean'],

            // opcionales
            'tipo_identificacion' => ['nullable', 'string', 'max:20'],
            'identificacion'      => ['nullable', 'string', 'max:50'],
            'curp' => [
                'nullable',
                'string',
                'size:18',
                'regex:/^[A-Z][AEIOU][A-Z]{2}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/',
            ],

            'facturapi_customer_id' => ['nullable', 'string', 'max:255'],
            'cfdi_default_use'      => ['nullable', 'string', 'size:3', Rule::exists('sat_usos_cfdi', 'clave')],
            'payment_form_default'  => ['nullable', 'string', 'size:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_razon_social.required' => 'El nombre o razón social es obligatorio.',
            'nombre_razon_social.regex'    => 'El nombre/razón social contiene caracteres no válidos.',

            'tipo_persona.required' => 'El tipo de persona es obligatorio.',
            'tipo_persona.in'       => 'El tipo de persona debe ser física o moral.',

            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.regex'    => 'El RFC no tiene un formato válido.',
            'rfc.rfc_unique' => 'El RFC ya está registrado.',

            'regimen_fiscal.required' => 'El régimen fiscal es obligatorio.',
            'regimen_fiscal.exists'   => 'El régimen fiscal no existe en el catálogo.',
            'regimen_fiscal.regimen_valid' => 'El régimen fiscal no es válido para el tipo de persona seleccionado.',

            'uso_cfdi.required' => 'El uso de CFDI es obligatorio.',
            'uso_cfdi.exists'   => 'El uso de CFDI seleccionado no es válido.',

            'email.required' => 'El email es obligatorio.',
            'email.email'    => 'El email debe tener un formato válido.',
            'email.unique'   => 'El email ya está registrado.',

            'telefono.regex' => 'El teléfono solo debe contener números, espacios, paréntesis, guiones y el signo +.',

            'calle.required'            => 'La calle es obligatoria.',
            'numero_exterior.required'  => 'El número exterior es obligatorio.',
            'colonia.required'          => 'La colonia es obligatoria.',

            'codigo_postal.required' => 'El código postal es obligatorio.',
            'codigo_postal.size'     => 'El código postal debe tener 5 dígitos.',
            'codigo_postal.regex'    => 'El código postal debe contener solo números.',

            'municipio.required' => 'El municipio es obligatorio.',

            'estado.required' => 'El estado es obligatorio.',
            'estado.exists'   => 'El estado seleccionado no es válido.',
            'estado.size'     => 'El estado debe ser una clave de 3 caracteres.',

            'pais.required' => 'El país es obligatorio.',
            'pais.in'       => 'El país debe ser MX.',

            'notas.max'  => 'Las notas no pueden exceder 1000 caracteres.',
            'curp.regex' => 'El CURP no tiene un formato válido.',
        ];
    }

    private function getRfcValidationRules(?int $clienteId): array
    {
        return [
            function ($attribute, $value, $fail) use ($clienteId) {
                $value = strtoupper(trim($value));
                $exists = Cliente::where('rfc', $value)
                    ->when($clienteId, fn($q) => $q->where('id', '!=', $clienteId))
                    ->exists();

                if ($exists) {
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
            function ($attribute, $value, $fail) {
                $tipo = $this->input('tipo_persona');
                $rf = SatRegimenFiscal::query()
                    ->where('clave', strtoupper(trim($value)))
                    ->first();

                if (!$rf) {
                    return; // 'exists' dará el mensaje correspondiente
                }
                if ($tipo === 'fisica' && !$rf->persona_fisica) {
                    $fail('El régimen fiscal no es válido para Persona Física.');
                }
                if ($tipo === 'moral' && !$rf->persona_moral) {
                    $fail('El régimen fiscal no es válido para Persona Moral.');
                }
            },
        ];
    }
}
