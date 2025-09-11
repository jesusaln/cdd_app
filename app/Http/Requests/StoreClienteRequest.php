<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
{
    public function rules()
    {
        return [
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
                    $tipoPersona = $this->input('tipo_persona');
                    if ($tipoPersona === 'fisica' && strlen($value) !== 13) {
                        $fail('El RFC debe tener exactamente 13 caracteres para persona física.');
                    }
                    if ($tipoPersona === 'moral' && strlen($value) !== 12) {
                        $fail('El RFC debe tener exactamente 12 caracteres para persona moral.');
                    }
                },
            ],
            'regimen_fiscal' => [
                'required',
                Rule::exists('sat_regimenes_fiscales', 'clave'),
                function ($attribute, $value, $fail) {
                    $tipoPersona = $this->input('tipo_persona');
                    $regimen = \App\Models\SatRegimenFiscal::where('clave', $value)->first();
                    if (!$regimen) {
                        $fail('El régimen fiscal no existe en el catálogo.');
                        return;
                    }
                    if ($tipoPersona === 'fisica' && !$regimen->persona_fisica) {
                        $fail('El régimen fiscal no es válido para persona física.');
                    }
                    if ($tipoPersona === 'moral' && !$regimen->persona_moral) {
                        $fail('El régimen fiscal no es válido para persona moral.');
                    }
                },
            ],
            'uso_cfdi' => [
                'required',
                Rule::exists('sat_usos_cfdi', 'clave'),
                function ($attribute, $value, $fail) {
                    $tipoPersona = $this->input('tipo_persona');
                    $uso = \App\Models\SatUsoCfdi::where('clave', $value)->first();
                    if (!$uso) {
                        $fail('El uso de CFDI no existe en el catálogo.');
                        return;
                    }
                    if ($tipoPersona === 'fisica' && !$uso->persona_fisica) {
                        $fail('El uso de CFDI no es válido para persona física.');
                    }
                    if ($tipoPersona === 'moral' && !$uso->persona_moral) {
                        $fail('El uso de CFDI no es válido para persona moral.');
                    }
                },
            ],
            'email' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'calle' => 'required|string|max:255',
            'numero_exterior' => 'required|string|max:20',
            'numero_interior' => 'nullable|string|max:20',
            'colonia' => 'required|string|max:255',
            'codigo_postal' => 'required|size:5|regex:/^[0-9]{5}$/',
            'municipio' => 'required|string|max:255',
            'estado' => [
                'required',
                Rule::exists('sat_estados', 'clave'),
            ],
            'pais' => 'required|string|size:2',
            'activo' => 'nullable|boolean',
            'notas' => 'nullable|string',
            'acepta_marketing' => 'nullable|boolean',
        ];
    }
}
