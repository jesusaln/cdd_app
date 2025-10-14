<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Cita;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Tecnico;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CrudCitaTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Cliente $cliente;
    protected Tecnico $tecnico;

    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutar seeders necesarios
        $this->seed([
            \Database\Seeders\DatabaseSeeder::class,
        ]);

        $this->user = User::factory()->create();
        $this->cliente = Cliente::factory()->create();
        $this->tecnico = Tecnico::factory()->create();
    }

    /**
     * Test: Listar citas
     */
    public function test_usuario_puede_ver_lista_de_citas()
    {
        Cita::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get('/citas');

        $response->assertStatus(200);

        Cita::all()->each(function ($cita) use ($response) {
            $response->assertSee($cita->tipo_servicio);
            $response->assertSee($cita->estado);
        });
    }

    /**
     * Test: Crear una cita válida
     */
    public function test_usuario_puede_crear_una_cita()
    {
        $data = [
            'tecnico_id' => $this->tecnico->id,
            'cliente_id' => $this->cliente->id,
            'tipo_servicio' => 'Mantenimiento preventivo',
            'fecha_hora' => now()->addDays(1)->setHour(10)->toDateTimeString(),
            'prioridad' => 'media',
            'descripcion' => 'Descripción de la cita de mantenimiento',
            'tipo_equipo' => 'Laptop',
            'marca_equipo' => 'Dell',
            'modelo_equipo' => 'XPS 13',
            'problema_reportado' => 'Requiere mantenimiento preventivo',
            'estado' => 'pendiente',
            'evidencias' => 'Sin evidencias iniciales',
        ];

        $response = $this->actingAs($this->user)->post('/citas', $data);

        $response->assertRedirect('/citas');
        $this->assertDatabaseHas('citas', [
            'tipo_servicio' => 'Mantenimiento preventivo',
            'cliente_id' => $this->cliente->id,
            'tecnico_id' => $this->tecnico->id,
        ]);
    }

    /**
     * Test: Validación - tipo_servicio es requerido
     */
    public function test_no_puede_crear_cita_sin_tipo_servicio()
    {
        $data = [
            'tecnico_id' => $this->tecnico->id,
            'cliente_id' => $this->cliente->id,
            'tipo_servicio' => '',
            'fecha_hora' => now()->addDays(1)->setHour(10)->toDateTimeString(),
            'tipo_equipo' => 'Laptop',
            'marca_equipo' => 'Dell',
            'modelo_equipo' => 'XPS 13',
            'estado' => 'pendiente',
        ];

        $response = $this->actingAs($this->user)->post('/citas', $data);

        $response->assertSessionHasErrors('tipo_servicio');
        $this->assertDatabaseMissing('citas', ['cliente_id' => $this->cliente->id]);
    }

    /**
     * Test: Validación - fecha_hora debe ser futura
     */
    public function test_fecha_hora_debe_ser_futura()
    {
        $data = [
            'tecnico_id' => $this->tecnico->id,
            'cliente_id' => $this->cliente->id,
            'tipo_servicio' => 'Test',
            'fecha_hora' => now()->subDays(1)->toDateTimeString(), // Fecha pasada
            'tipo_equipo' => 'Test',
            'marca_equipo' => 'Test',
            'modelo_equipo' => 'Test',
            'estado' => 'pendiente',
        ];

        $response = $this->actingAs($this->user)->post('/citas', $data);

        $response->assertSessionHasErrors('fecha_hora');
    }

    /**
     * Test: Validación - prioridad debe ser válida
     */
    public function test_prioridad_debe_ser_valida()
    {
        $data = [
            'tecnico_id' => $this->tecnico->id,
            'cliente_id' => $this->cliente->id,
            'tipo_servicio' => 'Test',
            'fecha_hora' => now()->addDays(1)->setHour(10)->toDateTimeString(),
            'prioridad' => 'invalid', // Prioridad inválida
            'tipo_equipo' => 'Test',
            'marca_equipo' => 'Test',
            'modelo_equipo' => 'Test',
            'estado' => 'pendiente',
        ];

        $response = $this->actingAs($this->user)->post('/citas', $data);

        $response->assertSessionHasErrors('prioridad');
    }

    /**
     * Test: Validación - estado debe ser válido
     */
    public function test_estado_debe_ser_valido()
    {
        $data = [
            'tecnico_id' => $this->tecnico->id,
            'cliente_id' => $this->cliente->id,
            'tipo_servicio' => 'Test',
            'fecha_hora' => now()->addDays(1)->setHour(10)->toDateTimeString(),
            'tipo_equipo' => 'Test',
            'marca_equipo' => 'Test',
            'modelo_equipo' => 'Test',
            'estado' => 'invalid', // Estado inválido
        ];

        $response = $this->actingAs($this->user)->post('/citas', $data);

        $response->assertSessionHasErrors('estado');
    }

    /**
     * Test: Ver detalle de una cita
     */
    public function test_usuario_puede_ver_detalle_de_cita()
    {
        $cita = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'tecnico_id' => $this->tecnico->id,
            'descripcion' => 'Descripción detallada de la cita',
        ]);

        $response = $this->actingAs($this->user)->get("/citas/{$cita->id}");

        $response->assertStatus(200);
        $response->assertSee($cita->tipo_servicio);
        $response->assertSee($cita->descripcion);
        $response->assertSee($cita->estado);
    }

    /**
     * Test: Editar una cita
     */
    public function test_usuario_puede_editar_cita()
    {
        $cita = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'tecnico_id' => $this->tecnico->id,
            'tipo_servicio' => 'Servicio original',
            'descripcion' => 'Descripción original',
        ]);

        $data = [
            'tecnico_id' => $this->tecnico->id,
            'cliente_id' => $this->cliente->id,
            'tipo_servicio' => 'Servicio actualizado',
            'fecha_hora' => now()->addDays(2)->setHour(14)->toDateTimeString(),
            'prioridad' => 'alta',
            'descripcion' => 'Descripción actualizada',
            'tipo_equipo' => 'Actualizado',
            'marca_equipo' => 'Actualizada',
            'modelo_equipo' => 'Actualizado',
            'problema_reportado' => 'Problema actualizado',
            'estado' => 'en_proceso',
            'evidencias' => 'Evidencias actualizadas',
        ];

        $response = $this->actingAs($this->user)->put("/citas/{$cita->id}", $data);

        $response->assertRedirect('/citas');
        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'tipo_servicio' => 'Servicio actualizado',
            'descripcion' => 'Descripción actualizada',
            'estado' => 'en_proceso',
        ]);
        $this->assertDatabaseMissing('citas', [
            'id' => $cita->id,
            'tipo_servicio' => 'Servicio original'
        ]);
    }

    /**
     * Test: Eliminar una cita
     */
    public function test_usuario_puede_eliminar_cita()
    {
        $cita = Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'tecnico_id' => $this->tecnico->id,
        ]);

        $response = $this->actingAs($this->user)->delete("/citas/{$cita->id}");

        $response->assertRedirect('/citas');
        $this->assertDatabaseMissing('citas', ['id' => $cita->id]);
    }

    /**
     * Test: No eliminar cita inexistente
     */
    public function test_no_puede_eliminar_cita_inexistente()
    {
        $response = $this->actingAs($this->user)->delete('/citas/999');

        $response->assertStatus(404);
    }

    /**
     * Test: Validación - no puede crear cita con fecha pasada si estado es pendiente
     */
    public function test_no_puede_crear_cita_pendiente_con_fecha_pasada()
    {
        $data = [
            'tecnico_id' => $this->tecnico->id,
            'cliente_id' => $this->cliente->id,
            'tipo_servicio' => 'Test',
            'fecha_hora' => now()->subDays(1)->toDateTimeString(),
            'tipo_equipo' => 'Test',
            'marca_equipo' => 'Test',
            'modelo_equipo' => 'Test',
            'estado' => 'pendiente',
        ];

        $response = $this->actingAs($this->user)->post('/citas', $data);

        $response->assertSessionHasErrors('fecha_hora');
    }

    /**
     * Test: Validación - horario laboral (8 AM - 6 PM)
     */
    public function test_horario_debe_ser_laboral()
    {
        // Hora antes de 8 AM
        $data = [
            'tecnico_id' => $this->tecnico->id,
            'cliente_id' => $this->cliente->id,
            'tipo_servicio' => 'Test',
            'fecha_hora' => now()->addDays(1)->setHour(7)->toDateTimeString(),
            'tipo_equipo' => 'Test',
            'marca_equipo' => 'Test',
            'modelo_equipo' => 'Test',
            'estado' => 'pendiente',
        ];

        $response = $this->actingAs($this->user)->post('/citas', $data);
        $response->assertSessionHasErrors('fecha_hora');

        // Hora después de 6 PM
        $data['fecha_hora'] = now()->addDays(1)->setHour(19)->toDateTimeString();
        $response = $this->actingAs($this->user)->post('/citas', $data);
        $response->assertSessionHasErrors('fecha_hora');
    }

    /**
     * Test: Validación - no domingos
     */
    public function test_no_puede_programar_citas_los_domingos()
    {
        $proximoDomingo = now()->next(0)->setHour(10); // 0 = domingo

        $data = [
            'tecnico_id' => $this->tecnico->id,
            'cliente_id' => $this->cliente->id,
            'tipo_servicio' => 'Test',
            'fecha_hora' => $proximoDomingo->toDateTimeString(),
            'tipo_equipo' => 'Test',
            'marca_equipo' => 'Test',
            'modelo_equipo' => 'Test',
            'estado' => 'pendiente',
        ];

        $response = $this->actingAs($this->user)->post('/citas', $data);

        $response->assertSessionHasErrors('fecha_hora');
    }

    /**
     * Test: Validación - técnico debe existir
     */
    public function test_tecnico_debe_existir()
    {
        $data = [
            'tecnico_id' => 999,
            'cliente_id' => $this->cliente->id,
            'tipo_servicio' => 'Test',
            'fecha_hora' => now()->addDays(1)->setHour(10)->toDateTimeString(),
            'tipo_equipo' => 'Test',
            'marca_equipo' => 'Test',
            'modelo_equipo' => 'Test',
            'estado' => 'pendiente',
        ];

        $response = $this->actingAs($this->user)->post('/citas', $data);

        $response->assertSessionHasErrors('tecnico_id');
    }

    /**
     * Test: Validación - cliente debe existir
     */
    public function test_cliente_debe_existir()
    {
        $data = [
            'tecnico_id' => $this->tecnico->id,
            'cliente_id' => 999,
            'tipo_servicio' => 'Test',
            'fecha_hora' => now()->addDays(1)->setHour(10)->toDateTimeString(),
            'tipo_equipo' => 'Test',
            'marca_equipo' => 'Test',
            'modelo_equipo' => 'Test',
            'estado' => 'pendiente',
        ];

        $response = $this->actingAs($this->user)->post('/citas', $data);

        $response->assertSessionHasErrors('cliente_id');
    }

    /**
     * Test: Validación - campos de equipo son requeridos
     */
    public function test_campos_equipo_son_requeridos()
    {
        $data = [
            'tecnico_id' => $this->tecnico->id,
            'cliente_id' => $this->cliente->id,
            'tipo_servicio' => 'Test',
            'fecha_hora' => now()->addDays(1)->setHour(10)->toDateTimeString(),
            'tipo_equipo' => '',
            'marca_equipo' => '',
            'modelo_equipo' => '',
            'estado' => 'pendiente',
        ];

        $response = $this->actingAs($this->user)->post('/citas', $data);

        $response->assertSessionHasErrors(['tipo_equipo', 'marca_equipo', 'modelo_equipo']);
    }

    /**
     * Test: Validación - longitud máxima de campos
     */
    public function test_longitud_maxima_campos()
    {
        $data = [
            'tecnico_id' => $this->tecnico->id,
            'cliente_id' => $this->cliente->id,
            'tipo_servicio' => str_repeat('A', 256), // Más de 255 caracteres
            'fecha_hora' => now()->addDays(1)->setHour(10)->toDateTimeString(),
            'descripcion' => str_repeat('B', 1001), // Más de 1000 caracteres
            'tipo_equipo' => str_repeat('C', 256), // Más de 255 caracteres
            'marca_equipo' => str_repeat('D', 256), // Más de 255 caracteres
            'modelo_equipo' => str_repeat('E', 256), // Más de 255 caracteres
            'problema_reportado' => str_repeat('F', 1001), // Más de 1000 caracteres
            'estado' => 'pendiente',
        ];

        $response = $this->actingAs($this->user)->post('/citas', $data);

        $response->assertSessionHasErrors([
            'tipo_servicio',
            'descripcion',
            'tipo_equipo',
            'marca_equipo',
            'modelo_equipo',
            'problema_reportado'
        ]);
    }

    /**
     * Test: Búsqueda con caracteres especiales
     */
    public function test_busqueda_con_caracteres_especiales()
    {
        Cita::factory()->create([
            'tipo_servicio' => 'Mantenimiento con Ñ y acentos áéíóú',
            'cliente_id' => $this->cliente->id,
            'tecnico_id' => $this->tecnico->id,
            'tipo_equipo' => 'Test',
            'marca_equipo' => 'Test',
            'modelo_equipo' => 'Test',
            'estado' => 'pendiente',
            'fecha_hora' => now()->addDays(1)->setHour(10)->toDateTimeString(),
        ]);

        $response = $this->actingAs($this->user)->get('/citas?search=Ñ');

        $response->assertStatus(200);
        // Debería encontrar la cita con caracteres especiales
    }

    /**
     * Test: Filtros por estado funcionan correctamente
     */
    public function test_filtros_por_estado()
    {
        Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'tecnico_id' => $this->tecnico->id,
            'tipo_servicio' => 'Pendiente',
            'estado' => 'pendiente',
            'tipo_equipo' => 'Test',
            'marca_equipo' => 'Test',
            'modelo_equipo' => 'Test',
            'fecha_hora' => now()->addDays(1)->setHour(10)->toDateTimeString(),
        ]);

        Cita::factory()->create([
            'cliente_id' => $this->cliente->id,
            'tecnico_id' => $this->tecnico->id,
            'tipo_servicio' => 'Completada',
            'estado' => 'completado',
            'tipo_equipo' => 'Test',
            'marca_equipo' => 'Test',
            'modelo_equipo' => 'Test',
            'fecha_hora' => now()->addDays(1)->setHour(10)->toDateTimeString(),
        ]);

        $response = $this->actingAs($this->user)->get('/citas?estado=pendiente');

        $response->assertStatus(200);
        $response->assertSee('Pendiente');
        $response->assertDontSee('Completada');
    }
}
