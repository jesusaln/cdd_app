<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CarroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $combustibles = ['Gasolina', 'Diésel', 'Eléctrico', 'Híbrido'];

        for ($i = 0; $i < 10; $i++) {
            DB::table('carros')->insert([
                'marca' => $faker->randomElement(['Toyota', 'Honda', 'Ford', 'Chevrolet', 'Tesla']),
                'modelo' => $faker->word,
                'anio' => $faker->numberBetween(2000, 2025),
                'color' => $faker->safeColorName,
                'precio' => $faker->randomFloat(2, 100000, 1000000),
                'numero_serie' => strtoupper($faker->bothify('??##########')),
                'combustible' => $faker->randomElement($combustibles),
                'kilometraje' => $faker->numberBetween(0, 200000),
                'placa' => strtoupper($faker->bothify('???-###')),
                'foto' => null, // o puedes usar $faker->imageUrl()
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
