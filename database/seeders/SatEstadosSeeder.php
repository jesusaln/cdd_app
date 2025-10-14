<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatEstadosSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['clave' => 'AGU', 'nombre' => 'Aguascalientes'],
            ['clave' => 'BCN', 'nombre' => 'Baja California'],
            ['clave' => 'BCS', 'nombre' => 'Baja California Sur'],
            ['clave' => 'CAM', 'nombre' => 'Campeche'],
            ['clave' => 'CHH', 'nombre' => 'Chihuahua'],
            ['clave' => 'CHP', 'nombre' => 'Chiapas'],
            ['clave' => 'CMX', 'nombre' => 'Ciudad de México'],
            ['clave' => 'COA', 'nombre' => 'Coahuila'],
            ['clave' => 'COL', 'nombre' => 'Colima'],
            ['clave' => 'DUR', 'nombre' => 'Durango'],
            ['clave' => 'GRO', 'nombre' => 'Guerrero'],
            ['clave' => 'GUA', 'nombre' => 'Guanajuato'],
            ['clave' => 'HID', 'nombre' => 'Hidalgo'],
            ['clave' => 'JAL', 'nombre' => 'Jalisco'],
            ['clave' => 'MEX', 'nombre' => 'Estado de México'],
            ['clave' => 'MIC', 'nombre' => 'Michoacán'],
            ['clave' => 'MOR', 'nombre' => 'Morelos'],
            ['clave' => 'NAY', 'nombre' => 'Nayarit'],
            ['clave' => 'NLE', 'nombre' => 'Nuevo León'],
            ['clave' => 'OAX', 'nombre' => 'Oaxaca'],
            ['clave' => 'PUE', 'nombre' => 'Puebla'],
            ['clave' => 'QUE', 'nombre' => 'Querétaro'],
            ['clave' => 'ROO', 'nombre' => 'Quintana Roo'],
            ['clave' => 'SIN', 'nombre' => 'Sinaloa'],
            ['clave' => 'SLP', 'nombre' => 'San Luis Potosí'],
            ['clave' => 'SON', 'nombre' => 'Sonora'],
            ['clave' => 'TAB', 'nombre' => 'Tabasco'],
            ['clave' => 'TAM', 'nombre' => 'Tamaulipas'],
            ['clave' => 'TLA', 'nombre' => 'Tlaxcala'],
            ['clave' => 'VER', 'nombre' => 'Veracruz'],
            ['clave' => 'YUC', 'nombre' => 'Yucatán'],
            ['clave' => 'ZAC', 'nombre' => 'Zacatecas'],
        ];

        DB::table('sat_estados')->upsert($rows, ['clave']);
    }
}
