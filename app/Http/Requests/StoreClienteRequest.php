<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Asegúrate de que el usuario esté autorizado para realizar esta acción
    }

    public function rules()
    {
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
                function ($attribute, $value, $fail) {
                    if ($value !== 'XAXX010101000' && \App\Models\Cliente::where('rfc', $value)->exists()) {
                        $fail('El RFC ya está registrado.');
                    }
                    if ($this->tipo_persona === 'fisica' && strlen($value) !== 13) {
                        $fail('El RFC debe tener exactamente 13 caracteres para persona física.');
                    }
                    if ($this->tipo_persona === 'moral' && strlen($value) !== 12) {
                        $fail('El RFC debe tener exactamente 12 caracteres para persona moral.');
                    }
                },
            ],
            'regimen_fiscal' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($regimenesFisicas, $regimenesMorales) {
                    if ($this->tipo_persona === 'fisica' && !in_array($value, $regimenesFisicas)) {
                        $fail('El régimen fiscal no es válido para persona física.');
                    }
                    if ($this->tipo_persona === 'moral' && !in_array($value, $regimenesMorales)) {
                        $fail('El régimen fiscal no es válido para persona moral.');
                    }
                },
            ],
            'uso_cfdi' => 'required|string|in:G01,G02,G03,I01,I02,I03,I04,I05,I06,I07,I08,D01,D02,D03,D04,D05,D06,D07,D08,D09,D10,S01,CP01,CN01',
            'email' => 'required|email|max:255|unique:clientes,email',
            'telefono' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'calle' => 'required|string|max:255',
            'numero_exterior' => 'required|string|max:20',
            'numero_interior' => 'nullable|string|max:20',
            'colonia' => 'required|string|max:255',
            'codigo_postal' => 'required|size:5',
            'municipio' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'pais' => 'required|string|max:255',

            'activo' => 'nullable|boolean',
            'notas' => 'nullable|string',
            'acepta_marketing' => 'nullable|boolean',

        ]; // Ajusta las reglas del RFC según el tipo de persona
        // (La validación de longitud del RFC se realiza ahora en la función de validación personalizada del campo 'rfc')

        return $rules;
    }

    public function messages()
    {
        return [
            'nombre_razon_social.required' => 'El nombre o razón social es obligatorio.',
            'tipo_persona.required' => 'El tipo de persona es obligatorio.',
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.regex' => 'El RFC no tiene un formato válido.',
            'rfc.unique' => 'El RFC ya está registrado.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'El email ya está registrado.',
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
}


//'requiere_factura' => 'required|boolean',
