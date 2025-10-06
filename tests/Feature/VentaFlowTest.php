<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\CuentasPorCobrar;
use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Enums\EstadoVenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VentaFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear datos básicos para las pruebas
        $this->createBasicData();

        // Crear usuario y autenticar
        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $team = \App\Models\Team::create([
            'name' => 'Test Team',
            'user_id' => $user->id,
            'personal_team' => true,
        ]);

        $user->update(['current_team_id' => $team->id]);
        $this->actingAs($user);
    }

    /**
     * Crear datos básicos para pruebas
     */
    private function createBasicData()
    {
        // Crear estado SAT
        \App\Models\SatEstado::create([
            'clave' => 'SON',
            'nombre' => 'Sonora'
        ]);

        // Crear regimen fiscal SAT
        \App\Models\SatRegimenFiscal::create([
            'clave' => '601',
            'descripcion' => 'General de Ley Personas Morales',
            'persona_fisica' => false,
            'persona_moral' => true
        ]);

        // Crear uso CFDI SAT
        \App\Models\SatUsoCfdi::create([
            'clave' => 'G01',
            'descripcion' => 'Adquisición de mercancías',
            'persona_fisica' => true,
            'persona_moral' => true,
            'regimen_fiscal_receptor' => '601'
        ]);

        // Crear almacén
        Almacen::create([
            'nombre' => 'Almacén Principal',
            'estado' => 'activo'
        ]);

        // Crear categoría, marca, proveedor
        $categoria = Categoria::create(['nombre' => 'Electrónicos']);
        $marca = Marca::create(['nombre' => 'Samsung']);
        $proveedor = Proveedor::create([
            'nombre_razon_social' => 'Proveedor Test',
            'email' => 'proveedor@test.com',
            'telefono' => '1234567890',
            'tipo_persona' => 'fisica',
            'rfc' => 'TEST123456789',
            'regimen_fiscal' => '601',
            'uso_cfdi' => 'G01',
            'calle' => 'Calle Test',
            'numero_exterior' => '123',
            'colonia' => 'Colonia Test',
            'codigo_postal' => '12345',
            'municipio' => 'Municipio Test',
            'estado' => 'Estado Test',
            'pais' => 'México'
        ]);

        // Crear producto con stock
        Producto::create([
            'nombre' => 'Monitor LED 24"',
            'codigo' => 'MON-001',
            'codigo_barras' => '1234567890123',
            'categoria_id' => $categoria->id,
            'marca_id' => $marca->id,
            'proveedor_id' => $proveedor->id,
            'almacen_id' => 1,
            'stock' => 10,
            'stock_minimo' => 5,
            'precio_compra' => 800.00,
            'precio_venta' => 1000.00,
            'impuesto' => 16.00,
            'unidad_medida' => 'pieza',
            'tipo_producto' => 'fisico',
            'estado' => 'activo'
        ]);

        // Crear cliente
        Cliente::create([
            'nombre_razon_social' => 'Cliente Test',
            'email' => 'cliente@test.com',
            'telefono' => '0987654321',
            'tipo_persona' => 'fisica',
            'rfc' => 'TEST123456789',
            'regimen_fiscal' => '601',
            'uso_cfdi' => 'G01',
            'calle' => 'Calle Test',
            'numero_exterior' => '123',
            'colonia' => 'Colonia Test',
            'codigo_postal' => '12345',
            'municipio' => 'Municipio Test',
            'estado' => 'SON',
            'pais' => 'MX'
        ]);
    }

    /**
     * Test flujo completo de venta: creación → validación stock → creación cuenta por cobrar
     */
    public function test_flujo_venta_completo()
    {
        // 1. Verificar que el producto tiene stock disponible
        $producto = Producto::first();
        $this->assertEquals(10, $producto->stock_disponible);
        $this->assertEquals(10, $producto->stock);
        $this->assertEquals(0, $producto->reservado);

        // 2. Crear venta con producto
        $cliente = Cliente::first();
        $ventaData = [
            'cliente_id' => $cliente->id,
            'productos' => [
                [
                    'id' => $producto->id,
                    'tipo' => 'producto',
                    'cantidad' => 2,
                    'precio' => 1000.00,
                    'descuento' => 0
                ]
            ],
            'descuento_general' => 0
        ];

        $response = $this->post(route('ventas.store'), $ventaData);
        $response->assertRedirect(route('ventas.index'));
        $this->assertDatabaseHas('ventas', [
            'cliente_id' => $cliente->id,
            'total' => 2160.00, // 2000 + 160 IVA
            'pagado' => false
        ]);

        // 3. Verificar que se creó la venta
        $venta = Venta::first();
        $this->assertNotNull($venta);
        $this->assertEquals(EstadoVenta::Borrador, $venta->estado);
        $this->assertFalse($venta->pagado);

        // 4. Verificar que se creó cuenta por cobrar automáticamente
        $cuentaPorCobrar = CuentasPorCobrar::where('venta_id', $venta->id)->first();
        $this->assertNotNull($cuentaPorCobrar);
        $this->assertEquals(2160.00, $cuentaPorCobrar->monto_total);
        $this->assertEquals(2160.00, $cuentaPorCobrar->monto_pendiente);
        $this->assertEquals(0, $cuentaPorCobrar->monto_pagado);
        $this->assertEquals('pendiente', $cuentaPorCobrar->estado);

        // 5. Verificar que el stock se mantiene (no se descuenta hasta envío a venta)
        $producto->refresh();
        $this->assertEquals(10, $producto->stock);
        $this->assertEquals(0, $producto->reservado);
    }

    /**
     * Test envío a venta con deducción de inventario
     */
    public function test_envio_a_venta_deduce_inventario()
    {
        // Crear venta primero
        $producto = Producto::first();
        $cliente = Cliente::first();
        $ventaData = [
            'cliente_id' => $cliente->id,
            'productos' => [
                [
                    'id' => $producto->id,
                    'tipo' => 'producto',
                    'cantidad' => 3,
                    'precio' => 1000.00,
                    'descuento' => 0
                ]
            ],
            'descuento_general' => 0
        ];

        $this->post(route('ventas.store'), $ventaData);
        $venta = Venta::first();

        // Enviar a venta (simular desde pedido)
        $response = $this->post(route('pedidos.enviar-a-venta', $venta->id));
        $response->assertJson(['success' => true]);

        // Verificar que el stock se dedujo
        $producto->refresh();
        $this->assertEquals(7, $producto->stock); // 10 - 3

        // Verificar que la venta cambió de estado
        $venta->refresh();
        $this->assertEquals(EstadoVenta::Pendiente, $venta->estado);
    }

    /**
     * Test validación de stock insuficiente
     */
    public function test_validacion_stock_insuficiente()
    {
        $producto = Producto::first();

        // Intentar vender más de lo disponible
        $cliente = Cliente::first();
        $ventaData = [
            'cliente_id' => $cliente->id,
            'productos' => [
                [
                    'id' => $producto->id,
                    'tipo' => 'producto',
                    'cantidad' => 15, // Más que 10 disponible
                    'precio' => 1000.00,
                    'descuento' => 0
                ]
            ],
            'descuento_general' => 0
        ];

        $response = $this->post(route('pedidos.enviar-a-venta', 1), $ventaData);
        $response->assertJson([
            'success' => false,
            'error' => 'Stock insuficiente para \'Monitor LED 24"\'. Disponible: 10, Solicitado: 15'
        ]);
    }

    /**
     * Test pago completo actualiza estados
     */
    public function test_pago_completo_actualiza_estados()
    {
        // Crear venta
        $producto = Producto::first();
        $cliente = Cliente::first();
        $ventaData = [
            'cliente_id' => $cliente->id,
            'productos' => [
                [
                    'id' => $producto->id,
                    'tipo' => 'producto',
                    'cantidad' => 1,
                    'precio' => 1000.00,
                    'descuento' => 0
                ]
            ],
            'descuento_general' => 0
        ];

        $this->post(route('ventas.store'), $ventaData);
        $venta = Venta::first();
        $cuentaPorCobrar = CuentasPorCobrar::where('venta_id', $venta->id)->first();

        // Registrar pago completo
        $pagoData = [
            'monto' => 1160.00, // Total con IVA
            'notas' => 'Pago completo'
        ];

        $response = $this->post(route('cuentas-por-cobrar.registrar-pago', $cuentaPorCobrar->id), $pagoData);
        $response->assertRedirect();

        // Verificar estados
        $cuentaPorCobrar->refresh();
        $venta->refresh();

        $this->assertEquals(0, $cuentaPorCobrar->monto_pendiente);
        $this->assertEquals(1160.00, $cuentaPorCobrar->monto_pagado);
        $this->assertEquals('pagado', $cuentaPorCobrar->estado);
        $this->assertTrue($venta->pagado);
        $this->assertEquals(EstadoVenta::Aprobada, $venta->estado);
    }

    /**
     * Test reserva de inventario en pedidos
     */
    public function test_reserva_inventario_pedidos()
    {
        $producto = Producto::first();

        // Crear pedido (simular)
        $cliente = Cliente::first();
        $pedidoData = [
            'cliente_id' => $cliente->id,
            'productos' => [
                [
                    'id' => $producto->id,
                    'tipo' => 'producto',
                    'cantidad' => 2,
                    'precio' => 1000.00,
                    'descuento' => 0
                ]
            ],
            'descuento_general' => 0
        ];

        $this->post(route('pedidos.store'), $pedidoData);
        $pedido = \App\Models\Pedido::first();

        // Confirmar pedido (reserva inventario)
        $response = $this->post(route('pedidos.confirmar', $pedido->id));
        $response->assertJson(['success' => true]);

        // Verificar reserva
        $producto->refresh();
        $this->assertEquals(2, $producto->reservado);
        $this->assertEquals(8, $producto->stock_disponible); // 10 - 2

        // Enviar a venta (consume reserva)
        $response = $this->post(route('pedidos.enviar-a-venta', $pedido->id));
        $response->assertJson(['success' => true]);

        // Verificar que reserva se consumió
        $producto->refresh();
        $this->assertEquals(0, $producto->reservado);
        $this->assertEquals(8, $producto->stock); // 10 - 2
    }
}
