<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1) Catálogos base
            RolesAndPermissionsSeeder::class,
            AlmacenSeeder::class,
            MarcaSeeder::class,
            CategoriaSeeder::class,
            ServicioSeeder::class,

            // ✅ SAT primero (necesarios para clientes)
            SatRegimenesFiscalesSeeder::class,
            SatUsosCfdiSeeder::class,
            SatEstadosSeeder::class,

            // 2) Proveedores (necesarios para compras)
            ProveedorSeeder::class,

            // 3) Productos básicos (sin stock inicial)
            ProductoSeeder::class,

            // 4) Compras (agregan stock a productos existentes)
            //CompraSeeder::class,

            // 5) Clientes (para cotizaciones y ventas)
            ClienteSeeder::class,

            // 6) Técnicos (para asignaciones)
            TecnicoSeeder::class,

            // 7) Cotizaciones y pedidos (basados en productos existentes)
            CotizacionSeeder::class,
            CotizacionItemSeeder::class,

            // 8) Órdenes de compra (pueden crear compras)
            OrdenCompraSeeder::class,

            // 9) Herramientas y equipos
            HerramientaSeeder::class,
            EquipoSeeder::class,

            // 10) Carros y mantenimientos
            CarroSeeder::class,
            CitaSeeder::class,

            // 11) Ventas (basadas en productos existentes)
            //VentaSeeder::class,

            // 11) Datos que referencian usuarios/clientes/etc.
            BitacoraActividadSeeder::class,
        ]);
    }
}
