<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\OrdenCompra;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Compra;
use App\Models\CompraItem;
use App\Mail\OrdenCompraEnviada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class OrdenCompraTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $proveedor;
    protected $producto1;
    protected $producto2;
    protected $categoria;
    protected $marca;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Crear datos de prueba básicos sin dependencias complejas
        $this->categoria = Categoria::create([
            'nombre' => 'Categoria Test',
            'descripcion' => 'Descripción categoria test',
            'activo' => true,
        ]);

        $this->marca = Marca::create([
            'nombre' => 'Marca Test',
            'descripcion' => 'Descripción marca test',
            'activo' => true,
        ]);

        $this->proveedor = Proveedor::create([
            'nombre_razon_social' => 'Proveedor Test S.A.',
            'tipo_persona' => 'moral',
            'rfc' => 'TES123456789',
            'regimen_fiscal' => '601',
            'uso_cfdi' => 'G01',
            'email' => 'proveedor@test.com',
            'telefono' => '555-123-4567',
            'calle' => 'Calle Test',
            'numero_exterior' => '123',
            'colonia' => 'Colonia Test',
            'codigo_postal' => '12345',
            'municipio' => 'Municipio Test',
            'estado' => 'Estado Test',
            'pais' => 'México',
            'activo' => true,
        ]);

        // Crear productos con categoria y marca
        $this->producto1 = Producto::create([
            'nombre' => 'Producto Test 1',
            'descripcion' => 'Descripción del producto test 1',
            'codigo' => 'TEST001',
            'codigo_barras' => '1234567890123',
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'proveedor_id' => $this->proveedor->id,
            'almacen_id' => null,
            'stock' => 50,
            'stock_minimo' => 10,
            'precio_compra' => 100.00,
            'precio_venta' => 150.00,
            'impuesto' => 16.00,
            'unidad_medida' => 'pieza',
            'tipo_producto' => 'fisico',
            'estado' => 'activo',
        ]);

        $this->producto2 = Producto::create([
            'nombre' => 'Producto Test 2',
            'descripcion' => 'Descripción del producto test 2',
            'codigo' => 'TEST002',
            'codigo_barras' => '9876543210987',
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'proveedor_id' => $this->proveedor->id,
            'almacen_id' => null,
            'stock' => 30,
            'stock_minimo' => 5,
            'precio_compra' => 200.00,
            'precio_venta' => 300.00,
            'impuesto' => 16.00,
            'unidad_medida' => 'pieza',
            'tipo_producto' => 'fisico',
            'estado' => 'activo',
        ]);
    }

    /** @test */
    public function puede_crear_orden_de_compra_basica()
    {
        $ordenData = [
            'fecha_orden' => now()->format('Y-m-d'),
            'fecha_entrega_esperada' => now()->addDays(7)->format('Y-m-d'),
            'prioridad' => 'media',
            'proveedor_id' => $this->proveedor->id,
            'direccion_entrega' => 'Dirección de entrega de prueba',
            'terminos_pago' => '30_dias',
            'metodo_pago' => 'transferencia',
            'subtotal' => 500.00,
            'descuento_items' => 0.00,
            'descuento_general' => 0.00,
            'iva' => 80.00, // Nota: Calculado dinámicamente en producción
            'total' => 580.00,
            'observaciones' => 'Orden de prueba',
            'items' => [
                [
                    'id' => $this->producto1->id,
                    'tipo' => 'producto',
                    'cantidad' => 2,
                    'precio' => 100.00,
                    'descuento' => 0
                ],
                [
                    'id' => $this->producto2->id,
                    'tipo' => 'producto',
                    'cantidad' => 1,
                    'precio' => 200.00,
                    'descuento' => 0
                ]
            ]
        ];

        $response = $this->post(route('ordenescompra.store'), $ordenData);

        $response->assertRedirect(route('ordenescompra.index'));
        $this->assertDatabaseHas('orden_compras', [
            'proveedor_id' => $this->proveedor->id,
            'total' => 580.00,
            'estado' => 'pendiente'
        ]);

        $orden = OrdenCompra::where('proveedor_id', $this->proveedor->id)->first();
        $this->assertNotNull($orden);
        $this->assertCount(2, $orden->productos);
    }

    /** @test */
    public function valida_datos_requeridos_al_crear_orden()
    {
        $response = $this->post(route('ordenescompra.store'), []);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'fecha_orden',
            'prioridad',
            'proveedor_id',
            'terminos_pago',
            'metodo_pago',
            'subtotal',
            'descuento_items',
            'descuento_general',
            'iva',
            'total',
            'items'
        ]);
    }

    /** @test */
    public function puede_enviar_orden_al_proveedor()
    {
        // Crear orden
        $orden = OrdenCompra::factory()->create([
            'proveedor_id' => $this->proveedor->id,
            'estado' => 'pendiente'
        ]);

        // Agregar productos
        $orden->productos()->attach($this->producto1->id, [
            'cantidad' => 5,
            'precio' => 100.00,
            'descuento' => 0,
            'unidad_medida' => 'pieza'
        ]);

        // Mock del envío de correo
        Mail::shouldReceive('to->send')
            ->once()
            ->andReturn(true);

        $response = $this->post(route('ordenescompra.enviar-compra', $orden->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('orden_compras', [
            'id' => $orden->id,
            'estado' => 'enviado_a_proveedor'
        ]);

        $orden->refresh();
        $this->assertTrue(str_contains($orden->observaciones, 'ORDEN ENVIADA AL PROVEEDOR'));
    }

    /** @test */
    public function puede_recibir_mercancia_de_orden_enviada()
    {
        // Crear orden enviada
        $orden = OrdenCompra::factory()->create([
            'proveedor_id' => $this->proveedor->id,
            'estado' => 'enviado_a_proveedor',
            'total' => 1000.00
        ]);

        // Agregar productos
        $orden->productos()->attach($this->producto1->id, [
            'cantidad' => 10,
            'precio' => 100.00,
            'descuento' => 0,
            'unidad_medida' => 'pieza'
        ]);

        $response = $this->post(route('ordenescompra.recibir-mercancia', $orden->id));

        $response->assertRedirect(route('compras.index'));

        // Verificar que se creó la compra
        $this->assertDatabaseHas('compras', [
            'proveedor_id' => $this->proveedor->id,
            'orden_compra_id' => $orden->id,
            'estado' => 'Procesada'
        ]);

        // Verificar que se actualizó el stock
        $this->producto1->refresh();
        $this->assertEquals(60, $this->producto1->stock); // 50 + 10

        // Verificar que cambió el estado de la orden
        $orden->refresh();
        $this->assertEquals('convertida', $orden->estado);
        $this->assertNotNull($orden->fecha_recepcion);
    }

    /** @test */
    public function puede_cancelar_orden_pendiente()
    {
        $orden = OrdenCompra::factory()->create([
            'proveedor_id' => $this->proveedor->id,
            'estado' => 'pendiente'
        ]);

        $response = $this->post(route('ordenescompra.cancelar', $orden->id));

        $response->assertJson([
            'success' => true,
            'message' => 'Orden de compra cancelada exitosamente'
        ]);

        $orden->refresh();
        $this->assertEquals('cancelada', $orden->estado);
        $this->assertTrue(str_contains($orden->observaciones, 'ORDEN CANCELADA'));
    }

    /** @test */
    public function puede_cancelar_orden_convertida_revirtiendo_stock()
    {
        // Crear orden convertida (con mercancía ya recibida)
        $orden = OrdenCompra::factory()->create([
            'proveedor_id' => $this->proveedor->id,
            'estado' => 'convertida'
        ]);

        // Agregar productos (simulando que ya se recibió mercancía)
        $orden->productos()->attach($this->producto1->id, [
            'cantidad' => 5,
            'precio' => 100.00,
            'descuento' => 0,
            'unidad_medida' => 'pieza'
        ]);

        // Simular que el stock ya se incrementó
        $this->producto1->increment('stock', 5);
        $this->assertEquals(55, $this->producto1->fresh()->stock);

        $response = $this->post(route('ordenescompra.cancelar', $orden->id));

        $response->assertJson([
            'success' => true,
            'message' => 'Orden de compra cancelada exitosamente'
        ]);

        // Verificar que se revirtió el stock
        $this->producto1->refresh();
        $this->assertEquals(50, $this->producto1->stock); // 55 - 5

        $orden->refresh();
        $this->assertEquals('cancelada', $orden->estado);
    }

    /** @test */
    public function puede_cambiar_estado_de_orden()
    {
        $orden = OrdenCompra::factory()->create([
            'estado' => 'pendiente'
        ]);

        $response = $this->post(route('ordenescompra.cambiar-estado', $orden->id), [
            'estado' => 'enviado_a_proveedor'
        ]);

        $response->assertJson([
            'success' => true,
            'estado' => 'enviado_a_proveedor'
        ]);

        $orden->refresh();
        $this->assertEquals('enviado_a_proveedor', $orden->estado);
    }

    /** @test */
    public function valida_transiciones_de_estado_permitidas()
    {
        $orden = OrdenCompra::factory()->create([
            'estado' => 'cancelada' // Estado final
        ]);

        $response = $this->post(route('ordenescompra.cambiar-estado', $orden->id), [
            'estado' => 'pendiente'
        ]);

        $response->assertJson([
            'success' => false
        ]);

        $orden->refresh();
        $this->assertEquals('cancelada', $orden->estado); // No cambió
    }

    /** @test */
    public function puede_duplicar_orden_de_compra()
    {
        $ordenOriginal = OrdenCompra::factory()->create([
            'proveedor_id' => $this->proveedor->id,
            'prioridad' => 'alta',
            'observaciones' => 'Orden original'
        ]);

        $ordenOriginal->productos()->attach($this->producto1->id, [
            'cantidad' => 3,
            'precio' => 100.00,
            'descuento' => 5,
            'unidad_medida' => 'pieza'
        ]);

        $response = $this->post(route('ordenescompra.duplicate', $ordenOriginal->id));

        $response->assertRedirect();
        $this->assertStringStartsWith('/ordenescompra/', $response->getTargetUrl());

        // Verificar que se creó la orden duplicada
        $this->assertDatabaseCount('orden_compras', 2);

        $ordenDuplicada = OrdenCompra::where('id', '!=', $ordenOriginal->id)->first();
        $this->assertEquals('borrador', $ordenDuplicada->estado);
        $this->assertStringStartsWith('DUP-', $ordenDuplicada->numero_orden);
        $this->assertCount(1, $ordenDuplicada->productos);
    }

    /** @test */
    public function puede_marcar_orden_como_urgente()
    {
        $orden = OrdenCompra::factory()->create([
            'prioridad' => 'media'
        ]);

        $response = $this->post(route('ordenescompra.urgente', $orden->id));

        $response->assertJson([
            'success' => true,
            'prioridad' => 'urgente'
        ]);

        $orden->refresh();
        $this->assertEquals('urgente', $orden->prioridad);
        $this->assertTrue(str_contains($orden->observaciones, 'ORDEN MARCADA COMO URGENTE'));
    }

    /** @test */
    public function puede_listar_ordenes_con_filtros()
    {
        // Crear órdenes de prueba
        OrdenCompra::factory()->create(['estado' => 'pendiente']);
        OrdenCompra::factory()->create(['estado' => 'enviado_a_proveedor']);
        OrdenCompra::factory()->create(['estado' => 'convertida']);

        $response = $this->get(route('ordenescompra.index', ['estado' => 'pendiente']));

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->has('ordenesCompra.data', 1);
        });
    }

    /** @test */
    public function puede_generar_numero_de_orden_automatico()
    {
        $numero = OrdenCompra::getProximoNumero();

        $this->assertIsString($numero);
        $this->assertStringStartsWith('OC-', $numero);
    }

    /** @test */
    public function valida_cantidad_positiva_en_items()
    {
        $ordenData = [
            'fecha_orden' => now()->format('Y-m-d'),
            'prioridad' => 'media',
            'proveedor_id' => $this->proveedor->id,
            'terminos_pago' => '30_dias',
            'metodo_pago' => 'transferencia',
            'subtotal' => 100.00,
            'descuento_items' => 0.00,
            'descuento_general' => 0.00,
            'iva' => 16.00, // Nota: Calculado dinámicamente en producción
            'total' => 116.00,
            'items' => [
                [
                    'id' => $this->producto1->id,
                    'tipo' => 'producto',
                    'cantidad' => 0, // Cantidad inválida
                    'precio' => 100.00,
                    'descuento' => 0
                ]
            ]
        ];

        $response = $this->post(route('ordenescompra.store'), $ordenData);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function valida_descuento_entre_0_y_100()
    {
        $ordenData = [
            'fecha_orden' => now()->format('Y-m-d'),
            'prioridad' => 'media',
            'proveedor_id' => $this->proveedor->id,
            'terminos_pago' => '30_dias',
            'metodo_pago' => 'transferencia',
            'subtotal' => 100.00,
            'descuento_items' => 0.00,
            'descuento_general' => 0.00,
            'iva' => 16.00, // Nota: Calculado dinámicamente en producción
            'total' => 116.00,
            'items' => [
                [
                    'id' => $this->producto1->id,
                    'tipo' => 'producto',
                    'cantidad' => 1,
                    'precio' => 100.00,
                    'descuento' => 150 // Descuento inválido
                ]
            ]
        ];

        $response = $this->post(route('ordenescompra.store'), $ordenData);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }
}
