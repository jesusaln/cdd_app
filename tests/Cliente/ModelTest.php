<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Cliente;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions; // Cambiado aquí


class ModelTest extends TestCase
{
    use DatabaseTransactions; // Ahora usa transacciones
    //use RefreshDatabase;

    public function test_tiene_los_campos_fillable_correctos()
    {
        $cliente = new Cliente();
        $fillable = $cliente->getFillable();

        $expected = [
            'nombre_razon_social',
            'rfc',
            'regimen_fiscal',
            'uso_cfdi',
            'email',
            'telefono',
            'calle',
            'numero_exterior',
            'numero_interior',
            'colonia',
            'codigo_postal',
            'municipio',
            'estado',
            'pais',
            'tipo_persona',
            'activo',
            'notas',
            'acepta_marketing',
        ];

        sort($expected);
        sort($fillable);

        $this->assertEquals($expected, $fillable);
    }

    public function test_tiene_los_casts_correctos()
    {
        $cliente = new Cliente();
        $casts = $cliente->getCasts();

        $this->assertArrayHasKey('activo', $casts);
        $this->assertArrayHasKey('acepta_marketing', $casts);

        $this->assertEquals('boolean', $casts['activo']);
        $this->assertEquals('boolean', $casts['acepta_marketing']);
    }

    public function test_puede_tener_relacion_con_cotizaciones()
    {
        $cliente = Cliente::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $cliente->cotizaciones);
    }

    public function test_atributo_activo_se_castea_como_booleano()
    {
        $cliente = Cliente::factory()->create([
            'activo' => true,
        ]);

        $this->assertTrue($cliente->activo);
        $this->assertIsBool($cliente->activo);
    }

    public function test_atributo_acepta_marketing_se_castea_como_booleano()
    {
        $cliente = Cliente::factory()->create([
            'acepta_marketing' => true,
        ]);

        $this->assertTrue($cliente->acepta_marketing);
        $this->assertIsBool($cliente->acepta_marketing);
    }
}
