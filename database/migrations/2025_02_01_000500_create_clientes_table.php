<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Nueva migración limpia y consolidada para tabla clientes
     */
    public function up(): void
    {
        // Si la tabla ya existe (producción con datos), no intentar recrearla
        if (Schema::hasTable('clientes')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        Schema::create('clientes', function (Blueprint $table) use ($driver) {
            $table->id();

            // Información básica
            $table->string('nombre_razon_social', 255);
            $table->enum('tipo_persona', ['fisica', 'moral'])->nullable();

            // Identificación
            $table->string('tipo_identificacion', 20)->nullable();
            $table->string('identificacion', 50)->nullable();
            $table->string('curp', 18)->nullable();

            // RFC - único y nullable para clientes sin factura
            $table->string('rfc', 13)->nullable()->unique()->comment('12 moral, 13 física');

            // Datos fiscales SAT
            $table->char('regimen_fiscal', 3)->nullable();
            $table->char('uso_cfdi', 3)->nullable();

            // Información de contacto
            $table->string('email', 255)->unique();
            $table->string('telefono', 20)->nullable();

            // Dirección completa
            $table->string('calle', 150);
            $table->string('numero_exterior', 30);
            $table->string('numero_interior', 30)->nullable();
            $table->string('colonia', 150);
            $table->char('codigo_postal', 5);

            // Ubicación geográfica
            $table->string('municipio', 120);
            $table->string('estado', 100)->comment('Nombre completo del estado');
            $table->char('pais', 2)->default('MX');

            // Campos adicionales para CFDI 4.0
            $table->char('domicilio_fiscal_cp', 5)->nullable()
                  ->comment('Código postal del domicilio fiscal del cliente (CFDI 4.0)');
            $table->char('residencia_fiscal', 3)->nullable()
                  ->comment('Clave de país de residencia fiscal para extranjeros (c_Pais)');
            $table->string('num_reg_id_trib', 40)->nullable()
                  ->comment('Número de registro fiscal extranjero');

            // Estado y notas
            $table->boolean('activo')->default(true);
            $table->text('notas')->nullable();

            // Timestamps y soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Índices de rendimiento
            $table->index('nombre_razon_social');
            $table->index('email');
            $table->index('rfc');
            $table->index('activo');
            $table->index('created_at');

            // Índices para campos fiscales
            $table->index('regimen_fiscal');
            $table->index('uso_cfdi');
            $table->index(['domicilio_fiscal_cp']);
            $table->index(['residencia_fiscal']);

            // Índice compuesto para búsquedas frecuentes
            $table->index(['activo', 'created_at']);
        });

        // Índices FULLTEXT solo para MySQL
        if ($driver === 'mysql') {
            Schema::table('clientes', function (Blueprint $table) {
                $table->fullText(['nombre_razon_social', 'email', 'rfc']);
            });
        }

        // Foreign Keys a catálogos SAT
        // Nota: se omiten en esta migración temprana para evitar dependencias de orden.
        // Se agregan en migraciones posteriores específicas de catálogos.

        // Constraints CHECK para MySQL
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE clientes ADD CONSTRAINT chk_pais CHECK (pais = 'MX')");
            DB::statement("ALTER TABLE clientes ADD CONSTRAINT chk_cp CHECK (codigo_postal REGEXP '^[0-9]{5}$')");
            DB::statement("ALTER TABLE clientes ADD CONSTRAINT chk_rfc CHECK (rfc IS NULL OR rfc REGEXP '^[A-ZÑ'&]{3,4}[0-9]{6}[A-Z0-9]{3}$')");
            DB::statement("ALTER TABLE clientes ADD CONSTRAINT chk_curp CHECK (curp IS NULL OR curp REGEXP '^[A-Z][AEIOU][A-Z]{2}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Quitar foreign keys antes de eliminar tabla
        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->dropForeign(['regimen_fiscal']);
                $table->dropForeign(['uso_cfdi']);
            });
        }

        Schema::dropIfExists('clientes');
    }
};
