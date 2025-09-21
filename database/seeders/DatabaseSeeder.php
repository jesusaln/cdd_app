<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1) Catálogos base, PERO primero los SAT
            RolesAndPermissionsSeeder::class,
            AlmacenSeeder::class,
            MarcaSeeder::class,
            CategoriaSeeder::class,
            ServicioSeeder::class,
            ProveedorSeeder::class,

            // ✅ SAT primero (necesarios para clientes)
            SatRegimenesFiscalesSeeder::class,
            SatUsosCfdiSeeder::class,
            SatEstadosSeeder::class,

            // 2) Ahora sí, dependientes
            ClienteSeeder::class,
            ProductoSeeder::class,
            TecnicoSeeder::class,
            HerramientaSeeder::class,
            EquipoSeeder::class,
            CarroSeeder::class,
            CitaSeeder::class,
            CotizacionSeeder::class,
            CotizacionItemSeeder::class,

            // 3) Datos que referencian usuarios/clientes/etc.
            BitacoraActividadSeeder::class,
        ]);
    }
}
