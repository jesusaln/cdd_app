<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = \App\Models\Cliente::class;

    public function definition()
    {
        return [
            'nombre_razon_social' => $this->faker->company,
            'rfc' => strtoupper($this->faker->bothify('????######???')),
            'regimen_fiscal' => 'General de Ley Personas Morales',
            'uso_cfdi' => 'G03',
            'email' => $this->faker->unique()->safeEmail,
            'telefono' => $this->faker->phoneNumber,
            'calle' => $this->faker->streetName,
            'numero_exterior' => $this->faker->buildingNumber,
            'numero_interior' => null,
            'colonia' => $this->faker->streetSuffix,
            'codigo_postal' => $this->faker->postcode,
            'municipio' => $this->faker->city,
            'estado' => $this->faker->state,
            'pais' => 'México',
            'tipo_persona' => 'Moral',

            'activo' => true,
            'notas' => null,

        ];
    }
}
