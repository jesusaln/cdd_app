<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\Almacen;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar si ya existen productos
        $productosExistentes = Producto::where('estado', 'activo')->get();

        if ($productosExistentes->count() >= 10) {
            $this->command->info('Ya existen productos activos suficientes. ProductoSeeder completado.');
            return;
        }

        // Obtener relaciones necesarias
        $categoriaDefault = Categoria::firstOrCreate(
            ['nombre' => 'Electrónicos'],
            ['descripcion' => 'Productos electrónicos y de tecnología']
        );

        $categoriaHerramientas = Categoria::firstOrCreate(
            ['nombre' => 'Herramientas'],
            ['descripcion' => 'Herramientas y equipo industrial']
        );

        $categoriaOficina = Categoria::firstOrCreate(
            ['nombre' => 'Oficina'],
            ['descripcion' => 'Suministros y equipo de oficina']
        );

        $marcaDefault = Marca::firstOrCreate(
            ['nombre' => 'Genérica'],
            ['descripcion' => 'Marca genérica para productos']
        );

        $proveedorDefault = Proveedor::firstOrCreate(
            ['rfc' => 'XAXX010101000'],
            [
                'nombre_razon_social' => 'SUMINISTROS INDUSTRIALES S.A. DE C.V.',
                'tipo_persona' => 'Moral',
                'regimen_fiscal' => '616'
            ]
        );

        $almacenDefault = Almacen::firstOrCreate(
            ['nombre' => 'Almacén Principal'],
            ['ubicacion' => 'Sucursal Central']
        );

        // Obtener unidades de medida reales de productos existentes
        $unidadesReales = Producto::where('estado', 'activo')
            ->whereNotNull('unidad_medida')
            ->distinct()
            ->pluck('unidad_medida')
            ->filter()
            ->values()
            ->toArray();

        // Si no hay unidades reales, usar unidades profesionales por defecto
        if (empty($unidadesReales)) {
            $unidadesReales = ['Pieza', 'Caja', 'Paquete', 'Juego', 'Resma', 'Kilogramo', 'Litro', 'Metro'];
        }

        // Productos reales profesionales usando unidades reales
        $productosReales = [
            [
                'nombre' => 'Monitor LED 24" Full HD',
                'descripcion' => 'Monitor LED de 24 pulgadas resolución Full HD 1920x1080, ideal para oficina y diseño gráfico',
                'codigo' => 'MON-001',
                'codigo_barras' => '7501234567890',
                'categoria_id' => $categoriaDefault->id,
                'marca_id' => $marcaDefault->id,
                'proveedor_id' => $proveedorDefault->id,
                'almacen_id' => $almacenDefault->id,
                'stock' => 0,
                'stock_minimo' => 5,
                'precio_compra' => 2500.00,
                'precio_venta' => 3500.00,
                'impuesto' => 16.00,
                'unidad_medida' => 'Pieza',
                'tipo_producto' => 'fisico',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Teclado Mecánico USB',
                'descripcion' => 'Teclado mecánico con switches azules, retroiluminación LED y conexión USB',
                'codigo' => 'TEC-001',
                'codigo_barras' => '7501234567891',
                'categoria_id' => $categoriaDefault->id,
                'marca_id' => $marcaDefault->id,
                'proveedor_id' => $proveedorDefault->id,
                'almacen_id' => $almacenDefault->id,
                'stock' => 0,
                'stock_minimo' => 10,
                'precio_compra' => 800.00,
                'precio_venta' => 1200.00,
                'impuesto' => 16.00,
                'unidad_medida' => 'Pieza',
                'tipo_producto' => 'fisico',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Mouse Óptico Inalámbrico',
                'descripcion' => 'Mouse óptico inalámbrico con batería recargable y precisión de 1600 DPI',
                'codigo' => 'MOU-001',
                'codigo_barras' => '7501234567892',
                'categoria_id' => $categoriaDefault->id,
                'marca_id' => $marcaDefault->id,
                'proveedor_id' => $proveedorDefault->id,
                'almacen_id' => $almacenDefault->id,
                'stock' => 0,
                'stock_minimo' => 15,
                'precio_compra' => 250.00,
                'precio_venta' => 400.00,
                'impuesto' => 16.00,
                'unidad_medida' => 'Pieza',
                'tipo_producto' => 'fisico',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Juego de Destornilladores 6 piezas',
                'descripcion' => 'Juego profesional de destornilladores con mangos ergonómicos y puntas magnéticas',
                'codigo' => 'DES-001',
                'codigo_barras' => '7501234567893',
                'categoria_id' => $categoriaHerramientas->id,
                'marca_id' => $marcaDefault->id,
                'proveedor_id' => $proveedorDefault->id,
                'almacen_id' => $almacenDefault->id,
                'stock' => 0,
                'stock_minimo' => 8,
                'precio_compra' => 450.00,
                'precio_venta' => 650.00,
                'impuesto' => 16.00,
                'unidad_medida' => 'Juego',
                'tipo_producto' => 'fisico',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Pinzas de Corte Diagonal',
                'descripcion' => 'Pinzas de corte diagonal profesionales de 6 pulgadas, fabricadas en acero al carbono',
                'codigo' => 'PIN-001',
                'codigo_barras' => '7501234567894',
                'categoria_id' => $categoriaHerramientas->id,
                'marca_id' => $marcaDefault->id,
                'proveedor_id' => $proveedorDefault->id,
                'almacen_id' => $almacenDefault->id,
                'stock' => 0,
                'stock_minimo' => 10,
                'precio_compra' => 180.00,
                'precio_venta' => 280.00,
                'impuesto' => 16.00,
                'unidad_medida' => 'Pieza',
                'tipo_producto' => 'fisico',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Cinta Métrica 5 Metros',
                'descripcion' => 'Cinta métrica profesional de 5 metros con carcasa metálica y freno automático',
                'codigo' => 'MET-001',
                'codigo_barras' => '7501234567895',
                'categoria_id' => $categoriaHerramientas->id,
                'marca_id' => $marcaDefault->id,
                'proveedor_id' => $proveedorDefault->id,
                'almacen_id' => $almacenDefault->id,
                'stock' => 0,
                'stock_minimo' => 12,
                'precio_compra' => 120.00,
                'precio_venta' => 200.00,
                'impuesto' => 16.00,
                'unidad_medida' => 'Pieza',
                'tipo_producto' => 'fisico',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Resma de Papel Bond A4',
                'descripcion' => 'Resma de papel bond tamaño carta 75gr, 500 hojas, ideal para impresión y oficina',
                'codigo' => 'PAP-001',
                'codigo_barras' => '7501234567896',
                'categoria_id' => $categoriaOficina->id,
                'marca_id' => $marcaDefault->id,
                'proveedor_id' => $proveedorDefault->id,
                'almacen_id' => $almacenDefault->id,
                'stock' => 0,
                'stock_minimo' => 20,
                'precio_compra' => 85.00,
                'precio_venta' => 130.00,
                'impuesto' => 0.00,
                'unidad_medida' => 'Resma',
                'tipo_producto' => 'fisico',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Bolígrafo Punto Fino Azul',
                'descripcion' => 'Caja de bolígrafos de punto fino tinta azul, 12 piezas, escritura suave y precisa',
                'codigo' => 'BOL-001',
                'codigo_barras' => '7501234567897',
                'categoria_id' => $categoriaOficina->id,
                'marca_id' => $marcaDefault->id,
                'proveedor_id' => $proveedorDefault->id,
                'almacen_id' => $almacenDefault->id,
                'stock' => 0,
                'stock_minimo' => 50,
                'precio_compra' => 45.00,
                'precio_venta' => 75.00,
                'impuesto' => 0.00,
                'unidad_medida' => 'Caja',
                'tipo_producto' => 'fisico',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Archivero Metálico 3 Gavetas',
                'descripcion' => 'Archivero metálico de 3 gavetas, sistema de rieles telescópicos, cerradura incluida',
                'codigo' => 'ARC-001',
                'codigo_barras' => '7501234567898',
                'categoria_id' => $categoriaOficina->id,
                'marca_id' => $marcaDefault->id,
                'proveedor_id' => $proveedorDefault->id,
                'almacen_id' => $almacenDefault->id,
                'stock' => 0,
                'stock_minimo' => 3,
                'precio_compra' => 1800.00,
                'precio_venta' => 2500.00,
                'impuesto' => 16.00,
                'unidad_medida' => 'Pieza',
                'tipo_producto' => 'fisico',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Calculadora Científica',
                'descripcion' => 'Calculadora científica con 240 funciones, pantalla LCD de 2 líneas, ideal para estudiantes',
                'codigo' => 'CAL-001',
                'codigo_barras' => '7501234567899',
                'categoria_id' => $categoriaOficina->id,
                'marca_id' => $marcaDefault->id,
                'proveedor_id' => $proveedorDefault->id,
                'almacen_id' => $almacenDefault->id,
                'stock' => 0,
                'stock_minimo' => 8,
                'precio_compra' => 320.00,
                'precio_venta' => 480.00,
                'impuesto' => 16.00,
                'unidad_medida' => 'Pieza',
                'tipo_producto' => 'fisico',
                'estado' => 'activo'
            ]
        ];

        // Crear productos reales profesionales
        foreach ($productosReales as $producto) {
            Producto::firstOrCreate(
                ['codigo' => $producto['codigo']],
                $producto
            );
        }

        $this->command->info('ProductoSeeder completado con productos profesionales reales. Total productos activos: ' . Producto::where('estado', 'activo')->count());
    }
}
