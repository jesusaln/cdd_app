<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajusta con Policies si usas permisos
    }

    public function rules(): array
    {
        // intenta resolver el id desde la ruta resource {cliente}
        $clienteId = $this->route('cliente')?->id ?? $this->route('cliente');

        $regexNombre = "/^[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s\.,&\-\']+$/";
        $estados = [
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
        $usosCfdi = ['G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I05', 'I06', 'I07', 'I08', 'D01', 'D02', 'D03', 'D04', 'D05', 'D06', 'D07', 'D08', 'D09', 'D10', 'S01', 'CP01', 'CN01'];
        $regimenesFisica = ['605', '606', '607', '608', '610', '611', '612', '614', '615', '616', '621', '625', '626'];
        $regimenesMoral  = ['601', '603', '609', '620', '622', '623', '624', '628', '629', '630'];

        return [
            'nombre_razon_social' => ['required', 'string', 'max:255', "regex:$regexNombre"],
            'tipo_persona'        => ['required', Rule::in(['fisica', 'moral'])],
            'rfc'                 => [
                'required',
                'string',
                'max:13',
                'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/',
                Rule::unique('clientes', 'rfc')->ignore($clienteId),
            ],
            'regimen_fiscal'      => [
                'required',
                'string',
                function ($attr, $val, $fail) use ($regimenesFisica, $regimenesMoral) {
                    $tipo = request('tipo_persona');
                    if ($tipo === 'fisica' && !in_array($val, $regimenesFisica)) {
                        $fail('El régimen fiscal no es válido para persona física.');
                    }
                    if ($tipo === 'moral' && !in_array($val, $regimenesMoral)) {
                        $fail('El régimen fiscal no es válido para persona moral.');
                    }
                }
            ],
            'uso_cfdi'            => ['required', 'string', Rule::in($usosCfdi)],
            'email'               => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('clientes', 'email')->ignore($clienteId),
            ],
            'telefono'            => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'calle'               => ['required', 'string', 'max:255'],
            'numero_exterior'     => ['required', 'string', 'max:20'],
            'numero_interior'     => ['nullable', 'string', 'max:20'],
            'colonia'             => ['required', 'string', 'max:255'],
            'codigo_postal'       => ['required', 'string', 'size:5', 'regex:/^[0-9]{5}$/'],
            'municipio'           => ['required', 'string', 'max:255'],
            'estado'              => ['required', 'string', 'max:255', Rule::in($estados)],
            'pais'                => ['required', 'string', 'max:255'],
            'notas'               => ['nullable', 'string', 'max:1000'],
            'activo'              => ['sometimes', 'boolean'],
            'acepta_marketing'    => ['sometimes', 'boolean'],

            // Valida largo del RFC según tipo_persona:
            // Nota: podría moverse a un CustomRule si prefieres.
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $tipo = $this->input('tipo_persona');
            $rfc  = strtoupper((string)$this->input('rfc'));
            if ($tipo === 'fisica' && strlen($rfc) !== 13) {
                $v->errors()->add('rfc', 'El RFC de persona física debe tener 13 caracteres.');
            }
            if ($tipo === 'moral' && strlen($rfc) !== 12) {
                $v->errors()->add('rfc', 'El RFC de persona moral debe tener 12 caracteres.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'nombre_razon_social.required' => 'El nombre o razón social es obligatorio.',
            'nombre_razon_social.regex'    => 'El nombre o razón social contiene caracteres no válidos.',
            'tipo_persona.required'        => 'El tipo de persona es obligatorio.',
            'tipo_persona.in'              => 'El tipo de persona debe ser física o moral.',
            'rfc.required'                 => 'El RFC es obligatorio.',
            'rfc.regex'                    => 'El RFC no tiene un formato válido.',
            'email.required'               => 'El email es obligatorio.',
            'email.email'                  => 'El email debe tener un formato válido.',
            'email.unique'                 => 'El email ya está registrado.',
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
}
