<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName(); // 'mysql', 'sqlite', 'pgsql', etc.

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            // ⚠️ Enums: en SQLite se tratan como TEXT; la integridad la refuerzas en FormRequest.
            $table->string('nombre_razon_social', 255);
            $table->enum('tipo_persona', ['fisica', 'moral']); // Asegúrate que tus seeders usan minúsculas

            // Identificación opcional
            $table->string('tipo_identificacion', 20)->nullable();
            $table->string('identificacion', 50)->nullable();
            $table->string('curp', 18)->nullable();

            // RFC MX (12 moral, 13 física)
            $table->string('rfc', 13)->unique()->comment('12 moral, 13 física');

            // Fiscales (claves SAT) - deben coincidir EXACTO con las tablas SAT
            $table->char('regimen_fiscal', 3);
            $table->char('uso_cfdi', 3);

            // Contacto
            $table->string('email', 255);
            $table->string('telefono', 20)->nullable();

            // Dirección MX
            $table->string('calle', 150);
            $table->string('numero_exterior', 30);
            $table->string('numero_interior', 30)->nullable();
            $table->string('colonia', 150);
            $table->char('codigo_postal', 5);
            $table->string('municipio', 120);
            $table->char('estado', 3)->comment('SAT c_Estado');
            $table->char('pais', 2)->default('MX');

            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();

            // Índices
            $table->index('nombre_razon_social');
            $table->index('email');
            $table->index('activo');
            $table->index('created_at');

            // Índices para FKs (recomendado/obligatorio en MySQL)
            $table->index('regimen_fiscal');
            $table->index('uso_cfdi');

            // En SQLite no uses fullText (no sirve aquí). Lo agregamos condicional abajo para MySQL.
        });

        // FULLTEXT solo en MySQL
        if ($driver === 'mysql') {
            Schema::table('clientes', function (Blueprint $table) {
                $table->fullText(['nombre_razon_social', 'email', 'rfc']);
            });
        }

        // FKs a catálogos SAT (asegúrate que esas tablas existen y 'clave' es CHAR(3) con índice/PK)
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreign('regimen_fiscal')
                ->references('clave')->on('sat_regimenes_fiscales')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('uso_cfdi')
                ->references('clave')->on('sat_usos_cfdi')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        // CHECKS solo si el motor los soporta y tu versión de Laravel no truena
        // En tu caso Blueprint::check() no existe, así que usamos SQL crudo para MySQL
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE clientes ADD CONSTRAINT chk_pais CHECK (pais = 'MX')");
            DB::statement("ALTER TABLE clientes ADD CONSTRAINT chk_cp CHECK (codigo_postal REGEXP '^[0-9]{5}$')");
            DB::statement("ALTER TABLE clientes ADD CONSTRAINT chk_rfc CHECK (rfc REGEXP '^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$')");
            DB::statement("ALTER TABLE clientes ADD CONSTRAINT chk_curp CHECK (curp IS NULL OR curp REGEXP '^[A-Z][AEIOU][A-Z]{2}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$')");
        }
        // En SQLite omite los CHECK: valida en FormRequest (abajo te dejo reglas).
    }

    public function down(): void
    {
        // Quita FKs antes de drop si estás en MySQL (evitas warnings)
        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $table) {
                // Silencioso si no existen (ambiente dev)
                try {
                    $table->dropForeign(['regimen_fiscal']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropForeign(['uso_cfdi']);
                } catch (\Throwable $e) {
                }
            });
        }

        Schema::dropIfExists('clientes');
    }
};
