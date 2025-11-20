<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Nueva migración consolidada para tabla users
     */
    public function up(): void
    {
        // Si la tabla ya existe (entorno instalado), no recrearla
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();

            // Autenticación de dos factores (2FA)
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();

            // Estado del usuario
            $table->boolean('activo')->default(true);

            // Información personal del empleado
            $table->string('telefono')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('curp', 18)->nullable();
            $table->string('rfc', 13)->nullable();
            $table->text('direccion')->nullable();
            $table->string('nss', 11)->nullable(); // Número de Seguro Social

            // Información laboral
            $table->string('puesto')->nullable();
            $table->string('departamento')->nullable();
            $table->date('fecha_contratacion')->nullable();
            $table->decimal('salario', 10, 2)->nullable();
            $table->string('tipo_contrato')->nullable(); // tiempo completo, medio tiempo, temporal
            $table->string('numero_empleado')->nullable()->unique();

            // Información de contacto de emergencia
            $table->string('contacto_emergencia_nombre')->nullable();
            $table->string('contacto_emergencia_telefono')->nullable();
            $table->string('contacto_emergencia_parentesco')->nullable();

            // Información bancaria
            $table->string('banco')->nullable();
            $table->string('numero_cuenta')->nullable();
            $table->string('clabe_interbancaria', 18)->nullable();

            // Información adicional
            $table->text('observaciones')->nullable();
            $table->boolean('es_empleado')->default(false);

            $table->timestamps();

            // Índices de rendimiento
            $table->index('email');
            $table->index('activo');
            $table->index('numero_empleado');
            $table->index(['es_empleado', 'activo']);
        });

        // Crear tablas relacionadas estándar de Laravel
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
