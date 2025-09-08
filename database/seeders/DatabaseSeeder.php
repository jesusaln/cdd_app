<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Llama a otros seeders, como RolesAndPermissionsSeeder
        $this->call([
            RolesAndPermissionsSeeder::class,
            AlmacenSeeder::class,
            MarcaSeeder::class,
            CategoriaSeeder::class,
            ServicioSeeder::class,
            ProveedorSeeder::class,
            ClienteSeeder::class,
            ProductoSeeder::class,
            TecnicoSeeder::class,
            CarroSeeder::class,
            CitaSeeder::class,
            CotizacionSeeder::class,
            CotizacionItemSeeder::class,
            SatRegimenesFiscalesSeeder::class,
            SatUsosCfdiSeeder::class,
            SatEstadosSeeder::class,

        ]);
    }
    //hola
}
