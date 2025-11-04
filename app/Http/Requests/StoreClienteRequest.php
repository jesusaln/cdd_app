<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use App\Models\SatRegimenFiscal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza inputs antes de validar (trim / mayúsculas donde aplique)
     */
    protected function prepareForValidation(): void
    {
        $requiereFactura = $this->input('requiere_factura', false);

        // Si no envían país, por defecto MX para permitir clientes mínimos sin dirección
        $paisInput = strtoupper(trim((string) $this->input('pais')));
        if ($paisInput === '' || $paisInput === null) {
            $paisInput = 'MX';
        }

        $normalized = [
            'nombre_razon_social' => trim((string) $this->input('nombre_razon_social')),
            'calle'               => trim((string) $this->input('calle')),
            'numero_exterior'     => trim((string) $this->input('numero_exterior')),
            'numero_interior'     => trim((string) $this->input('numero_interior')),
            'colonia'             => trim((string) $this->input('colonia')),
            'codigo_postal'       => trim((string) $this->input('codigo_postal')),
            'municipio'           => trim((string) $this->input('municipio')),
            'estado'              => strtoupper(trim((string) $this->input('estado'))),
            'pais'                => $paisInput,
        ];

        // Solo normalizar campos fiscales si requiere factura
        if ($requiereFactura) {
            $normalized = array_merge($normalized, [
                'tipo_persona'        => strtolower(trim((string) $this->input('tipo_persona'))),
                'rfc'                 => strtoupper(trim((string) $this->input('rfc'))),
                'regimen_fiscal'      => strtoupper(trim((string) $this->input('regimen_fiscal'))),
                'uso_cfdi'            => strtoupper(trim((string) $this->input('uso_cfdi'))),
                'email'               => trim((string) $this->input('email')),
                'telefono'            => trim((string) $this->input('telefono')),
            ]);
        } else {
            // Para clientes sin factura, hacer email y teléfono opcionales
            $normalized = array_merge($normalized, [
                'email'    => trim((string) $this->input('email')),
                'telefono' => trim((string) $this->input('telefono')),
            ]);
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $requiereFactura = $this->input('requiere_factura', false);
        $mostrarDireccion = $this->input('mostrar_direccion', false);

        $rules = [
            'nombre_razon_social' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜ\s\.,&\-\']+$/',
            ],

            'calle' => [$mostrarDireccion ? 'required' : 'nullable', 'string', 'max:255'],
            'numero_exterior' => [$mostrarDireccion ? 'required' : 'nullable', 'string', 'max:20'],
            'numero_interior' => ['nullable', 'string', 'max:20'],
            'colonia' => [$mostrarDireccion ? 'required' : 'nullable', 'string', 'max:255'],

            'codigo_postal' => [
                $mostrarDireccion ? 'required' : 'nullable',
                'string',
                'size:5',
                'regex:/^[0-9]{5}$/',
            ],

            'municipio' => [$mostrarDireccion ? 'required' : 'nullable', 'string', 'max:255'],

            // Estado: hacer opcional para clientes extranjeros
            'estado' => [
                'nullable',
                'string',
                'size:3',
                Rule::exists('sat_estados', 'clave'),
            ],

            'pais' => ['required', 'string', Rule::in(['MX'])],

            'notas' => ['nullable', 'string', 'max:1000'],

            'activo' => ['boolean'],

            'tipo_identificacion' => ['nullable', 'string', 'max:20'],
            'identificacion' => ['nullable', 'string', 'max:50'],

            // Facturapi (opcionales)
            'facturapi_customer_id' => ['nullable', 'string', 'max:255'],
            'cfdi_default_use'      => ['nullable', 'string', 'size:3', Rule::exists('sat_usos_cfdi', 'clave')],
            'payment_form_default'  => ['nullable', 'string', 'size:2'],
        ];

        // Solo agregar reglas fiscales si requiere factura
        if ($requiereFactura) {
            $rules = array_merge($rules, [
                'tipo_persona' => ['required', Rule::in(['fisica', 'moral'])],

                'rfc' => array_merge($this->getRfcValidationRules(), [
                    'required',
                    'string',
                    'max:13',
                    'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/',
                ]),

                // Clave SAT (3)
                'regimen_fiscal' => array_merge($this->getRegimenFiscalValidationRules(), [
                    'required',
                    'string',
                    'size:3',
                    Rule::exists('sat_regimenes_fiscales', 'clave'),
                ]),

                // Clave SAT (3)
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
                    'unique:clientes,email',
                ],

                'telefono' => [
                    'required',
                    'string',
                    'size:10',
                    'regex:/^[0-9]{10}$/',
                ],

                'curp' => [
                    'nullable',
                    'string',
                    'size:18',
                    'regex:/^[A-Z][AEIOU][A-Z]{2}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/',
                ],
            ]);
        } else {
            // Si no requiere factura, hacer email y teléfono opcionales
            $rules = array_merge($rules, [
                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                    'unique:clientes,email',
                ],

                'telefono' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[0-9+\-\s()]+$/',
                ],
            ]);
        }

        return $rules;
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

            'calle.required'           => 'La calle es obligatoria cuando se agrega información de dirección.',
            'numero_exterior.required' => 'El número exterior es obligatorio cuando se agrega información de dirección.',
            'colonia.required'         => 'La colonia es obligatoria cuando se agrega información de dirección.',

            'codigo_postal.required' => 'El código postal es obligatorio cuando se agrega información de dirección.',
            'codigo_postal.size'     => 'El código postal debe tener 5 dígitos.',
            'codigo_postal.regex'    => 'El código postal debe contener solo números.',

            'municipio.required' => 'El municipio es obligatorio cuando se agrega información de dirección.',

            'estado.required' => 'El estado es obligatorio.',
            'estado.size'     => 'El estado debe ser una clave de 3 caracteres.',
            'estado.exists'   => 'El estado seleccionado no es válido.', // ← ajustado

            'pais.required' => 'El país es obligatorio.',
            'pais.in'       => 'El país debe ser MX.',

            'notas.max'     => 'Las notas no pueden exceder 1000 caracteres.',

            'curp.regex'    => 'El CURP no tiene un formato válido.',
        ];
    }

    /**
     * RFC único (y caso especial XAXX)
     */
    private function getRfcValidationRules(): array
    {
        return [
            function ($attribute, $value, $fail) {
                $value = strtoupper(trim((string) $value));
                if (Cliente::where('rfc', $value)->exists()) {
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
     * Compatibilidad de régimen fiscal con tipo_persona
     */
    private function getRegimenFiscalValidationRules(): array
    {
        return [
            function ($attribute, $value, $fail) {
                $tipo = strtolower((string) $this->input('tipo_persona'));   // 'fisica' | 'moral'
                $clave = strtoupper(trim((string) $value));                  // p. ej. '626', '601'

                // Buscar por CLAVE, no por id
                $rf = SatRegimenFiscal::where('clave', $clave)->first();
                if (!$rf) {
                    return; // la regla exists de arriba marcará el error
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
