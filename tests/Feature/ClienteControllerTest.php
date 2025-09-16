<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cliente;
use App\Models\SatRegimenFiscal;
use App\Models\SatUsoCfdi;
use App\Models\SatEstado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia;
use Mockery;

class ClienteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function index_returns_paginated_clients_with_stats(): void
    {
        Cliente::factory(16)->create(['activo' => true]);
        Cliente::factory(3)->create(['activo' => false]);

        $response = $this->get(route('clientes.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Clientes/Index')
                ->has('clientes.data', 10) // página 1, por defecto 10 por página
                ->where('clientes.total', 19)
                ->where('stats.total', 19)
                ->where('stats.activos', 16)
                ->where('stats.inactivos', 3)
        );
    }

    /** @test */
    public function index_applies_filters(): void
    {
        // 5 activos que deben aparecer
        Cliente::factory(5)->create([
            'nombre_razon_social' => 'Test Search',
            'activo' => true,
        ]);

        // ruido que NO debe aparecer
        Cliente::factory(3)->create([
            'nombre_razon_social' => 'Otro',
            'activo' => true,
        ]);
        Cliente::factory(2)->create([
            'nombre_razon_social' => 'Test Search',
            'activo' => false,
        ]);

        $response = $this->get(route('clientes.index', [
            'search' => 'Test',
            'activo' => '1',
        ]));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Clientes/Index')
                ->has('clientes.data', 5)
                ->where('clientes.data', function (array $data) {
                    foreach ($data as $client) {
                        if (!($client['activo'] === true && $client['nombre_razon_social'] === 'Test Search')) {
                            return false;
                        }
                    }
                    return true;
                })
        );
    }

    /** @test */
    public function create_renders_form_with_catalogs(): void
    {
        SatRegimenFiscal::factory()->create();
        SatUsoCfdi::factory()->create();
        SatEstado::factory()->create();

        $response = $this->get(route('clientes.create'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Clientes/Create')
                ->has('catalogs.tiposPersona')
                ->has('catalogs.estados')
                ->has('catalogs.regimenesFiscales')
                ->has('catalogs.usosCFDI')
                ->where('cliente.tipo_persona', 'fisica')
                ->where('cliente.pais', 'MX')
        );
    }

    /** @test */
    public function store_creates_valid_client(): void
    {
        $regimen = SatRegimenFiscal::factory()->create(['persona_fisica' => true, 'persona_moral' => false]);
        $uso     = SatUsoCfdi::factory()->create();
        $estado  = SatEstado::factory()->create();

        $data = [
            'nombre_razon_social' => 'Test Cliente',
            'email'               => 'test@example.com',
            'tipo_persona'        => 'fisica',
            'rfc'                 => 'XAXX010101000', // 13 chars PF
            'regimen_fiscal'      => $regimen->clave,
            'uso_cfdi'            => $uso->clave,
            'calle'               => 'Test Calle',
            'numero_exterior'     => '123',
            'colonia'             => 'Test Colonia',
            'codigo_postal'       => '12345',
            'municipio'           => 'Test Municipio',
            'estado'              => $estado->clave,
            'pais'                => 'MX',
            'telefono'            => '+52 123 456 7890',
            'activo'              => true,
        ];

        // Mock Facturapi (patrón estable: propiedad customers)
        $facturapi = Mockery::mock('overload:Facturapi\Facturapi');
        $customers = Mockery::mock();
        $customers->shouldReceive('create')->once()->andReturn((object)['id' => 'facturapi-123']);
        $facturapi->customers = $customers;

        $response = $this->post(route('clientes.store'), $data);

        $response->assertRedirect(route('clientes.index'));
        $response->assertSessionHas('success', 'Cliente creado correctamente');

        $this->assertDatabaseHas('clientes', [
            'nombre_razon_social'     => 'Test Cliente',
            'email'                   => 'test@example.com',
            'rfc'                     => 'XAXX010101000',
            'facturapi_customer_id'   => 'facturapi-123',
            'activo'                  => true,
        ]);
    }

    /** @test */
    public function store_fails_validation(): void
    {
        $data = [
            'nombre_razon_social' => '',
            'email'               => 'invalid-email',
            'tipo_persona'        => 'invalid',
            'rfc'                 => 'invalid',
            'regimen_fiscal'      => 'nonexistent',
            'uso_cfdi'            => 'nonexistent',
            'calle'               => '',
            'numero_exterior'     => '',
            'colonia'             => '',
            'codigo_postal'       => '123',
            'municipio'           => '',
            'estado'              => 'XXX',
            'pais'                => 'US',
        ];

        $response = $this->post(route('clientes.store'), $data);

        // En Inertia, por defecto retorna 302 con errores en sesión. Si tu controlador
        // usa Inertia::render con 422, deja 422. Cambia si tu flujo es 302.
        $status = $response->getStatusCode();
        $this->assertTrue(in_array($status, [302, 422]), "Unexpected status $status");

        // Asegura que NO se insertó un registro inválido
        $this->assertDatabaseCount('clientes', 0);
    }

    /** @test */
    public function show_displays_client_with_relations(): void
    {
        $regimen = SatRegimenFiscal::factory()->create(['persona_fisica' => true]);
        $uso     = SatUsoCfdi::factory()->create();
        $estado  = SatEstado::factory()->create();

        $cliente = Cliente::factory()->create([
            'regimen_fiscal' => $regimen->clave,
            'uso_cfdi'       => $uso->clave,
            'estado'         => $estado->clave,
            'pais'           => 'MX',
        ]);

        $response = $this->get(route('clientes.show', $cliente));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(AssertableInertia $page) =>
            // Ajusta el componente si tu ruta usa otro (p. ej. 'Clientes/Edit')
            $page->component('Clientes/Show')
                ->has(
                    'cliente',
                    fn(AssertableInertia $c) =>
                    $c->where('id', $cliente->id)
                        ->where('nombre_razon_social', $cliente->nombre_razon_social)
                        ->where('regimen_fiscal', $regimen->clave)
                        ->where('uso_cfdi', $uso->clave)
                        ->where('estado', $estado->clave)
                        ->where('pais', 'MX')
                        ->etc()
                )
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
