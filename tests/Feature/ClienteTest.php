<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Creamos un usuario una vez para usar en todos los tests
        $this->user = User::factory()->create();
    }

    /**
     * Test: Listar clientes
     */
    public function test_usuario_puede_ver_lista_de_clientes()
    {
        Cliente::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get('/clientes');

        $response->assertStatus(200);

        Cliente::all()->each(function ($cliente) use ($response) {
            $response->assertSee($cliente->nombre_razon_social);
            $response->assertSee($cliente->rfc);
            $response->assertSee($cliente->email);
        });
    }

    /**
     * Test: Crear un cliente válido
     */
    public function test_usuario_puede_crear_un_cliente()
    {
        $data = [
            'nombre_razon_social' => 'Cliente de Prueba',
            'rfc' => 'RFC123456789',
            'regimen_fiscal' => '601',
            'uso_cfdi' => 'G01',
            'email' => 'cliente@prueba.com',
            'telefono' => '5551234567',
            'calle' => 'Calle Principal',
            'numero_exterior' => '100',
            'numero_interior' => 'B',
            'colonia' => 'Centro',
            'codigo_postal' => '01000',
            'municipio' => 'Ciudad de México',
            'estado' => 'Ciudad de México',
            'pais' => 'México',
            'tipo_persona' => 'moral',
            'activo' => true,
            'notas' => 'Cliente importante',

        ];

        $response = $this->actingAs($this->user)->post('/clientes', $data);

        $response->assertRedirect('/clientes');
        $this->assertDatabaseHas('clientes', $data);
    }

    /**
     * Test: Validación - nombre_razon_social es requerido
     */
    public function test_no_puede_crear_cliente_sin_nombre()
    {
        $data = [
            'nombre_razon_social' => '',
            'rfc' => 'RFC123456789',
            'email' => 'cliente@prueba.com',
        ];

        $response = $this->actingAs($this->user)->post('/clientes', $data);

        $response->assertSessionHasErrors('nombre_razon_social');
        $this->assertDatabaseMissing('clientes', ['rfc' => 'RFC123456789']);
    }

    /**
     * Test: Validación - RFC debe ser único (si aplica)
     */
    public function test_rfc_debe_ser_unico()
    {
        Cliente::factory()->create(['rfc' => 'RFCUNICO']);

        $data = [
            'nombre_razon_social' => 'Cliente Duplicado',
            'rfc' => 'RFCUNICO',
            'email' => 'otro@prueba.com',
        ];

        $response = $this->actingAs($this->user)->post('/clientes', $data);

        $response->assertSessionHasErrors('rfc');
    }

    /**
     * Test: Ver detalle de un cliente
     */
    public function test_usuario_puede_ver_detalle_de_cliente()
    {
        $cliente = Cliente::factory()->create([
            'notas' => 'Cliente VIP',

        ]);

        $response = $this->actingAs($this->user)->get("/clientes/{$cliente->id}");

        $response->assertStatus(200);
        $response->assertSee($cliente->nombre_razon_social);
        $response->assertSee($cliente->rfc);
        $response->assertSee($cliente->notas);
        $response->assertSee($cliente->email);
    }

    /**
     * Test: Editar un cliente
     */
    public function test_usuario_puede_editar_cliente()
    {
        $cliente = Cliente::factory()->create([
            'nombre_razon_social' => 'Cliente Original',
            'email' => 'original@prueba.com',
        ]);

        $data = [
            'nombre_razon_social' => 'Cliente Actualizado',
            'rfc' => 'ABC010203AB9', // RFC válido (10-13 caracteres, formato estándar)
            'regimen_fiscal' => '601', // Uno de los valores permitidos
            'uso_cfdi' => 'G01',       // Válido
            'email' => 'actualizado@prueba.com',
            'telefono' => '5559876543',
            'calle' => 'Nueva Calle',
            'numero_exterior' => '200',
            'colonia' => 'Nueva Colonia',
            'codigo_postal' => '06000',
            'municipio' => 'Guadalajara',
            'estado' => 'Jalisco',
            'pais' => 'México',
            'tipo_persona' => 'moral', // Debe ser 'fisica' o 'moral'
            'activo' => false,
            'notas' => 'Actualizado en test',

            
        ];

        $response = $this->actingAs($this->user)->put("/clientes/{$cliente->id}", $data);

        $response->assertRedirect('/clientes');
        $this->assertDatabaseHas('clientes', $data);
        $this->assertDatabaseMissing('clientes', ['nombre_razon_social' => 'Cliente Original']);
    }

    /**
     * Test: Eliminar un cliente
     */
    public function test_usuario_puede_eliminar_cliente()
    {
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($this->user)->delete("/clientes/{$cliente->id}");

        $response->assertRedirect('/clientes');
        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }

    /**
     * Test: No eliminar cliente inexistente
     */
    public function test_no_puede_eliminar_cliente_inexistente()
    {
        $response = $this->actingAs($this->user)->delete('/clientes/999');

        $response->assertStatus(404);
    }

    /**
     * Test: Validar que el campo email sea un email válido
     */
    public function test_email_debe_ser_valido()
    {
        $data = [
            'nombre_razon_social' => 'Cliente Inválido',
            'rfc' => 'RFC123',
            'email' => 'email-no-valido',
        ];

        $response = $this->actingAs($this->user)->post('/clientes', $data);

        $response->assertSessionHasErrors('email');
    }
}
