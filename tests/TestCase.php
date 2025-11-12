<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Http\Middleware\VerifyCsrfToken;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Deshabilitar CSRF en pruebas para simplificar POSTs a rutas web
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }
}
