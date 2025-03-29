<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            $table->string('nombre_razon_social'); // Nombre o razón social
            $table->string('tipo_persona'); // Tipo de persona (Física o Moral)
            $table->string('contacto')->nullable(); // Tipo de identificación (INE, Pasaporte, etc.)
            $table->string('tel_contacto')->nullable(); // Número de identificación
            $table->string('curp')->nullable(); // CURP (opcional)
            $table->string('rfc', 13); // RFC (13 caracteres máximo)
            $table->string('regimen_fiscal'); // Clave del régimen fiscal
            $table->string('uso_cfdi'); // Clave de uso del CFDI
            $table->string('email'); // Correo electrónico (único)
            $table->string('telefono')->nullable(); // Teléfono (opcional)
            $table->string('calle'); // Calle
            $table->string('numero_exterior'); // Número exterior
            $table->string('numero_interior')->nullable(); // Número interior (opcional)
            $table->string('colonia'); // Colonia
            $table->string('codigo_postal', 5); // Código postal (5 dígitos)
            $table->string('municipio'); // Municipio o alcaldía
            $table->string('estado'); // Estado
            $table->string('pais')->default('México'); // País (por defecto, México)
            $table->timestamps(); // Columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
