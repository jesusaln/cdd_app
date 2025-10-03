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
            UnidadMedidaSeeder::class,
            MarcaSeeder::class,
            CategoriaSeeder::class,
            ProductoSeeder::class,
            ServicioSeeder::class,

            // ✅ SAT primero (necesarios para clientes)
            SatRegimenesFiscalesSeeder::class,
            SatUsosCfdiSeeder::class,
            SatEstadosSeeder::class,

            // 2) Proveedores (necesarios para compras)
            ProveedorSeeder::class,

            // 3) Compras (crean productos automáticamente)
            CompraSeeder::class,

            // 4) Clientes (para cotizaciones y ventas)
            ClienteSeeder::class,

            // 5) Técnicos (para asignaciones)
            TecnicoSeeder::class,

            // 6) Cotizaciones y pedidos (basados en productos existentes)
            CotizacionSeeder::class,
            CotizacionItemSeeder::class,

            // 7) Órdenes de compra (pueden crear compras)
            OrdenCompraSeeder::class,

            // 8) Herramientas y equipos
            HerramientaSeeder::class,
            EquipoSeeder::class,

            // 9) Carros y mantenimientos
            CarroSeeder::class,
            CitaSeeder::class,

            // 10) Ventas (basadas en productos existentes)
            VentaSeeder::class,

            // 11) Datos que referencian usuarios/clientes/etc.
            BitacoraActividadSeeder::class,
        ]);
    }
}
