<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Tecnico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaApiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_can_list_citas()
    {
        Tecnico::factory()->create();
        Cliente::factory()->create();
        Cita::factory()->count(3)->create();

        $response = $this->getJson('/api/citas');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'citas' => [
                         '*' => [
                             'id',
                             'tecnico_id',
                             'cliente_id',
                             'tipo_servicio',
                             'fecha_hora',
                             'estado'
                         ]
                     ],
                     'meta' => [
                         'total',
                         'per_page',
                         'current_page',
                         'last_page'
                     ]
                 ]);
    }

    public function test_api_can_create_cita()
    {
        $tecnico = Tecnico::factory()->create();
        $cliente = Cliente::factory()->create();

        $citaData = [
            'tecnico_id' => $tecnico->id,
            'cliente_id' => $cliente->id,
            'tipo_servicio' => 'Reparación',
            'fecha_hora' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'tipo_equipo' => 'Computadora',
            'marca_equipo' => 'Dell',
            'modelo_equipo' => 'Inspiron 15',
            'estado' => Cita::ESTADO_PENDIENTE,
        ];

        $response = $this->postJson('/api/citas', $citaData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'cita' => [
                         'id',
                         'tecnico_id',
                         'cliente_id',
                         'tipo_servicio',
                         'estado'
                     ]
                 ]);

        $this->assertDatabaseHas('citas', $citaData);
    }

    public function test_api_validates_cita_creation()
    {
        $response = $this->postJson('/api/citas', []);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'message',
                     'errors'
                 ]);
    }

    public function test_api_can_show_cita()
    {
        $tecnico = Tecnico::factory()->create();
        $cliente = Cliente::factory()->create();
        $cita = Cita::factory()->create([
            'tecnico_id' => $tecnico->id,
            'cliente_id' => $cliente->id
        ]);

        $response = $this->getJson("/api/citas/{$cita->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'tecnico_id',
                     'cliente_id',
                     'tipo_servicio',
                     'fecha_hora',
                     'estado',
                     'cliente' => [
                         'id',
                         'nombre_razon_social'
                     ],
                     'tecnico' => [
                         'id',
                         'nombre'
                     ]
                 ]);
    }

    public function test_api_returns_404_for_non_existent_cita()
    {
        $response = $this->getJson('/api/citas/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'message' => 'Cita no encontrada'
                 ]);
    }

    public function test_api_can_update_cita()
    {
        $tecnico = Tecnico::factory()->create();
        $cliente = Cliente::factory()->create();
        $cita = Cita::factory()->create([
            'tecnico_id' => $tecnico->id,
            'cliente_id' => $cliente->id
        ]);

        $updateData = [
            'tipo_servicio' => 'Mantenimiento',
            'estado' => Cita::ESTADO_EN_PROCESO
        ];

        $response = $this->putJson("/api/citas/{$cita->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'cita'
                 ]);

        $this->assertDatabaseHas('citas', array_merge(['id' => $cita->id], $updateData));
    }

    public function test_api_can_delete_cita()
    {
        $tecnico = Tecnico::factory()->create();
        $cliente = Cliente::factory()->create();
        $cita = Cita::factory()->create([
            'tecnico_id' => $tecnico->id,
            'cliente_id' => $cliente->id
        ]);

        $response = $this->deleteJson("/api/citas/{$cita->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Cita eliminada exitosamente.'
                 ]);

        $this->assertSoftDeleted('citas', ['id' => $cita->id]);
    }

    public function test_api_validates_fecha_hora_constraints()
    {
        $tecnico = Tecnico::factory()->create();
        $cliente = Cliente::factory()->create();

        // Fecha en el pasado
        $citaData = [
            'tecnico_id' => $tecnico->id,
            'cliente_id' => $cliente->id,
            'tipo_servicio' => 'Reparación',
            'fecha_hora' => now()->subDays(1)->format('Y-m-d H:i:s'),
            'tipo_equipo' => 'Computadora',
            'marca_equipo' => 'Dell',
            'modelo_equipo' => 'Inspiron 15',
            'estado' => Cita::ESTADO_PENDIENTE,
        ];

        $response = $this->postJson('/api/citas', $citaData);

        $response->assertStatus(422);
    }

    public function test_api_validates_tecnico_availability()
    {
        $tecnico = Tecnico::factory()->create();
        $cliente = Cliente::factory()->create();

        // Crear primera cita
        Cita::factory()->create([
            'tecnico_id' => $tecnico->id,
            'cliente_id' => $cliente->id,
            'fecha_hora' => now()->addDays(1)->setHour(10),
            'estado' => Cita::ESTADO_PENDIENTE
        ]);

        // Intentar crear segunda cita a la misma hora
        $citaData = [
            'tecnico_id' => $tecnico->id,
            'cliente_id' => $cliente->id,
            'tipo_servicio' => 'Reparación',
            'fecha_hora' => now()->addDays(1)->setHour(10)->format('Y-m-d H:i:s'),
            'tipo_equipo' => 'Computadora',
            'marca_equipo' => 'Dell',
            'modelo_equipo' => 'Inspiron 15',
            'estado' => Cita::ESTADO_PENDIENTE,
        ];

        $response = $this->postJson('/api/citas', $citaData);

        $response->assertStatus(422);
    }
}
