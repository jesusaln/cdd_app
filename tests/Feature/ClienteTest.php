<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_clientes()
    {
        $user = User::factory()->create();  // Crear usuario de prueba

        Cliente::factory()->count(3)->create();

        // Petición autenticada a la ruta clientes
        $response = $this->actingAs($user)->get('/clientes');

        $response->assertStatus(200);

        $clientes = Cliente::all();
        foreach ($clientes as $cliente) {
            $response->assertSee($cliente->nombre_razon_social);
        }
    }
}
